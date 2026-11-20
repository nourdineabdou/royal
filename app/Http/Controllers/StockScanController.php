<?php

namespace App\Http\Controllers;

use App\Models\Packaging;
use App\Models\Product;
use App\Models\ProductPackaging;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Support\Barcode;
use App\Support\ScanCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Sorties de stock par scan de codes-barres (les entrées passent par les réceptions BL,
 * voir ReceptionController ; les corrections par l'ajustement admin).
 *
 * Un produit peut avoir plusieurs codes : un par niveau de conditionnement
 * (bouteille, pack, caisse…). Chaque code porte un coefficient vers l'unité de base
 * du produit ; le stock est toujours tenu dans cette unité de base.
 */
class StockScanController extends Controller
{
    public function index(Request $request)
    {
        $this->perm('stock.products.adjust');

        $stocks = Stock::orderBy('name')->get(['id', 'name', 'module']);
        $stockId = (int) ($request->stock_id ?: $stocks->first()?->id);

        $catalog = ScanCatalog::all();

        $availability = StockItem::where('quantity', '!=', 0)->get(['stock_id', 'product_id', 'quantity'])
            ->groupBy('stock_id')
            ->map(fn ($items) => $items->mapWithKeys(fn ($i) => [$i->product_id => (float) $i->quantity]));

        $packagingNames = Packaging::orderBy('name')->pluck('name');
        $canLink = (bool) auth()->user()?->can('products.edit');

        return view('stock.scan', compact('stocks', 'stockId', 'catalog', 'availability', 'packagingNames', 'canLink'));
    }

    /** Recherche serveur d'un code (codes associés par un autre poste depuis l'ouverture de la page). */
    public function lookup(Request $request)
    {
        // Utilisé par la page de sortie et par l'écran de réception BL.
        abort_unless(auth()->user()?->canAny(['stock.products.adjust', 'purchases.orders.receipt']), 403);
        $request->validate(['code' => 'required|string|max:64']);

        $found = Barcode::resolve($request->code);
        if (!$found) {
            return response()->json(['found' => false]);
        }

        $product = $found['product']->load(['unit', 'productPackagings.packaging']);

        return response()->json([
            'found'   => true,
            'product' => ScanCatalog::entry($product),
        ]);
    }

    public function store(Request $request)
    {
        $this->perm('stock.products.adjust');

        $data = $request->validate([
            'stock_id'                     => 'required|exists:stocks,id',
            'notes'                        => 'nullable|string|max:300',
            'lines'                        => 'required|array|min:1',
            'lines.*.product_id'           => 'required|integer|exists:products,id',
            'lines.*.product_packaging_id' => 'nullable|integer',
            'lines.*.count'                => 'required|numeric|gt:0|max:1000000',
        ], [
            'lines.required' => 'Aucun article scanné.',
        ]);

        $stockId = (int) $data['stock_id'];
        $type    = 'out'; // les entrées passent par une réception fournisseur (BL)

        // Regroupe par produit ; les coefficients viennent de la base, jamais du navigateur.
        $perProduct = [];
        foreach ($data['lines'] as $line) {
            $productId = (int) $line['product_id'];
            $count     = (float) $line['count'];
            $factor    = 1.0;
            $label     = null;

            if (!empty($line['product_packaging_id'])) {
                $pp = ProductPackaging::with('packaging')
                    ->where('id', $line['product_packaging_id'])
                    ->where('product_id', $productId)
                    ->first();
                if (!$pp) {
                    throw ValidationException::withMessages(['lines' => 'Un conditionnement scanné n\'existe plus. Rechargez la page.']);
                }
                $factor = (float) $pp->quantity;
                $label  = $pp->packaging->name ?? 'Emballage';
            }

            $perProduct[$productId]['qty']     = ($perProduct[$productId]['qty'] ?? 0) + $count * $factor;
            $perProduct[$productId]['parts'][] = $this->fmt($count) . ' × ' . ($label ?? 'unité');
        }

        $userNotes = trim($data['notes'] ?? '');

        $updated = DB::transaction(function () use ($perProduct, $stockId, $type, $userNotes) {
            $products = Product::with('unit')->whereIn('id', array_keys($perProduct))->get()->keyBy('id');
            $shortages = [];
            $updated = [];

            foreach ($perProduct as $productId => $info) {
                $qty = round($info['qty'], 2);
                if ($qty <= 0) {
                    continue;
                }

                $item = StockItem::where('stock_id', $stockId)->where('product_id', $productId)->lockForUpdate()->first()
                    ?? StockItem::create(['stock_id' => $stockId, 'product_id' => $productId, 'quantity' => 0]);

                if ($type === 'out' && (float) $item->quantity + 0.0001 < $qty) {
                    $unit = $products[$productId]->unit->symbol ?? '';
                    $shortages[] = ($products[$productId]->name ?? "#$productId")
                        . " : disponible {$this->fmt($item->quantity)} {$unit}, demandé {$this->fmt($qty)} {$unit}";
                    continue;
                }

                $type === 'in' ? $item->increment('quantity', $qty) : $item->decrement('quantity', $qty);

                StockMovement::create([
                    'stock_id'      => $stockId,
                    'product_id'    => $productId,
                    'type'          => $type,
                    'quantity'      => $qty,
                    'origin_module' => 'scan',
                    'origin_type'   => 'scan',
                    'user_id'       => auth()->id(),
                    'notes'         => mb_substr(
                        ($userNotes !== '' ? $userNotes . ' — ' : '') . 'Scan : ' . implode(', ', $info['parts']),
                        0, 1000
                    ),
                ]);

                $updated[$productId] = (float) $item->fresh()->quantity;
            }

            if ($shortages) {
                // Annule toute la validation : on ne fait jamais une sortie partielle.
                throw ValidationException::withMessages(['lines' => array_merge(['Stock insuffisant :'], $shortages)]);
            }

            return $updated;
        });

        $count = count($updated);

        return response()->json([
            'message'      => $count . ' produit(s) ' . ($type === 'in' ? 'entré(s)' : 'sorti(s)') . ' du stock.',
            'availability' => $updated,
        ]);
    }

    /**
     * Associe un code inconnu à un produit : unité de base, emballage existant,
     * ou nouvel emballage (créé à la volée avec sa contenance).
     */
    public function link(Request $request)
    {
        $this->perm('products.edit');

        $data = $request->validate([
            'code'                 => 'required|string|max:64',
            'product_id'           => 'required|exists:products,id',
            'target'               => 'required|string', // "base" | "pp:{id}" | "new"
            'packaging_name'       => 'required_if:target,new|nullable|string|max:255',
            'packaging_quantity'   => 'required_if:target,new|nullable|numeric|gt:0|max:99999999',
        ], [
            'packaging_name.required_if'     => 'Indiquez le nom de l\'emballage (ex : Caisse de 12).',
            'packaging_quantity.required_if' => 'Indiquez la contenance de l\'emballage.',
        ]);

        $code = Barcode::normalize($data['code']);
        $product = Product::findOrFail($data['product_id']);

        if ($found = Barcode::resolve($code)) {
            $where = $found['packaging'] ? ($found['packaging']->packaging->name ?? 'emballage') : 'unité de base';
            throw ValidationException::withMessages([
                'code' => "Ce code est déjà associé à « {$found['product']->name} » ({$where}).",
            ]);
        }

        DB::transaction(function () use ($data, $code, $product) {
            if ($data['target'] === 'base') {
                $product->update(['barcode' => $code]);
                return;
            }

            if ($data['target'] === 'new') {
                $packaging = Packaging::firstOrCreate(['name' => trim($data['packaging_name'])]);
                $pp = ProductPackaging::firstOrNew(['product_id' => $product->id, 'packaging_id' => $packaging->id]);
                if ($pp->exists && $pp->barcode) {
                    throw ValidationException::withMessages([
                        'packaging_name' => "Cet emballage a déjà le code {$pp->barcode} pour ce produit. Choisissez-le dans la liste pour remplacer son code.",
                    ]);
                }
                $pp->fill(['quantity' => $data['packaging_quantity'], 'barcode' => $code])->save();
                if (!$product->packaging_id) {
                    $product->update(['packaging_id' => $packaging->id]);
                }
                return;
            }

            if (str_starts_with($data['target'], 'pp:')) {
                $pp = ProductPackaging::where('product_id', $product->id)
                    ->findOrFail((int) substr($data['target'], 3));
                $pp->update(['barcode' => $code]);
                return;
            }

            throw ValidationException::withMessages(['target' => 'Choix invalide.']);
        });

        $product->load(['unit', 'productPackagings.packaging']);

        return response()->json([
            'message' => 'Code associé à ' . $product->name . '.',
            'product' => ScanCatalog::entry($product),
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function fmt($n): string
    {
        return rtrim(rtrim(number_format((float) $n, 2, ',', ' '), '0'), ',');
    }
}
