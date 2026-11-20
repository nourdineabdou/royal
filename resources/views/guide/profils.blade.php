<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
{{-- Guide par profil : ce que chaque utilisateur peut faire dans le système, avec les vraies captures de ses écrans. --}}
<title>Guide stock et achats — Complex Royal</title>
<link rel="icon" href="{{ asset('royal_complex_groupe.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Source+Sans+3:wght@400;600;700&display=swap">
<style>
:root {
  --bg: #f6f7f9; --surface: #fff; --surface-2: #eef1f5; --ink: #17202c; --ink-2: #4a5566; --ink-3: #7b8696; --line: #dde2ea;
  --mag: #0d7a55; --mag-soft: #dcf2e8;
  --ach: #c8561f; --ach-soft: #fbeadf;
  --resp: #5b4bc4; --resp-soft: #e9e6fb;
  --dg: #1f6fb2; --dg-soft: #e0edf8;
  --cui: #b7791f; --cui-soft: #fbf0d9;
  --proc: #c8561f; --proc-soft: #fbeadf;
  --no: #c03535; --no-soft: #fbe3e3;
  --focus: #1f6fb2;
  --shadow: 0 1px 2px rgba(23,32,44,.06), 0 8px 24px rgba(23,32,44,.06);
  --f-display: "Bricolage Grotesque", "Segoe UI", system-ui, sans-serif;
  --f-body: "Source Sans 3", "Segoe UI", system-ui, sans-serif;
}
@media (prefers-color-scheme: dark) {
  :root:not([data-theme="light"]) {
    --bg: #11161d; --surface: #18202a; --surface-2: #202a36; --ink: #e8edf3; --ink-2: #b2bccb; --ink-3: #8592a4; --line: #2c3746;
    --mag: #3fc893; --mag-soft: #15342a; --ach: #f08a52; --ach-soft: #3a2519; --resp: #a79cf5; --resp-soft: #2a2550;
    --dg: #6cb2ec; --dg-soft: #172d42; --cui: #e8b04f; --cui-soft: #3a2f17; --proc: #f08a52; --proc-soft: #3a2519; --no: #f07a7a; --no-soft: #3d1d1d; --focus: #6cb2ec;
    --shadow: 0 1px 2px rgba(0,0,0,.3), 0 8px 24px rgba(0,0,0,.25); color-scheme: dark;
  }
}
:root[data-theme="dark"] {
  --bg: #11161d; --surface: #18202a; --surface-2: #202a36; --ink: #e8edf3; --ink-2: #b2bccb; --ink-3: #8592a4; --line: #2c3746;
  --mag: #3fc893; --mag-soft: #15342a; --ach: #f08a52; --ach-soft: #3a2519; --resp: #a79cf5; --resp-soft: #2a2550;
  --dg: #6cb2ec; --dg-soft: #172d42; --cui: #e8b04f; --cui-soft: #3a2f17; --proc: #f08a52; --proc-soft: #3a2519; --no: #f07a7a; --no-soft: #3d1d1d; --focus: #6cb2ec;
  --shadow: 0 1px 2px rgba(0,0,0,.3), 0 8px 24px rgba(0,0,0,.25); color-scheme: dark;
}
* { box-sizing: border-box; }
body { margin: 0; background: var(--bg); color: var(--ink); font-family: var(--f-body); font-size: 16px; line-height: 1.55;
  padding: 0 max(16px, env(safe-area-inset-right, 0px)) 56px max(16px, env(safe-area-inset-left, 0px)); }
.wrap { max-width: 1120px; margin: 0 auto; }
h1, h2, h3 { font-family: var(--f-display); line-height: 1.15; margin: 0; text-wrap: balance; }
p { margin: 0; }
button { font: inherit; color: inherit; cursor: pointer; }
:focus-visible { outline: 2px solid var(--focus); outline-offset: 2px; border-radius: 6px; }
img { max-width: 100%; }

header.top { display: flex; align-items: center; gap: 14px; padding: 22px 0 18px; border-bottom: 1px solid var(--line); }
header.top img { height: 44px; width: auto; border-radius: 8px; background: #fff; padding: 4px; }
header.top h1 { font-size: clamp(1.35rem, 3vw, 1.8rem); font-weight: 800; }
header.top p { color: var(--ink-2); font-size: .95rem; }
.theme { margin-left: auto; border: 1px solid var(--line); background: var(--surface); border-radius: 999px; padding: 6px 12px; font-size: .85rem; }

.intro { margin: 22px 0 14px; color: var(--ink-2); max-width: 70ch; }

/* Choix du profil */
.profiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px; margin-bottom: 26px; }
.pcard { text-align: left; background: var(--surface); border: 2px solid var(--line); border-radius: 12px; padding: 12px 14px; box-shadow: var(--shadow);
  display: grid; gap: 2px; transition: border-color .15s, transform .15s; }
.pcard:hover { transform: translateY(-1px); }
.pcard .who { font-weight: 700; font-size: 1.05rem; }
.pcard .role { font-size: .85rem; color: var(--ink-2); }
.pcard .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 6px; background: var(--c); }
.pcard[aria-pressed="true"] { border-color: var(--c); background: var(--c-soft); }

/* Circuit */
.circuit { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin: 4px 0 26px; font-size: .86rem; }
.circuit .st { border: 1px solid var(--line); background: var(--surface); border-radius: 999px; padding: 4px 11px; color: var(--ink-3); }
.circuit .st.me { background: var(--c); border-color: var(--c); color: var(--surface); font-weight: 700; }
.circuit .arr { color: var(--ink-3); }

