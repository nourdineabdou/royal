{{--
    Kit de scan partagé (sorties de stock, réceptions BL…).

    Fournit : index des codes-barres (variantes GTIN), caméra (html5-qrcode), douchette (frappes
    clavier rapides), saisie manuelle, multiplicateur, bip / vibration, notifications, et panneau
    d'association d'un code inconnu à un produit / emballage.

    Variables Blade attendues : $packagingNames (noms d'emballages pour l'autocomplétion).
    Utilisation JS :
        const kit = ScanKit({ catalog, canLink, csrf, routes: { lookup, link }, onHit(pid, ppid, n) {...} });
--}}
<style>
    .sk-input { font-size: 16px; } /* évite le zoom iOS */
    .sk-input::-webkit-outer-spin-button, .sk-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .sk-input[type=number] { -moz-appearance: textfield; }
    #reader video { object-fit: cover; border-radius: 0.75rem; }
    #reader { overflow: hidden; border-radius: 0.75rem; }
    .sk-flash { animation: skFlash 0.9s ease-out; }
    @keyframes skFlash { from { background: #bbf7d0; } to { background: transparent; } }
</style>

{{-- Panneau d'association d'un code inconnu --}}
<div id="linkSheet" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/40" data-close></div>
    <div class="absolute bottom-0 left-0 right-0 sm:top-1/2 sm:bottom-auto sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2 sm:w-[32rem]
                bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl max-h-[90vh] flex flex-col">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div class="min-w-0">
                <p class="font-bold text-slate-800 text-sm">Associer un code-barres</p>
                <p class="font-mono text-xs text-slate-500 truncate" id="linkCode"></p>
            </div>
            <button type="button" class="w-9 h-9 rounded-lg hover:bg-slate-100 text-slate-500" data-close aria-label="Fermer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div id="linkStep1" class="p-4 flex flex-col gap-3 min-h-0">
            <input type="search" id="linkSearch" placeholder="Rechercher le produit…"
                   class="sk-input w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400">
            <div id="linkResults" class="overflow-y-auto -mx-1 divide-y divide-slate-100" style="max-height:50vh"></div>
        </div>
        <div id="linkStep2" class="hidden p-4 overflow-y-auto space-y-3">
            <div class="flex items-center justify-between gap-2 bg-slate-50 rounded-lg px-3 py-2">
                <p class="font-semibold text-sm text-slate-800 truncate" id="linkProductName"></p>
                <button type="button" id="linkChangeProduct" class="text-xs text-emerald-700 font-semibold shrink-0">Changer</button>
            </div>
            <p class="text-xs text-slate-500">Ce code correspond à :</p>
            <div id="linkTargets" class="space-y-2"></div>
            <div id="linkNewBox" class="hidden grid grid-cols-5 gap-2 pl-7">
                <input type="text" id="linkNewName" list="skPackagingNames" placeholder="Nom (ex : Caisse de 12)"
                       class="sk-input col-span-3 px-3 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <div class="col-span-2 flex items-center border border-slate-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-emerald-400">
                    <input type="number" id="linkNewQty" inputmode="decimal" min="0" step="any" placeholder="Contenance"
                           class="sk-input w-full min-w-0 px-2 py-2.5 focus:outline-none">
                    <span class="px-2 text-xs text-slate-500 bg-slate-50 self-stretch flex items-center" id="linkNewUnit"></span>
                </div>
                <p class="col-span-5 text-xs text-slate-400">Contenance en unité de base. Ex : caisse de 12 bouteilles de 1 L = 12000 mL.</p>
            </div>
            <datalist id="skPackagingNames">
                @foreach($packagingNames ?? [] as $n)<option value="{{ $n }}">@endforeach
            </datalist>
            <p id="linkError" class="hidden text-sm text-red-600"></p>
            <button type="button" id="linkSave"
                    class="w-full py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm">
                Associer et ajouter
            </button>
        </div>
    </div>
</div>

<div id="skToast" class="hidden fixed top-4 left-1/2 -translate-x-1/2 z-[60] px-4 py-2.5 rounded-lg shadow-lg text-sm font-semibold text-white max-w-[90vw]"></div>

<script>
window.ScanKit = function (opts) {
    'use strict';
    const kit = {};
    const products = kit.products = new Map();   // id → produit
    const codeIndex = new Map();                  // variante de code → { pid, ppid }
    const $id = id => document.getElementById(id);

    // ── Formatage ───────────────────────────────────────────────────────────
    const nf = kit.nf = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 });
    kit.esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const esc = kit.esc;
    /** Unité d'affichage confortable : g → kg, mL → L (facteur vers l'unité de base). */
    kit.displayUnit = unit => {
        const u = (unit || '').toLowerCase();
        if (u === 'g') return { label: 'kg', factor: 1000 };
        if (u === 'ml') return { label: 'L', factor: 1000 };
        return { label: unit || '', factor: 1 };
    };
    kit.friendly = (q, unit) => {
        const d = kit.displayUnit(unit);
        if (d.factor > 1 && Math.abs(q) >= d.factor) return nf.format(q / d.factor) + ' ' + d.label;
        return nf.format(q) + (unit ? ' ' + unit : '');
    };
    kit.levelOf = (p, ppid) => {
        if (!ppid) return { name: 'Unité', factor: 1, breakdown: null };
        const pp = p.packagings.find(x => x.id === ppid);
        return pp ? { name: pp.name, factor: pp.qty, breakdown: pp.breakdown } : null;
    };

    // ── Codes-barres (identique à App\Support\Barcode) ──────────────────────
    const normalize = code => String(code || '').replace(/\s+/g, '');
    function variants(code) {
        code = normalize(code);
        if (!code) return [];
        const out = new Set([code]);
        if (/^\d+$/.test(code)) {
            const core = code.replace(/^0+/, '');
            [8, 12, 13, 14].forEach(len => { if (core.length <= len) out.add(core.padStart(len, '0')); });
            if (core) out.add(core);
        }
        return [...out];
    }
    kit.indexProduct = p => {
        for (const [k, v] of codeIndex) if (v.pid === p.id) codeIndex.delete(k);
        products.set(p.id, p);
        if (p.barcode) variants(p.barcode).forEach(v => codeIndex.set(v, { pid: p.id, ppid: null }));
        p.packagings.forEach(pp => { if (pp.barcode) variants(pp.barcode).forEach(v => codeIndex.set(v, { pid: p.id, ppid: pp.id })); });
    };
    (opts.catalog || []).forEach(kit.indexProduct);
    kit.findLocal = code => {
        for (const v of variants(code)) if (codeIndex.has(v)) return codeIndex.get(v);
        return null;
    };

    // ── Retour sonore / visuel ──────────────────────────────────────────────
    let audioCtx = null;
    kit.beep = ok => {
        try {
            audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
            const play = (freq, start, dur) => {
                const o = audioCtx.createOscillator(), g = audioCtx.createGain();
                o.frequency.value = freq; o.type = 'square'; g.gain.value = 0.06;
                o.connect(g); g.connect(audioCtx.destination);
                o.start(audioCtx.currentTime + start); o.stop(audioCtx.currentTime + start + dur);
            };
            if (ok) play(1400, 0, 0.07); else { play(320, 0, 0.15); play(250, 0.2, 0.2); }
        } catch (e) {}
        try { navigator.vibrate && navigator.vibrate(ok ? 40 : [90, 60, 90]); } catch (e) {}
    };
    let toastTimer;
    kit.toast = (msg, ok = true) => {
        const t = $id('skToast');
        t.textContent = msg;
        t.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[60] px-4 py-2.5 rounded-lg shadow-lg text-sm font-semibold text-white max-w-[90vw] ' + (ok ? 'bg-emerald-600' : 'bg-red-600');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => t.classList.add('hidden'), 3500);
    };
    kit.showLast = (html, kind) => {
        const el = $id('lastScan');
        if (!el) return;
        el.className = 'rounded-lg px-3 py-2.5 text-sm flex items-center gap-3 ' +
            (kind === 'ok' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200');
        el.innerHTML = html;
    };
    kit.hideLast = () => { const el = $id('lastScan'); if (el) el.className = 'hidden'; };
    kit.okLine = (p, level, n, sign) =>
        '<i class="fa-solid fa-circle-check text-lg"></i><div class="flex-1 min-w-0">' +
        '<p class="font-semibold truncate">' + esc(p.name) + '</p>' +
        '<p class="text-xs">' + sign + nf.format(n) + ' × ' + esc(level.name) +
        ' <span class="opacity-70">(' + kit.friendly(n * level.factor, p.unit) + ')</span></p></div>';
    kit.error = (title, detail) => {
        kit.beep(false);
        kit.showLast('<i class="fa-solid fa-circle-xmark text-lg"></i><div class="flex-1 min-w-0"><p class="font-semibold">' + esc(title) + '</p>' +
            (detail ? '<p class="text-xs">' + esc(detail) + '</p>' : '') + '</div>', 'err');
    };

    // ── Multiplicateur « prochain scan » ────────────────────────────────────
    let multiplier = 1;
    const multInput = $id('multiplier');
    const setMult = n => { multiplier = Math.max(1, Math.round(n * 100) / 100 || 1); if (multInput) multInput.value = multiplier; };
    if (multInput) {
        $id('multInc').addEventListener('click', () => setMult(multiplier + 1));
        $id('multDec').addEventListener('click', () => setMult(multiplier - 1));
        multInput.addEventListener('change', () => setMult(parseFloat(multInput.value)));
    }
    kit.takeMultiplier = () => { const n = multiplier; setMult(1); return n; };

    // ── Traitement d'un code ────────────────────────────────────────────────
    kit.handleCode = async raw => {
        const code = normalize(raw);
        if (!code) return;
        let hit = kit.findLocal(code);
        if (!hit) {
            try { // code associé depuis un autre poste après l'ouverture de la page
                const r = await fetch(opts.routes.lookup + '?code=' + encodeURIComponent(code), { headers: { Accept: 'application/json' } });
                const data = await r.json();
                if (data.found) { kit.indexProduct(data.product); hit = kit.findLocal(code); }
            } catch (e) {}
        }
        if (!hit) {
            kit.beep(false);
            kit.showLast(
                '<i class="fa-solid fa-circle-question text-lg"></i><div class="flex-1 min-w-0">' +
                '<p class="font-semibold">Code inconnu</p><p class="font-mono text-xs truncate">' + esc(code) + '</p></div>' +
                (opts.canLink ? '<button type="button" class="shrink-0 px-3 py-2 rounded-lg bg-red-600 text-white text-xs font-bold" data-sk-link="' + esc(code) + '">Associer</button>'
                              : '<span class="text-xs">Demandez à un responsable de l\'associer.</span>'),
                'err');
            if (opts.canLink) kit.openLink(code);
            return;
        }
        opts.onHit(hit.pid, hit.ppid, kit.takeMultiplier(), code);
    };
    const last = $id('lastScan');
    if (last) last.addEventListener('click', e => { const b = e.target.closest('[data-sk-link]'); if (b) kit.openLink(b.dataset.skLink); });

    const manualForm = $id('manualForm');
    if (manualForm) manualForm.addEventListener('submit', e => {
        e.preventDefault();
        const input = $id('manualCode');
        kit.handleCode(input.value);
        input.value = '';
    });

    // ── Douchette (émulation clavier) : frappes très rapides hors des champs ──
    let buf = '', lastKey = 0;
    document.addEventListener('keydown', e => {
        if (!$id('linkSheet').classList.contains('hidden')) return;
        if (opts.isBlocked && opts.isBlocked()) return;
        if (e.target.closest && e.target.closest('input, textarea, select, [contenteditable], .select2-container')) return;
        const now = performance.now();
        if (now - lastKey > 80) buf = '';
        lastKey = now;
        if (e.key === 'Enter' || e.key === 'Tab') {
            if (buf.length >= 3) { e.preventDefault(); kit.handleCode(buf); }
            buf = '';
        } else if (e.key.length === 1) {
            buf += e.key;
        }
    });

    // ── Caméra ──────────────────────────────────────────────────────────────
    let scanner = null, cameraOn = false, lastCam = { code: '', t: 0 };
    const loadScript = urls => urls.reduce((p, url) => p.catch(() => new Promise((ok, ko) => {
        const s = document.createElement('script');
        s.src = url; s.onload = ok; s.onerror = () => { s.remove(); ko(); };
        document.head.appendChild(s);
    })), Promise.reject());
    const cameraError = msg => { const m = $id('cameraMsg'); m.textContent = msg; m.classList.remove('hidden'); };
    const setCameraBtn = () => {
        const b = $id('cameraBtn');
        b.querySelector('span').textContent = cameraOn ? 'Arrêter' : 'Caméra';
        b.classList.toggle('bg-red-600', cameraOn);
    };
    async function startCamera() {
        $id('cameraBox').classList.remove('hidden');
        $id('cameraMsg').classList.add('hidden');
        if (!window.isSecureContext) {
            cameraError("La caméra n'est disponible qu'en HTTPS (ou sur localhost). Utilisez la saisie manuelle ou une douchette, ou ouvrez le site en https://.");
            return;
        }
        try {
            if (!window.Html5Qrcode) {
                await loadScript([
                    'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js',
                    'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js',
                ]);
            }
            const F = Html5QrcodeSupportedFormats;
            scanner = scanner || new Html5Qrcode('reader', {
                formatsToSupport: [F.EAN_13, F.EAN_8, F.UPC_A, F.UPC_E, F.CODE_128, F.CODE_39, F.ITF, F.QR_CODE],
                experimentalFeatures: { useBarCodeDetectorIfSupported: true },
                verbose: false,
            });
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 12, qrbox: (w, h) => ({ width: Math.floor(Math.min(w * 0.85, 360)), height: Math.floor(Math.min(h * 0.5, 170)) }) },
                code => {
                    const now = Date.now();
                    if (code === lastCam.code && now - lastCam.t < 1800) return; // même code encore devant l'objectif
                    lastCam = { code, t: now };
                    kit.handleCode(code);
                },
                () => {}
            );
            cameraOn = true;
            setCameraBtn();
        } catch (e) {
            cameraError("Impossible d'ouvrir la caméra : " + (e && e.message ? e.message : e) + ". Vérifiez l'autorisation dans le navigateur.");
        }
    }
    async function stopCamera() {
        try { if (scanner && cameraOn) await scanner.stop(); } catch (e) {}
        cameraOn = false;
        $id('cameraBox').classList.add('hidden');
        setCameraBtn();
    }
    const pauseCamera = p => { try { if (scanner && cameraOn) p ? scanner.pause(true) : scanner.resume(); } catch (e) {} };
    if ($id('cameraBtn')) $id('cameraBtn').addEventListener('click', () => cameraOn ? stopCamera() : startCamera());

    // ── Association d'un code inconnu ───────────────────────────────────────
    const sheet = $id('linkSheet');
    let link = { code: '', product: null };
    const showStep = n => { $id('linkStep1').classList.toggle('hidden', n !== 1); $id('linkStep2').classList.toggle('hidden', n !== 2); };
    kit.openLink = code => {
        link = { code, product: null };
        $id('linkCode').textContent = code;
        $id('linkSearch').value = '';
        $id('linkError').classList.add('hidden');
        showStep(1);
        renderResults();
        sheet.classList.remove('hidden');
        pauseCamera(true);
        setTimeout(() => $id('linkSearch').focus(), 50);
    };
    const closeLink = () => { sheet.classList.add('hidden'); pauseCamera(false); };
    sheet.addEventListener('click', e => { if (e.target.closest('[data-close]')) closeLink(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !sheet.classList.contains('hidden')) closeLink(); });

    function renderResults() {
        const q = $id('linkSearch').value.trim().toLowerCase();
        const list = [...products.values()].filter(p => !q || (p.name + ' ' + (p.ref || '')).toLowerCase().includes(q)).slice(0, 60);
        $id('linkResults').innerHTML = list.length
            ? list.map(p => '<button type="button" class="w-full text-left px-3 py-3 hover:bg-emerald-50 flex justify-between gap-2" data-pid="' + p.id + '">' +
                '<span class="text-sm text-slate-800">' + esc(p.name) + '</span><span class="text-xs text-slate-400 shrink-0">' + esc(p.ref || '') + '</span></button>').join('')
            : '<p class="text-sm text-slate-400 text-center py-6">Aucun produit.</p>';
    }
    $id('linkSearch').addEventListener('input', renderResults);
    $id('linkResults').addEventListener('click', e => {
        const b = e.target.closest('[data-pid]');
        if (!b) return;
        link.product = products.get(+b.dataset.pid);
        renderTargets();
        showStep(2);
    });
    $id('linkChangeProduct').addEventListener('click', () => showStep(1));

    function renderTargets() {
        const p = link.product;
        $id('linkProductName').textContent = p.name;
        $id('linkNewUnit').textContent = p.unit || '';
        const opt = (value, title, sub, checked) =>
            '<label class="flex items-start gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">' +
            '<input type="radio" name="linkTarget" value="' + value + '" class="mt-1 accent-emerald-600"' + (checked ? ' checked' : '') + '>' +
            '<span class="min-w-0"><span class="block text-sm font-semibold text-slate-800">' + title + '</span>' +
            (sub ? '<span class="block text-xs text-slate-500">' + sub + '</span>' : '') + '</span></label>';
        const replaced = code => code ? ' · <span class="text-amber-600">remplace le code ' + esc(code) + '</span>' : '';
        let html = p.packagings.map((pp, i) =>
            opt('pp:' + pp.id, esc(pp.name), '1 = ' + kit.friendly(pp.qty, p.unit) + (pp.breakdown ? ' · ' + esc(pp.breakdown) : '') + replaced(pp.barcode), i === 0)).join('');
        html += opt('base', 'Unité de base', '1 = 1 ' + esc(p.unit) + replaced(p.barcode), p.packagings.length === 0);
        html += opt('new', '<i class="fa-solid fa-plus mr-1"></i>Nouvel emballage', 'Bouteille, pack, caisse… avec sa contenance', false);
        $id('linkTargets').innerHTML = html;
        toggleNewBox();
    }
    const toggleNewBox = () => {
        const v = (document.querySelector('input[name=linkTarget]:checked') || {}).value;
        $id('linkNewBox').classList.toggle('hidden', v !== 'new');
    };
    $id('linkTargets').addEventListener('change', toggleNewBox);

    $id('linkSave').addEventListener('click', async () => {
        const target = (document.querySelector('input[name=linkTarget]:checked') || {}).value;
        const err = $id('linkError');
        err.classList.add('hidden');
        if (!target) return;
        const body = { code: link.code, product_id: link.product.id, target };
        if (target === 'new') {
            body.packaging_name = $id('linkNewName').value.trim();
            body.packaging_quantity = $id('linkNewQty').value;
        }
        const btn = $id('linkSave');
        btn.disabled = true;
        try {
            const r = await fetch(opts.routes.link, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': opts.csrf },
                body: JSON.stringify(body),
            });
            const data = await r.json();
            if (!r.ok) throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Erreur'));
            kit.indexProduct(data.product);
            closeLink();
            kit.toast(data.message);
            const hit = kit.findLocal(link.code);
            if (hit) opts.onHit(hit.pid, hit.ppid, kit.takeMultiplier(), link.code);
        } catch (e) {
            err.textContent = e.message;
            err.classList.remove('hidden');
        } finally {
            btn.disabled = false;
        }
    });

    return kit;
};
</script>
