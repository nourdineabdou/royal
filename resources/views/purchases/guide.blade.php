<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
{{-- Guide utilisateur du circuit d'achat (demande → commande → réception BL → stock). --}}
<title>Guide Stock et Achats — Complex Royal</title>
<link rel="icon" href="{{ asset('royal_complex_groupe.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Source+Sans+3:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap">
<style>
/* Layout : en-tête sobre, puis sections pleine largeur ; le cœur est un « lecteur de scénario »
   (liste à gauche, étapes + maquette d'écran à droite) qui s'empile sur téléphone. */
:root {
  --bg: #f6f7f9;
  --surface: #ffffff;
  --surface-2: #eef1f5;
  --ink: #17202c;
  --ink-2: #4a5566;
  --ink-3: #7b8696;
  --line: #dde2ea;
  --achat: #c8561f;        /* module Achats : orange brûlé */
  --achat-soft: #fbeadf;
  --stock: #0d7a55;        /* stock / réception : vert */
  --stock-soft: #dcf2e8;
  --admin: #5b4bc4;
  --admin-soft: #e9e6fb;
  --compta: #1f6fb2;
  --compta-soft: #e0edf8;
  --demandeur: #6b7280;
  --demandeur-soft: #eceef1;
  --warn: #b7791f;
  --warn-soft: #fbf0d9;
  --danger: #c03535;
  --danger-soft: #fbe3e3;
  --focus: #1f6fb2;
  --shadow: 0 1px 2px rgba(23,32,44,.06), 0 8px 24px rgba(23,32,44,.06);
  --f-display: "Bricolage Grotesque", "Segoe UI", system-ui, sans-serif;
  --f-body: "Source Sans 3", "Segoe UI", system-ui, sans-serif;
  --f-mono: "JetBrains Mono", ui-monospace, "Cascadia Mono", Consolas, monospace;
  --r: 10px;
}
@media (prefers-color-scheme: dark) {
  :root:not([data-theme="light"]) {
    --bg: #11161d; --surface: #18202a; --surface-2: #202a36; --ink: #e8edf3; --ink-2: #b2bccb; --ink-3: #8592a4; --line: #2c3746;
    --achat: #f08a52; --achat-soft: #3a2519; --stock: #3fc893; --stock-soft: #15342a; --admin: #a79cf5; --admin-soft: #2a2550;
    --compta: #6cb2ec; --compta-soft: #172d42; --demandeur: #a3acb9; --demandeur-soft: #262f3b;
    --warn: #e8b04f; --warn-soft: #3a2f17; --danger: #f07a7a; --danger-soft: #3d1d1d; --focus: #6cb2ec;
    --shadow: 0 1px 2px rgba(0,0,0,.3), 0 8px 24px rgba(0,0,0,.25);
    color-scheme: dark;
  }
}
:root[data-theme="dark"] {
  --bg: #11161d; --surface: #18202a; --surface-2: #202a36; --ink: #e8edf3; --ink-2: #b2bccb; --ink-3: #8592a4; --line: #2c3746;
  --achat: #f08a52; --achat-soft: #3a2519; --stock: #3fc893; --stock-soft: #15342a; --admin: #a79cf5; --admin-soft: #2a2550;
  --compta: #6cb2ec; --compta-soft: #172d42; --demandeur: #a3acb9; --demandeur-soft: #262f3b;
  --warn: #e8b04f; --warn-soft: #3a2f17; --danger: #f07a7a; --danger-soft: #3d1d1d; --focus: #6cb2ec;
  --shadow: 0 1px 2px rgba(0,0,0,.3), 0 8px 24px rgba(0,0,0,.25);
  color-scheme: dark;
}

* { box-sizing: border-box; }
body {
  background: var(--bg); color: var(--ink);
  font-family: var(--f-body); font-size: 16px; line-height: 1.55;
  padding-left: max(16px, env(safe-area-inset-left, 0px));
  padding-right: max(16px, env(safe-area-inset-right, 0px));
  padding-block: 0 48px;
}
.wrap { max-width: 1120px; margin: 0 auto; }
h1, h2, h3 { font-family: var(--f-display); text-wrap: balance; line-height: 1.15; margin: 0; }
h2 { font-size: clamp(1.45rem, 2.4vw, 1.9rem); font-weight: 700; }
h3 { font-size: 1.12rem; font-weight: 700; }
p { margin: 0; }
.mono { font-family: var(--f-mono); font-size: .86em; }
.muted { color: var(--ink-2); }
.eyebrow { font-size: .74rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--ink-3); }
a { color: var(--compta); }
:focus-visible { outline: 2px solid var(--focus); outline-offset: 2px; border-radius: 6px; }
button { font: inherit; color: inherit; cursor: pointer; }

/* ── En-tête ───────────────────────────────────────────── */
header.top { padding-block: 36px 24px; display: grid; gap: 18px; }
header.top h1 { font-size: clamp(2rem, 5vw, 3.1rem); font-weight: 800; letter-spacing: -.02em; }
header.top h1 .arrow { color: var(--stock); }
header.top h1 .from { color: var(--achat); }
.lede { max-width: 66ch; font-size: 1.08rem; color: var(--ink-2); }
.role-bar { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.role-bar .eyebrow { margin-right: 4px; }
.chip {
  display: inline-flex; align-items: center; gap: 6px; border: 1px solid var(--line); background: var(--surface);
  padding: 6px 12px; border-radius: 999px; font-size: .9rem; font-weight: 600;
}
.chip .dot { width: 9px; height: 9px; border-radius: 50%; background: var(--c, var(--ink-3)); }
.chip[aria-pressed="true"] { background: var(--c-soft, var(--surface-2)); border-color: var(--c, var(--ink-2)); }
nav.toc { display: flex; flex-wrap: wrap; gap: 6px 18px; font-size: .95rem; }
nav.toc a { color: var(--ink-2); text-decoration: none; border-bottom: 1px solid transparent; }
nav.toc a:hover { color: var(--ink); border-color: var(--line); }

section { padding-block: 34px 8px; display: grid; gap: 18px; }
section > .head { display: grid; gap: 6px; }
section > .head p { max-width: 70ch; color: var(--ink-2); }

/* rôles */
[data-role="achat"]     { --c: var(--achat);     --c-soft: var(--achat-soft); }
[data-role="magasinier"]{ --c: var(--stock);     --c-soft: var(--stock-soft); }
[data-role="admin"]     { --c: var(--admin);     --c-soft: var(--admin-soft); }
[data-role="compta"]    { --c: var(--compta);    --c-soft: var(--compta-soft); }
[data-role="demandeur"] { --c: var(--demandeur); --c-soft: var(--demandeur-soft); }
.who {
  display: inline-flex; align-items: center; gap: 6px; font-size: .78rem; font-weight: 700; letter-spacing: .03em;
  color: var(--c); background: var(--c-soft); padding: 2px 9px; border-radius: 999px; white-space: nowrap;
}
.who::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: var(--c); }

/* ── Circuit ───────────────────────────────────────────── */
.flow { display: grid; grid-template-columns: repeat(8, minmax(0, 1fr)); gap: 8px; position: relative; }
.flow-step {
  text-align: left; background: var(--surface); border: 1px solid var(--line); border-radius: var(--r);
  padding: 12px 12px 14px; display: grid; gap: 6px; align-content: start; position: relative; min-width: 0;
  transition: border-color .15s, transform .15s, opacity .2s;
}
.flow-step:hover { border-color: var(--c); }
.flow-step[aria-selected="true"] { border-color: var(--c); box-shadow: 0 0 0 2px var(--c-soft); }
.flow-step .n { font-family: var(--f-mono); font-size: .78rem; color: var(--ink-3); }
.flow-step .t { font-family: var(--f-display); font-weight: 700; font-size: 1rem; line-height: 1.2; }
.flow-step.key { background: var(--stock-soft); border-color: var(--stock); }
.flow-step.dim { opacity: .38; }
.flow-step .bar { height: 3px; border-radius: 3px; background: var(--c); }
.flow-detail {
  background: var(--surface); border: 1px solid var(--line); border-radius: var(--r); box-shadow: var(--shadow);
  padding: 18px; display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); gap: 22px;
}
.kv { display: grid; grid-template-columns: max-content minmax(0, 1fr); gap: 8px 14px; align-items: baseline; font-size: .97rem; }
.kv dt { color: var(--ink-3); font-size: .8rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
.kv dd { margin: 0; min-width: 0; }
.path { font-family: var(--f-mono); font-size: .84rem; background: var(--surface-2); padding: 2px 7px; border-radius: 6px; display: inline; line-height: 1.9; }
.shortcut {
  border: 1px dashed var(--stock); border-radius: var(--r); padding: 12px 14px; background: var(--stock-soft);
  display: flex; gap: 12px; align-items: flex-start; font-size: .97rem;
}
.shortcut b { color: var(--stock); }

/* ── Scénarios ─────────────────────────────────────────── */
.player { display: grid; grid-template-columns: minmax(250px, 320px) minmax(0, 1fr); gap: 18px; align-items: start; }
.sc-list { display: grid; gap: 6px; position: sticky; top: calc(env(safe-area-inset-top, 0px) + 12px); }
.sc-btn {
  text-align: left; background: var(--surface); border: 1px solid var(--line); border-radius: var(--r);
  padding: 10px 12px; display: grid; gap: 3px; transition: border-color .15s, opacity .2s;
}
.sc-btn:hover { border-color: var(--ink-3); }
.sc-btn[aria-selected="true"] { border-color: var(--ink); box-shadow: inset 3px 0 0 var(--tagc, var(--ink)); }
.sc-btn .ttl { font-weight: 700; line-height: 1.25; }
.sc-btn .sub { font-size: .85rem; color: var(--ink-2); line-height: 1.3; }
.sc-btn.dim { opacity: .4; }
.tag { font-size: .7rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: var(--tagc); }
.tag-courant { --tagc: var(--stock); }
.tag-exception { --tagc: var(--warn); }
.tag-incident { --tagc: var(--danger); }
.tag-admin { --tagc: var(--admin); }

.sc-panel { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r); box-shadow: var(--shadow); overflow: hidden; min-width: 0; }
.sc-head { padding: 18px 20px 14px; display: grid; gap: 8px; border-bottom: 1px solid var(--line); }
.sc-head .when { color: var(--ink-2); max-width: 68ch; }
.stepper { display: flex; gap: 6px; padding: 12px 20px; border-bottom: 1px solid var(--line); overflow-x: auto; }
.stepper button {
  flex: 0 0 auto; border: 1px solid var(--line); background: var(--surface); border-radius: 999px;
  padding: 5px 12px; font-size: .86rem; font-weight: 600; display: inline-flex; gap: 6px; align-items: center;
}
.stepper button .k { font-family: var(--f-mono); color: var(--ink-3); }
.stepper button[aria-current="step"] { background: var(--ink); color: var(--surface); border-color: var(--ink); }
.stepper button[aria-current="step"] .k { color: inherit; opacity: .7; }
.stepper button.done { border-color: var(--stock); }
.step-body { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr); gap: 20px; padding: 20px; }
.step-text { display: grid; gap: 14px; align-content: start; min-width: 0; }
.step-text h3 { font-size: 1.25rem; }
.step-text ul { margin: 0; padding-left: 1.1em; display: grid; gap: 4px; }
.result {
  display: grid; gap: 4px; padding: 10px 12px; border-radius: 8px; background: var(--stock-soft); color: var(--ink); font-size: .95rem;
}
.result > b { color: var(--stock); font-size: .78rem; letter-spacing: .06em; text-transform: uppercase; }
.result.warn { background: var(--warn-soft); } .result.warn > b { color: var(--warn); }
.result.bad { background: var(--danger-soft); } .result.bad > b { color: var(--danger); }
.step-nav { display: flex; justify-content: space-between; gap: 10px; padding: 0 20px 18px; }
.btn {
  border: 1px solid var(--line); background: var(--surface); border-radius: 8px; padding: 9px 14px; font-weight: 600;
  display: inline-flex; align-items: center; gap: 6px;
}
.btn.primary { background: var(--ink); color: var(--surface); border-color: var(--ink); }
.btn:disabled { opacity: .35; cursor: default; }

