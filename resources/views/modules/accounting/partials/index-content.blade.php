
@extends('layouts.accounting')

@section('accounting_content')
<div class="max-w-7xl mx-auto px-4 py-6">
	@php
		$filterModule = $filters['module'] ?? '';
		$filterCashier = $filters['cashier_user_id'] ?? '';
		$filterFrom = $filters['from_date'] ?? '';
		$filterTo = $filters['to_date'] ?? '';
		$queryString = http_build_query(array_filter([
			'module' => $filterModule,
			'cashier_user_id' => $filterCashier,
			'from_date' => $filterFrom,
			'to_date' => $filterTo,
		]));
	@endphp

	<!-- En-tête -->
	<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
		<div>
			<div class="text-2xl font-bold text-indigo-600 flex items-center gap-2">
				<i class="fas fa-calculator"></i>
				Comptabilité des Caisses
			</div>
			<div class="text-sm text-gray-500 mt-1">Suivi des sessions d'encaissement et validation comptable</div>
		</div>
		<div class="flex flex-wrap gap-2">
			<a href="{{ route('dashboard-modern') }}" class="rounded-full bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 font-semibold shadow flex items-center gap-2 transition">
				<i class="fas fa-arrow-left"></i> Dashboard principal
			</a>
			<a href="{{ route('accounting.transactions') }}" class="rounded-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 font-semibold shadow flex items-center gap-2 transition">
				<i class="fas fa-list"></i> Transactions
			</a>
			@can('pos.accounting.register.open')
			<button class="rounded-full bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 font-semibold shadow flex items-center gap-2 transition" onclick="openModal('openRegisterModal')">
				<i class="fas fa-plus-circle"></i> Ouvrir une caisse
			</button>
			@endcan
			@can('pos.accounting.traces.export')
			<a href="{{ route('accounting.traces') }}{{ $queryString ? '?' . $queryString : '' }}" class="rounded-full bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 font-semibold shadow flex items-center gap-2 transition">
				<i class="fas fa-file-invoice-dollar"></i> Traces globales
			</a>
			@endcan
		</div>
	</div>

	<form method="GET" action="{{ route('modules.accounting') }}" class="bg-white rounded-xl shadow p-4 mb-6">
		<div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
			<div>
				<label class="block text-xs font-bold text-gray-700 mb-1">Module</label>
				<select name="module" class="form-control rounded-lg border-gray-300">
					<option value="">Tous modules</option>
					<option value="restaurant" {{ $filterModule === 'restaurant' ? 'selected' : '' }}>Restaurant</option>
					<option value="catering" {{ $filterModule === 'catering' ? 'selected' : '' }}>Catering</option>
					<option value="events" {{ $filterModule === 'events' ? 'selected' : '' }}>Événements</option>
					<option value="residence" {{ $filterModule === 'residence' ? 'selected' : '' }}>Résidence</option>
				</select>
			</div>
			<div>
				<label class="block text-xs font-bold text-gray-700 mb-1">Caissier</label>
				<select name="cashier_user_id" class="form-control rounded-lg border-gray-300">
					<option value="">Tous caissiers</option>
					@foreach(($cashiers ?? collect()) as $cashier)
						<option value="{{ $cashier->id }}" {{ (string)$filterCashier === (string)$cashier->id ? 'selected' : '' }}>{{ $cashier->name }}</option>
					@endforeach
				</select>
			</div>
			<div>
				<label class="block text-xs font-bold text-gray-700 mb-1">Du</label>
				<input type="date" name="from_date" value="{{ $filterFrom }}" class="form-control rounded-lg border-gray-300">
			</div>
			<div>
				<label class="block text-xs font-bold text-gray-700 mb-1">Au</label>
				<input type="date" name="to_date" value="{{ $filterTo }}" class="form-control rounded-lg border-gray-300">
			</div>
			<div class="flex gap-2">
				<button type="submit" class="rounded-full bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 font-semibold shadow flex items-center gap-2 transition">
					<i class="fas fa-filter"></i> Filtrer
				</button>
				<a href="{{ route('modules.accounting') }}" class="rounded-full bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 font-semibold shadow flex items-center gap-2 transition">
					<i class="fas fa-undo"></i> Reset
				</a>
			</div>
		</div>
	</form>

	<!-- Statistiques -->
	<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 mb-8">
		<div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
			<div class="text-2xl font-bold text-indigo-600">{{ $stats->total ?? 0 }}</div>
			<div class="text-xs text-gray-500 mt-1">Total sessions</div>
		</div>
		<div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
			<div class="text-2xl font-bold text-yellow-500">{{ $stats->open ?? 0 }}</div>
			<div class="text-xs text-gray-500 mt-1">Ouvertes</div>
		</div>
		<div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
			<div class="text-2xl font-bold text-blue-500">{{ $stats->closed ?? 0 }}</div>
			<div class="text-xs text-gray-500 mt-1">À valider</div>
		</div>
		<div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
			<div class="text-2xl font-bold text-green-500">{{ $stats->validated ?? 0 }}</div>
			<div class="text-xs text-gray-500 mt-1">Validées</div>
		</div>
		<div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
			<div class="text-2xl font-bold text-red-500">{{ $stats->flagged ?? 0 }}</div>
			<div class="text-xs text-gray-500 mt-1">Problèmes</div>
		</div>
	</div>

	<!-- Tableau sessions -->
	<div class="bg-white rounded-xl shadow p-4 mb-8 overflow-x-auto">
		<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
			<div class="text-lg font-bold text-gray-700 flex items-center gap-2"><i class="fas fa-history"></i> Historique des sessions</div>
			<div class="text-sm text-gray-400">{{ $registers->total() }} sessions au total</div>
		</div>
		<table class="min-w-full divide-y divide-gray-200">
			<thead class="bg-gray-50">
				<tr>
					<th class="px-3 py-2 text-xs font-bold text-gray-500">#</th>
					<th class="px-3 py-2 text-xs font-bold text-gray-500">Caissier</th>
					<th class="px-3 py-2 text-xs font-bold text-gray-500">Module</th>
					<th class="px-3 py-2 text-xs font-bold text-gray-500">Poste</th>
					<th class="px-3 py-2 text-xs font-bold text-gray-500">Ouverture</th>
					<th class="px-3 py-2 text-xs font-bold text-gray-500">Fermeture</th>
					<th class="px-3 py-2 text-xs font-bold text-gray-500">Fond ouv.</th>
					<th class="px-3 py-2 text-xs font-bold text-gray-500">Encaissé</th>
					<th class="px-3 py-2 text-xs font-bold text-gray-500">Solde réel</th>
					<th class="px-3 py-2 text-xs font-bold text-gray-500">Écart</th>
					<th class="px-3 py-2 text-xs font-bold text-gray-500">Statut</th>
					<th class="px-3 py-2 text-xs font-bold text-gray-500">Actions</th>
				</tr>
			</thead>
			<tbody class="bg-white divide-y divide-gray-100">
				@forelse($registers as $reg)
				@php
					$systemTotal = (float)($reg->payments_sum_amount ?? 0);
					$expectedCash = (float)$reg->opening_balance;
					$realClosing = $reg->closing_balance !== null ? (float)$reg->closing_balance : null;
					$ecart = $realClosing !== null ? $realClosing - $expectedCash : null;
					$status = $reg->status ?? 'open';
					$pillMap = [
						'open' => 'bg-yellow-100 text-yellow-700',
						'closed' => 'bg-blue-100 text-blue-700',
						'validated' => 'bg-green-100 text-green-700',
						'flagged' => 'bg-red-100 text-red-700',
					];
					$labelMap = [
						'open' => 'Ouverte',
						'closed' => 'Fermée',
						'validated' => 'Validée',
						'flagged' => 'Problème',
					];
					$iconMap = [
						'open' => 'fa-circle',
						'closed' => 'fa-lock',
						'validated' => 'fa-check-circle',
						'flagged' => 'fa-exclamation-circle',
					];
				@endphp
				<tr>
					<td class="px-3 py-2 font-bold text-indigo-600">#{{ $reg->id }}</td>
					<td class="px-3 py-2 font-semibold text-gray-700">{{ $reg->user?->name ?? 'Inconnu' }}</td>
					<td class="px-3 py-2">
						<span class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold">
							<i class="fas fa-layer-group text-indigo-400"></i>
							{{ ucfirst($reg->module ?? 'restaurant') }}
						</span>
					</td>
					<td class="px-3 py-2">
						@if($reg->shift === 'morning')
							<span class="inline-flex items-center gap-1 text-yellow-600 font-bold"><i class="fas fa-sun"></i> Matin</span>
						@else
							<span class="inline-flex items-center gap-1 text-purple-600 font-bold"><i class="fas fa-moon"></i> Soir</span>
						@endif
					</td>
					<td class="px-3 py-2 text-xs text-gray-600">{{ $reg->opened_at?->format('d/m/Y H:i') }}</td>
					<td class="px-3 py-2 text-xs text-gray-600">{{ $reg->closed_at?->format('d/m/Y H:i') ?? '—' }}</td>
					<td class="px-3 py-2 text-xs text-gray-700">{{ number_format($reg->opening_balance, 2, ',', ' ') }} <span class="text-gray-400">MRU</span></td>
					<td class="px-3 py-2 text-xs font-bold text-green-600">{{ number_format($systemTotal, 2, ',', ' ') }} <span class="text-gray-400">MRU</span></td>
					<td class="px-3 py-2 text-xs text-gray-700">{{ $realClosing !== null ? number_format($realClosing, 2, ',', ' ') . ' MRU' : '—' }}</td>
					<td class="px-3 py-2">
						@if($ecart !== null)
							@if($ecart > 0.005)
								<span class="text-green-600 font-bold">+{{ number_format($ecart, 2, ',', ' ') }} MRU</span>
							@elseif($ecart < -0.005)
								<span class="text-red-600 font-bold">{{ number_format($ecart, 2, ',', ' ') }} MRU</span>
							@else
								<span class="text-green-500 font-bold">✓ Équilibré</span>
							@endif
						@else
							<span class="text-gray-300">—</span>
						@endif
					</td>
					<td class="px-3 py-2">
						<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold {{ $pillMap[$status] ?? 'bg-gray-200 text-gray-700' }}">
							<i class="fas {{ $iconMap[$status] ?? 'fa-circle' }}"></i>
							{{ $labelMap[$status] ?? $status }}
						</span>
					</td>
					<td class="px-3 py-2">
						<a href="{{ route('accounting.detail', $reg->id) }}" class="rounded-full bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 font-semibold shadow flex items-center gap-2 transition text-xs">
							<i class="fas fa-eye"></i> Voir
						</a>
					</td>
				</tr>
				@empty
				<tr>
					<td colspan="12" class="text-center py-8 text-gray-400 text-lg"><i class="fas fa-inbox text-3xl mb-2"></i><br>Aucune session de caisse enregistrée</td>
				</tr>
				@endforelse
			</tbody>
		</table>
		@if($registers->hasPages())
		<div class="pt-4">
			{{ $registers->links() }}
		</div>
		@endif
	</div>
