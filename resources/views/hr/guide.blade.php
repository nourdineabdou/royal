<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guide de formation RH — Complex Royal</title>
    <link rel="icon" href="{{ asset('royal_complex_groupe.png') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&family=Source+Sans+3:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      :root{
        --ink:#182230; --ink-soft:#4b5566; --ink-faint:#7c8698;
        --brand:#1d4ed8; --brand-dark:#13348f; --brand-soft:#e8eefc;
        --accent:#0f8a7e; --accent-dark:#0b6d63; --accent-soft:#e3f5f2;
        --indigo:#4f46e5; --indigo-soft:#eceafd;
        --violet:#7c3aed; --violet-soft:#f2eafe;
        --amber:#b45309; --amber-soft:#fdf1de;
        --green:#15803d; --green-soft:#e7f6ec;
        --surface:#ffffff; --surface-alt:#f6f8fb; --surface-sunken:#eef1f7;
        --border:#dfe4ee; --border-soft:#eceff5;
        --danger:#b91c1c; --danger-soft:#fdecec;
        --shadow:0 1px 2px rgba(24,34,48,.04), 0 8px 24px -12px rgba(24,34,48,.12);
      }
      *{box-sizing:border-box;}
      html,body{ margin:0; padding:0; }
      body{
        background:var(--surface-alt); color:var(--ink);
        font-family:'Source Sans 3', -apple-system, Segoe UI, sans-serif;
        font-size:15.5px; line-height:1.6;
      }
      h1,h2,h3,h4{ font-family:'Manrope', sans-serif; color:var(--ink); letter-spacing:-0.01em; margin-top:0; }
      code, .mono{ font-family:'IBM Plex Mono', monospace; font-variant-numeric:tabular-nums; }
      a{ color:var(--brand); }
      img{ max-width:100%; display:block; }

      /* ── Barre d'impression ───────────────────────────────────── */
      .print-bar{ position:sticky; top:0; z-index:50; display:flex; align-items:center; justify-content:space-between; gap:12px; background:var(--brand-dark); color:#fff; padding:9px 18px; font-size:13px; }
      .print-bar .pb-left{ display:flex; align-items:center; gap:8px; font-weight:600; }
      .print-bar button{ background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); color:#fff; padding:6px 13px; border-radius:8px; font-size:12.8px; font-weight:700; cursor:pointer; font-family:inherit; }
      .print-bar button:hover{ background:rgba(255,255,255,.25); }

      /* ── Shell layout ─────────────────────────────────────────── */
      .shell{ display:flex; min-height:100%; max-width:1360px; margin:0 auto; }
      .sidebar{
        width:270px; flex-shrink:0; position:sticky; top:42px; align-self:flex-start;
        height:calc(100vh - 42px); overflow-y:auto; padding:22px 16px 40px;
        border-right:1px solid var(--border);
        background:var(--surface);
      }
      .brand{ display:flex; align-items:center; gap:10px; padding:4px 8px 18px; border-bottom:1px solid var(--border-soft); margin-bottom:14px;}
      .brand-logo{ width:36px; height:36px; border-radius:9px; flex-shrink:0; object-fit:contain; background:#fff; border:1px solid var(--border-soft); padding:2px; }
      .brand-text b{ display:block; font-family:'Manrope'; font-weight:800; font-size:14.5px; color:var(--ink); }
      .brand-text span{ font-size:11.5px; color:var(--ink-faint); }

      .nav-group-title{ font-size:11px; text-transform:uppercase; letter-spacing:.07em; color:var(--ink-faint); font-weight:700; margin:16px 10px 6px; }
      .nav-link{ display:flex; align-items:center; gap:9px; padding:7px 10px; border-radius:8px; color:var(--ink-soft); text-decoration:none; font-size:13.6px; font-weight:600; }
      .nav-link:hover{ background:var(--surface-alt); color:var(--ink); }
      .nav-link .dot{ width:7px; height:7px; border-radius:50%; background:var(--border); flex-shrink:0; }

      .content{ flex:1; min-width:0; padding:38px clamp(18px, 4vw, 56px) 100px; }

      /* ── Cover ────────────────────────────────────────────────── */
      .cover{
        background:linear-gradient(135deg, var(--brand-dark), var(--brand) 55%, var(--accent));
        border-radius:18px; padding:clamp(28px,4vw,48px); color:#fff; margin-bottom:34px;
        position:relative; overflow:hidden;
      }
      .cover::after{ content:""; position:absolute; inset:0; background:radial-gradient(600px 200px at 85% -10%, rgba(255,255,255,.18), transparent 60%); }
      .cover .eyebrow{ font-size:12.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; opacity:.85; margin-bottom:10px; }
      .cover h1{ color:#fff; font-size:clamp(26px,3.4vw,38px); margin:0 0 12px; max-width:640px; }
      .cover p{ color:rgba(255,255,255,.88); max-width:600px; font-size:15.5px; margin:0; }
      .cover .meta{ display:flex; gap:22px; flex-wrap:wrap; margin-top:24px; position:relative; z-index:1;}
      .cover .meta div b{ display:block; font-family:'Manrope'; font-weight:800; font-size:20px; }
      .cover .meta div span{ font-size:12px; opacity:.85; }

      .notice{ display:flex; gap:12px; background:var(--amber-soft); border:1px solid #e3b673; border-radius:12px; padding:14px 16px; margin-bottom:36px; font-size:13.8px; color:var(--ink-soft); }
      .notice svg{ flex-shrink:0; margin-top:2px; }
      .notice b{ color:var(--ink); }

      /* ── Sections ─────────────────────────────────────────────── */
      section.module{ margin-bottom:56px; scroll-margin-top:56px; }
      .module-head{ display:flex; align-items:flex-start; gap:14px; margin-bottom:6px; }
      .module-icon{ width:42px; height:42px; border-radius:11px; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-family:'Manrope'; font-weight:800; font-size:16px; color:#fff; }
      .module-head h2{ font-size:23px; margin:0 0 3px; }
      .module-head p.lede{ color:var(--ink-soft); margin:0; font-size:14.5px; max-width:640px;}
      .perm-row{ display:flex; gap:8px; flex-wrap:wrap; margin:14px 0 24px; }
      .perm-tag{ font-family:'IBM Plex Mono'; font-size:11.3px; font-weight:600; padding:3px 9px; border-radius:999px; background:var(--surface-sunken); border:1px solid var(--border); color:var(--ink-soft); }

      .grid-2{ display:grid; grid-template-columns:1.05fr .95fr; gap:22px; align-items:start; }
      @media (max-width:840px){ .grid-2{ grid-template-columns:1fr; } }

      .card{ background:var(--surface); border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow); overflow:hidden; margin-bottom:20px;}
      .card .shot-cap{ padding:9px 14px; font-size:12.3px; color:var(--ink-faint); border-top:1px solid var(--border-soft); background:var(--surface-alt); }
      .card img{ display:block; width:100%; }

      ol.steps{ counter-reset:step; list-style:none; margin:0 0 20px; padding:0; }
      ol.steps li{ counter-increment:step; position:relative; padding:2px 0 14px 34px; font-size:14.3px; color:var(--ink-soft); }
      ol.steps li::before{
        content:counter(step); position:absolute; left:0; top:1px; width:22px; height:22px; border-radius:50%;
        background:var(--accent); color:#fff; font-family:'IBM Plex Mono'; font-weight:600; font-size:11.5px;
        display:flex; align-items:center; justify-content:center;
      }
      ol.steps li b{ color:var(--ink); }
      ol.steps.c-blue li::before{ background:var(--brand); }
      ol.steps.c-indigo li::before{ background:var(--indigo); }
      ol.steps.c-violet li::before{ background:var(--violet); }
      ol.steps.c-green li::before{ background:var(--green); }
      ol.steps.c-amber li::before{ background:var(--amber); }

      .example{ background:var(--surface-sunken); border-left:3px solid var(--accent); border-radius:0 10px 10px 0; padding:12px 16px; font-size:13.6px; color:var(--ink-soft); margin:16px 0; }
      .example b{ color:var(--accent-dark); display:block; font-size:11.5px; text-transform:uppercase; letter-spacing:.05em; margin-bottom:5px; font-family:'Manrope'; }

      /* ── Accounting ledger card ──────────────────────────────── */
      .ledger{ background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden; margin:16px 0; box-shadow:var(--shadow); }
      .ledger-head{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding:11px 16px; background:var(--indigo-soft); border-bottom:1px solid var(--border); }
      .ledger-head b{ font-family:'Manrope'; font-size:13.5px; color:var(--ink); }
      .ledger-head span{ font-family:'IBM Plex Mono'; font-size:11.5px; color:var(--ink-faint); }
      .ledger table{ width:100%; border-collapse:collapse; font-size:13.3px; }
      .ledger th{ text-align:left; font-size:10.8px; text-transform:uppercase; letter-spacing:.06em; color:var(--ink-faint); padding:9px 16px 6px; font-weight:700; }
      .ledger td{ padding:7px 16px; border-top:1px solid var(--border-soft); }
      .ledger td.acct{ font-family:'IBM Plex Mono'; font-size:12.6px; }
      .ledger td.num{ font-family:'IBM Plex Mono'; text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
      .ledger td.num.debit{ color:var(--indigo); font-weight:600; }
      .ledger td.num.credit{ color:var(--accent-dark); font-weight:600; }

      table.ref{ width:100%; border-collapse:collapse; font-size:13.4px; margin:14px 0 28px; }
      table.ref th{ text-align:left; background:var(--surface-sunken); font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:var(--ink-faint); padding:8px 12px; font-weight:700; }
      table.ref td{ padding:9px 12px; border-top:1px solid var(--border-soft); color:var(--ink-soft); }
      table.ref tr:hover td{ background:var(--surface-alt); }
      .tbl-wrap{ overflow-x:auto; border:1px solid var(--border); border-radius:12px; }
      .tbl-wrap table.ref{ margin:0; }
      .tbl-wrap table.ref th:first-child, .tbl-wrap table.ref td:first-child{ padding-left:14px; }

      .badge{ display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px; }
      .badge.ok{ background:var(--green-soft); color:var(--green); }
      .badge.warn{ background:var(--amber-soft); color:var(--amber); }
      .badge.info{ background:var(--brand-soft); color:var(--brand); }

      .faq{ border:1px solid var(--border); border-radius:12px; padding:14px 18px; margin-bottom:10px; background:var(--surface); }
      .faq b{ display:block; font-family:'Manrope'; font-size:14px; margin-bottom:4px; }
      .faq p{ margin:0; color:var(--ink-soft); font-size:13.6px; }

      .menu-btn{ display:none; }

      @media (max-width:960px){
        .shell{ display:block; }
        .sidebar{ position:fixed; inset:0 auto 0 0; width:78vw; max-width:300px; transform:translateX(-100%); transition:transform .22s ease; z-index:40; box-shadow:16px 0 40px rgba(0,0,0,.25); top:0; height:100vh; }
        .sidebar.open{ transform:translateX(0); }
        .menu-btn{ display:flex; align-items:center; gap:8px; position:sticky; top:54px; margin:12px 0 0 16px; z-index:30; background:var(--surface); border:1px solid var(--border); border-radius:10px; padding:9px 14px; font-family:'Manrope'; font-weight:700; font-size:13px; color:var(--ink); box-shadow:var(--shadow); }
        .content{ padding:8px 16px 90px; }
        .scrim{ display:none; position:fixed; inset:0; background:rgba(10,14,20,.45); z-index:35; }
        .scrim.show{ display:block; }
      }

      @media print{
        .print-bar, .menu-btn, .sidebar, .scrim{ display:none !important; }
        .content{ padding:0; }
        .shell{ display:block; max-width:100%; }
        section.module{ page-break-inside:avoid; }
      }
    </style>
</head>
<body>

    <div class="print-bar">
        <div class="pb-left">
            <img src="{{ asset('royal_complex_groupe.png') }}" alt="" style="height:20px; width:auto; background:#fff; border-radius:4px; padding:2px;">
            Guide de formation RH — Complex Royal
        </div>
        <button type="button" onclick="window.print()"><i class="fas fa-print" style="margin-right:5px;"></i>Imprimer / Télécharger en PDF</button>
    </div>

    <button class="menu-btn" id="menuBtn" onclick="document.getElementById('sidebar').classList.add('open'); document.getElementById('scrim').classList.add('show');">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      Sommaire
    </button>
    <div class="scrim" id="scrim" onclick="document.getElementById('sidebar').classList.remove('open'); this.classList.remove('show');"></div>

    <div class="shell">
      <aside class="sidebar" id="sidebar">
        <div class="brand">
          <img class="brand-logo" src="{{ asset('royal_complex_groupe.png') }}" alt="Complex Royal">
          <div class="brand-text"><b>Complex Royal</b><span>Guide de formation RH</span></div>
        </div>

        <div class="nav-group-title">Prise en main</div>
        <a class="nav-link" href="#intro"><span class="dot"></span>Introduction</a>
        <a class="nav-link" href="#connexion"><span class="dot" style="background:var(--brand)"></span>Connexion</a>
        <a class="nav-link" href="#menu"><span class="dot" style="background:var(--brand)"></span>Menu principal</a>
        <a class="nav-link" href="#dashboard"><span class="dot" style="background:var(--accent)"></span>Tableau de bord RH</a>

        <div class="nav-group-title">Personnel</div>
        <a class="nav-link" href="#employes"><span class="dot" style="background:var(--green)"></span>Employés</a>
        <a class="nav-link" href="#emplacements"><span class="dot" style="background:var(--green)"></span>Emplacements</a>
        <a class="nav-link" href="#contrats"><span class="dot" style="background:var(--indigo)"></span>Contrats</a>

        <div class="nav-group-title">Temps de travail</div>
        <a class="nav-link" href="#presences"><span class="dot" style="background:var(--accent)"></span>Présences &amp; pointage</a>
        <a class="nav-link" href="#conges"><span class="dot" style="background:var(--amber)"></span>Congés</a>

        <div class="nav-group-title">Rémunération</div>
        <a class="nav-link" href="#paie"><span class="dot" style="background:var(--accent)"></span>Paie</a>
        <a class="nav-link" href="#avances"><span class="dot" style="background:var(--violet)"></span>Avances sur salaire</a>

        <div class="nav-group-title">Comptabilité &amp; référence</div>
        <a class="nav-link" href="#comptabilite"><span class="dot" style="background:var(--indigo)"></span>Écritures comptables</a>
        <a class="nav-link" href="#permissions"><span class="dot" style="background:var(--ink-faint)"></span>Rôles &amp; permissions</a>
        <a class="nav-link" href="#faq"><span class="dot" style="background:var(--ink-faint)"></span>Questions fréquentes</a>
      </aside>

      <main class="content">

        <section id="intro">
          <div class="cover">
            <div class="eyebrow">Module Ressources Humaines · Complex Royal</div>
            <h1>Guide de formation RH, de A à Z</h1>
            <p>Manuel pas-à-pas pour maîtriser le module RH de l'application Complex Royal : employés, contrats, présences, congés, paie, avances sur salaire — et les écritures comptables générées automatiquement derrière chaque opération.</p>
            <div class="meta">
              <div><b>9</b><span>écrans couverts</span></div>
              <div><b>26</b><span>captures annotées</span></div>
              <div><b>2</b><span>flux comptables détaillés</span></div>
            </div>
          </div>

          <div class="notice">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
            <div>
              <b>À propos des captures d'écran :</b> toutes les images de ce guide sont de vraies captures prises dans l'application, avec les données réelles de Complex Royal (20 employés, 39 fiches de paie, etc.). Pour illustrer certains écrans qui étaient vides au moment de la rédaction (Contrats, Présences), un contrat et un pointage de démonstration ont été créés pour l'employé <b>Ahmed Lebah</b>, capturés, puis supprimés — aucune donnée réelle de production n'a été modifiée de façon permanente.
            </div>
          </div>
        </section>

        <section class="module" id="connexion">
          <div class="module-head">
            <div class="module-icon" style="background:linear-gradient(135deg, var(--brand), var(--brand-dark));">01</div>
            <div><h2>Connexion à l'application</h2><p class="lede">Chaque utilisateur se connecte avec son compte e-mail et mot de passe personnel.</p></div>
          </div>

          <div class="grid-2">
            <div>
              <ol class="steps c-blue">
                <li>Ouvrir l'application dans le navigateur — la page de connexion s'affiche automatiquement.</li>
                <li>Saisir son <b>adresse e-mail</b> dans le champ 1.</li>
                <li>Saisir son <b>mot de passe</b> dans le champ 2 (l'icône œil permet de le révéler pour vérifier la saisie).</li>
                <li>Cocher éventuellement <b>« Se souvenir de moi »</b> pour rester connecté plus longtemps sur cet appareil.</li>
                <li>Cliquer sur <b>Se Connecter</b> (champ 3).</li>
              </ol>
              <div class="example"><b>Bon à savoir</b>Chaque compte n'a accès qu'aux modules et actions autorisés par son rôle (voir la section <a href="#permissions">Rôles &amp; permissions</a>). Un formateur peut donc préparer des comptes de démonstration avec des droits limités pour simuler différents profils (RH junior, comptable, direction).</div>
            </div>
            <div class="card"><img src="{{ asset('guide/hr/00-login.png') }}" alt="Page de connexion annotée"><div class="shot-cap">Page de connexion — champs annotés</div></div>
          </div>
        </section>

        <section class="module" id="menu">
          <div class="module-head">
            <div class="module-icon" style="background:linear-gradient(135deg, var(--brand), var(--brand-dark));">02</div>
            <div><h2>Menu principal</h2><p class="lede">Après connexion, l'utilisateur arrive sur le tableau des modules de gestion.</p></div>
          </div>
          <div class="grid-2">
            <div>
              <ol class="steps c-blue">
                <li>Chaque carte représente un module (Point de Vente, Production, Comptabilité, RH…).</li>
                <li>Seuls les modules pour lesquels l'utilisateur a une permission apparaissent <b>cliquables</b> ; les autres affichent « Accès restreint ».</li>
                <li>Cliquer sur la carte <b>RH</b> (repère 1) pour entrer dans le module Ressources Humaines.</li>
              </ol>
            </div>
            <div class="card"><img src="{{ asset('guide/hr/01-dashboard-menu.png') }}" alt="Menu principal des modules"><div class="shot-cap">Menu principal — accès au module RH</div></div>
          </div>
        </section>

        <section class="module" id="dashboard">
          <div class="module-head">
            <div class="module-icon" style="background:var(--accent);">03</div>
            <div><h2>Tableau de bord RH</h2><p class="lede">Vue d'ensemble du mois en cours : effectifs, congés, présence du jour, paie et avances.</p></div>
          </div>
          <div class="perm-row"><span class="perm-tag">hr.dashboard</span></div>

          <div class="card"><img src="{{ asset('guide/hr/02-hr-dashboard.png') }}" alt="Tableau de bord RH"><div class="shot-cap">Tableau de bord RH — vue complète</div></div>

          <table class="ref">
            <tr><th>Bloc</th><th>Ce qu'il indique</th></tr>
            <tr><td>Total employés</td><td>Effectif total, avec répartition actifs / inactifs</td></tr>
            <tr><td>Postes / Fonctions</td><td>Nombre de postes distincts définis dans l'organisation</td></tr>
            <tr><td>Congés en attente</td><td>Demandes de congé non encore traitées + absents du jour</td></tr>
            <tr><td>Avances en attente</td><td>Nombre et montant total des avances non encore approuvées</td></tr>
            <tr><td>Présents aujourd'hui</td><td>Pointages du jour : présents, absents, retards</td></tr>
            <tr><td>Paie en attente / payée</td><td>Fiches de paie du mois courant, par statut</td></tr>
            <tr><td>Masse salariale — 6 derniers mois</td><td>Graphique du total des salaires nets versés, mois par mois</td></tr>
            <tr><td>Présences — 7 derniers jours</td><td>Évolution quotidienne présents / absents / retards</td></tr>
            <tr><td>Actions rapides</td><td>Raccourcis directs vers les formulaires d'ajout de chaque section</td></tr>
          </table>
        </section>

        <section class="module" id="employes">
          <div class="module-head">
            <div class="module-icon" style="background:var(--green);">04</div>
            <div><h2>Gestion des employés</h2><p class="lede">Fiche de chaque salarié : identité, poste, site, salaire de base, statut et documents.</p></div>
          </div>
          <div class="perm-row">
            <span class="perm-tag">hr.employees.view</span><span class="perm-tag">hr.employees.create</span>
            <span class="perm-tag">hr.employees.edit</span><span class="perm-tag">hr.employees.delete</span>
          </div>

          <h3>Liste des employés</h3>
          <div class="card"><img src="{{ asset('guide/hr/03-hr-employees-list.png') }}" alt="Liste des employés"><div class="shot-cap">Liste des employés — filtres et bouton d'ajout</div></div>
          <ol class="steps c-green">
            <li>La liste peut être filtrée par <b>nom</b>, <b>poste</b> ou <b>téléphone</b> à l'aide du formulaire de filtres.</li>
            <li>Chaque ligne affiche l'avatar (initiales), le site, le poste, le téléphone, la date d'embauche, le salaire de base et le statut (Actif / Inactif).</li>
            <li>Les icônes d'action à droite permettent de <b>modifier</b> (crayon), gérer les <b>documents</b> (dossier) ou <b>supprimer</b> (corbeille) — selon les droits de l'utilisateur.</li>
          </ol>

          <h3>Ajouter un employé</h3>
          <div class="grid-2">
            <ol class="steps c-green">
              <li>Cliquer sur <b>Ajouter employé</b>.</li>
              <li>Renseigner <b>Prénom</b> et <b>Nom</b> (obligatoires).</li>
              <li>Choisir le <b>Poste</b> (obligatoire) et, si applicable, l'<b>Emplacement</b> de travail.</li>
              <li>Ajouter <b>téléphone</b> et <b>adresse</b> (facultatifs).</li>
              <li>Renseigner la <b>date d'embauche</b> (obligatoire) et, si connu, le <b>salaire de base</b> mensuel en MRU — il servira de base par défaut pour générer la paie.</li>
              <li>Définir le <b>statut</b> : Actif (compte dans les effectifs / paie) ou Inactif.</li>
              <li>Cliquer sur <b>Enregistrer</b>.</li>
            </ol>
            <div class="card"><img src="{{ asset('guide/hr/04-hr-employees-add-modal.png') }}" alt="Formulaire d'ajout d'employé"><div class="shot-cap">Formulaire d'ajout d'un employé</div></div>
          </div>
          <div class="example"><b>Exemple pratique</b>Un nouveau serveur est embauché le 1ᵉʳ du mois avec un salaire de base de 45 000 MRU. Une fois enregistré avec le statut « Actif », il apparaîtra automatiquement dans la liste des employés proposés lors de la prochaine génération de paie du mois.</div>

          <h3>Modifier un employé</h3>
          <div class="grid-2">
            <ol class="steps c-green">
              <li>Cliquer sur l'icône crayon de la ligne concernée : le formulaire s'ouvre déjà pré-rempli avec les informations actuelles.</li>
              <li>Modifier les champs nécessaires (poste, site, salaire, statut…).</li>
              <li>Cliquer sur <b>Enregistrer</b> pour appliquer les changements.</li>
            </ol>
            <div class="card"><img src="{{ asset('guide/hr/05-hr-employees-edit-modal.png') }}" alt="Formulaire de modification d'employé"><div class="shot-cap">Modification d'un employé existant</div></div>
          </div>

          <h3>Documents de l'employé</h3>
          <div class="grid-2">
            <ol class="steps c-green">
              <li>Cliquer sur l'icône dossier d'une ligne pour ouvrir le dossier documentaire de l'employé.</li>
              <li>Les documents déjà déposés (pièce d'identité, diplôme, contrat scanné…) sont listés, avec un lien pour les <b>consulter</b> ou les <b>supprimer</b>.</li>
              <li>Pour ajouter un document : choisir le <b>type</b>, donner un <b>nom</b> explicite (ex. « CNI recto-verso ») et sélectionner le <b>fichier</b> (PDF, JPG ou PNG, 5 Mo maximum).</li>
              <li>Cliquer sur <b>Ajouter le document</b>.</li>
            </ol>
            <div class="card"><img src="{{ asset('guide/hr/06-hr-employees-docs-modal.png') }}" alt="Gestion des documents employé"><div class="shot-cap">Dossier documentaire d'un employé</div></div>
          </div>
        </section>

        <section class="module" id="emplacements">
          <div class="module-head">
            <div class="module-icon" style="background:var(--green);">05</div>
            <div><h2>Emplacements (sites)</h2><p class="lede">Les lieux de travail (restaurant, site catering, résidence…) auxquels un employé peut être rattaché.</p></div>
          </div>
          <div class="perm-row"><span class="perm-tag">hr.employees.view</span><span class="perm-tag">hr.employees.edit</span><span class="perm-tag">hr.employees.delete</span></div>

          <div class="grid-2">
            <ol class="steps c-green">
              <li>La liste affiche tous les emplacements définis, avec adresse et téléphone.</li>
              <li>Cliquer sur <b>Ajouter emplacement</b>, renseigner <b>Nom</b> (obligatoire), <b>Adresse</b> et <b>Téléphone</b>, puis <b>Enregistrer</b>.</li>
              <li>L'icône crayon permet de modifier un emplacement existant ; la corbeille le supprime.</li>
            </ol>
            <div class="card"><img src="{{ asset('guide/hr/07-hr-sites-list.png') }}" alt="Liste des emplacements"><div class="shot-cap">Liste des emplacements</div></div>
          </div>
          <div class="card"><img src="{{ asset('guide/hr/08-hr-sites-add-modal.png') }}" alt="Ajout d'un emplacement"><div class="shot-cap">Formulaire d'ajout d'un emplacement</div></div>
          <div class="example"><b>Exemple pratique</b>Créer un emplacement « Site Catering — Nouakchott Nord » permet ensuite de filtrer les présences et d'assigner les employés qui y travaillent, pour un suivi séparé du restaurant principal.</div>
        </section>

        <section class="module" id="contrats">
          <div class="module-head">
            <div class="module-icon" style="background:var(--indigo);">06</div>
            <div><h2>Contrats de travail</h2><p class="lede">CDI, CDD, Stage ou Prestation — avec suivi automatique des fins de contrat et périodes d'essai.</p></div>
          </div>
          <div class="perm-row"><span class="perm-tag">hr.employees.view</span><span class="perm-tag">hr.employees.edit</span></div>

          <div class="card"><img src="{{ asset('guide/hr/09-hr-contracts-list-empty.png') }}" alt="Liste des contrats, état initial"><div class="shot-cap">Écran Contrats — les trois compteurs en haut filtrent la liste au clic : Tous les contrats, Fin de contrat &lt; 30 jours, Expirés (non clôturés)</div></div>

          <h3>Créer un nouveau contrat</h3>
          <div class="grid-2">
            <ol class="steps c-indigo">
              <li>Cliquer sur <b>Nouveau contrat</b>.</li>
              <li>Choisir l'<b>employé</b> concerné (repère 1).</li>
              <li>Choisir le <b>type</b> de contrat (repère 2) : CDI, CDD, Stage ou Prestation. Pour un CDI, le champ « Date de fin » se masque automatiquement puisqu'un CDI n'a pas de terme.</li>
              <li>Indiquer le <b>salaire</b> contractuel (facultatif), la <b>date de début</b> (repère 3, obligatoire) et, pour un CDD/Stage/Prestation, la <b>date de fin</b>.</li>
              <li>Indiquer si besoin la <b>fin de période d'essai</b> et des <b>notes</b> libres.</li>
              <li>Cliquer sur <b>Enregistrer</b>.</li>
            </ol>
            <div class="card"><img src="{{ asset('guide/hr/10-hr-contracts-add-modal-filled.png') }}" alt="Formulaire de nouveau contrat rempli"><div class="shot-cap">Formulaire rempli pour Ahmed Lebah — contrat CDI</div></div>
          </div>

          <div class="card"><img src="{{ asset('guide/hr/11-hr-contracts-list-filled.png') }}" alt="Liste des contrats après création"><div class="shot-cap">Le nouveau contrat apparaît immédiatement dans la liste, statut « Actif »</div></div>

          <div class="example"><b>Règle importante — renouvellement</b>Si l'employé choisi possède déjà un contrat <b>actif</b>, celui-ci est automatiquement basculé au statut <b>Terminé</b> dès l'enregistrement du nouveau contrat. Il n'est donc pas nécessaire de clôturer manuellement l'ancien contrat avant d'en créer un nouveau : c'est la façon normale d'enregistrer un renouvellement ou un changement de type de contrat.</div>

          <table class="ref">
            <tr><th>Statut</th><th>Signification</th></tr>
            <tr><td><span class="badge ok">Actif</span></td><td>Contrat en cours d'exécution</td></tr>
            <tr><td><span class="badge warn">Fin &lt; 30 j</span></td><td>Contrat actif dont la date de fin arrive dans moins de 30 jours — alerte de renouvellement</td></tr>
            <tr><td><span class="badge">Expiré (non clôturé)</span></td><td>La date de fin est dépassée mais le contrat est toujours marqué « Actif » en base — à régulariser (renouveler ou rompre)</td></tr>
            <tr><td>Terminé</td><td>Remplacé automatiquement par un contrat plus récent</td></tr>
            <tr><td>Rompu</td><td>Mis fin manuellement via le bouton <b>Rompre</b>, sans nouveau contrat derrière</td></tr>
          </table>
        </section>

        <section class="module" id="presences">
          <div class="module-head">
            <div class="module-icon" style="background:var(--accent);">07</div>
            <div><h2>Présences &amp; pointage</h2><p class="lede">Suivi journalier des présences, avec un mode « pointage » en libre-service pour chaque employé.</p></div>
          </div>
          <div class="perm-row"><span class="perm-tag">hr.attendance.view</span><span class="perm-tag">hr.attendance.record</span></div>

          <div class="card"><img src="{{ asset('guide/hr/12-hr-attendance-list-empty.png') }}" alt="Écran présences, état initial"><div class="shot-cap">Écran Présences — filtrable par date et par emplacement</div></div>

          <h3>Enregistrer une présence (saisie manuelle)</h3>
          <div class="grid-2">
            <ol class="steps">
              <li>Cliquer sur <b>Enregistrer</b>.</li>
              <li>Choisir l'<b>employé</b> (repère 1), la <b>date</b> et la <b>récession</b> (Matin / Soir).</li>
              <li>Renseigner l'<b>heure d'entrée</b> (repère 2) et l'<b>heure de sortie</b> (repère 3) si connues.</li>
              <li>Choisir le <b>statut</b> (repère 4) : Présent, Absent ou Retard.</li>
              <li>Cliquer sur <b>Enregistrer</b>. Si une présence existe déjà pour cet employé à cette date, elle est mise à jour plutôt que dupliquée.</li>
            </ol>
            <div class="card"><img src="{{ asset('guide/hr/13-hr-attendance-add-modal-filled.png') }}" alt="Formulaire de présence rempli"><div class="shot-cap">Formulaire rempli pour Ahmed Lebah</div></div>
          </div>
          <div class="card"><img src="{{ asset('guide/hr/14-hr-attendance-list-filled.png') }}" alt="Liste des présences après enregistrement"><div class="shot-cap">La présence apparaît dans le tableau et dans les compteurs Présents/Absents/Retards</div></div>

          <h3>Pointage en libre-service</h3>
          <div class="grid-2">
            <ol class="steps">
              <li>Un employé (ou un responsable en son nom) se rend sur l'écran <b>Pointage</b>.</li>
              <li>Bouton <b>Début de travail</b> : enregistre l'heure d'arrivée. Si elle est postérieure à <b>08:00</b>, le statut passe automatiquement à <b>Retard</b>.</li>
              <li>Bouton <b>Fin de travail</b> : enregistre l'heure de départ.</li>
              <li>Chaque bouton se désactive une fois l'action déjà effectuée pour la journée, pour éviter un double pointage.</li>
            </ol>
            <div class="card"><img src="{{ asset('guide/hr/15-hr-clock.png') }}" alt="Écran de pointage"><div class="shot-cap">Écran de pointage — historique des pointages</div></div>
          </div>
          <div class="example"><b>Exemple pratique</b>Un serveur pointe son arrivée à 08:14. Le système détecte automatiquement le dépassement de l'heure de référence (08:00) et enregistre le statut « Retard » sans intervention du responsable RH.</div>
        </section>

        <section class="module" id="conges">
          <div class="module-head">
            <div class="module-icon" style="background:var(--amber);">08</div>
            <div><h2>Congés</h2><p class="lede">Demandes de congé annuel, maladie ou sans solde, avec circuit d'approbation.</p></div>
          </div>
          <div class="perm-row"><span class="perm-tag">hr.leaves.view</span><span class="perm-tag">hr.leaves.request</span><span class="perm-tag">hr.leaves.approve</span><span class="perm-tag">hr.leaves.reject</span></div>

          <div class="card"><img src="{{ asset('guide/hr/16-hr-leaves-list.png') }}" alt="Liste des demandes de congé"><div class="shot-cap">Liste des congés — actions Approuver / Rejeter pour les demandes en attente</div></div>

          <div class="grid-2">
            <ol class="steps c-amber">
              <li>Cliquer sur <b>Nouvelle demande</b>.</li>
              <li>Choisir l'<b>employé</b> et le <b>type</b> de congé : Annuel, Maladie ou Non payé.</li>
              <li>Renseigner les dates de <b>début</b> et de <b>fin</b> — le nombre de jours est calculé automatiquement (inclusif).</li>
              <li>Ajouter une <b>raison</b> (facultatif) puis cliquer sur <b>Soumettre</b>. La demande est créée avec le statut <b>En attente</b>.</li>
            </ol>
            <div class="card"><img src="{{ asset('guide/hr/17-hr-leaves-add-modal.png') }}" alt="Formulaire de demande de congé"><div class="shot-cap">Nouvelle demande de congé</div></div>
          </div>
          <ol class="steps c-amber">
            <li>Un utilisateur disposant du droit d'approbation clique sur la coche verte pour <b>approuver</b>, ou sur la croix rouge pour <b>rejeter</b> une demande en attente.</li>
            <li>Une fois traitée (Approuvé/Rejeté), la demande ne peut plus être modifiée depuis cet écran.</li>
          </ol>
          <div class="example"><b>Exemple pratique</b>Un employé demande un congé annuel du 10 au 14 (inclus) : le système calcule automatiquement 5 jours. Une fois approuvée, cette période apparaît dans le compteur « absent(s) aujourd'hui » du tableau de bord RH pour toute date comprise dans l'intervalle.</div>
        </section>

        <section class="module" id="paie">
          <div class="module-head">
            <div class="module-icon" style="background:var(--accent);">09</div>
            <div><h2>Paie</h2><p class="lede">Génération mensuelle des fiches de paie, paiement et fiche imprimable — avec écriture comptable automatique.</p></div>
          </div>
          <div class="perm-row"><span class="perm-tag">hr.payroll.view</span><span class="perm-tag">hr.payroll.generate</span><span class="perm-tag">hr.payroll.mark-paid</span></div>

          <div class="card"><img src="{{ asset('guide/hr/18-hr-payroll-list.png') }}" alt="Liste des fiches de paie"><div class="shot-cap">Écran Paie — filtrable par mois, année, poste, nom ou téléphone</div></div>

          <h3>1. Générer la paie du mois</h3>
          <ol class="steps">
            <li>Sélectionner le <b>mois</b> et l'<b>année</b> souhaités dans les filtres.</li>
            <li>Cliquer sur <b>Générer paie</b> : une fiche est créée pour chaque employé <b>Actif</b> qui n'en a pas déjà une pour cette période.</li>
          </ol>
          <div class="example"><b>Comment le montant net est calculé</b>
            <code>Net = Salaire de base + Bonus du mois − Déductions du mois − Remboursement d'avance du mois</code><br><br>
            • Le <b>salaire de base</b> vient de la fiche employé (ou, à défaut, du poste).<br>
            • <b>Bonus</b> et <b>déductions</b> proviennent des ajustements de salaire saisis pour ce mois.<br>
            • Le <b>remboursement d'avance</b> est calculé automatiquement pour chaque avance approuvée encore en cours : <code>min(solde restant, salaire de base × % de remboursement mensuel)</code>. Le solde de l'avance est réduit d'autant, et elle passe au statut <b>Soldée</b> dès que le solde atteint zéro.
          </div>

          <h3>2. Marquer une fiche comme payée</h3>
          <div class="grid-2">
            <ol class="steps">
              <li>Sur la ligne d'une fiche <b>En attente</b>, cliquer sur <b>Marquer payé</b>.</li>
              <li>Choisir le <b>mode de paiement</b> (repère 1) : Espèces, Virement bancaire, etc.</li>
              <li>Cliquer sur <b>Confirmer le paiement</b>. La fiche passe au statut <b>Payé</b> et l'écriture comptable correspondante est générée automatiquement (voir <a href="#comptabilite">Écritures comptables</a>).</li>
            </ol>
            <div class="card"><img src="{{ asset('guide/hr/19-hr-payroll-pay-modal.png') }}" alt="Modale de paiement de paie"><div class="shot-cap">Confirmation du paiement d'une fiche de paie</div></div>
          </div>
          <div class="notice" style="margin-top:0;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
            <div>Une fiche déjà payée <b>sans</b> mode de paiement enregistré (import historique, par exemple) affiche un bouton d'alerte <b>« Renseigner le paiement »</b> : tant qu'il n'est pas complété, cette paie reste invisible dans le journal comptable.</div>
          </div>

          <h3>3. Fiche de paie imprimable</h3>
          <div class="grid-2">
            <ol class="steps">
              <li>Cliquer sur l'icône PDF à droite d'une ligne pour ouvrir la fiche de paie individuelle.</li>
              <li>Elle reprend le salaire de base, le total brut, les déductions/avances éventuelles, le mode et la date de paiement, et le net à payer — prête à être imprimée ou téléchargée en PDF.</li>
            </ol>
            <div class="card"><img src="{{ asset('guide/hr/20-hr-payslip.png') }}" alt="Fiche de paie imprimable"><div class="shot-cap">Fiche de paie — Ahmed Lebah, septembre 2026</div></div>
          </div>
        </section>

        <section class="module" id="avances">
          <div class="module-head">
            <div class="module-icon" style="background:var(--violet);">10</div>
            <div><h2>Avances sur salaire</h2><p class="lede">Avance accordée à un employé, remboursée automatiquement par prélèvement sur ses paies suivantes.</p></div>
          </div>
          <div class="perm-row"><span class="perm-tag">hr.advances.view</span><span class="perm-tag">hr.advances.request</span><span class="perm-tag">hr.advances.approve</span></div>

          <div class="card"><img src="{{ asset('guide/hr/21-hr-advances-list-before.png') }}" alt="Liste des avances"><div class="shot-cap">Liste des avances sur salaire</div></div>

          <div class="grid-2">
            <ol class="steps c-violet">
              <li>Cliquer sur <b>Nouvelle avance</b>.</li>
              <li>Choisir l'<b>employé</b> (repère 1).</li>
              <li>Indiquer le <b>montant total</b> de l'avance en MRU (repère 2).</li>
              <li>Définir le <b>pourcentage de remboursement mensuel</b> (repère 3) — la part du salaire prélevée chaque mois jusqu'au remboursement complet (20 % par défaut).</li>
              <li>Vérifier la <b>date</b> (aujourd'hui par défaut) et cliquer sur <b>Enregistrer</b>.</li>
            </ol>
            <div class="card"><img src="{{ asset('guide/hr/22-hr-advances-add-modal-filled.png') }}" alt="Formulaire de nouvelle avance"><div class="shot-cap">Nouvelle avance pour Ahmed Lebah — 5 000 MRU, 20 %/mois</div></div>
          </div>

          <div class="card"><img src="{{ asset('guide/hr/23-hr-advances-list-after.png') }}" alt="Liste des avances après création"><div class="shot-cap">L'avance apparaît immédiatement avec son solde restant à rembourser</div></div>

          <div class="notice" style="margin-top:0;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
            <div><b>Point d'attention pour la formation :</b> contrairement aux congés (où l'approbation est un vrai contrôle avant effet), l'avance est déjà <b>décaissée comptablement dès son enregistrement</b> — l'écriture en trésorerie est postée immédiatement, avant même le clic sur « Approuver ». Le bouton <b>Approuver</b> ne fait pour l'instant que changer le statut affiché ; c'est la génération de paie qui applique ensuite le remboursement mensuel sur les avances approuvées.</div>
          </div>

          <table class="ref">
            <tr><th>Statut</th><th>Signification</th></tr>
            <tr><td><span class="badge warn">En attente</span></td><td>Avance créée, en attente d'approbation</td></tr>
            <tr><td><span class="badge info">En remboursement</span></td><td>Approuvée ; un remboursement mensuel est prélevé automatiquement lors de chaque génération de paie</td></tr>
            <tr><td><span class="badge ok">Soldée</span></td><td>Solde restant tombé à zéro — entièrement remboursée</td></tr>
          </table>
        </section>

        <section class="module" id="comptabilite">
          <div class="module-head">
            <div class="module-icon" style="background:var(--indigo);">11</div>
            <div><h2>Écritures comptables générées automatiquement</h2><p class="lede">Deux opérations RH postent directement une écriture en partie double dans le journal comptable — sans aucune saisie manuelle côté comptabilité.</p></div>
          </div>

          <h3>A. Avance sur salaire (à l'enregistrement)</h3>
          <p style="color:var(--ink-soft); font-size:14px;">Dès qu'une avance est créée, elle est enregistrée comme une créance sur le personnel, réglée immédiatement en trésorerie.</p>
          <div class="ledger">
            <div class="ledger-head"><b>Avance sur salaire — Ahmed Lebah</b><span>Réf. ADV-6 · Journal CA</span></div>
            <table>
              <tr><th>Compte</th><th>Libellé</th><th style="text-align:right">Débit</th><th style="text-align:right">Crédit</th></tr>
              <tr><td class="acct">42</td><td>Personnel — comptes courants</td><td class="num debit">5 000,00</td><td class="num"></td></tr>
              <tr><td class="acct">56</td><td>Caisse</td><td class="num"></td><td class="num credit">5 000,00</td></tr>
            </table>
          </div>
          <div class="card"><img src="{{ asset('guide/hr/24-accounting-journal-advance.png') }}" alt="Écriture comptable de l'avance dans le journal"><div class="shot-cap">Capture réelle du journal comptable après création de l'avance</div></div>

          <h3>B. Paiement de la paie (au clic sur « Marquer payé »)</h3>
          <p style="color:var(--ink-soft); font-size:14px;">Trois lignes possibles : la charge de personnel au montant brut, le remboursement d'avance (s'il y en a un ce mois-ci) qui solde le compte personnel, et le décaissement net en trésorerie.</p>
          <div class="ledger">
            <div class="ledger-head"><b>Salaire — Sans Compte, 9/2026</b><span>Réf. PAIE-191 · Journal CA</span></div>
            <table>
              <tr><th>Compte</th><th>Libellé</th><th style="text-align:right">Débit</th><th style="text-align:right">Crédit</th></tr>
              <tr><td class="acct">65000001</td><td>Charges de personnel — Salaires</td><td class="num debit">60 000,00</td><td class="num"></td></tr>
              <tr><td class="acct">56</td><td>Caisse</td><td class="num"></td><td class="num credit">60 000,00</td></tr>
            </table>
          </div>
          <div class="example"><b>Avec remboursement d'avance en cours</b>Si l'employé rembourse une avance ce mois-ci, une troisième ligne apparaît : <code>Débit 65000001 (brut) / Crédit 42 (remboursement avance) / Crédit trésorerie (net)</code>. Le compte 42 « Personnel » est ainsi soldé progressivement à mesure des remboursements, en miroir de l'avance initiale.</div>
          <div class="card"><img src="{{ asset('guide/hr/25-accounting-journal-payroll.png') }}" alt="Écritures comptables de paie dans le journal"><div class="shot-cap">Capture réelle du journal — écritures PAIE- du 04/09/2026</div></div>

          <h3>Règles communes aux deux écritures</h3>
          <table class="ref">
            <tr><th>Règle</th><th>Détail</th></tr>
            <tr><td>Compte de trésorerie</td><td>Déterminé par le <b>mode de paiement</b> choisi ; à défaut, le compte de caisse par défaut de l'application est utilisé</td></tr>
            <tr><td>Code journal</td><td><span class="mono">CA</span> si le compte de trésorerie est une caisse (racine 56), <span class="mono">BQ</span> si c'est une banque (racine 50), sinon <span class="mono">OD</span> (opérations diverses)</td></tr>
            <tr><td>Idempotence</td><td>Chaque écriture n'est postée <b>qu'une seule fois</b> par avance / par fiche de paie — impossible de la dupliquer en re-cliquant</td></tr>
            <tr><td>Où consulter</td><td>Comptabilité → <b>Journal</b> (toutes les écritures), ou <b>Grand Livre</b> / <b>Balance</b> pour une vue par compte</td></tr>
          </table>
        </section>

        <section class="module" id="permissions">
          <div class="module-head">
            <div class="module-icon" style="background:var(--ink-faint);">12</div>
            <div><h2>Rôles &amp; permissions</h2><p class="lede">Chaque action du module RH est protégée par une permission nommée — utile pour construire les profils d'accès (rôles) de vos utilisateurs.</p></div>
          </div>
          <div class="tbl-wrap">
          <table class="ref">
            <tr><th>Permission</th><th>Donne accès à</th></tr>
            <tr><td class="mono">hr.dashboard</td><td>Voir le tableau de bord RH</td></tr>
            <tr><td class="mono">hr.employees.view</td><td>Voir la liste des employés, des contrats et des emplacements</td></tr>
            <tr><td class="mono">hr.employees.create</td><td>Ajouter un employé</td></tr>
            <tr><td class="mono">hr.employees.edit</td><td>Modifier un employé, gérer ses documents, créer/rompre un contrat, gérer les emplacements</td></tr>
            <tr><td class="mono">hr.employees.delete</td><td>Supprimer un employé ou un emplacement</td></tr>
            <tr><td class="mono">hr.attendance.view</td><td>Consulter les présences et le pointage</td></tr>
            <tr><td class="mono">hr.attendance.record</td><td>Saisir manuellement une présence</td></tr>
            <tr><td class="mono">hr.leaves.view</td><td>Voir les demandes de congé</td></tr>
            <tr><td class="mono">hr.leaves.request</td><td>Soumettre une demande de congé</td></tr>
            <tr><td class="mono">hr.leaves.approve</td><td>Approuver une demande de congé</td></tr>
            <tr><td class="mono">hr.leaves.reject</td><td>Rejeter une demande de congé</td></tr>
            <tr><td class="mono">hr.payroll.view</td><td>Voir les fiches de paie et les imprimer</td></tr>
            <tr><td class="mono">hr.payroll.generate</td><td>Générer les fiches de paie du mois</td></tr>
            <tr><td class="mono">hr.payroll.mark-paid</td><td>Marquer une fiche comme payée (déclenche l'écriture comptable)</td></tr>
            <tr><td class="mono">hr.advances.view</td><td>Voir les avances sur salaire</td></tr>
            <tr><td class="mono">hr.advances.request</td><td>Créer une nouvelle avance</td></tr>
            <tr><td class="mono">hr.advances.approve</td><td>Approuver une avance en attente</td></tr>
          </table>
          </div>
          <div class="example"><b>Pour les formateurs</b>Les permissions se distribuent par rôle dans Paramètres → Utilisateurs. Un profil « Agent RH terrain » peut par exemple recevoir <span class="mono">hr.attendance.record</span> et <span class="mono">hr.leaves.request</span> sans avoir accès à la paie ni aux avances, pour un accès strictement limité à son périmètre.</div>
        </section>

        <section class="module" id="faq" style="margin-bottom:20px;">
          <div class="module-head">
            <div class="module-icon" style="background:var(--ink-faint);">13</div>
            <div><h2>Questions fréquentes</h2></div>
          </div>

          <div class="faq"><b>Pourquoi un contrat que je viens de créer a-t-il fait disparaître l'ancien ?</b><p>C'est le comportement normal : créer un nouveau contrat <b>actif</b> pour un employé clôture automatiquement (statut « Terminé ») tout contrat actif précédent — c'est ainsi que l'application gère les renouvellements.</p></div>

          <div class="faq"><b>Une fiche de paie « Payée » n'apparaît pas dans le journal comptable — pourquoi ?</b><p>Vérifiez qu'un <b>mode de paiement</b> a bien été renseigné lors du marquage. Sans mode de paiement, l'écriture comptable n'est pas générée ; l'écran Paie affiche alors un bouton d'alerte « Renseigner le paiement » sur cette ligne.</p></div>

          <div class="faq"><b>Une avance approuvée n'est jamais déduite de la paie — pourquoi ?</b><p>Le remboursement n'est calculé qu'au moment où l'on clique sur <b>Générer paie</b> pour le mois concerné, et seulement pour les avances au statut <b>En remboursement</b> (approuvées) avec un solde restant supérieur à zéro. Si la paie du mois a déjà été générée avant l'approbation de l'avance, il faudra attendre le mois suivant.</p></div>

          <div class="faq"><b>Peut-on supprimer une fiche de paie déjà payée pour corriger une erreur ?</b><p>Non, ce n'est pas prévu depuis l'interface RH — une fois postée, l'écriture comptable associée ne doit pas être laissée orpheline. Toute correction doit passer par un ajustement de salaire (bonus/déduction négatif) sur la période suivante, ou une intervention directe en base par un administrateur technique.</p></div>

          <div class="faq"><b>Le champ « Date d'embauche » apparaît vide en ouvrant le formulaire de modification d'un employé, alors qu'il est bien renseigné.</b><p>C'est un défaut d'affichage connu du formulaire de modification (le champ n'est pas repeuplé correctement) : la date réelle en base n'est pas perdue, mais il faut la ressaisir manuellement avant d'enregistrer, sous peine de l'effacer par erreur. Signalé pour correction.</p></div>
        </section>

      </main>
    </div>

    <script>
      document.querySelectorAll('.nav-link').forEach(function (a) {
        a.addEventListener('click', function () {
          document.getElementById('sidebar').classList.remove('open');
          document.getElementById('scrim') && document.getElementById('scrim').classList.remove('show');
        });
      });
    </script>
</body>
</html>