/* maquettes d'écran */
.screen {
  border: 1px solid var(--line); border-radius: 12px; background: var(--bg); overflow: hidden; align-self: start;
  font-size: .86rem; min-width: 0;
}
.screen .chrome { display: flex; align-items: center; gap: 6px; padding: 7px 10px; background: var(--surface-2); border-bottom: 1px solid var(--line); }
.screen .chrome i { width: 8px; height: 8px; border-radius: 50%; background: var(--line); }
.screen .chrome span { font-family: var(--f-mono); font-size: .72rem; color: var(--ink-3); margin-left: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.screen .in { padding: 12px; display: grid; gap: 8px; }
.m-card { background: var(--surface); border: 1px solid var(--line); border-radius: 9px; padding: 10px; display: grid; gap: 6px; }
.m-card.ok { border-color: var(--stock); } .m-card.warn { border-color: var(--warn); } .m-card.bad { border-color: var(--danger); }
.m-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; min-width: 0; }
.m-row > :first-child { min-width: 0; }
.m-title { font-weight: 700; }
.m-sub { font-size: .76rem; color: var(--ink-3); }
.m-field { border: 1px solid var(--line); border-radius: 6px; padding: 4px 8px; background: var(--surface); font-size: .8rem; color: var(--ink-2); }
.m-field.focus { border-color: var(--stock); box-shadow: 0 0 0 2px var(--stock-soft); color: var(--ink); font-weight: 700; }
.m-step { display: inline-flex; border: 1px solid var(--stock); border-radius: 6px; overflow: hidden; font-weight: 700; }
.m-step span { padding: 2px 9px; } .m-step span:nth-child(2) { background: var(--surface); min-width: 34px; text-align: center; }
.m-step span:first-child, .m-step span:last-child { background: var(--surface-2); color: var(--ink-3); }
.m-pill { font-size: .7rem; font-weight: 700; padding: 1px 8px; border-radius: 999px; background: var(--surface-2); color: var(--ink-2); white-space: nowrap; }
.m-pill.ok { background: var(--stock-soft); color: var(--stock); } .m-pill.warn { background: var(--warn-soft); color: var(--warn); }
.m-pill.bad { background: var(--danger-soft); color: var(--danger); } .m-pill.achat { background: var(--achat-soft); color: var(--achat); }
.m-pill.info { background: var(--compta-soft); color: var(--compta); }
.m-btn { justify-self: end; font-weight: 700; font-size: .8rem; padding: 5px 11px; border-radius: 7px; background: var(--stock); color: var(--surface); }
.m-btn.achat { background: var(--achat); } .m-btn.red { background: var(--danger); } .m-btn.admin { background: var(--admin); } .m-btn.ghost { background: var(--surface-2); color: var(--ink-2); }
.m-banner { border-radius: 9px; padding: 10px; background: var(--stock); color: var(--surface); font-weight: 700; display: flex; justify-content: space-between; gap: 8px; }
.m-alert { border-radius: 8px; padding: 8px 10px; font-size: .8rem; background: var(--danger-soft); color: var(--danger); font-weight: 600; }
.m-alert.ok { background: var(--stock-soft); color: var(--stock); }
.m-bar { height: 5px; border-radius: 5px; background: var(--surface-2); overflow: hidden; }
.m-bar i { display: block; height: 100%; background: var(--stock); }
.m-bar.over i { background: var(--warn); }
.m-ref { font-family: var(--f-mono); font-size: .76rem; }

/* ── Calculateur ───────────────────────────────────────── */
.calc { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 18px; }
.panel { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r); padding: 18px; display: grid; gap: 14px; align-content: start; min-width: 0; }
.tree { display: grid; gap: 8px; font-size: .95rem; }
.tree .lvl { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: 10px; align-items: center; padding: 10px 12px; border-radius: 8px; background: var(--surface-2); }
.tree .lvl .ic { width: 30px; height: 30px; border-radius: 8px; display: grid; place-items: center; background: var(--surface); font-family: var(--f-mono); font-weight: 600; font-size: .8rem; color: var(--ink-2); border: 1px solid var(--line); }
.tree .lvl.child { margin-left: 26px; }
.tree .code { font-family: var(--f-mono); font-size: .76rem; color: var(--ink-3); }
.num-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; align-items: center; }
.num-row label { font-weight: 600; }
.num-row small { display: block; color: var(--ink-3); font-weight: 400; }
.stepper-in { display: inline-flex; border: 1px solid var(--line); border-radius: 8px; overflow: hidden; background: var(--surface); }
.stepper-in button { width: 38px; border: 0; background: var(--surface-2); font-size: 1.1rem; }
.stepper-in input { width: 64px; border: 0; text-align: center; font: 700 1rem var(--f-body); background: var(--surface); color: var(--ink); padding: 8px 4px; -moz-appearance: textfield; }
.stepper-in input::-webkit-inner-spin-button, .stepper-in input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.total { display: grid; gap: 6px; border-top: 1px solid var(--line); padding-top: 14px; }
.total .big { font-family: var(--f-display); font-size: 2.1rem; font-weight: 800; color: var(--stock); font-variant-numeric: tabular-nums; line-height: 1; }
.total .calc-line { font-family: var(--f-mono); font-size: .82rem; color: var(--ink-2); overflow-wrap: anywhere; }
.status-pill { justify-self: start; font-weight: 700; font-size: .9rem; padding: 4px 12px; border-radius: 999px; }
.status-pill.full { background: var(--stock-soft); color: var(--stock); }
.status-pill.partial { background: var(--warn-soft); color: var(--warn); }
.status-pill.over { background: var(--warn-soft); color: var(--warn); border: 1px solid var(--warn); }
.status-pill.none { background: var(--surface-2); color: var(--ink-2); }

/* ── Statuts / tables ──────────────────────────────────── */
.statuses { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; }
.st { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r); padding: 12px; display: grid; gap: 6px; align-content: start; min-width: 0; }
.st .m-pill { justify-self: start; font-size: .78rem; }
.st p { font-size: .9rem; color: var(--ink-2); }
.table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: var(--r); background: var(--surface); }
table { border-collapse: collapse; width: 100%; font-size: .94rem; min-width: 640px; }
th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--line); vertical-align: top; }
th { font-size: .76rem; letter-spacing: .06em; text-transform: uppercase; color: var(--ink-3); background: var(--surface-2); }
tr:last-child td { border-bottom: 0; }
td.yes { color: var(--stock); font-weight: 700; } td.no { color: var(--ink-3); }
td .err { font-family: var(--f-mono); font-size: .8rem; color: var(--danger); }

details.faq { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r); padding: 0 16px; }
details.faq + details.faq { margin-top: 8px; }
details.faq summary { cursor: pointer; padding: 13px 0; font-weight: 700; list-style: none; display: flex; justify-content: space-between; gap: 10px; }
details.faq summary::after { content: "+"; font-family: var(--f-mono); color: var(--ink-3); }
details.faq[open] summary::after { content: "–"; }
details.faq div { padding: 0 0 14px; color: var(--ink-2); display: grid; gap: 8px; }
.gloss { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 10px; }
.gloss div { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r); padding: 12px 14px; display: grid; gap: 4px; }
.gloss b { font-family: var(--f-display); font-size: 1.02rem; }
.gloss span { color: var(--ink-2); font-size: .93rem; }
footer { padding-block: 36px 0; color: var(--ink-3); font-size: .88rem; }

@media (max-width: 980px) {
  .flow { grid-template-columns: repeat(4, minmax(0, 1fr)); }
  .statuses { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 860px) {
  .player { grid-template-columns: minmax(0, 1fr); }
  .sc-list { position: static; grid-auto-flow: column; grid-auto-columns: minmax(210px, 72%); overflow-x: auto; padding-bottom: 6px; }
  .step-body, .flow-detail, .calc { grid-template-columns: minmax(0, 1fr); }
}
@media (max-width: 520px) {
  .flow { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .statuses { grid-template-columns: minmax(0, 1fr); }
  .step-body { padding: 16px; } .sc-head { padding: 16px; } .stepper { padding: 10px 16px; } .step-nav { padding: 0 16px 16px; }
}
@media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }
.appbar { display: flex; justify-content: space-between; gap: 12px; padding-block: 12px; border-bottom: 1px solid var(--line); max-width: 1120px; margin: 0 auto; font-weight: 600; font-size: .95rem; }
.appbar a { color: var(--ink-2); text-decoration: none; }
.appbar a:hover { color: var(--ink); }
[hidden] { display: none !important; }
html, body { margin: 0; }
img { max-width: 100%; }
/* Captures réelles de l'application */
.shots { display: grid; gap: 12px; align-content: start; min-width: 0; }
.shots.phone { grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); }
.shots .real-badge { grid-column: 1 / -1; }
.shot { margin: 0; display: grid; gap: 6px; min-width: 0; align-content: start; }
.shot button { padding: 0; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; background: var(--surface-2); cursor: zoom-in; display: block; width: 100%; box-shadow: var(--shadow); }
.shot img { display: block; width: 100%; height: auto; }
.shots.phone .shot button { max-height: 560px; overflow-y: auto; }
.shot figcaption { font-size: .84rem; color: var(--ink-2); }
.step-body.wide { grid-template-columns: minmax(0, 1fr); }
.real-badge { font-size: .7rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--stock); }
#zoom { position: fixed; inset: 0; z-index: 50; background: rgba(10,14,20,.88); display: grid; place-items: center; padding: max(16px, env(safe-area-inset-top, 0px)) max(16px, env(safe-area-inset-right, 0px)) max(16px, env(safe-area-inset-bottom, 0px)) max(16px, env(safe-area-inset-left, 0px)); overflow: auto; cursor: zoom-out; }
#zoom img { max-width: min(100%, 1280px); height: auto; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,.5); }
#zoom p { color: #e8edf3; margin-top: 10px; text-align: center; font-size: .95rem; }
</style>