/* En-tête du profil */
.phead { background: var(--surface); border-radius: 14px; box-shadow: var(--shadow); padding: 20px; border-top: 5px solid var(--c); margin-bottom: 26px; }
.phead h2 { font-size: clamp(1.4rem, 3vw, 1.9rem); font-weight: 800; }
.phead .sub { color: var(--ink-2); margin-top: 4px; }
.login { margin-top: 10px; font-size: .9rem; background: var(--surface-2); display: inline-block; padding: 6px 10px; border-radius: 8px; }
.cando { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px; }
.cando ul { margin: 6px 0 0; padding-left: 0; list-style: none; display: grid; gap: 5px; }
.cando li { padding-left: 24px; position: relative; }
.cando .yes li::before { content: "✓"; position: absolute; left: 2px; color: var(--mag); font-weight: 800; }
.cando .no li::before { content: "✕"; position: absolute; left: 2px; color: var(--no); font-weight: 800; }
.cando h3 { font-size: .8rem; letter-spacing: .08em; text-transform: uppercase; color: var(--ink-3); }

/* Fiches */
.step { display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); gap: 22px; background: var(--surface); border-radius: 14px; box-shadow: var(--shadow); padding: 20px; margin-bottom: 18px; }
.step .num { display: inline-grid; place-items: center; width: 30px; height: 30px; border-radius: 50%; background: var(--c); color: var(--surface); font-weight: 800; margin-bottom: 8px; }
.step h3 { font-size: 1.2rem; }
.where { margin: 8px 0 10px; font-size: .88rem; color: var(--ink-2); background: var(--surface-2); border-radius: 8px; padding: 6px 10px; }
.where b { color: var(--ink); }
.step ol { margin: 0; padding-left: 20px; display: grid; gap: 6px; }
.result { margin-top: 12px; border-left: 4px solid var(--c); background: var(--c-soft); padding: 8px 12px; border-radius: 6px; font-size: .93rem; }
.shots { display: grid; gap: 12px; align-content: start; }
.shots.two { grid-template-columns: 1fr 1fr; }
.shot { margin: 0; }
.shot button { padding: 0; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; display: block; width: 100%; background: var(--surface-2); }
.shot.phone button { max-width: 300px; margin: 0 auto; max-height: 560px; overflow: hidden; }
.shot img { display: block; width: 100%; height: auto; }
.shot figcaption { font-size: .82rem; color: var(--ink-3); margin-top: 5px; text-align: center; }

#zoom { position: fixed; inset: 0; background: rgba(8,12,18,.88); display: none; place-items: center; padding: 16px; z-index: 50; overflow: auto; }
#zoom.on { display: grid; }
#zoom img { max-width: min(100%, 1366px); border-radius: 10px; }
#zoom p { color: #fff; text-align: center; margin-top: 8px; }

.rules { width: 100%; border-collapse: collapse; margin-top: 14px; font-size: .92rem; }
.rules th, .rules td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--line); vertical-align: top; }
.rules th { font-size: .75rem; letter-spacing: .06em; text-transform: uppercase; color: var(--ink-3); }
.rules-wrap { overflow-x: auto; }
.who-chips { display: flex; flex-wrap: wrap; gap: 6px; margin: 2px 0 6px; }
.chip { font-size: .78rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; background: var(--k-soft); color: var(--k); border: 1px solid var(--k); }
.pcard.proc .who { font-size: 1.1rem; }
footer { margin-top: 30px; color: var(--ink-3); font-size: .85rem; text-align: center; }