</div>
<!-- ── Modal Ouvrir une caisse ── -->
<div id="openRegisterModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 transition-all duration-200" style="display:none;">
	<div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-auto p-6 relative animate-fade-in-up">
		<button class="absolute top-3 right-3 text-gray-400 hover:text-red-500 text-xl" onclick="closeModal('openRegisterModal')" aria-label="Fermer">
			<i class="fas fa-times"></i>
		</button>
		<div class="flex items-center gap-2 mb-4">
			<i class="fas fa-cash-register text-indigo-600 text-2xl"></i>
			<span class="text-lg font-bold text-gray-700">Ouvrir une caisse</span>
		</div>
		<div class="space-y-4">
			<div>
				<label class="block text-xs font-bold text-gray-700 mb-1"><i class="fas fa-sun mr-1"></i>Poste</label>
				<select id="openShift" class="form-control w-full rounded-lg border-gray-300">
					<option value="morning">Matin</option>
					<option value="evening">Soir</option>
				</select>
			</div>
			<div>
				<label class="block text-xs font-bold text-gray-700 mb-1"><i class="fas fa-layer-group mr-1"></i>Module</label>
				<select id="openModule" class="form-control w-full rounded-lg border-gray-300">
					<option value="restaurant">Restaurant</option>
					<option value="catering">Catering</option>
					<option value="events">Événements</option>
					<option value="residence">Résidence</option>
				</select>
			</div>
			<div>
				<label class="block text-xs font-bold text-gray-700 mb-1"><i class="fas fa-user mr-1"></i>Caissier assigné</label>
				<select id="openCashierUser" class="form-control w-full rounded-lg border-gray-300">
					@foreach(($cashiers ?? collect()) as $cashier)
						<option value="{{ $cashier->id }}">{{ $cashier->name }}</option>
					@endforeach
				</select>
			</div>
			<div>
				<label class="block text-xs font-bold text-gray-700 mb-1"><i class="fas fa-money-bill-wave mr-1"></i>Fond d'ouverture (MRU)</label>
				<input type="number" id="openBalance" class="form-control w-full rounded-lg border-gray-300" placeholder="0.00" min="0" step="0.01" value="0">
			</div>
			@if(($cashiers ?? collect())->isEmpty())
				<div class="bg-red-100 text-red-700 rounded-lg px-3 py-2 text-xs font-semibold mb-2">
					Aucun utilisateur avec le rôle caissier. Créez un caissier d'abord.
				</div>
			@endif
			<form method="POST" action="{{ route('accounting.sessions.store') }}" id="openRegisterForm">
				@csrf
				<input type="hidden" name="shift" id="formShift">
				<input type="hidden" name="module" id="formModule">
				<input type="hidden" name="cashier_user_id" id="formCashierUser">
				<input type="hidden" name="opening_balance" id="formBalance">
				<button type="button" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg flex items-center justify-center gap-2 transition disabled:opacity-60" onclick="submitOpenRegister()" {{ ($cashiers ?? collect())->isEmpty() ? 'disabled' : '' }}>
					<i class="fas fa-check-circle"></i> Ouvrir la caisse
				</button>
			</form>
		</div>
	</div>