</head>
<body>
<div class="appbar">
  @auth
    <a href="{{ auth()->user()->can('kitchen.requests.create') ? route('kitchen.requests.index') : (auth()->user()->can('purchases.orders.receipt') ? route('purchases.deliveries') : route('dashboard-modern')) }}">← Retour à l'application</a>
    <a href="{{ route('dashboard-modern') }}">Accueil</a>
  @else
    <span></span>
    <a href="{{ route('login') }}">Se connecter à l'application →</a>
  @endauth
</div>
<style>
[data-role="cuisinier"] { --c: var(--demandeur); --c-soft: var(--demandeur-soft); }
[data-role="achat"]     { --c: var(--achat);     --c-soft: var(--achat-soft); }
[data-role="rh"]        { --c: var(--compta);    --c-soft: var(--compta-soft); }
.flow.b { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.wf-label { display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; }
.wf-label b { font-family: var(--f-display); font-size: 1.05rem; }
.rules { display: grid; gap: 8px; }
.rule { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 4px 12px; background: var(--surface); border: 1px solid var(--line); border-radius: var(--r); padding: 12px 14px; align-items: baseline; }
.rule .ok { color: var(--stock); font-weight: 800; } .rule .todo { color: var(--warn); font-weight: 800; }
.rule p { color: var(--ink-2); font-size: .93rem; grid-column: 2; }
.tag-directeur { --tagc: var(--compta); }
@media (max-width: 520px) { .flow.b { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>

<div class="wrap">

<header class="top">
  <p class="eyebrow">Complex Royal · Procédure de gestion des entrées, sorties et commandes de stock</p>
  <h1><span class="from">Du besoin</span> <span class="arrow">→</span> au stock, et du stock aux cuisines</h1>
  <p class="lede">Le stock de référence est le <b>Stock Catering</b>. Chaque produit est suivi depuis la demande d'achat jusqu'à sa réception, son stockage et sa remise aux cuisines. Ce guide montre, écran par écran, comment l'application applique la procédure : qui fait quoi, où cliquer, et ce qui change dans le stock.</p>
  <div class="role-bar" role="group" aria-label="Filtrer par rôle">
    <span class="eyebrow">Je suis</span>
    <button class="chip" data-filter="all" aria-pressed="true"><span class="dot"></span>Tout le monde</button>
    <button class="chip" data-filter="magasinier" data-role="magasinier" aria-pressed="false"><span class="dot"></span>Magasinier</button>
    <button class="chip" data-filter="achat" data-role="achat" aria-pressed="false"><span class="dot"></span>Responsable achat</button>
    <button class="chip" data-filter="cuisinier" data-role="cuisinier" aria-pressed="false"><span class="dot"></span>Cuisinier</button>
    <button class="chip" data-filter="rh" data-role="rh" aria-pressed="false"><span class="dot"></span>Responsable RH (validation, paiement)</button>
    <button class="chip" data-filter="admin" data-role="admin" aria-pressed="false"><span class="dot"></span>Administrateur</button>
  </div>
  <nav class="toc" aria-label="Sommaire">
    <a href="#circuit">Les deux circuits</a>
    <a href="#scenarios">Les scénarios</a>
    <a href="#documents">Documents</a>
    <a href="#emballages">Unité de base et emballage</a>
    <a href="#regles">Règles de contrôle</a>
    <a href="#statuts">Statuts</a>
    <a href="#roles">Qui fait quoi</a>
    <a href="#messages">Messages d'erreur</a>
    <a href="#glossaire">Glossaire</a>
  </nav>
</header>

<!-- ── 1. Circuits ─────────────────────────────────────────────── -->
<section id="circuit">
  <div class="head">
    <p class="eyebrow">1 · Vue d'ensemble</p>
    <h2>Les deux circuits du stock</h2>
    <p>Cliquez sur une étape pour voir qui la fait, où, et ce qui change ensuite. Seules deux étapes modifient le stock : la <b>réception (BE)</b>, qui le fait monter, et la <b>validation du cuisinier (BS)</b>, qui le fait baisser.</p>
  </div>
  <div class="wf-label"><b>Workflow A</b><span class="muted">Réapprovisionnement et entrée de stock</span></div>
  <div class="flow" id="flowA" role="tablist" aria-label="Workflow A"></div>
  <div class="wf-label"><b>Workflow B</b><span class="muted">Sortie de stock vers les cuisines</span></div>
  <div class="flow b" id="flowB" role="tablist" aria-label="Workflow B"></div>
  <div class="flow-detail" id="flowDetail" aria-live="polite"></div>
  <div class="shortcut">
    <span aria-hidden="true">⤷</span>
    <p><b>Achat du jour sans bon de commande</b> (marché, livraison immédiate) : le magasinier fait une <b>réception directe</b>. Le bon de commande est créé automatiquement, déjà reçu, et la comptabilité le retrouve pour le paiement.</p>
  </div>
</section>

<!-- ── 2. Scénarios ───────────────────────────────────────────── -->
<section id="scenarios">
  <div class="head">
    <p class="eyebrow">2 · Pas à pas, avec les écrans réels</p>
    <h2>Tous les scénarios, de A à Z</h2>
    <p>Le premier scénario est celui de la procédure, à présenter à la Direction. Les suivants couvrent les cas particuliers. Touchez une capture pour l'agrandir.</p>
  </div>
  <div class="player">
    <div class="sc-list" id="scList" role="tablist" aria-label="Scénarios"></div>
    <div class="sc-panel" id="scPanel"></div>
  </div>
</section>

<!-- ── 3. Documents ───────────────────────────────────────────── -->
<section id="documents">
  <div class="head">
    <p class="eyebrow">3 · Pièces justificatives</p>
    <h2>Les documents et leur place dans l'application</h2>
    <p>Aucun mouvement sans pièce numérotée. Chaque document papier correspond à un enregistrement dans l'application, avec son numéro.</p>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Document</th><th>Numéro</th><th>Créé par</th><th>Où dans l'application</th><th>Effet sur le stock</th></tr></thead>
      <tbody>
        <tr><td><b>DA</b> · Demande d'achat</td><td class="mono">DA-261115-AUTO…</td><td>Le système (brouillon), relue et envoyée par le magasinier</td><td><span class="path">Achats › Ruptures › Demande d'achat automatique</span> ou <span class="path">Achats › Demandes d'achat</span> (à la main)</td><td>Aucun</td></tr>
        <tr><td><b>BC</b> · Bon de commande</td><td class="mono">CMD-…</td><td>Responsable achat, après validation RH de la DA</td><td><span class="path">Page de la DA › Créer un Bon de Commande</span> (un par fournisseur), puis <b>Confirmer &amp; envoyer</b></td><td>Aucun</td></tr>
        <tr><td><b>BL</b> · Bon de livraison du fournisseur</td><td>N° du papier fournisseur</td><td>Fournisseur</td><td>Saisi dans le champ « N° du BL fournisseur » à la réception</td><td>—</td></tr>
        <tr><td><b>BE</b> · Bon d'entrée (réception)</td><td class="mono">REC-00042</td><td>Magasinier</td><td><span class="path">Réceptions (BL) › la commande › Valider la réception</span> · imprimable</td><td><b style="color:var(--stock)">+ entrée</b></td></tr>
        <tr><td><b>DC</b> · Demande de la cuisine</td><td class="mono">DC-00012</td><td>Cuisinier, ou magasinier pour le compte d'une cuisine</td><td><span class="path">Demandes au magasin › Nouvelle demande</span> ou <span class="path">Demandes des cuisines › Nouvelle sortie vers une cuisine</span></td><td>Aucun (validée par le cuisinier)</td></tr>
        <tr><td><b>BS</b> · Bon de sortie</td><td class="mono">BS-00012</td><td>Créé à la validation du cuisinier</td><td><span class="path">Demandes au magasin › Je confirme, je prends</span> · imprimable avec signatures</td><td><b style="color:var(--danger)">− sortie</b></td></tr>
        <tr><td><b>Fiche de stock</b></td><td>—</td><td>Automatique</td><td><span class="path">Stock › Tous les mouvements</span> (filtrer par produit) : entrées, sorties, ajustements</td><td>—</td></tr>
        <tr><td><b>Bon de retour</b> cuisine → magasin</td><td>—</td><td>—</td><td><i>Pas encore dans l'application</i></td><td>—</td></tr>
      </tbody>
    </table>
  </div>
</section>

<!-- ── 4. Unité de base / emballage ──────────────────────────── -->
<section id="emballages">
  <div class="head">
    <p class="eyebrow">4 · Distinction indispensable</p>
    <h2>Unité de base et emballage</h2>
    <p>L'<b>unité de base</b> sert au calcul du stock (g, mL, pièce). L'<b>emballage</b> décrit la forme achetée ou distribuée (sac, carton, bouteille). Chaque emballage indique sa contenance et peut avoir son propre code-barres.</p>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Produit</th><th>Unité de base</th><th>Emballage</th><th>Conversion</th></tr></thead>
      <tbody>
        <tr><td>Riz</td><td>g (affiché en kg)</td><td>Sac de 25 kg</td><td class="mono">1 sac = 25 000 g</td></tr>
        <tr><td>Spaghetti</td><td>g</td><td>Carton de 20 paquets de 500 g</td><td class="mono">1 carton = 10 000 g</td></tr>
        <tr><td>Vinaigre</td><td>mL (affiché en L)</td><td>Carton de 12 bouteilles de 1 L</td><td class="mono">1 carton = 12 000 mL</td></tr>
        <tr><td>Cuillères jetables</td><td>pièce</td><td>Carton de 40 paquets de 50</td><td class="mono">1 carton = 2 000 pcs</td></tr>
      </tbody>
    </table>
  </div>
  <div class="calc">
    <div class="panel">
      <h3>Exemple de la procédure : 10 sacs de riz commandés</h3>
      <div class="tree">
        <div class="lvl"><span class="ic">S</span><div><b>Sac de 25 kg</b><div class="code">1 sac = 25 000 g · code-barres du sac</div></div><span class="m-pill">emballage</span></div>
        <div class="lvl child"><span class="ic">g</span><div><b>Gramme</b><div class="code">unité de calcul du stock, affichée en kg</div></div><span class="m-pill">unité de base</span></div>
      </div>
      <p class="muted" style="font-size:.93rem">Règle : on n'enregistre jamais les 10 sacs avant de les avoir reçus. On saisit ce qui est réellement arrivé, et le reste devient un reliquat.</p>
    </div>
    <div class="panel" aria-live="polite">
      <h3>Simulation de la réception</h3>
      <p class="muted" style="font-size:.93rem">BC : <b>10 sacs</b> (250 kg). Le fournisseur livre :</p>
      <div class="num-row">
        <label for="calcSacs">Sacs reçus<small>scan du code du sac = +1</small></label>
        <div class="stepper-in"><button type="button" data-step="calcSacs" data-d="-1" aria-label="Moins un sac">−</button><input id="calcSacs" type="number" min="0" value="8" inputmode="numeric"><button type="button" data-step="calcSacs" data-d="1" aria-label="Plus un sac">+</button></div>
      </div>
      <div class="num-row">
        <label for="calcKg">Vrac en plus (kg)<small>sac entamé, pesé</small></label>
        <div class="stepper-in"><button type="button" data-step="calcKg" data-d="-1" aria-label="Moins un kilo">−</button><input id="calcKg" type="number" min="0" value="0" inputmode="decimal"><button type="button" data-step="calcKg" data-d="1" aria-label="Plus un kilo">+</button></div>
      </div>
      <div class="total">
        <span class="eyebrow">Bon d'entrée (BE)</span>
        <span class="big" id="calcTotal">200 kg</span>
        <span class="calc-line" id="calcLine"></span>
        <span class="status-pill" id="calcStatus"></span>
      </div>
    </div>
  </div>
</section>

<!-- ── 5. Règles ─────────────────────────────────────────────── -->
<section id="regles">
  <div class="head">
    <p class="eyebrow">5 · Contrôle</p>
    <h2>Règles de contrôle et comment l'application les applique</h2>
  </div>
  <div class="rules">
    <div class="rule"><span class="ok">✓</span><b>Une DA n'est pas une entrée.</b><p>La demande d'achat ne touche pas le stock.</p></div>
    <div class="rule"><span class="ok">✓</span><b>Une commande n'est pas une réception.</b><p>Le stock ne monte qu'à la validation de la réception (BE), avec la quantité réellement arrivée. Le reste devient un reliquat.</p></div>
    <div class="rule"><span class="ok">✓</span><b>Une demande cuisine n'est pas une sortie tant que les produits ne sont pas remis.</b><p>Que la demande vienne de la cuisine ou soit saisie par le magasinier, le stock ne baisse que lorsque le cuisinier valide « je prends ». Entre la remise et la validation, les quantités sont réservées.</p></div>
    <div class="rule"><span class="ok">✓</span><b>Aucun mouvement sans pièce numérotée.</b><p>Chaque entrée porte un REC (BE) et un n° de BL fournisseur ; chaque sortie cuisine un BS ; chaque ajustement un motif et son auteur.</p></div>
    <div class="rule"><span class="ok">✓</span><b>Stock négatif interdit.</b><p>Une remise ou une sortie supérieure au stock disponible est refusée.</p></div>
    <div class="rule"><span class="ok">✓</span><b>Alerte sur seuil minimum, avec demande d'achat préparée.</b><p>Chaque produit a un seuil, comparé au <b>Stock Catering</b> : <b>3 emballages d'achat par défaut</b> (3 sacs, 3 cartons…), ou un seuil précis fixé dans sa fiche. Dès qu'il est atteint, le système ajoute le produit à une demande d'achat brouillon (sauf s'il est déjà en commande). Le magasinier la relit et l'envoie.</p></div>
    <div class="rule"><span class="ok">✓</span><b>Tout ajustement est documenté et approuvé.</b><p>Seul l'administrateur peut corriger le stock (page Ajustement), avec un motif.</p></div>
    <div class="rule"><span class="ok">✓</span><b>Aucun achat sans double approbation.</b><p>Une DA du magasinier est approuvée par le responsable achat, puis validée par la responsable RH. Les bons de commande ne peuvent être créés qu'après cette validation. Le paiement est fait par la responsable RH.</p></div>
    <div class="rule"><span class="todo">…</span><b>Bon de retour cuisine et pertes/casses séparées.</b><p>Aujourd'hui, une perte ou casse se saisit comme sortie avec un motif (Scanner une sortie). Un bon de retour dédié reste à créer.</p></div>
  </div>
</section>

<!-- ── 6. Statuts ─────────────────────────────────────────────── -->
<section id="statuts">
  <div class="head">
    <p class="eyebrow">6 · Où en est mon document ?</p>
    <h2>Les statuts</h2>
  </div>
  <p class="eyebrow">Demande d'achat (DA)</p>
  <div class="statuses" style="grid-template-columns:repeat(4,minmax(0,1fr))">
    <div class="st"><span class="m-pill warn">À approuver (resp. achat)</span><p>Envoyée par le magasinier. Le responsable achat approuve ou refuse.</p></div>
    <div class="st"><span class="m-pill">À valider (RH)</span><p>Approuvée par le responsable achat (ou créée par lui). La responsable RH valide ou refuse.</p></div>
    <div class="st"><span class="m-pill info">Validée — à commander</span><p>Le responsable achat saisit les devis et crée les BC.</p></div>
    <div class="st"><span class="m-pill ok">Commandée</span><p>Partiellement ou entièrement transformée en BC. <span class="m-pill bad">Refusée</span> : avec un motif.</p></div>
  </div>
  <p class="eyebrow">Bon de commande</p>
  <div class="statuses">
    <div class="st"><span class="m-pill">En attente</span><p>Préparé par le responsable achat, pas encore envoyé. <b>Invisible pour le magasinier.</b></p></div>
    <div class="st"><span class="m-pill info">Commandée</span><p>Envoyée au fournisseur. Apparaît dans « Commandes à réceptionner ».</p></div>
    <div class="st"><span class="m-pill warn">Partielle</span><p>Une partie est arrivée ; le reste est un reliquat attendu.</p></div>
    <div class="st"><span class="m-pill ok">Reçue</span><p>Toutes les lignes sont livrées.</p></div>
    <div class="st"><span class="m-pill bad">Annulée</span><p>Abandonnée, seulement si rien n'a été reçu.</p></div>
  </div>
  <p class="eyebrow">Demande de la cuisine</p>
  <div class="statuses" style="grid-template-columns:repeat(4,minmax(0,1fr))">
    <div class="st"><span class="m-pill info">À préparer</span><p>Envoyée par le cuisinier. Apparaît chez le magasinier.</p></div>
    <div class="st"><span class="m-pill warn">À confirmer</span><p>Remise par le magasinier, quantités réservées. Le cuisinier doit valider.</p></div>
    <div class="st"><span class="m-pill ok">Sortie validée</span><p>Le cuisinier a pris : stock diminué, BS créé.</p></div>
    <div class="st"><span class="m-pill">Annulée</span><p>Annulée par la cuisine avant préparation.</p></div>
  </div>
</section>

<!-- ── 7. Rôles ───────────────────────────────────────────────── -->
<section id="roles">
  <div class="head">
    <p class="eyebrow">7 · Responsabilités</p>
    <h2>Qui fait quoi</h2>
    <p>La Direction peut tout consulter à tout moment : sorties par cuisine, commandes, reliquats et stock disponible.</p>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Action</th><th>Où</th><th>Magasinier</th><th>Resp. achat</th><th>Resp. RH</th><th>Cuisinier</th><th>Admin</th></tr></thead>
      <tbody id="rolesBody"></tbody>
    </table>
  </div>
</section>

<!-- ── 8. Messages ────────────────────────────────────────────── -->
<section id="messages">
  <div class="head">
    <p class="eyebrow">8 · Dépannage</p>
    <h2>Messages d'erreur et que faire</h2>
    <p>Ce sont les messages exacts affichés par l'application.</p>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Message</th><th>Pourquoi</th><th>Que faire</th></tr></thead>
      <tbody>
        <tr><td><span class="err">Stock insuffisant : Riz cassé : disponible 85 kg, remis 90 kg</span></td><td>Le magasinier remet plus que le stock libre (stock moins ce qui est déjà remis à d'autres cuisines).</td><td>Remettre moins, ou choisir un autre magasin. Créer une DA si le produit manque.</td></tr>
        <tr><td><span class="err">Cette demande n'est plus à confirmer.</span></td><td>Le cuisinier valide une demande déjà validée ou renvoyée.</td><td>Ouvrir « Mes demandes » pour voir son statut.</td></tr>
        <tr><td><span class="err">« Javel » ne fait pas partie de cette commande : faites une réception directe pour ce produit.</span></td><td>Produit livré qui n'est pas sur le BC.</td><td>Réceptionner le BC sans ce produit, puis faire une réception directe.</td></tr>
        <tr><td><span class="err">Confirmez d'abord la commande avant de la réceptionner.</span></td><td>Le BC est encore « En attente ».</td><td>Le responsable achat clique sur <b>Confirmer &amp; envoyer</b>.</td></tr>
        <tr><td><span class="err">Cette commande n'est plus à réceptionner.</span></td><td>Déjà entièrement reçue ou annulée.</td><td>Vérifier dans <span class="path">Réceptions (BL) › Dernières réceptions</span>.</td></tr>
        <tr><td><span class="err">Code inconnu · Demandez à un responsable de l'associer.</span></td><td>Code-barres relié à aucun produit.</td><td>Un responsable clique sur <b>Associer</b>, ou saisir la quantité à la main.</td></tr>
        <tr><td><span class="err">La facture doit d'abord être validée par la comptabilité (contrôle comptable).</span></td><td>Paiement avant le rapprochement BC + BE + facture.</td><td>Cliquer d'abord sur <b>Valider la facture</b>.</td></tr>
        <tr><td><span class="err">Seul un administrateur peut ajuster le stock.</span></td><td>La page Ajustement est réservée aux admins.</td><td>Les entrées passent par une réception BE ; les corrections par l'admin.</td></tr>
      </tbody>
    </table>
  </div>
  <div>
    <details class="faq"><summary>Le camion arrive mais la commande n'apparaît pas chez le magasinier</summary><div><p>Le BC n'est sans doute pas confirmé (« En attente »). Le responsable achat doit cliquer sur <b>Confirmer &amp; envoyer</b>. Pour un achat sans BC, faire une <b>réception directe</b>.</p></div></details>
    <details class="faq"><summary>Le cuisinier ne trouve pas le bouton « Je confirme, je prends »</summary><div><p>Le magasinier n'a pas encore remis la demande. Elle apparaît dans « À confirmer » dès que le magasinier a cliqué sur <b>Remettre à la cuisine</b>.</p></div></details>
    <details class="faq"><summary>Les quantités remises ne correspondent pas à ce que la cuisine a reçu</summary><div><p>Le cuisinier ne valide pas : il ouvre « Ce n'est pas correct », explique l'écart, et la demande revient chez le magasinier avec la remarque. Le stock ne bouge pas.</p></div></details>
    <details class="faq"><summary>J'ai validé une réception avec une erreur de quantité</summary><div><p>Une réception validée ne se modifie pas. Trop reçu : retour fournisseur (marchandise rendue) ou ajustement par l'administrateur (erreur de comptage). Pas assez : réceptionner le reliquat quand il arrive.</p></div></details>
    <details class="faq"><summary>Les feuilles manuscrites des cuisines, que faut-il en faire ?</summary><div><p>Le magasinier les saisit avec <b>Nouvelle sortie vers une cuisine</b> (une sortie par cuisine). Elles ne sont pas encore des sorties : la marchandise ne sort du stock que lorsque le cuisinier valide « je prends ».</p></div></details>
    <details class="faq"><summary>La caméra ne s'ouvre pas sur mon téléphone</summary><div><p>La caméra ne fonctionne qu'en <b>https://</b>. En attendant, tapez le code ou utilisez une douchette.</p></div></details>
  </div>
</section>

<!-- ── 9. Glossaire ───────────────────────────────────────────── -->
<section id="glossaire">
  <div class="head">
    <p class="eyebrow">9 · Vocabulaire</p>
    <h2>Glossaire</h2>
  </div>
  <div class="gloss">
    <div><b>DA · Demande d'achat</b><span>Besoin à commander : produit, quantité, motif. Ne crée rien en stock.</span></div>
    <div><b>DA automatique</b><span>Brouillon tenu à jour par le système avec les produits du Stock Catering arrivés à leur seuil. Le magasinier la relit et l'envoie.</span></div>
    <div><b>BC · Bon de commande</b><span>Préparé par le responsable achat pour un fournisseur, après validation RH de la DA. Référence <span class="mono">CMD-…</span>.</span></div>
    <div><b>BL · Bon de livraison</b><span>Le papier remis par le fournisseur avec la marchandise.</span></div>
    <div><b>BE · Bon d'entrée</b><span>La réception enregistrée par le magasinier (<span class="mono">REC-00042</span>). Fait monter le stock.</span></div>
    <div><b>DC · Demande de cuisine</b><span>Ce qu'une cuisine demande au magasin (<span class="mono">DC-00012</span>).</span></div>
    <div><b>BS · Bon de sortie</b><span>Créé quand le cuisinier valide (<span class="mono">BS-00012</span>). Fait baisser le stock.</span></div>
    <div><b>Reliquat</b><span>Ce qui reste à livrer quand une livraison est incomplète.</span></div>
    <div><b>Seuil minimum</b><span>Quantité sous laquelle le produit doit être recommandé. Par défaut : 3 emballages d'achat (3 sacs, 3 cartons…).</span></div>
    <div><b>Réservé</b><span>Quantité remise à une cuisine mais pas encore validée : elle n'est plus disponible pour les autres.</span></div>
    <div><b>Unité de base</b><span>L'unité du stock : g, mL ou pièce. Jamais « sac » ni « carton ».</span></div>
    <div><b>Réception directe</b><span>Entrée sans BC préalable (marché, achat du jour).</span></div>
    <div><b>Ajustement</b><span>Correction du stock ou stock initial, par l'administrateur.</span></div>
  </div>
</section>

<footer>Complex Royal · Procédure de gestion des entrées, sorties et commandes de stock · guide de l'application.</footer>
</div>

<script>
(function () {
  'use strict';
  const ROLE_LABEL = { magasinier: 'Magasinier', achat: 'Responsable achat', cuisinier: 'Cuisinier', rh: 'Responsable RH', admin: 'Administrateur' };
  const who = r => '<span class="who" data-role="' + r + '">' + ROLE_LABEL[r] + '</span>';
  const path = p => '<span class="path">' + p + '</span>';
  const IMG_BASE = @json(asset('guide/achats')) + '/';
  const escAttr = t => t.replace(/&/g, '&amp;').replace(/"/g, '&quot;');

  /* ── Workflows ─────────────────────────────────────────── */
  const FLOW_A = [
    { id: 'a1', t: 'Seuil atteint → DA automatique', role: 'magasinier', where: 'Automatique (Stock Catering)', does: 'Dès qu\'un produit du Stock Catering atteint son seuil (3 emballages par défaut), le système l\'ajoute à une demande d\'achat brouillon, avec la quantité proposée. Les produits déjà en commande ne sont pas ajoutés.', after: 'DA <span class="m-pill">Brouillon</span> · alerte sur l\'accueil du magasinier' },
    { id: 'a2', t: 'Demande d\'achat (DA)', role: 'magasinier', where: 'Demande d\'achat auto › Envoyer, ou Achats › Demandes d\'achat', does: 'Le magasinier relit et envoie la DA automatique (ou crée une DA à la main). Le responsable achat peut aussi créer une DA : elle est alors approuvée d\'office.', after: 'DA <span class="m-pill warn">À approuver (resp. achat)</span>' },
    { id: 'a3', t: 'Approbation · resp. achat', role: 'achat', where: 'Page de la DA › Approuver', does: 'Le responsable achat approuve la DA du magasinier, ou la refuse avec un motif.', after: 'DA <span class="m-pill">À valider (RH)</span>' },
    { id: 'a4', t: 'Validation · resp. RH', role: 'rh', key: true, where: 'Page de la DA › Valider la demande', does: 'La responsable RH valide la demande (ou la refuse). Aucun achat n\'est possible avant cette validation.', after: 'DA <span class="m-pill info">Validée — à commander</span>' },
    { id: 'a5', t: 'Devis et commandes (BC)', role: 'achat', where: 'Page de la DA › Ajouter un devis · Créer un Bon de Commande › Confirmer & envoyer', does: 'Le responsable achat saisit les devis (prix par fournisseur) et crée les BC : un par fournisseur si la demande est répartie. Il confirme chaque BC.', after: 'BC <span class="m-pill info">Commandée</span> · visible chez le magasinier' },
    { id: 'a6', t: 'Réception · BE', role: 'magasinier', key: true, where: 'Réceptions (BL) › la commande', does: 'Il compare BC, BL et marchandise, puis saisit ce qui est réellement arrivé (scan ou saisie, vrac pesé).', after: 'Stock <b style="color:var(--stock)">+</b> · BE REC-… · reliquat éventuel' },
    { id: 'a7', t: 'Facture et paiement', role: 'rh', where: 'Page du BC › Valider la facture › Enregistrer paiement', does: 'Le responsable achat apporte la facture ; la responsable RH la contrôle (BC + BE + facture) puis paie, en une ou plusieurs fois.', after: '<span class="m-pill warn">Partiel</span> puis <span class="m-pill ok">Payé</span>' },
    { id: 'a8', t: 'Retour fournisseur', role: 'achat', where: 'Page du BC › Retour fournisseur', does: 'Si besoin : marchandise abîmée ou erronée rendue.', after: 'Stock <b style="color:var(--danger)">−</b> · avoir fournisseur' },
  ];
  const FLOW_B = [
    { id: 'b1', t: 'Demande (cuisine ou magasin)', role: 'cuisinier', where: 'Cuisinier : Demandes au magasin › Nouvelle demande · ou magasinier : Stock › Demandes des cuisines › Nouvelle sortie vers une cuisine', does: 'Le cuisinier fait sa demande depuis son téléphone. Le magasinier peut aussi l\'initier lui-même (feuille manuscrite, cuisinier au comptoir) : il saisit ce qu\'il remet et la demande passe directement « à confirmer ».', after: 'DC <span class="m-pill info">À préparer</span> (ou <span class="m-pill warn">À confirmer</span> si saisie au magasin) · stock inchangé' },
    { id: 'b2', t: 'Préparation et remise', role: 'magasinier', where: 'Stock › Demandes des cuisines › Préparer', does: 'Le magasinier vérifie la disponibilité, choisit le magasin et saisit les quantités réellement remises.', after: 'DC <span class="m-pill warn">À confirmer</span> · quantités réservées' },
    { id: 'b3', t: 'Validation « je prends »', role: 'cuisinier', key: true, where: 'Demandes au magasin › À confirmer › Je confirme, je prends', does: 'Le cuisinier vérifie ce qu\'il reçoit et valide. En cas d\'écart, il renvoie la demande au magasinier avec une remarque.', after: 'Stock <b style="color:var(--danger)">−</b> · BS-… créé · alerte si sous le seuil' },
    { id: 'b4', t: 'Bon de sortie signé', role: 'magasinier', where: 'Demandes des cuisines › Dernières sorties › 🖨', does: 'Le BS s\'imprime avec les quantités demandées et remises, et les zones de signature magasinier et cuisinier.', after: 'Pièce papier archivée' },
  ];
  let flowSel = 'a4', roleFilter = 'all';
  function renderFlow() {
    const btn = s => '<button class="flow-step' + (s.key ? ' key' : '') + (roleFilter !== 'all' && s.role !== roleFilter ? ' dim' : '') + '" role="tab" data-role="' + s.role + '" data-n="' + s.id + '" aria-selected="' + (s.id === flowSel) + '">' +
      '<span class="n">' + s.id.toUpperCase() + '</span><span class="t">' + s.t + '</span><span class="bar"></span></button>';
    document.getElementById('flowA').innerHTML = FLOW_A.map(btn).join('');
    document.getElementById('flowB').innerHTML = FLOW_B.map(btn).join('');
    const s = FLOW_A.concat(FLOW_B).find(x => x.id === flowSel);
    document.getElementById('flowDetail').innerHTML =
      '<div style="display:grid;gap:12px;min-width:0"><p class="eyebrow">' + (s.id[0] === 'a' ? 'Workflow A' : 'Workflow B') + ' · étape ' + s.id.slice(1) + '</p><h3 style="font-size:1.35rem">' + s.t + '</h3><p>' + s.does + '</p></div>' +
      '<dl class="kv"><dt>Qui</dt><dd>' + who(s.role) + '</dd><dt>Où</dt><dd>' + path(s.where) + '</dd><dt>Ensuite</dt><dd>' + s.after + '</dd></dl>';
  }
  ['flowA', 'flowB'].forEach(id => document.getElementById(id).addEventListener('click', e => { const b = e.target.closest('[data-n]'); if (b) { flowSel = b.dataset.n; renderFlow(); } }));

  /* ── Scénarios ─────────────────────────────────────────── */
  // shots : [type, fichier, légende] — p = téléphone, d = ordinateur
  const SC = [
    {
      id: 'directeur', tag: 'directeur', title: 'Scénario complet de la procédure', sub: 'Cuisine TCN → rupture → DA → BC → 8 sacs sur 10',
      roles: ['cuisinier', 'magasinier', 'achat', 'rh'],
      when: 'Lundi matin, la Cuisine TCN demande 15 kg de riz et 5 L d\'huile pour 250 déjeuners. Après la sortie, le riz du Stock Catering passe sous son seuil : le système prépare tout seul la demande d\'achat, le magasinier la passe à 10 sacs de 25 kg et l\'envoie ; le fournisseur en livre 8. À tout moment, la Direction peut consulter la sortie, la commande, le reliquat et le stock.',
      steps: [
        { who: 'cuisinier', title: 'La cuisine demande', where: 'Demandes au magasin › Nouvelle demande', points: ['Cuisine : <b>Cuisine TCN</b>, pour aujourd\'hui, remarque « 250 déjeuners ».', 'Riz cassé : <b>15 kg</b> · Derwile (huile) : <b>5 L</b>.', '<b>Envoyer au magasin</b>.'], result: ['Demande DC-… « À préparer »', 'Stock inchangé'], shots: [['p', 'k-demande', 'Le cuisinier saisit sa demande sur son téléphone']] },
        { who: 'magasinier', title: 'Le magasinier prépare et remet', where: 'Stock › Demandes des cuisines › Préparer', points: ['La demande apparaît dans « À préparer » (compteur dans le menu).', 'Il choisit le magasin, voit le disponible, ajuste si besoin.', '<b>Remettre à la cuisine</b>.'], result: ['DC « À confirmer »', '15 kg de riz et 5 L d\'huile réservés', 'Stock toujours inchangé'], shots: [['d', 'k-file', 'File des demandes des cuisines'], ['d', 'k-preparer', 'Préparation : quantités remises et disponible']] },
        { who: 'cuisinier', title: 'Le cuisinier vérifie et prend', where: 'Demandes au magasin › À confirmer', points: ['Il compare les quantités remises avec ce qu\'il reçoit.', '<b>Je confirme, je prends</b>.'], result: ['Stock : riz 100 kg → <b>85 kg</b>, huile 20 L → 15 L', 'Bon de sortie <b>BS-…</b> créé', 'Message : « Sous le seuil minimum : Riz cassé »'], resultKind: 'warn', shots: [['p', 'k-confirmer', 'Avant : « Je confirme, je prends »'], ['p', 'k-validee', 'Après : sortie validée, BS créé, alerte de seuil']] },
        { who: 'magasinier', title: 'Bon de sortie signé', where: 'Demandes des cuisines › Dernières sorties validées › 🖨', points: ['Le BS s\'imprime avec les quantités demandées et remises.', 'Magasinier et cuisinier signent ; le papier est archivé.'], result: ['Pièce justificative BS-… archivée'], shots: [['d', 'k-bs', 'Bon de sortie imprimable avec signatures']] },
        { who: 'magasinier', title: 'Le système prépare la DA', where: 'Automatique · Accueil › Demande d\'achat prête', points: ['Seuil du riz : 90 kg ; Stock Catering : 85 kg.', 'Le système a déjà ajouté le riz à la <b>demande d\'achat automatique</b>, avec une quantité arrondie en sacs.', 'Le magasinier est prévenu sur son accueil ; la page Ruptures montre aussi ce qui est déjà en commande.'], result: ['DA brouillon prête, sans rien saisir'], resultKind: 'warn', shots: [['p', 'accueil-alertes', 'Accueil du magasinier : « Demande d\'achat prête »'], ['d', 'ruptures', 'Ruptures : riz sous le seuil, dans la demande automatique']] },
        { who: 'magasinier', title: 'Relire et envoyer la DA', where: 'Demande d\'achat automatique', points: ['La quantité se saisit <b>en emballages</b> : il tape <b>10</b> (sacs de 25 kg) et l\'écran affiche « = 250 kg ».', 'Les moitiés sont possibles : 10,5 sacs = 262,5 kg (boutons − / + de 0,5). Un produit sans emballage se saisit en kg, L ou pièces.', 'Il peut retirer un produit ou en corriger un autre.', '<b>Envoyer</b> : la DA part chez le responsable achat.'], result: ['DA-… « À approuver (resp. achat) »', 'Le riz compte désormais comme « en commande » : il ne sera pas redemandé', 'Stock inchangé (une DA n\'est pas une entrée)'], shots: [['d', 'da-auto', 'La demande d\'achat automatique, à relire'], ['d', 'da-envoyee', 'DA envoyée à la responsable RH']] },
        { who: 'achat', title: 'Approbation du responsable achat', where: 'Achats › Demandes d\'achat › la DA › Approuver', points: ['La DA envoyée par le magasinier apparaît sur l\'accueil du responsable achat : « demandes d\'achat à approuver ».', '<b>Approuver</b>, ou <b>Refuser</b> avec un motif.'], result: ['DA « À valider (RH) »'], shots: [['d', 'da-approuver', 'Le responsable achat approuve la DA']] },
        { who: 'rh', title: 'Validation de la responsable RH', where: 'Accueil › demandes d\'achat à valider › la DA › Valider la demande', points: ['Rien ne peut être acheté avant cette validation.', '<b>Valider la demande</b>, ou <b>Refuser</b> avec un motif.'], result: ['DA « Validée — à commander »'], shots: [['d', 'da-valider', 'La responsable RH valide la DA']] },
        { who: 'achat', title: 'Devis, bon de commande (BC) et envoi', where: 'Page de la DA › Ajouter un devis › Créer un Bon de Commande › Confirmer & envoyer', points: ['Il saisit les devis reçus (prix par fournisseur) ; le moins cher est mis en évidence.', 'Il crée le BC : fournisseur, 10 × Sac de 25 kg, prix. Une DA peut donner plusieurs BC, un par fournisseur.', '<b>Confirmer &amp; envoyer</b> : le BC passe chez le magasinier.'], result: ['BC « Commandée »', 'Visible dans « Commandes à réceptionner »'], shots: [['d', 'da-validee', 'DA validée : devis et bons de commande'], ['d', 'riz-bc', 'Le BC du riz avant envoi']] },
        { who: 'magasinier', title: 'Réception : 8 sacs sur 10 (BE)', where: 'Réceptions (BL) › la commande', points: ['Il compare BC, BL et marchandise : 8 sacs.', 'Tape <b>8</b> dans la case « Reçu » du sac (ou scanne 8 fois).', 'L\'écran affiche « Reliquat : 50 kg ». <b>Valider la réception</b>.'], result: ['Stock Catering riz : 85 kg → <b>285 kg</b>', 'BE REC-… avec le n° du BL', 'BC « Partielle » : 2 sacs en reliquat'], resultKind: 'warn', shots: [['p', 'riz-reception', '8 sacs reçus sur 10 : reliquat 50 kg']] },
        { who: 'rh', title: 'Facture, paiement et suivi', where: 'Achats › Commandes › le BC', points: ['Le BC montre la réception partielle et les 2 sacs attendus.', 'Le responsable achat apporte la facture ; la responsable RH rapproche BC + BE + facture, puis paie (en une ou plusieurs fois).', 'La Direction consulte à tout moment : sortie TCN (BS), commande, reliquat, stock.'], result: ['Traçabilité complète du besoin au stock'], shots: [['d', 'riz-bc-partielle', 'BC « Partielle » avec sa réception']] },
      ],
    },
    {
      id: 'magasin', tag: 'courant', title: 'Sortie initiée par le magasinier', sub: 'Feuille manuscrite ou cuisinier au comptoir',
      roles: ['magasinier', 'cuisinier'],
      when: 'La Cuisine TCN a remis sa liste sur papier. Le magasinier saisit lui-même la sortie ; le cuisinier doit quand même la valider pour qu\'elle sorte du stock.',
      steps: [
        { who: 'magasinier', title: 'Saisir la sortie', where: 'Stock › Demandes des cuisines › Nouvelle sortie vers une cuisine', points: ['Cuisine destinataire, magasin, remarque (« feuille manuscrite du matin »).', 'Chercher les produits et taper les quantités remises ; le disponible s\'affiche (stock moins ce qui est déjà réservé).', '<b>Remettre à la cuisine</b>.'], result: ['DC « À confirmer », marquée « saisie au magasin »', 'Quantités réservées, stock inchangé'], shots: [['d', 'k-sortie-magasin', 'Nouvelle sortie vers une cuisine'], ['d', 'k-file-magasin', 'La sortie attend la validation de la cuisine']] },
        { who: 'cuisinier', title: 'Le cuisinier valide', where: 'Demandes au magasin › À confirmer', points: ['La sortie apparaît avec « Saisie et remise par Magasinier ».', 'Il vérifie, puis <b>Je confirme, je prends</b> (ou « Ce n\'est pas correct »).'], result: ['Stock diminué, BS créé', 'Sans validation, rien ne sort du stock'], shots: [['p', 'k-a-confirmer-magasin', 'Chez le cuisinier : à confirmer'], ['p', 'k-confirmer', 'Je confirme, je prends']] },
        { who: 'magasinier', title: 'Annuler si besoin', where: 'Demandes des cuisines › Remises en attente › Annuler', points: ['Si la cuisine ne vient pas chercher la marchandise, le magasinier annule la remise.', 'Les quantités réservées redeviennent disponibles.'], result: ['Demande « Annulée », stock inchangé'], shots: [['d', 'k-file-magasin', 'Bouton « Annuler » sur une remise en attente']] },
      ],
    },
    {
      id: 'complet', tag: 'courant', title: 'Commande livrée en totalité', sub: 'Le cas le plus simple',
      roles: ['magasinier', 'achat', 'rh'],
      when: 'Une commande de Javel (cartons) et de poisson (vrac) arrive complète.',
      steps: [
        { who: 'magasinier', title: 'Demande d\'achat', where: 'Achats › Demandes d\'achat › Nouvelle demande', points: ['Produits et quantités en unité de base.', '<b>Créer la demande</b>.'], result: ['DA « En attente »'], shots: [['d', 'demande', 'Nouvelle demande d\'achat']] },
        { who: 'achat', title: 'BC et envoi', where: 'Page du BC › Confirmer & envoyer', points: ['Sans confirmation, le magasinier ne voit pas la commande.'], result: ['BC « Commandée »'], shots: [['d', 'bc-confirmer', 'BC en attente : « Confirmer & envoyer »']] },
        { who: 'magasinier', title: 'La livraison est attendue', where: 'Accueil (bannière verte) ou Réceptions (BL)', points: ['À la connexion, le magasinier arrive sur ses réceptions.', 'Recherche par n° de commande, fournisseur ou produit.'], result: ['Commande dans « Commandes à réceptionner »'], shots: [['p', 'receptions-liste', 'Réceptions (BL) : commandes attendues']] },
        { who: 'magasinier', title: 'Saisir et valider (BE)', where: 'Réceptions (BL) › la commande', points: ['N° du BL fournisseur en haut.', '<b>Tout reçu</b> si tout est arrivé, puis <b>Valider la réception</b>.'], result: ['Stock monté', 'BE REC-… imprimable', 'BC « Reçue »'], shots: [['p', 'reception-saisie', 'Après « Tout reçu » : tout est « Complet »'], ['d', 'reception-validee', 'BC « Reçue » avec sa réception']] },
        { who: 'rh', title: 'Facture et paiement', where: 'Page du BC › Valider la facture › Enregistrer paiement', points: ['Rapprochement BC + BE + facture.'], result: ['Payé'], shots: [['d', 'paiement', 'Enregistrer le paiement']] },
      ],
    },
    {
      id: 'partielle', tag: 'exception', title: 'Livraison incomplète (reliquat)', sub: 'Il manque une partie',
      roles: ['magasinier', 'achat'],
      when: '10 cartons commandés, 8 livrés. Les 2 autres arriveront plus tard.',
      steps: [
        { who: 'magasinier', title: 'Saisir seulement ce qui est arrivé', where: 'Réceptions (BL) › la commande', points: ['Taper 8 : l\'écran affiche « Reliquat ».', 'Valider : le BC passe « Partielle ».'], result: ['Stock + 8 cartons', 'BC « Partielle », reste dans la liste'], resultKind: 'warn', shots: [['p', 'reliquat-saisie', '8 cartons sur 10']] },
        { who: 'magasinier', title: 'Recevoir le reliquat', where: 'Réceptions (BL) › même commande (badge « Reliquat »)', points: ['La ligne indique « déjà reçu 8 · reste 2 ».'], result: ['BC « Reçue »'], shots: [['p', 'reliquat-suite', 'Prochaine livraison']] },
        { who: 'achat', title: 'Si le reliquat ne viendra pas', where: 'Contacter le fournisseur', points: ['Régler l\'écart avec la comptabilité au moment de la facture.', 'Ne pas annuler un BC déjà en partie reçu.'], result: ['À traiter au cas par cas'], resultKind: 'warn', shots: [['d', 'bc-partielle', 'BC « Partielle »']] },
      ],
    },
    {
      id: 'surplus', tag: 'exception', title: 'Vrac pesé : plus que commandé', sub: '10 kg commandés, 10,4 kg pesés',
      roles: ['magasinier'],
      when: 'Le poisson ne tombe jamais pile : la balance indique 10,4 kg.',
      steps: [
        { who: 'magasinier', title: 'Taper le poids réel', where: 'Réceptions (BL) › ligne Poisson', points: ['Saisir 10,4 kg : alerte orange « Surplus », pas un blocage.', 'Noter un éventuel problème dans « Remarque ».'], result: ['Stock + 10 400 g', 'Écart visible sur la réception'], resultKind: 'warn', shots: [['p', 'surplus', 'Surplus signalé et accepté']] },
      ],
    },
    {
      id: 'autrement', tag: 'exception', title: 'Livré dans un autre emballage', sub: 'Bouteilles au lieu de cartons',
      roles: ['magasinier'],
      when: 'Commandé en cartons ; livré 7 cartons et 24 bouteilles.',
      steps: [
        { who: 'magasinier', title: '« Livré autrement ? »', where: 'Sous la ligne de la commande', points: ['7 dans la case Carton, 24 dans la ligne Bouteille.', 'Le total se calcule en unité de base.'], result: ['Stock + 108 L', 'Détail sur le BE'], shots: [['p', 'autrement', '7 cartons + 24 bouteilles']] },
      ],
    },
    {
      id: 'directe', tag: 'courant', title: 'Achat du jour sans bon de commande', sub: 'Marché, achat rapide',
      roles: ['magasinier', 'rh'],
      when: 'Tomates et poisson achetés au marché le matin.',
      steps: [
        { who: 'magasinier', title: 'Ouvrir une réception directe', where: 'Réceptions (BL) › Réception directe', points: ['Fournisseur « Marché (divers) », stock, n° du ticket.'], result: ['Écran prêt'], shots: [['p', 'directe-entete', 'En-tête de la réception directe']] },
        { who: 'magasinier', title: 'Ajouter les produits', where: '« + Ajouter un produit »', points: ['Poids en kg et montant payé.'], result: ['Chaque produit avec sa quantité et son montant'], shots: [['p', 'directe-recherche', 'Recherche du produit'], ['p', 'directe-produit', 'Poids et montant']] },
        { who: 'magasinier', title: 'Valider', where: 'Valider la réception', points: ['Le BC est créé automatiquement, déjà « Reçu ».'], result: ['Stock monté', 'BC avec le total payé'], shots: [['d', 'directe-validee', 'BC créé automatiquement']] },
        { who: 'rh', title: 'Régulariser le paiement', where: 'Achats › Commandes › le BC', points: ['Comme une commande normale.'], result: ['Achat tracé et payé'], shots: [['d', 'paiement', 'Paiement']] },
      ],
    },
    {
      id: 'horsbc', tag: 'exception', title: 'Produit non commandé dans la livraison', sub: 'Le fournisseur ajoute un article',
      roles: ['magasinier'],
      when: 'Un carton de sel arrive sans être sur le BC.',
      steps: [
        { who: 'magasinier', title: 'Le scan est refusé', where: 'Réceptions (BL) › la commande', points: ['Alerte rouge : produit pas sur ce BC.', 'Réceptionner d\'abord ce qui était commandé.'], result: ['Le sel n\'est pas ajouté à ce BE'], resultKind: 'bad', shots: [['p', 'hors-commande', 'Produit hors commande refusé']] },
        { who: 'magasinier', title: 'Réception directe pour l\'article en plus', where: 'Réceptions (BL) › Réception directe', points: ['Même fournisseur, même n° de BL.'], result: ['Le sel entre avec son propre BE'], shots: [['p', 'directe-recherche', 'Réception directe']] },
      ],
    },
    {
      id: 'codebarre', tag: 'exception', title: 'Code-barres inconnu', sub: 'Nouvel article ou nouvel emballage',
      roles: ['magasinier', 'admin'],
      when: 'On scanne un carton dont le code n\'est relié à aucun produit.',
      steps: [
        { who: 'magasinier', title: '« Code inconnu »', where: 'Écran de réception ou de sortie', points: ['Bip grave et alerte rouge.', 'Saisir la quantité à la main et prévenir un responsable.'], result: ['Rien n\'est ajouté par le scan'], resultKind: 'bad', shots: [['p', 'code-inconnu', 'Vu par le magasinier']] },
        { who: 'admin', title: 'Associer le code', where: 'Panneau « Associer un code-barres »', points: ['Choisir le produit puis l\'emballage (ou en créer un).'], result: ['Code reconnu partout, définitivement'], shots: [['p', 'code-recherche', 'Choisir le produit'], ['p', 'code-associer', 'Choisir l\'emballage']] },
        { who: 'admin', title: 'Ou depuis la fiche produit', where: 'Achats › Produits › ✏️ Modifier', points: ['Un code par emballage, avec sa contenance.'], result: ['« contient 12 × Bouteille »'], shots: [['d', 'produit-codes', 'Codes-barres des emballages']] },
      ],
    },
    {
      id: 'contestation', tag: 'exception', title: 'Le cuisinier conteste la remise', sub: 'Écart entre remis et reçu',
      roles: ['cuisinier', 'magasinier'],
      when: 'La cuisine Pain demande 20 kg de farine ; le magasinier en remet 15, mais le cuisinier n\'en reçoit que 10.',
      steps: [
        { who: 'cuisinier', title: 'Ne pas valider, renvoyer', where: 'Demandes au magasin › la demande › « Ce n\'est pas correct »', points: ['Expliquer l\'écart : « Je n\'ai reçu que 10 kg ».', '<b>Renvoyer au magasinier</b>.'], result: ['Stock inchangé', 'Demande de nouveau « À préparer »'], resultKind: 'warn', shots: [['p', 'k-contester', 'Le cuisinier explique l\'écart']] },
        { who: 'magasinier', title: 'Corriger et remettre', where: 'Stock › Demandes des cuisines', points: ['La demande revient en rouge avec la remarque du cuisinier.', 'Corriger la quantité et remettre.'], result: ['Nouvelle remise à valider'], shots: [['d', 'k-contestee', 'La demande renvoyée, avec la remarque']] },
      ],
    },
    {
      id: 'seuil', tag: 'admin', title: 'Fixer les seuils minimum', sub: 'Pour être alerté avant la rupture',
      roles: ['admin', 'magasinier'],
      when: 'Par défaut, chaque produit a un seuil de 3 emballages d\'achat (riz : 3 sacs de 25 kg = 75 kg ; Javel : 3 cartons = 36 L ; sans emballage : 3 kg, 3 L ou 3 pièces). On peut fixer un seuil précis par produit (ex : riz 90 kg).',
      steps: [
        { who: 'admin', title: 'Seuil par défaut ou seuil précis', where: 'Achats › Produits › ✏️ Modifier › Seuil minimum', points: ['Champ vide : seuil par défaut de <b>3 emballages d\'achat</b> (le champ l\'indique : « vide = 3 × Carton de 15 L » pour l\'huile).', 'Pour un seuil précis, le saisir en kg pour les produits en grammes, en L pour les mL.'], result: ['Le produit est surveillé au Stock Catering'], shots: [['d', 'produit-seuil', 'Champ « Seuil minimum » : vide = seuil par défaut']] },
        { who: 'magasinier', title: 'Suivre les ruptures', where: 'Achats › Ruptures de stock', points: ['Rupture (stock 0) et sous le seuil, dans le Stock Catering.', 'Pour chaque produit : dans la demande automatique, ou déjà en commande.'], result: ['Rien à saisir : la DA est préparée par le système'], shots: [['d', 'ruptures', 'Page Ruptures']] },
      ],
    },
    {
      id: 'retour', tag: 'incident', title: 'Marchandise abîmée : retour fournisseur', sub: 'Après une réception',
      roles: ['achat'],
      when: 'Deux cartons reçus sont percés et rendus au fournisseur.',
      steps: [
        { who: 'achat', title: 'Créer le retour', where: 'Page du BC › Retour fournisseur', points: ['Quantités rendues et motif.'], result: ['Stock en baisse', 'Avoir fournisseur'], resultKind: 'warn', shots: [['d', 'retour', 'Retour fournisseur']] },
      ],
    },
    {
      id: 'perte', tag: 'incident', title: 'Perte, casse ou sortie exceptionnelle', sub: 'Hors demande de cuisine',
      roles: ['magasinier'],
      when: 'Un sac de farine est mouillé, ou une sortie exceptionnelle doit être enregistrée.',
      steps: [
        { who: 'magasinier', title: 'Scanner une sortie avec un motif', where: 'Stock › Scanner une sortie', points: ['Scanner ou taper le produit et la quantité.', 'Motif obligatoire dans la pratique : « casse », « périmé »…'], result: ['Sortie tracée avec son motif', 'Stock négatif refusé'], shots: [['p', 'sortie', 'Sortie scannée avec motif']] },
      ],
    },
    {
      id: 'initial', tag: 'admin', title: 'Stock initial et inventaire', sub: 'Réservé à l\'administrateur',
      roles: ['admin'],
      when: 'Au démarrage, ou quand l\'inventaire physique montre un écart.',
      steps: [
        { who: 'admin', title: 'Saisir le stock compté', where: 'Stock › Ajustement / Stock initial', points: ['Quantité réellement comptée par produit.', 'Motif : inventaire mensuel, casse…'], result: ['Mouvements « Stock initial » ou « Ajustement »'], shots: [['d', 'ajustement', 'Ajustement']] },
        { who: 'admin', title: 'Fiche de stock', where: 'Stock › Tous les mouvements', points: ['Filtrer par produit : entrées, sorties cuisine (BS), ajustements.'], result: ['Rapprochement stock théorique / physique'], shots: [['d', 'mouvements', 'Tous les mouvements']] },
      ],
    },
  ];

  let scSel = 'directeur', stepSel = 0;
  const listEl = document.getElementById('scList'), panelEl = document.getElementById('scPanel');
  const tagLabel = { directeur: 'À présenter', courant: 'Courant', exception: 'Exception', incident: 'Incident', admin: 'Administration' };
  function shotsHtml(list) {
    if (!list || !list.length) return '';
    const phone = list.every(x => x[0] === 'p');
    return '<div class="shots' + (phone ? ' phone' : '') + '"><span class="real-badge">Capture de l\'application</span>' + list.map(([, file, cap]) =>
      '<figure class="shot"><button type="button" data-zoom="' + IMG_BASE + file + '.jpg" data-cap="' + escAttr(cap) + '" aria-label="Agrandir : ' + escAttr(cap) + '">' +
      '<img src="' + IMG_BASE + file + '.jpg" alt="' + escAttr(cap) + '" loading="lazy"></button><figcaption>' + cap.replace(/&/g, '&amp;') + '</figcaption></figure>').join('') + '</div>';
  }
  function renderList() {
    listEl.innerHTML = SC.map(s =>
      '<button class="sc-btn tag-' + s.tag + (roleFilter !== 'all' && !s.roles.includes(roleFilter) ? ' dim' : '') + '" role="tab" data-id="' + s.id + '" aria-selected="' + (s.id === scSel) + '">' +
      '<span class="tag">' + tagLabel[s.tag] + '</span><span class="ttl">' + s.title + '</span><span class="sub">' + s.sub + '</span></button>').join('');
  }
  function renderPanel() {
    const s = SC.find(x => x.id === scSel), st = s.steps[stepSel];
    const kind = st.resultKind ? ' ' + st.resultKind : '';
    const wide = (st.shots || []).some(x => x[0] === 'd');
    panelEl.innerHTML =
      '<div class="sc-head"><span class="tag tag-' + s.tag + '">' + tagLabel[s.tag] + '</span><h3 style="font-size:1.4rem">' + s.title + '</h3><p class="when">' + s.when + '</p>' +
        '<div style="display:flex;flex-wrap:wrap;gap:6px">' + s.roles.map(who).join('') + '</div></div>' +
      '<div class="stepper" role="tablist" aria-label="Étapes">' + s.steps.map((x, i) =>
        '<button role="tab" data-step="' + i + '"' + (i === stepSel ? ' aria-current="step"' : '') + (i < stepSel ? ' class="done"' : '') + '><span class="k">' + (i + 1) + '</span>' + x.title + '</button>').join('') + '</div>' +
      '<div class="step-body' + (wide ? ' wide' : '') + '"><div class="step-text">' +
        '<div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center"><span class="eyebrow">Étape ' + (stepSel + 1) + ' / ' + s.steps.length + '</span>' + who(st.who) + '</div>' +
        '<h3>' + st.title + '</h3>' +
        '<p><span class="eyebrow">Où&nbsp;</span> ' + path(st.where) + '</p>' +
        '<ul>' + st.points.map(p => '<li>' + p + '</li>').join('') + '</ul>' +
        '<div class="result' + kind + '"><b>Résultat</b>' + st.result.map(r => '<span>' + r + '</span>').join('') + '</div>' +
      '</div>' + shotsHtml(st.shots) + '</div>' +
      '<div class="step-nav"><button class="btn" data-nav="-1"' + (stepSel === 0 ? ' disabled' : '') + '>← Précédent</button>' +
        '<button class="btn primary" data-nav="1"' + (stepSel === s.steps.length - 1 ? ' disabled' : '') + '>Étape suivante →</button></div>';
  }
  function pickScenario(id, fromHash) {
    if (!SC.some(s => s.id === id)) return;
    scSel = id; stepSel = 0; renderList(); renderPanel();
    if (!fromHash) { try { history.replaceState(null, '', '#scenario-' + id); } catch (e) {} }
  }
  listEl.addEventListener('click', e => { const b = e.target.closest('[data-id]'); if (b) pickScenario(b.dataset.id); });
  panelEl.addEventListener('click', e => {
    const z = e.target.closest('[data-zoom]');
    if (z) { openZoom(z.dataset.zoom, z.dataset.cap); return; }
    const st = e.target.closest('[data-step]'), nav = e.target.closest('[data-nav]');
    const n = SC.find(x => x.id === scSel).steps.length;
    if (st) stepSel = +st.dataset.step;
    else if (nav && !nav.disabled) stepSel = Math.max(0, Math.min(n - 1, stepSel + +nav.dataset.nav));
    else return;
    renderPanel();
  });

  /* ── Agrandir une capture ──────────────────────────────── */
  const zoom = document.createElement('div');
  zoom.id = 'zoom'; zoom.hidden = true; zoom.setAttribute('role', 'dialog'); zoom.setAttribute('aria-label', 'Capture agrandie');
  zoom.innerHTML = '<div><img alt=""><p></p></div>';
  document.body.appendChild(zoom);
  function openZoom(src, cap) {
    zoom.querySelector('img').src = src; zoom.querySelector('img').alt = cap;
    zoom.querySelector('p').textContent = cap + ' — touchez pour fermer';
    zoom.hidden = false;
  }
  zoom.addEventListener('click', () => { zoom.hidden = true; });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') zoom.hidden = true; });

  /* ── Qui fait quoi ─────────────────────────────────────── */
  const ROWS = [
    ['Relire et envoyer la DA automatique', 'Achats › Ruptures › Demande d\'achat automatique', ['magasinier', 'admin']],
    ['Demande d\'achat à la main', 'Achats › Demandes d\'achat', ['magasinier', 'achat', 'admin']],
    ['Approuver une DA du magasinier', 'Page de la DA › Approuver', ['achat', 'admin']],
    ['Valider une DA (avant tout achat)', 'Page de la DA › Valider la demande', ['rh', 'admin']],
    ['Devis fournisseurs', 'Page de la DA › Ajouter un devis', ['achat', 'admin']],
    ['Bon de commande (BC) et envoi', 'Achats › Commandes', ['achat', 'admin']],
    ['Réception (BE), avec ou sans BC', 'Réceptions (BL)', ['magasinier', 'achat', 'admin']],
    ['Demande au magasin', 'Demandes au magasin', ['cuisinier', 'admin']],
    ['Saisir une sortie pour une cuisine (feuille papier)', 'Stock › Demandes des cuisines › Nouvelle sortie', ['magasinier', 'admin']],
    ['Préparer et remettre aux cuisines', 'Stock › Demandes des cuisines', ['magasinier', 'admin']],
    ['Valider « je prends » (BS)', 'Demandes au magasin › À confirmer', ['cuisinier', 'admin']],
    ['Perte / casse / sortie exceptionnelle', 'Stock › Scanner une sortie', ['magasinier', 'admin']],
    ['Rapprocher la facture', 'Page du BC › Valider la facture', ['rh', 'admin']],
    ['Payer le fournisseur (total ou partiel)', 'Page du BC › Enregistrer paiement', ['rh', 'admin']],
    ['Retour fournisseur', 'Page du BC', ['achat', 'admin']],
    ['Produits, emballages, codes-barres, seuils', 'Achats › Produits', ['admin']],
    ['Stock initial / inventaire', 'Stock › Ajustement', ['admin']],
  ];
  const COLS = ['magasinier', 'achat', 'rh', 'cuisinier', 'admin'];
  function renderRoles() {
    document.getElementById('rolesBody').innerHTML = ROWS.map(([a, w, r]) =>
      '<tr' + (roleFilter !== 'all' && !r.includes(roleFilter) ? ' style="opacity:.4"' : '') + '><td><b>' + a + '</b></td><td>' + path(w) + '</td>' +
      COLS.map(c => r.includes(c) ? '<td class="yes">✓</td>' : '<td class="no">—</td>').join('') + '</tr>').join('');
  }

  /* ── Filtre par rôle ───────────────────────────────────── */
  document.querySelector('.role-bar').addEventListener('click', e => {
    const b = e.target.closest('[data-filter]');
    if (!b) return;
    roleFilter = b.dataset.filter;
    document.querySelectorAll('.role-bar [data-filter]').forEach(x => x.setAttribute('aria-pressed', x === b));
    try { localStorage.setItem('guideStockRole', roleFilter); } catch (err) {}
    const firstFlow = FLOW_A.concat(FLOW_B).find(s => roleFilter === 'all' || s.role === roleFilter);
    if (firstFlow && roleFilter !== 'all') flowSel = firstFlow.id;
    renderFlow(); renderRoles();
    const first = SC.find(s => roleFilter === 'all' || s.roles.includes(roleFilter));
    if (first && roleFilter !== 'all' && !SC.find(s => s.id === scSel).roles.includes(roleFilter)) pickScenario(first.id);
    else renderList();
  });

  /* ── Simulation riz ────────────────────────────────────── */
  const nf = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 });
  function calc() {
    const sacs = Math.max(0, parseInt(document.getElementById('calcSacs').value, 10) || 0);
    const kg = Math.max(0, parseFloat(document.getElementById('calcKg').value) || 0);
    const g = sacs * 25000 + kg * 1000, ordered = 250000;
    document.getElementById('calcTotal').textContent = nf.format(g / 1000) + ' kg';
    document.getElementById('calcLine').textContent = sacs + ' × 25 000 g + ' + nf.format(kg * 1000) + ' g = ' + nf.format(g) + ' g';
    const pill = document.getElementById('calcStatus');
    if (g === 0) { pill.className = 'status-pill none'; pill.textContent = 'À recevoir : 250 kg'; }
    else if (g === ordered) { pill.className = 'status-pill full'; pill.textContent = '✓ Complet'; }
    else if (g < ordered) { pill.className = 'status-pill partial'; pill.textContent = 'Reliquat : ' + nf.format((ordered - g) / 1000) + ' kg (' + nf.format((ordered - g) / 25000) + ' sac(s)) restent à livrer'; }
    else { pill.className = 'status-pill over'; pill.textContent = '⚠ Surplus +' + nf.format((g - ordered) / 1000) + ' kg (accepté, signalé)'; }
  }
  document.querySelectorAll('.stepper-in button').forEach(btn => btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.step);
    input.value = Math.max(0, (parseFloat(input.value) || 0) + +btn.dataset.d);
    calc();
  }));
  ['calcSacs', 'calcKg'].forEach(id => document.getElementById(id).addEventListener('input', calc));

  /* ── Démarrage ─────────────────────────────────────────── */
  try {
    const r = localStorage.getItem('guideStockRole');
    if (r && document.querySelector('.role-bar [data-filter="' + r + '"]')) {
      roleFilter = r;
      document.querySelectorAll('.role-bar [data-filter]').forEach(x => x.setAttribute('aria-pressed', x.dataset.filter === r));
    }
  } catch (e) {}
  const fromHash = () => {
    const h = location.hash.replace('#', '');
    if (h.startsWith('scenario-')) { pickScenario(h.slice(9), true); const sec = document.getElementById('scenarios'); if (sec) sec.scrollIntoView(); }
  };
  renderFlow(); renderList(); renderPanel(); renderRoles(); calc();
  fromHash();
  window.addEventListener('hashchange', fromHash);
})();
</script>
</body>
</html>