@media (max-width: 820px) {
  .step { grid-template-columns: 1fr; }
  .cando { grid-template-columns: 1fr; }
}
@media (max-width: 480px) { .shots.two { grid-template-columns: 1fr; } }
@media print {
  .profiles, .theme, #zoom { display: none !important; }
  .step { break-inside: avoid; box-shadow: none; border: 1px solid #ccc; }
}
</style>
</head>
<body>
<div class="wrap">
  <header class="top">
    <img src="{{ asset('royal_complex_groupe.png') }}" alt="Royal Complex Group">
    <div>
      <h1>Guide stock et achats</h1>
      <p>Le processus d’achat complet, et ce que chacun fait dans le système</p>
    </div>
    <button class="theme" id="themeBtn" type="button" aria-label="Changer le thème">◐ Thème</button>
  </header>

  <p class="intro">Commencez par <b>Processus complet</b> pour voir tout le circuit, puis choisissez votre profil. Vous verrez ce que vous pouvez faire, où cliquer et à quoi ressemble votre écran. Les images sont de vraies captures du système, prises en étant connecté avec chaque utilisateur. Cliquez sur une image pour l'agrandir.</p>

  <nav class="profiles" id="profiles" aria-label="Profils"></nav>
  <div class="circuit" id="circuit" aria-label="Circuit"></div>
  <main id="content"></main>

  <footer>Complex Royal · Guide mis à jour le {{ now()->format('d/m/Y') }}</footer>
</div>

<div id="zoom" role="dialog" aria-modal="true" aria-label="Capture agrandie"><div><img alt=""><p></p></div></div>

<script>
const IMG = @json(asset('guide/profils')) + '/';

// Le circuit complet, dans l'ordre : chaque étape appartient à un profil.
const CIRCUIT = [
  ['cuisinier', 'Demande de la cuisine'],
  ['magasinier', 'Sortie + bon de sortie'],
  ['magasinier', "Demande d'achat"],
  ['acheteur', 'Approbation + devis'],
  ['responsable', 'Validation de la DA'],
  ['acheteur', 'Bon de commande'],
  ['responsable', 'Confirmation du BC'],
  ['magasinier', 'Réception BL'],
  ['responsable', 'Contrôle facture'],
  ['direction', 'Paiement'],
];

// ── Le processus d'achat complet, de bout en bout (qui fait quoi, dans l'ordre) ──
const PROCESS = {
  title: "Le processus d'achat complet",
  sub: "De la demande de la cuisine jusqu'au paiement du fournisseur : chaque étape, qui la fait, où cliquer, et ce qui se passe ensuite. Le stock ne bouge qu'à deux moments : quand le cuisinier confirme une sortie (il baisse) et quand le magasinier réceptionne une livraison (il monte).",
  rules: [
    ["Demande d'achat (DA)", "Sambou (magasinier) ou Youba (acheteur) — page « Demande d'achat auto »", "DA de Sambou : Youba approuve, puis Tandia valide. DA de Youba : Tandia valide. DA de Tandia : validée d'office."],
    ['Devis', 'Youba : il va chez les fournisseurs et saisit les prix', '—'],
    ['Bon de commande (BC)', 'Youba (fournisseur, prix, devis joint) ou Tandia', 'BC de Youba : Tandia le confirme. BC de Tandia : confirmé d\'office.'],
    ['Réception (BL)', 'Sambou. En son absence : Abdel Rahim (noté « en remplacement »)', '— (la réception fait monter le stock)'],
    ['Sortie vers la cuisine', 'Sambou prépare et choisit le cuisinier qui reçoit', 'Seul ce cuisinier confirme (« Je prends ») : le stock baisse.'],
    ['Facture', '—', 'Tandia la contrôle (BC + réception + facture).'],
    ['Paiement', 'Abdel Rahim (Direction)', '—'],
    ['Stock initial', 'Un super-admin (Abdel Rahim…) : Stock › Stock initial', 'Sans commande ni réception, tracé à son nom.'],
  ],
  steps: [
    { who: ['direction'], t: 'Au démarrage : le stock initial', where: '<b>Stock</b> › <b>Stock initial</b> (super-admin)', pts: ['Une seule fois, ou après un inventaire : cherchez le produit, tapez ce qu\'il y a dans le magasin en sacs, cartons… ou en vrac, ajoutez à la liste, <b>Enregistrer</b>.', 'Le Stock Catering est proposé par défaut. La quantité saisie <b>devient</b> le stock.'], res: 'Pas de commande ni de réception : le mouvement « Stock initial » est tracé au nom du super-admin.', shots: [['proc-stock-initial', 'Stock initial : 3 sacs + 10 kg en vrac = 85 kg']] },
    { who: ['cuisinier'], t: 'La cuisine demande au magasin', where: 'Téléphone du cuisinier › <b>Demandes au magasin</b> › <b>Nouvelle demande</b>', pts: ['Fatimatou ou Boris choisit les produits et les quantités, puis <b>Envoyer au magasin</b>.', 'Le cuisinier peut aussi venir au comptoir : c\'est alors Sambou qui crée la sortie (étape suivante).'], res: 'La demande arrive chez Sambou, « À préparer ». Le stock ne bouge pas.', shots: [['cui-nouvelle', 'Nouvelle demande', 'p']] },
    { who: ['magasinier'], t: 'Le magasinier prépare la sortie', where: '<b>Stock</b> › <b>Demandes des cuisines</b> › Préparer · ou <b>Nouvelle sortie vers une cuisine</b>', pts: ['Il choisit le magasin et le <b>cuisinier qui reçoit</b> (obligatoire).', 'Produits : par <b>scan</b> (caméra, douchette) ou ligne par ligne, en emballages.', '<b>Remettre à la cuisine</b>.'], res: 'Les quantités sont réservées. Seul le cuisinier choisi voit la sortie à confirmer.', shots: [['mag-preparer', 'Préparation : cuisinier qui reçoit + quantités'], ['mag-sortie', 'Sortie saisie au magasin : scan et lignes']] },
    { who: ['cuisinier', 'magasinier'], t: 'Le cuisinier confirme, le bon de sortie est signé', where: 'Cuisinier : demande « À confirmer » › <b>Je confirme, je prends</b> · Magasinier : 🖨 bon de sortie', pts: ['Le cuisinier vérifie ce qu\'il reçoit et confirme. Sinon il conteste : la sortie revient au magasinier.', 'Sambou imprime le bon de sortie : les deux noms sont déjà sous les signatures.'], res: '<b>Le stock baisse.</b> Si un produit passe sous son seuil, il est ajouté tout seul à la demande d\'achat automatique.', shots: [['cui-confirmer', 'Je confirme, je prends', 'p'], ['mag-bs', 'Bon de sortie signé par les deux']] },
    { who: ['magasinier', 'acheteur'], t: "La demande d'achat (DA)", where: '<b>Achats</b> › <b>Demande d\'achat auto</b>', pts: ['Le système y met les produits sous le seuil, en emballages (ex. 4 × Sac de 25 kg).', 'Sambou ou Youba peuvent <b>ajouter d\'autres produits</b> en bas de la page ; sans brouillon, le premier ajout le crée.', 'Si rien n\'est préparé, un bandeau dit pourquoi (ex. produits déjà dans une DA validée, en attente du bon de commande).', '<b>Envoyer pour approbation</b>.'], res: 'Envoyée par Sambou : Youba l\'approuve. Envoyée par Youba : elle part directement chez Tandia.', shots: [['ach-da-auto', 'Demande d\'achat : produits proposés et ajoutés à la main']] },
    { who: ['acheteur'], t: 'Approbation et devis', where: 'Page de la DA › <b>Approuver (acheteur)</b> · <b>Imprimer</b> · <b>Ajouter un devis</b>', pts: ['Youba approuve (ou refuse avec un motif) la DA de Sambou.', 'Il imprime la DA (emballages, sans prix) et va chez les fournisseurs.', 'Il saisit un devis par fournisseur, prix <b>par emballage</b> : le moins cher est mis en évidence.'], shots: [['ach-da-approuver', 'DA à approuver'], ['ach-devis-compare', 'Devis comparés']] },
    { who: ['responsable'], t: 'Tandia valide la DA', where: 'Accueil › bandeau « demandes d\'achat à valider » › <b>Valider la demande</b>', pts: ['Rien ne peut être commandé avant cette validation.', 'Une DA créée par Tandia lui-même est validée d\'office.'], shots: [['resp-da-valider', 'DA à valider']] },
    { who: ['acheteur', 'responsable'], t: 'Le bon de commande (BC)', where: 'Page de la DA validée › <b>Créer un Bon de Commande</b>', pts: ['Fournisseur (le moins cher des devis est proposé), emballages, quantités, prix, photo du devis.', 'Créé par <b>Youba</b> : le BC attend la <b>confirmation de Tandia</b> (bandeau « bons de commande à confirmer » sur son accueil).', 'Créé par <b>Tandia</b> : il est <b>confirmé d\'office</b>.', 'Le BC s\'imprime pour le fournisseur : quantité en emballages (ex. 4 × Sac de 25 kg) et prix par emballage.'], res: 'Une fois confirmé, le BC passe chez le magasinier dans « Commandes à réceptionner ».', shots: [['resp-bc-creer', 'Créer le bon de commande'], ['resp-bc-imprime', 'BC imprimé pour le fournisseur']] },
    { who: ['magasinier'], t: 'La réception de la livraison (BL)', where: '<b>Réceptions (BL)</b> › la commande', pts: ['Sambou compare le BC, le bon de livraison du fournisseur et la marchandise.', 'Il saisit les quantités reçues en emballages (ou <b>Tout reçu</b>), le n° du BL, puis <b>Valider la réception</b>.', 'S\'il manque quelque chose, le reliquat reste attendu.', 'Sambou absent : Abdel Rahim réceptionne sur le même écran (la liste l\'indique « en remplacement du magasinier »).'], res: '<b>Le stock monte.</b> La liste des réceptions indique si la livraison est <b>conforme à la DA</b>, en reliquat, ou en <b>écart</b> (manque, surplus).', shots: [['mag-reception', 'Saisie des quantités reçues'], ['proc-ecart', 'Comparaison avec la demande : écart signalé']] },
    { who: ['responsable'], t: 'Contrôle de la facture', where: 'Page du BC reçu › <b>Valider la facture</b>', pts: ['Tandia compare BC, réception et facture du fournisseur, puis valide.'], res: 'Le BC passe « à payer ».', shots: [['resp-facture', 'Facture à contrôler']] },
    { who: ['direction'], t: 'Paiement du fournisseur', where: 'Page du BC › <b>Enregistrer paiement</b>', pts: ['Abdel Rahim choisit le mode de paiement et le montant ; on peut payer en plusieurs fois.', 'Le reçu s\'imprime.'], res: 'Le BC passe « Payé ».', shots: [['dg-paiement', 'Enregistrer un paiement']] },
    { who: ['responsable', 'magasinier'], t: 'Suivre ce qui reste à faire', where: '<b>Achats</b> › <b>Commandes</b> · <b>Demandes d\'achat</b> · <b>Réceptions (BL)</b>', pts: ['Commandes : cases <b>À confirmer</b>, <b>À réceptionner</b>, <b>Facture à contrôler</b>, <b>À payer</b>, avec leur nombre ; filtres par facture et par période.', 'Demandes d\'achat : une pastille par statut (dont « À valider »), recherche par n°, période.', 'Réceptions : filtres par date et par origine (sur DA, directe, BC sans DA).'], shots: [['proc-a-traiter', 'Commandes : ce qui reste à traiter'], ['proc-filtres-bl', 'Réceptions filtrées par date et par origine']] },
  ],
};

// shots : [fichier, légende, 'p' pour une capture téléphone]
const PROFILES = {
  magasinier: {
    who: 'Sambou', role: 'Magasinier · responsable du stock', c: 'mag', login: 'sambou',
    sub: 'Vous gérez tout ce qui entre et sort du magasin : sorties vers les cuisines, réceptions des livraisons, et demandes d\'achat quand le stock baisse.',
    yes: ['Préparer et remettre les demandes des cuisines', 'Créer une sortie vers une cuisine (le cuisinier la confirme)', 'Imprimer le bon de sortie (BS)', 'Réceptionner les livraisons (BL) : le stock augmente', 'Voir le stock, les mouvements, transférer entre magasins', 'Faire une demande d\'achat (DA)'],
    no: ['Approuver une demande d\'achat', 'Créer un bon de commande ou fixer un prix', 'Valider une facture ou payer un fournisseur'],
    steps: [
      { t: 'Votre accueil', where: 'Après la connexion', pts: ['Les bandeaux en couleur vous disent quoi faire : <b>demandes des cuisines à préparer</b>, <b>produits sous le seuil</b>, <b>commandes à réceptionner</b>.', 'Cliquez sur un bandeau pour aller directement à la bonne page.'], shots: [['mag-accueil', 'Accueil de Sambou']] },
      { t: 'Préparer une demande de la cuisine', where: '<b>Stock</b> › Demandes des cuisines › <b>Préparer</b>', pts: ['La demande envoyée par le cuisinier apparaît dans « À préparer ».', 'Ouvrez-la, choisissez le magasin : le disponible s\'affiche à côté de chaque produit.', 'Choisissez le <b>cuisinier qui reçoit</b> (Fatimatou, Boris…). Celui qui a fait la demande est proposé d\'office.', 'Corrigez la quantité remise si besoin, puis cliquez <b>Remettre à la cuisine</b>.'], res: 'Les quantités sont réservées. Le stock ne baisse <b>que lorsque le cuisinier choisi se connecte et confirme</b> qu\'il a bien pris la marchandise.', shots: [['mag-file-cuisine', 'File des demandes des cuisines'], ['mag-preparer', 'Préparation : quantités remises et disponible']] },
      { t: 'Créer une sortie vous-même', where: '<b>Stock</b> › Demandes des cuisines › <b>Nouvelle sortie vers une cuisine</b>', pts: ['Choisissez le <b>cuisinier qui reçoit</b> (obligatoire) et le magasin.', '<b>Scannez</b> les produits : caméra, douchette, ou code tapé puis <b>OK</b>. Chaque scan ajoute l\'emballage scanné (sac, carton…) ; scanner deux fois le même ajoute 2. Le chiffre « Prochain scan » permet d\'en ajouter plusieurs d\'un coup.', 'Sans code-barres : <b>Ajouter une ligne</b>, tapez le nom du produit, choisissez l\'emballage et le nombre. L\'écran affiche l\'équivalent (« = 50 kg ») et le disponible.', '<b>Remettre à la cuisine</b>. Si un code est inconnu ou s\'il n\'y a pas assez en stock, l\'écran le signale.'], res: 'La sortie attend la confirmation de <b>ce cuisinier seulement</b> : il se connecte et confirme. Les autres cuisiniers ne la voient pas.', shots: [['mag-sortie', 'Sortie : scan et lignes en emballages']] },
      { t: 'Imprimer le bon de sortie (BS)', where: 'Demandes des cuisines › Dernières sorties validées › 🖨', pts: ['Une fois que le cuisinier a confirmé, le bon de sortie est créé.', 'Imprimez-le : votre nom et celui du cuisinier sont déjà écrits sous les signatures.', 'Vous signez tous les deux, puis vous l\'archivez.'], shots: [['mag-bs', 'Bon de sortie : signatures du magasinier et du cuisinier']] },
      { t: 'Faire une demande d\'achat', where: '<b>Achats</b> › Ruptures › <b>Demande d\'achat automatique</b>', pts: ['La page <b>Ruptures</b> liste les produits sous le seuil.', 'Le système prépare tout seul la demande d\'achat. Relisez les quantités : elles se saisissent <b>en emballages</b> (ex. 10 sacs de 25 kg = 250 kg).', 'Retirez ou corrigez un produit si besoin, puis <b>Envoyer</b>.'], res: 'La demande part chez <b>Youba</b> (acheteur) pour approbation.', shots: [['mag-ruptures', 'Produits sous le seuil'], ['mag-da-auto', 'Demande d\'achat automatique, en emballages']] },
      { t: 'Demande d\'achat manuelle et suivi', where: '<b>Achats</b> › Demandes d\'achat › <b>Nouvelle demande</b>', pts: ['Pour un besoin qui n\'est pas une rupture, créez la demande vous-même.', 'Sur la page de la demande, le circuit montre où elle en est : demandée → approuvée par l\'acheteur → validée par le responsable des achats → commandée.'], shots: [['mag-da-nouvelle', 'Nouvelle demande d\'achat'], ['mag-da-envoyee', 'Suivi de la demande envoyée']] },
      { t: 'Réceptionner une livraison (BL)', where: '<b>Réceptions (BL)</b> › la commande', pts: ['Les commandes à recevoir sont listées. Ouvrez celle qui arrive.', 'Comparez le bon de commande, le bon de livraison du fournisseur et la marchandise.', 'Tapez la quantité reçue <b>en emballages</b> (ou scannez), le n° du BL, puis <b>Valider la réception</b>.', 'Si tout n\'est pas arrivé, l\'écran affiche le <b>reliquat</b> : la commande reste ouverte pour la suite.'], res: 'Le stock augmente immédiatement. Un bon de réception est créé.', shots: [['mag-livraisons', 'Commandes à réceptionner'], ['mag-reception', 'Saisie des quantités reçues']] },
      { t: 'Réception sur téléphone', where: 'Même page, depuis le téléphone', pts: ['La réception fonctionne aussi sur téléphone, directement au quai de livraison.'], shots: [['mag-reception-tel', 'Réception sur téléphone', 'p']] },
      { t: 'Livraison sans commande', where: '<b>Réceptions (BL)</b> › <b>Réception directe</b>', pts: ['Pour une marchandise arrivée sans bon de commande dans le système.', 'Choisissez le fournisseur, le magasin, puis les produits reçus.'], shots: [['mag-reception-directe', 'Réception directe']] },
      { t: 'Imprimer le bon de réception', where: 'Page de la commande › Réceptions › 🖨', pts: ['Le bon montre l\'emballage, la quantité commandée et la quantité reçue.', 'Signé par vous et le livreur.'], shots: [['mag-bl', 'Bon de réception imprimable']] },
      { t: 'Voir le stock et les mouvements', where: '<b>Stock</b> › un magasin · <b>Tous les mouvements</b>', pts: ['Chaque magasin affiche ses produits, en emballages et en unités.', 'Les mouvements montrent chaque entrée et sortie : qui, quand, quelle pièce.'], shots: [['mag-stock', 'Les magasins'], ['mag-stock-detail', 'Le stock d\'un magasin'], ['mag-mouvements', 'Tous les mouvements']] },
      { t: 'Transférer et scanner', where: '<b>Stock</b> › <b>Transfert</b> · <b>Scanner</b>', pts: ['<b>Transfert</b> : déplacer un produit d\'un magasin à un autre.', '<b>Scanner</b> : retrouver un produit avec le code-barres, depuis le téléphone.'], shots: [['mag-transfert', 'Transfert entre magasins'], ['mag-scan', 'Scanner sur téléphone', 'p']] },
    ],
  },
  acheteur: {
    who: 'Youba', role: 'Acheteur', c: 'ach', login: 'youba',
    sub: 'Vous faites ou approuvez les demandes d\'achat, vous allez chez les fournisseurs, vous ramenez les devis et vous préparez le bon de commande (fournisseur, prix, devis joint). Tandia le confirme ; ensuite le magasinier réceptionne.',
    yes: ['Faire une demande d\'achat (page DA automatique : ajouter des produits, envoyer)', 'Approuver ou refuser une demande d\'achat', 'Imprimer la demande pour la montrer aux fournisseurs', 'Saisir les devis des fournisseurs et les comparer', 'Préparer le bon de commande : fournisseur, emballages, prix, devis joint', 'Gérer la liste des fournisseurs', 'Voir les ruptures de stock'],
    no: ['Confirmer un bon de commande (c\'est Tandia)', 'Valider la demande à la place du responsable des achats', 'Réceptionner une livraison', 'Payer un fournisseur'],
    steps: [
      { t: 'Votre accueil', where: 'Après la connexion', pts: ['Un bandeau vous indique les <b>demandes d\'achat à approuver</b>.'], shots: [['ach-accueil', 'Accueil de Youba']] },
      { t: 'Les demandes d\'achat', where: '<b>Achats</b> › <b>Demandes d\'achat</b>', pts: ['Toutes les demandes avec leur état. Filtrez par état pour retrouver celles « À approuver ».'], shots: [['ach-da-liste', 'Liste des demandes d\'achat']] },
      { t: 'Approuver ou refuser', where: 'Page de la demande › <b>Approuver (acheteur)</b>', pts: ['Vérifiez les produits et les quantités.', '<b>Approuver</b>, ou <b>Refuser</b> en écrivant le motif.'], res: 'La demande passe chez <b>Tandia</b> (responsable des achats) pour validation.', shots: [['ach-da-approuver', 'Demande à approuver']] },
      { t: 'Imprimer la demande pour le fournisseur', where: 'Page de la demande › <b>Imprimer</b>', pts: ['La feuille montre chaque produit avec son <b>emballage</b> (sac, carton…) et la quantité, sans prix.', 'Vous l\'emportez chez les fournisseurs pour demander leurs prix.'], shots: [['ach-da-imprimee', 'Demande d\'achat imprimée']] },
      { t: 'Saisir les devis', where: 'Page de la demande validée › <b>Ajouter un devis</b>', pts: ['Choisissez le fournisseur, tapez le prix de chaque produit <b>par emballage</b>.', 'Saisissez un devis par fournisseur consulté.', 'Le tableau compare les devis : le moins cher est mis en évidence.'], res: 'Tandia voit les devis et choisit le fournisseur pour le bon de commande.', shots: [['ach-devis', 'Ajouter un devis'], ['ach-devis-compare', 'Comparaison des devis']] },
      { t: 'Faire la demande d\'achat', where: '<b>Achats</b> › <b>Demande d\'achat auto</b>', pts: ['Le système y met déjà les produits sous le seuil. En bas : <b>Ajouter un produit à la demande</b> (produit, emballage, quantité) pour en ajouter d\'autres. Sans brouillon, le premier ajout le crée.', 'Le magasinier peut aussi ajouter des produits au même brouillon.', '<b>Envoyer pour approbation</b> : envoyée par vous, elle part directement chez Tandia pour validation.'], shots: [['ach-da-auto', 'Demande d’achat : produits ajoutés à la main']] },
      { t: 'Préparer le bon de commande', where: 'Page de la demande validée › <b>Créer un Bon de Commande</b>', pts: ['Quand Tandia a validé la demande : choisissez le fournisseur (le moins cher des devis est proposé), vérifiez emballages, quantités et prix, joignez la photo du devis.', '<b>Créer ce Bon de Commande</b>.'], res: 'Le BC est « en attente » : <b>Tandia doit le confirmer</b>. Ensuite seulement le magasinier peut le réceptionner.', shots: [['resp-bc-creer', 'Préparer le bon de commande']] },
      { t: 'Fournisseurs et ruptures', where: '<b>Achats</b> › <b>Fournisseurs</b> · <b>Ruptures</b>', pts: ['Ajoutez ou corrigez un fournisseur (téléphone, adresse).', 'Les ruptures montrent ce qui manque et ce qui est déjà en commande.'], shots: [['ach-fournisseurs', 'Fournisseurs'], ['ach-ruptures', 'Ruptures de stock']] },
    ],
  },
  responsable: {
    who: 'Tandia', role: 'Responsable des achats', c: 'resp', login: 'tandia',
    sub: 'Vous êtes le responsable : vous suivez et validez les achats et les prix. Ce que Youba prépare (DA, BC), vous le validez ou le confirmez. Ce que vous créez vous-même est validé d\'office : une DA créée par vous est déjà validée, un BC créé par vous est déjà confirmé et part au magasinier. Vous contrôlez la facture.',
    yes: ['Valider ou refuser une demande d\'achat', 'Créer une DA : elle est validée d\'office', 'Comparer les devis et choisir le fournisseur', 'Créer un bon de commande : il est confirmé d\'office', 'Confirmer les BC préparés par Youba (bandeau « à confirmer » sur l\'accueil)', 'Imprimer le BC pour le fournisseur', 'Contrôler la facture (BC + réception + facture)'],
    no: ['Réceptionner une livraison (c\'est le magasinier)', 'Payer un fournisseur (c\'est la Direction)'],
    steps: [
      { t: 'Votre accueil', where: 'Après la connexion', pts: ['Un bandeau vous indique les <b>demandes d\'achat à valider</b> et les <b>factures à contrôler</b>.'], shots: [['resp-accueil', 'Accueil de Tandia']] },
      { t: 'Valider la demande', where: 'Page de la demande › <b>Valider la demande</b>', pts: ['La demande a déjà été approuvée par Youba.', '<b>Valider la demande</b>, ou <b>Refuser</b> avec un motif. Rien ne peut être acheté avant votre validation.'], shots: [['resp-da-valider', 'Demande à valider']] },
      { t: 'Comparer les devis', where: 'Page de la demande validée › Devis reçus', pts: ['Les devis saisis par Youba sont côte à côte, par emballage.', 'Le prix le plus bas est mis en évidence.'], shots: [['resp-da-validee', 'Devis reçus']] },
      { t: 'Créer le bon de commande', where: 'Page de la demande › <b>Créer un Bon de Commande</b>', pts: ['Choisissez le fournisseur. Pour chaque produit : l\'<b>emballage</b>, la quantité et le prix.', 'Une demande peut donner plusieurs BC, un par fournisseur.'], res: 'Créé par vous, le BC est <b>confirmé d\'office</b> : il part directement chez le magasinier pour la réception. (Créé par Youba, il attend votre confirmation.)', shots: [['resp-bc-creer', 'Création du bon de commande']] },
      { t: 'Confirmer, envoyer et imprimer', where: 'Page du BC › <b>Confirmer &amp; envoyer</b> · 🖨 Bon de commande', pts: ['Vérifiez les lignes, puis <b>Confirmer &amp; envoyer</b> quand la commande est passée.', 'Imprimez le BC pour le fournisseur : il montre l\'<b>emballage</b>, la quantité en emballages (ex. 6 × Carton de 12) et le prix par emballage.'], res: 'La commande apparaît chez Sambou dans « Commandes à réceptionner ».', shots: [['resp-bc-confirmer', 'BC à confirmer'], ['resp-bc-imprime', 'Bon de commande imprimé pour le fournisseur']] },
      { t: 'Suivre les commandes', where: '<b>Achats</b> › <b>Commandes</b>', pts: ['Toutes les commandes avec leur état : en attente, commandée, partielle, reçue, payée.'], shots: [['resp-commandes', 'Liste des commandes']] },
      { t: 'Contrôler la facture', where: 'Page du BC reçu › <b>Valider la facture</b>', pts: ['Comparez le BC, la réception du magasinier et la facture du fournisseur.', 'Si tout correspond, <b>Valider la facture</b>.'], res: 'Le BC passe chez <b>Abdel Rahim</b> (Direction) pour le paiement.', shots: [['resp-facture', 'Facture à contrôler']] },
    ],
  },
  direction: {
    who: 'Abdel Rahim', role: 'Direction · paiements', c: 'dg', login: 'abdel.rahim',
    sub: 'Vous avez accès à tout le système. Dans le circuit des achats, c\'est vous qui payez les fournisseurs, et vous réceptionnez les livraisons quand le magasinier est absent.',
    yes: ['Tout voir et tout faire dans le système', 'Payer les fournisseurs, en une ou plusieurs fois', 'Imprimer les reçus de paiement', 'Réceptionner une livraison (BL) en l\'absence du magasinier : menu Achats › Réceptions (BL), même écran que lui'],
    no: [],
    steps: [
      { t: 'Votre accueil', where: 'Après la connexion', pts: ['Tous les modules sont accessibles. Les bandeaux signalent les commandes <b>à payer</b>.'], shots: [['dg-accueil', 'Accueil de la Direction']] },
      { t: 'Payer un fournisseur', where: 'Page du BC › <b>Enregistrer paiement</b>', pts: ['Ouvrez le BC dont la facture est validée.', 'Cliquez <b>Enregistrer paiement</b>, tapez le montant et le mode de paiement.', 'Vous pouvez payer en plusieurs fois : le reste à payer est suivi.'], res: 'Le BC passe à « Partiel » puis « Payé ».', shots: [['dg-a-payer', 'BC à payer'], ['dg-paiement', 'Enregistrer un paiement']] },
      { t: 'Reçu de paiement', where: 'Page du BC › Paiements › 🖨', pts: ['Le reçu s\'imprime pour le fournisseur et pour l\'archive.'], shots: [['dg-recu', 'Reçu de paiement']] },
    ],
  },
  cuisinier: {
    who: 'Fatimatou · Boris', role: 'Cuisiniers', c: 'cui', login: 'fatimatou · boris',
    sub: 'Vous demandez au magasin ce dont la cuisine a besoin. Quand le magasinier vous remet la marchandise, vous vous connectez pour confirmer. C\'est votre confirmation qui fait sortir la marchandise du stock. Ensuite vous signez le bon de sortie avec le magasinier.',
    yes: ['Demander des produits au magasin', 'Confirmer les sorties qui vous sont remises', 'Signer le bon de sortie avec le magasinier', 'Annuler une demande pas encore préparée'],
    no: ['Confirmer une sortie remise à un autre cuisinier', 'Toucher au stock directement', 'Faire une demande d\'achat'],
    steps: [
      { t: 'Vos demandes', where: '<b>Demandes au magasin</b>', pts: ['Vos demandes et leur état : à préparer, à confirmer, sortie validée.', 'En haut, « À confirmer » : les sorties que le magasinier <b>vous</b> a remises. Celles d\'un autre cuisinier n\'apparaissent pas.'], shots: [['cui-liste', 'Mes demandes (Boris)', 'p']] },
      { t: 'Faire une demande', where: '<b>Demandes au magasin</b> › <b>Nouvelle demande</b>', pts: ['Choisissez la cuisine, la date et les produits avec les quantités.', '<b>Envoyer au magasin</b>.'], res: 'La demande arrive chez Sambou.', shots: [['cui-nouvelle', 'Nouvelle demande', 'p']] },
      { t: 'Confirmer ce que vous prenez', where: 'Demande « À confirmer » › <b>Je confirme, je prends</b>', pts: ['Comparez les quantités remises avec ce que vous recevez.', 'Si c\'est bon : <b>Je confirme, je prends</b>. Sinon, contestez en expliquant : la sortie revient au magasinier.', 'Passez ensuite au magasin signer le bon de sortie.'], res: 'Le stock baisse et le bon de sortie (BS) est créé, à votre nom.', shots: [['cui-confirmer', 'Avant la confirmation', 'p'], ['cui-validee', 'Sortie validée', 'p']] },
    ],
  },
};

const $ = s => document.querySelector(s);
const nav = $('#profiles'), content = $('#content'), circuit = $('#circuit');
const ORDER = Object.keys(PROFILES);

function shotHtml([file, cap, kind]) {
  return `<figure class="shot ${kind === 'p' ? 'phone' : ''}"><button type="button" data-src="${IMG + file}.jpg" data-cap="${cap}"><img loading="lazy" src="${IMG + file}.jpg" alt="${cap}"></button><figcaption>${cap}</figcaption></figure>`;
}

function stepHtml(s, i) {
  const chips = s.who ? `<div class="who-chips">${s.who.map(k => `<span class="chip" style="--k:var(--${PROFILES[k].c});--k-soft:var(--${PROFILES[k].c}-soft)">${PROFILES[k].who}</span>`).join('')}</div>` : '';
  return `
      <article class="step">
        <div>
          <span class="num">${i + 1}</span>
          <h3>${s.t}</h3>
          ${chips}
          <p class="where">Où : ${s.where}</p>
          <ol>${s.pts.map(x => `<li>${x}</li>`).join('')}</ol>
          ${s.res ? `<p class="result">${s.res}</p>` : ''}
        </div>
        <div class="shots ${s.shots.length > 1 && s.shots.every(x => x[2] === 'p') ? 'two' : ''}">${s.shots.map(shotHtml).join('')}</div>
      </article>`;
}

function renderProcess() {
  document.body.style.setProperty('--c', 'var(--proc)');
  document.body.style.setProperty('--c-soft', 'var(--proc-soft)');
  nav.querySelectorAll('.pcard').forEach(b => b.setAttribute('aria-pressed', b.dataset.k === 'processus'));
  circuit.innerHTML = CIRCUIT.map(([k, t], i) => `${i ? '<span class="arr">→</span>' : ''}<span class="st me" style="--c:var(--${PROFILES[k].c})" title="${PROFILES[k].who}">${t}</span>`).join('');
  content.innerHTML = `
    <section class="phead">
      <h2>${PROCESS.title}</h2>
      <p class="sub">${PROCESS.sub}</p>
      <div class="rules-wrap"><table class="rules">
        <thead><tr><th>Étape</th><th>Qui la fait</th><th>Qui valide / ce qui se passe</th></tr></thead>
        <tbody>${PROCESS.rules.map(r => `<tr><td><b>${r[0]}</b></td><td>${r[1]}</td><td>${r[2]}</td></tr>`).join('')}</tbody>
      </table></div>
    </section>
    ${PROCESS.steps.map(stepHtml).join('')}`;
}

function render(key) {
  if (key === 'processus') return renderProcess();
  const p = PROFILES[key];
  document.body.style.setProperty('--c', `var(--${p.c})`);
  document.body.style.setProperty('--c-soft', `var(--${p.c}-soft)`);
  nav.querySelectorAll('.pcard').forEach(b => b.setAttribute('aria-pressed', b.dataset.k === key));
  circuit.innerHTML = CIRCUIT.map(([k, t], i) => `${i ? '<span class="arr">→</span>' : ''}<span class="st ${k === key ? 'me' : ''}">${t}</span>`).join('');
  content.innerHTML = `
    <section class="phead">
      <h2>${p.who} — ${p.role}</h2>
      <p class="sub">${p.sub}</p>
      <p class="login">Nom d'utilisateur : <b>${p.login}</b> (ou votre e-mail) · première fois : mot de passe <b>password</b>, puis le système vous demande d'en choisir un nouveau. Dans <b>Mon compte</b> (en haut de l'écran), vous pouvez changer votre mot de passe et votre nom d'utilisateur, par exemple un numéro.</p>
      <div class="cando">
        <div class="yes"><h3>Vous pouvez</h3><ul>${p.yes.map(x => `<li>${x}</li>`).join('')}</ul></div>
        ${p.no.length ? `<div class="no"><h3>Vous ne pouvez pas</h3><ul>${p.no.map(x => `<li>${x}</li>`).join('')}</ul></div>` : ''}
      </div>
    </section>
    ${p.steps.map((s, i) => `
      <article class="step">
        <div>
          <span class="num">${i + 1}</span>
          <h3>${s.t}</h3>
          <p class="where">Où : ${s.where}</p>
          <ol>${s.pts.map(x => `<li>${x}</li>`).join('')}</ol>
          ${s.res ? `<p class="result">${s.res}</p>` : ''}
        </div>
        <div class="shots ${s.shots.length > 1 && s.shots.every(x => x[2] === 'p') ? 'two' : ''}">${s.shots.map(shotHtml).join('')}</div>
      </article>`).join('')}`;
}

nav.innerHTML = `<button type="button" class="pcard proc" data-k="processus" style="--c:var(--proc);--c-soft:var(--proc-soft)" aria-pressed="false">
    <span class="who"><span class="dot"></span>Processus complet</span><span class="role">Tout le circuit d'achat, de A à Z</span></button>` + ORDER.map(k => {
  const p = PROFILES[k];
  return `<button type="button" class="pcard" data-k="${k}" style="--c:var(--${p.c});--c-soft:var(--${p.c}-soft)" aria-pressed="false">
    <span class="who"><span class="dot"></span>${p.who}</span><span class="role">${p.role}</span></button>`;
}).join('');

nav.addEventListener('click', e => {
  const b = e.target.closest('.pcard'); if (!b) return;
  history.replaceState(null, '', '#' + b.dataset.k);
  render(b.dataset.k);
  content.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

const zoom = $('#zoom');
content.addEventListener('click', e => {
  const b = e.target.closest('.shot button'); if (!b) return;
  zoom.querySelector('img').src = b.dataset.src;
  zoom.querySelector('p').textContent = b.dataset.cap;
  zoom.classList.add('on');
});
zoom.addEventListener('click', () => zoom.classList.remove('on'));
document.addEventListener('keydown', e => { if (e.key === 'Escape') zoom.classList.remove('on'); });

$('#themeBtn').addEventListener('click', () => {
  const dark = document.documentElement.dataset.theme === 'dark'
    || (!document.documentElement.dataset.theme && matchMedia('(prefers-color-scheme: dark)').matches);
  document.documentElement.dataset.theme = dark ? 'light' : 'dark';
  try { localStorage.setItem('guide-theme', document.documentElement.dataset.theme); } catch (e) {}
});
try { const t = localStorage.getItem('guide-theme'); if (t) document.documentElement.dataset.theme = t; } catch (e) {}

window.addEventListener('hashchange', () => { const k = location.hash.slice(1); if (PROFILES[k] || k === 'processus') render(k); });
const start = location.hash.slice(1);
render(PROFILES[start] || start === 'processus' ? start : 'processus');
</script>
</body>
</html>
