{{--
    Zone de saisie de scan réutilisable (caméra, douchette, saisie manuelle, multiplicateur).
    À utiliser avec partials/scan-kit qui fournit le JavaScript.
--}}
<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-3 space-y-3">
    <div id="cameraBox" class="hidden">
        <div id="reader" class="bg-black w-full"></div>
        <p id="cameraMsg" class="hidden text-xs text-red-600 mt-2"></p>
    </div>

    <div class="flex gap-2">
        <button type="button" id="cameraBtn"
                class="shrink-0 px-4 py-3 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold flex items-center gap-2">
            <i class="fa-solid fa-camera"></i> <span>Caméra</span>
        </button>
        <form id="manualForm" class="flex-1 flex min-w-0" autocomplete="off">
            <input id="manualCode" type="text" inputmode="numeric" placeholder="Code-barres…"
                   class="sk-input flex-1 min-w-0 px-3 py-3 border border-slate-300 rounded-l-lg font-mono focus:outline-none focus:ring-2 focus:ring-emerald-400">
            <button class="px-4 rounded-r-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm">OK</button>
        </form>
    </div>

    <div class="flex items-center justify-between gap-3">
        <p class="text-xs text-slate-500 leading-snug">
            <i class="fa-solid fa-barcode mr-1"></i>Douchette USB / Bluetooth : scannez directement.
        </p>
        <div class="flex items-center gap-1 shrink-0" title="Quantité ajoutée au prochain scan">
            <span class="text-xs text-slate-500 mr-1 hidden sm:inline">Prochain scan</span>
            <span class="text-xs text-slate-500 mr-1 sm:hidden">×</span>
            <button type="button" id="multDec" class="w-9 h-9 rounded-lg border border-slate-300 text-slate-600 text-lg leading-none">−</button>
            <input id="multiplier" type="number" inputmode="numeric" min="1" value="1"
                   class="sk-input w-14 h-9 text-center font-bold border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400">
            <button type="button" id="multInc" class="w-9 h-9 rounded-lg border border-slate-300 text-slate-600 text-lg leading-none">+</button>
        </div>
    </div>

    <div id="lastScan" class="hidden rounded-lg px-3 py-2.5 text-sm flex items-center gap-3"></div>
</div>