</div>

<!-- Traces et transactions -->
<div class="tx-wrap">
	<div class="tx-head">
		<div style="font-size:14px;font-weight:700;">
			<i class="fas fa-file-invoice-dollar mr-2"></i>Traces globales comptables — Aujourd'hui
		</div>
		<div style="font-size:12px;opacity:.8;">Transactions · Ventes · Mouvements de stock du jour</div>
	</div>

	{{-- Résumé du jour --}}
	<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;padding:16px 18px;border-bottom:1px solid #f3f4f6;">
		<div style="text-align:center;padding:10px;background:#f8fafc;border-radius:10px;">
			<div style="font-size:22px;font-weight:700;color:#374151;">{{ ($recentTransactions ?? collect())->count() }}</div>
			<div style="font-size:11px;color:#6b7280;margin-top:2px;"><i class="fas fa-file-invoice-dollar" style="color:#374151;"></i> Transactions</div>
		</div>
		<div style="text-align:center;padding:10px;background:#eef2ff;border-radius:10px;">
			<div style="font-size:22px;font-weight:700;color:#4f46e5;">{{ ($recentPaidOrders ?? collect())->count() }}</div>
			<div style="font-size:11px;color:#6b7280;margin-top:2px;"><i class="fas fa-receipt" style="color:#4f46e5;"></i> Ventes</div>
		</div>
		<div style="text-align:center;padding:10px;background:#ecfdf5;border-radius:10px;">
			<div style="font-size:22px;font-weight:700;color:#059669;">{{ ($recentStockMovements ?? collect())->count() }}</div>
			<div style="font-size:11px;color:#6b7280;margin-top:2px;"><i class="fas fa-boxes-stacked" style="color:#059669;"></i> Mvts stock</div>
		</div>
		<div style="text-align:center;padding:10px;background:#fef3c7;border-radius:10px;">
			<div style="font-size:22px;font-weight:700;color:#d97706;">
				{{ number_format(($recentPaidOrders ?? collect())->sum('total_amount'), 0, ',', ' ') }}
			</div>
			<div style="font-size:11px;color:#6b7280;margin-top:2px;">Total encaissé (MRU)</div>
		</div>
	</div>

	{{-- Dernières ventes du jour --}}
	@if(($recentPaidOrders ?? collect())->isNotEmpty())
	<div style="overflow-x:auto;border-bottom:1px solid #f3f4f6;">
		<div style="padding:10px 18px;font-size:12px;font-weight:700;color:#6b7280;background:#fafafa;">
			<i class="fas fa-receipt mr-1"></i>Dernières ventes du jour
		</div>
		<table class="tx-table">
			<thead>
				<tr>
					<th>Commande</th>
					<th>Module</th>
					<th>Caissier</th>
					<th>Articles</th>
					<th>Paiement</th>
					<th style="text-align:right;">Montant</th>
					<th>Heure</th>
				</tr>
			</thead>
			<tbody>
				@foreach(($recentPaidOrders ?? collect()) as $order)
				<tr>
					<td>
						<a href="{{ route('orders.show', $order->id) }}" style="text-decoration:none;color:#4f46e5;font-weight:700;">#{{ $order->id }}</a>
						<div style="font-size:11px;color:#9ca3af;">{{ $order->customer_number ? 'Table '.$order->customer_number : 'Client direct' }}</div>
					</td>
					<td>
						<span style="display:inline-flex;align-items:center;gap:4px;background:#eef2ff;color:#4f46e5;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;">
							{{ ucfirst($order->cashRegister?->module ?? 'restaurant') }}
						</span>
					</td>
					<td style="font-size:12px;color:#6b7280;">{{ $order->cashRegister?->user?->name ?? '—' }}</td>
					<td style="max-width:250px;font-size:12px;">
						<div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $order->items->map(fn($i)=>(($i->meal->name??'Article').' ×'.$i->quantity))->join(', ') }}">
							{{ $order->items->map(fn($i)=>(($i->meal->name??'Article').' ×'.$i->quantity))->join(', ') ?: '—' }}
						</div>
					</td>
					<td style="font-size:12px;color:#6b7280;">{{ $order->payment?->paymentType?->name ?? '—' }}</td>
					<td style="text-align:right;font-weight:700;white-space:nowrap;">{{ number_format($order->total_amount, 2, ',', ' ') }} MRU</td>
					<td style="font-size:11px;color:#6b7280;">{{ $order->paid_at?->format('H:i') ?? '—' }}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	@endif

	{{-- Derniers mouvements de stock du jour --}}
	@if(($recentStockMovements ?? collect())->isNotEmpty())
	<div style="overflow-x:auto;border-bottom:1px solid #f3f4f6;">
		<div style="padding:10px 18px;font-size:12px;font-weight:700;color:#6b7280;background:#fafafa;">
			<i class="fas fa-boxes-stacked mr-1" style="color:#059669;"></i>Mouvements de stock du jour
		</div>
		<table class="tx-table">
			<thead>
				<tr>
					<th>Heure</th>
					<th>Stock</th>
					<th>Produit</th>
					<th>Module</th>
					<th>Type</th>
					<th style="text-align:right;">Qté</th>
					<th>Opérateur</th>
				</tr>
			</thead>
			<tbody>
				@foreach(($recentStockMovements ?? collect()) as $mv)
				@php
					$oc = [
						'restaurant' => ['bg'=>'#dbeafe','color'=>'#1e40af'],
						'catering'   => ['bg'=>'#f3e8ff','color'=>'#6b21a8'],
						'events'     => ['bg'=>'#fff7ed','color'=>'#c2410c'],
					][$mv->origin_module] ?? ['bg'=>'#f1f5f9','color'=>'#475569'];
				@endphp
				<tr>
					<td style="font-size:11px;color:#6b7280;">{{ $mv->created_at->format('H:i') }}</td>
					<td style="font-size:12px;font-weight:600;color:#059669;">{{ $mv->stock?->name ?? '—' }}</td>
					<td style="font-size:12px;">{{ $mv->product?->name ?? '—' }} <small style="color:#9ca3af;">{{ $mv->product?->unit?->name }}</small></td>
					<td>
						<span style="display:inline-flex;align-items:center;gap:3px;background:{{ $oc['bg'] }};color:{{ $oc['color'] }};padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">
							{{ $mv->origin_module_label }}
						</span>
					</td>
					<td>
						@if($mv->type === 'out')
							<span style="display:inline-flex;align-items:center;background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">Sortie</span>
						@elseif($mv->type === 'in')
							<span style="display:inline-flex;align-items:center;background:#dcfce7;color:#166534;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">Entrée</span>
						@else
							<span style="display:inline-flex;align-items:center;background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">Transfert</span>
						@endif
					</td>
					<td style="text-align:right;font-weight:700;color:{{ $mv->type === 'out' ? '#dc2626' : '#16a34a' }};">
						{{ $mv->type === 'out' ? '-' : '+' }}{{ number_format($mv->quantity, 2, ',', ' ') }}
					</td>
					<td style="font-size:11px;color:#6b7280;">{{ $mv->user?->name ?? '—' }}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	@endif

	<div style="padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
		<span style="font-size:12px;color:#9ca3af;font-style:italic;">
			Affichage limité aux 50 dernières opérations du jour
		</span>
		<a href="{{ route('accounting.traces') }}{{ $queryString ? '?'.$queryString : '' }}"
		   style="display:inline-flex;align-items:center;gap:6px;color:#4f46e5;font-size:12px;font-weight:700;text-decoration:none;">
			<i class="fas fa-external-link-alt"></i>
			Voir toutes les traces (filtres avancés + export Excel)
		</a>
	</div>
</div>
@endsection
