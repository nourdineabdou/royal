<?php

namespace App\Http\Controllers;

use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class PaymentTypeController extends Controller
{
    public function index(Request $request)
    {
        $this->perm('payment-types.view');
        $query = PaymentType::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $paymentTypes = $query->paginate(15);

        return view('parameters.payment-types.index', compact('paymentTypes'));
    }

    public function create()
    {
        $this->perm('payment-types.create');
        return view('parameters.payment-types.create');
    }

    public function store(Request $request)
    {
        $this->perm('payment-types.create');
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_types',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        PaymentType::create($validated);

        return redirect()->route('payment-types.index')->with('success', 'Type de paiement créé avec succès!');
    }

    public function edit(PaymentType $paymentType)
    {
        $this->perm('payment-types.edit');
        return view('parameters.payment-types.edit', compact('paymentType'));
    }

    public function update(Request $request, PaymentType $paymentType)
    {
        $this->perm('payment-types.edit');
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_types,name,' . $paymentType->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $paymentType->update($validated);

        return redirect()->route('payment-types.index')->with('success', 'Type de paiement mis à jour avec succès!');
    }

    public function destroy(PaymentType $paymentType)
    {
        $this->perm('payment-types.delete');

        $linkedPayments = $paymentType->payments()->count();

        if ($linkedPayments > 0) {
            return redirect()->route('payment-types.index')->with(
                'error',
                "Suppression impossible: ce mode de paiement est déjà utilisé dans {$linkedPayments} paiement(s)."
            );
        }

        try {
            $paymentType->delete();
            return redirect()->route('payment-types.index')->with('success', 'Type de paiement supprimé avec succès!');
        } catch (QueryException $e) {
            return redirect()->route('payment-types.index')->with(
                'error',
                'Suppression impossible: ce mode de paiement est référencé par des données existantes.'
            );
        }
    }
}
