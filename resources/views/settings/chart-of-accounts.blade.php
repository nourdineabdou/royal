<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan comptable — Paramètres — Complex Royal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">

<header class="bg-slate-800 border-b border-slate-700 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="{{ url('/dashboard-modern') }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center">
            <i class="fas fa-sitemap text-white"></i>
        </div>
        <div>
            <h1 class="font-bold text-lg leading-tight">Plan comptable mauritanien</h1>
            <p class="text-slate-400 text-xs">Liste des comptes utilisés pour la comptabilisation automatique</p>
        </div>
    </div>
    <a href="{{ route('accounting.journal') }}"
       class="flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl text-sm font-semibold transition">
        <i class="fas fa-book"></i> Voir le journal
    </a>
</header>

<div class="max-w-5xl mx-auto p-6">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-700/50 text-slate-400 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Classe</th>
                    <th class="px-4 py-3 text-left">Code</th>
                    <th class="px-4 py-3 text-left">Libellé</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @foreach($accounts as $account)
                    <tr class="hover:bg-slate-700/30 {{ $account->parent_code ? '' : 'bg-slate-700/20' }}">
                        <td class="px-4 py-3 text-slate-500">{{ $account->parent_code ? '' : $account->class }}</td>
                        <td class="px-4 py-3 {{ $account->parent_code ? 'pl-8 text-slate-300' : 'font-bold text-slate-100' }}">{{ $account->code }}</td>
                        <td class="px-4 py-3 {{ $account->parent_code ? 'text-slate-300' : 'font-semibold text-slate-100' }}">{{ $account->label }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
