{{--
    Bouton « caméra » à côté des champs code-barres d'un formulaire (fiche produit : unité de base
    et chaque emballage). Sur téléphone : on touche le bouton, on vise le code, il se remplit tout seul.

    Les champs concernés sont trouvés automatiquement (sélecteur ci-dessous), y compris les lignes
    d'emballage ajoutées après le chargement de la page.
    Utilise html5-qrcode, comme la page « Scanner une sortie ». La caméra exige HTTPS (ou localhost).
--}}
@php $selector = $selector ?? 'input[name="barcode"], input[name="packaging_barcode[]"]'; @endphp

<div id="bcCam" class="hidden fixed inset-0 z-[70] bg-black/90 flex flex-col">
    <div class="flex items-center justify-between gap-3 px-4 py-3 text-white">
        <div class="min-w-0">
            <p class="font-bold text-base">Scanner le code-barres</p>
            <p id="bcCamFor" class="text-sm text-slate-300 truncate"></p>
        </div>
        <button type="button" id="bcCamClose" class="shrink-0 w-11 h-11 rounded-xl bg-white/15 text-xl" aria-label="Fermer">
            <i class="fas fa-xmark"></i>
        </button>
    </div>
    <div class="flex-1 flex items-center justify-center px-3 min-h-0">
        <div id="bcCamReader" class="w-full max-w-md bg-black rounded-xl overflow-hidden"></div>
    </div>
    <div class="px-4 py-4 space-y-3" style="padding-bottom: max(1rem, env(safe-area-inset-bottom))">
        <p id="bcCamMsg" class="hidden text-sm rounded-lg px-3 py-2"></p>
        <p class="text-center text-sm text-slate-300">Placez le code-barres dans le cadre. Il se remplit tout seul.</p>
        <button type="button" id="bcCamCancel" class="w-full py-3.5 rounded-xl bg-white text-slate-800 font-bold text-base">Annuler</button>
    </div>
</div>

