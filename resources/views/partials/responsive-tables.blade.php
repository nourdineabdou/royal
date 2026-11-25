{{--
    Tableaux lisibles sur téléphone : sous 768 px, chaque ligne d'un tableau de <main> devient une carte
    (« nom de la colonne : valeur »). Automatique pour tous les tableaux, y compris les lignes ajoutées
    après coup dans les formulaires. Pour garder un tableau classique : <table data-no-cards>.
--}}
<style>
/* Listes déroulantes améliorées (select2) : jamais plus larges que leur bloc */
main .select2-container { max-width: 100%; }
@media (max-width: 767.98px) {
    main select, main input:not([type=checkbox]):not([type=radio]) { max-width: 100%; }
    main table.rt { display: block; width: 100%; min-width: 0 !important; border: 0; background: transparent; }
    main table.rt > thead { display: none; }
    main table.rt > tbody, main table.rt > tfoot { display: block; width: 100%; }
    main table.rt > tbody > tr, main table.rt > tfoot > tr {
        display: block; position: relative; margin: 0 0 .75rem; padding: .35rem 0;
        background: #fff; border: 1px solid #e2e8f0 !important; border-radius: .9rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    }
    main table.rt > tfoot > tr { background: #f8fafc; }
    main table.rt > tbody > tr > td, main table.rt > tfoot > tr > td,
    main table.rt > tbody > tr > th, main table.rt > tfoot > tr > th {
        display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; gap: .35rem .5rem;
        width: 100% !important; min-width: 0 !important; max-width: none !important;
        padding: .55rem .95rem !important; border: 0 !important; border-bottom: 1px dashed #eef2f6 !important;
        text-align: right !important; font-size: .95rem; line-height: 1.35; white-space: normal !important;
        word-break: break-word;
    }
    main table.rt > tbody > tr > td:last-child, main table.rt > tfoot > tr > td:last-child { border-bottom: 0 !important; }
    main table.rt td[data-label]::before, main table.rt th[data-label]::before {
        content: attr(data-label); flex: 0 1 auto; max-width: 45%; margin-right: auto; /* libellé à gauche, contenu groupé à droite */
        text-align: left; font-size: .8rem; font-weight: 600; color: #64748b; text-transform: none; letter-spacing: 0;
    }
    /* Première colonne = titre de la carte */
    main table.rt td.rt-title { display: block; text-align: left !important; font-size: 1.02rem; font-weight: 700; color: #0f172a; }
    main table.rt td.rt-title::before { display: none; }
    /* Colonne sans en-tête (boutons d'action) : alignée à droite, sans libellé */
    main table.rt td.rt-actions { justify-content: flex-end; flex-wrap: wrap; }
    main table.rt td.rt-actions > * { min-height: 2.5rem; }
    /* Lignes « Aucun résultat », totaux sur plusieurs colonnes */
    main table.rt td.rt-full { display: block; text-align: center !important; }
    main table.rt td.rt-full::before { display: none; }
    /* Cellule avec un champ de saisie : libellé au-dessus, champ en pleine largeur */
    main table.rt td.rt-field { display: block; text-align: left !important; }
    main table.rt td.rt-field::before { display: block; max-width: none; margin-bottom: .3rem; }
    main table.rt td.rt-field input:not([type=checkbox]):not([type=radio]):not([type=hidden]),
    main table.rt td.rt-field select, main table.rt td.rt-field textarea,
    main table.rt td.rt-field .select2-container { width: 100% !important; max-width: none !important; font-size: 16px; }
    main table.rt td.rt-field.rt-actions { display: flex; }
    main table.rt td:empty, main table.rt td.rt-blank { display: none; }
    main table.rt img { max-width: 3.5rem; height: auto; }
    /* Boutons-icônes (voir, modifier, supprimer) : assez grands pour le doigt */
    main table.rt td a:has(> i:only-child), main table.rt td button:has(> i:only-child) {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 2.6rem; min-height: 2.6rem; border-radius: .6rem; font-size: 1.1rem;
    }
    /* Les conteneurs à défilement horizontal deviennent inutiles */
    main .overflow-x-auto:has(> table.rt) { overflow: visible; }
}
</style>
<script>
(function () {
    'use strict';
    const FIELD = 'input:not([type=hidden]), select, textarea';

    function headers(table) {
        const rows = table.tHead ? table.tHead.rows : [];
        if (!rows.length) return null;
        const out = [];
        for (const th of rows[rows.length - 1].cells) {
            const label = th.textContent.replace(/\s+/g, ' ').trim();
            for (let i = 0; i < (th.colSpan || 1); i++) out.push(label);
        }
        return out;
    }

    function label(table) {
        if (table.hasAttribute('data-no-cards') || table.closest('[data-no-cards]')) return;
        const heads = headers(table);
        if (!heads || heads.length < 2) return;
        table.classList.add('rt');
        const sections = [...table.tBodies, ...(table.tFoot ? [table.tFoot] : [])];
        for (const section of sections) {
            for (const tr of section.rows) {
                let col = 0, titled = false, carried = '';
                const cells = [...tr.cells];
                cells.forEach((cell, idx) => {
                    const span = cell.colSpan || 1;
                    const text = cell.textContent.replace(/\s+/g, ' ').trim();
                    // « Total : » sur plusieurs colonnes, suivi du montant : il devient le libellé du montant
                    if (span >= 2 && idx < cells.length - 1 && text && !cell.querySelector(FIELD)) {
                        carried = text.replace(/\s*:\s*$/, '');
                        cell.classList.add('rt-blank');
                        col += span;
                        return;
                    }
                    const head = carried || heads[col] || '';
                    if (carried) { cell.setAttribute('data-label', carried); carried = ''; }
                    if (span >= Math.max(2, heads.length / 2)) {
                        cell.classList.add('rt-full');
                    } else {
                        if (head && !cell.hasAttribute('data-label')) cell.setAttribute('data-label', head);
                        if (!head) cell.classList.add('rt-actions');
                        if (cell.querySelector(FIELD)) cell.classList.add('rt-field');
                        // Première cellule utile d'une ligne du tableau = titre de la carte
                        if (!titled && section.tagName === 'TBODY' && head && !cell.classList.contains('rt-field') && cell.textContent.trim()) {
                            if (col === 0) cell.classList.add('rt-title');
                            titled = true;
                        }
                        if (!cell.textContent.trim() && !cell.querySelector('img, svg, i, button, a, ' + FIELD)) cell.classList.add('rt-blank');
                    }
                    col += span;
                });
            }
        }
    }

    const run = () => document.querySelectorAll('main table').forEach(label);
    document.addEventListener('DOMContentLoaded', () => {
        run();
        // Lignes ajoutées par les formulaires (commande, demande d'achat, retour…)
        let t;
        const main = document.querySelector('main');
        if (main) new MutationObserver(() => { clearTimeout(t); t = setTimeout(run, 60); })
            .observe(main, { childList: true, subtree: true });
    });
})();
</script>