<style>
    #bcCamReader video { object-fit: cover; }
    .bc-flash { animation: bcFlash 1.2s ease-out; }
    @keyframes bcFlash { from { background: #bbf7d0; } to { background: #fff; } }
</style>

<script>
(function () {
    'use strict';
    const SELECTOR = @json($selector);
    const $ = id => document.getElementById(id);
    const box = $('bcCam');
    let scanner = null, running = false, target = null;

    // ── Un bouton caméra à côté de chaque champ code-barres ─────────────────
    function attach(input) {
        if (input.dataset.bcCam) return;
        input.dataset.bcCam = '1';
        const wrap = document.createElement('div');
        // Code de l'unité de base : pleine largeur. Emballage : pleine largeur sur téléphone.
        wrap.className = input.name === 'barcode' ? 'flex gap-2 w-full' : 'flex gap-1 w-full sm:w-auto';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        input.classList.remove('w-full', 'w-48', 'text-sm');
        input.classList.add('flex-1', 'min-w-0', 'bg-white');
        input.style.fontSize = window.matchMedia('(min-width: 640px)').matches ? '' : '16px'; // évite le zoom iOS
        if (input.name !== 'barcode') input.classList.add('sm:w-48');
        input.setAttribute('inputmode', 'numeric');
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'bc-cam-btn shrink-0 px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold flex items-center gap-2';
        btn.title = 'Scanner avec la caméra du téléphone';
        btn.innerHTML = '<i class="fas fa-camera"></i><span class="sm:hidden">Scanner</span>';
        wrap.appendChild(btn);
    }
    const attachAll = () => document.querySelectorAll(SELECTOR).forEach(attach);
    attachAll();
    // Lignes d'emballage ajoutées plus tard (« Ajouter un emballage »)
    new MutationObserver(attachAll).observe(document.body, { childList: true, subtree: true });

    document.addEventListener('click', e => {
        const btn = e.target.closest('.bc-cam-btn');
        if (btn) open(btn.parentNode.querySelector('input'));
    });

    // ── Fenêtre caméra ──────────────────────────────────────────────────────
    function labelFor(input) {
        if (input.name === 'barcode') return 'Unité de base';
        const row = input.closest('.packaging-row');
        const sel = row && row.querySelector('select');
        const name = sel && sel.value ? sel.options[sel.selectedIndex].text : '';
        return name ? 'Emballage : ' + name : 'Emballage';
    }
    function message(text, ok) {
        const m = $('bcCamMsg');
        m.textContent = text;
        m.className = 'text-sm rounded-lg px-3 py-2 ' + (ok ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white');
    }
    const normalize = c => String(c || '').replace(/\s+/g, '');
    function beep(ok) {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const o = ctx.createOscillator(), g = ctx.createGain();
            o.frequency.value = ok ? 1400 : 300; o.type = 'square'; g.gain.value = 0.06;
            o.connect(g); g.connect(ctx.destination); o.start(); o.stop(ctx.currentTime + (ok ? 0.08 : 0.25));
        } catch (e) {}
        try { navigator.vibrate && navigator.vibrate(ok ? 50 : [90, 60, 90]); } catch (e) {}
    }
    const loadLib = urls => urls.reduce((p, url) => p.catch(() => new Promise((ok, ko) => {
        const s = document.createElement('script');
        s.src = url; s.onload = ok; s.onerror = () => { s.remove(); ko(); };
        document.head.appendChild(s);
    })), Promise.reject());

    async function open(input) {
        target = input;
        $('bcCamFor').textContent = labelFor(input);
        $('bcCamMsg').className = 'hidden';
        box.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (!window.isSecureContext) {
            message("La caméra ne fonctionne qu'avec une adresse en https://. Ouvrez le site en https, ou tapez le code à la main.", false);
            return;
        }
        try {
            if (!window.Html5Qrcode) {
                await loadLib([
                    'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js',
                    'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js',
                ]);
            }
            const F = Html5QrcodeSupportedFormats;
            scanner = scanner || new Html5Qrcode('bcCamReader', {
                formatsToSupport: [F.EAN_13, F.EAN_8, F.UPC_A, F.UPC_E, F.CODE_128, F.CODE_39, F.ITF, F.QR_CODE],
                experimentalFeatures: { useBarCodeDetectorIfSupported: true },
                verbose: false,
            });
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 12, qrbox: (w, h) => ({ width: Math.floor(Math.min(w * 0.85, 360)), height: Math.floor(Math.min(h * 0.45, 170)) }) },
                onCode,
                () => {}
            );
            running = true;
        } catch (e) {
            message("Impossible d'ouvrir la caméra : " + (e && e.message ? e.message : e) + '. Autorisez la caméra pour ce site dans le navigateur.', false);
        }
    }

    let busy = false;
    function onCode(raw) {
        if (busy || !target) return;
        const code = normalize(raw);
        if (!code) return;
        // Le même code ne peut pas servir deux fois sur ce produit (base + emballages)
        const dup = [...document.querySelectorAll(SELECTOR)].find(i => i !== target && normalize(i.value) === code);
        if (dup) {
            beep(false);
            message('Ce code est déjà mis sur « ' + labelFor(dup) + ' ». Scannez le code de cet emballage-ci.', false);
            return;
        }
        busy = true;
        beep(true);
        target.value = code;
        target.dispatchEvent(new Event('input', { bubbles: true }));
        target.dispatchEvent(new Event('change', { bubbles: true }));
        const filled = target;
        filled.classList.add('bc-flash');
        setTimeout(() => filled.classList.remove('bc-flash'), 1300);
        message('Code lu : ' + code, true);
        setTimeout(close, 600);
    }

    async function close() {
        try { if (scanner && running) await scanner.stop(); } catch (e) {}
        running = false;
        busy = false;
        box.classList.add('hidden');
        document.body.style.overflow = '';
        target = null;
    }
    $('bcCamClose').addEventListener('click', close);
    $('bcCamCancel').addEventListener('click', close);
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !box.classList.contains('hidden')) close(); });
})();
</script>
