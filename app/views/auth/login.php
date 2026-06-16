<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary:   #0a2342;
            --secondary: #1c4f82;
            --gold:      #c9a227;
            --gold-light:#f0d060;
            --light-bg:  #f4f6f9;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: var(--primary);
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(28,79,130,0.6) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(201,162,39,0.1) 0%, transparent 50%);
            display: flex;
            flex-direction: column;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* ── Top institutional bar ── */
        .gov-bar {
            background: linear-gradient(90deg, #051525 0%, #0a2342 50%, #051525 100%);
            border-bottom: 3px solid var(--gold);
            padding: 10px 0;
        }
        .gov-bar-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .gov-title {
            color: #fff;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            opacity: 0.85;
        }
        .gov-title strong {
            color: var(--gold-light);
            font-size: 0.82rem;
            display: block;
        }
        .gov-emblem {
            width: 48px;
            height: 48px;
            background: radial-gradient(circle, rgba(201,162,39,0.25) 0%, transparent 70%);
            border: 2px solid rgba(201,162,39,0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* ── Main wrapper ── */
        .login-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .login-container {
            width: 100%;
            max-width: 940px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0,0,0,0.55), 0 0 0 1px rgba(201,162,39,0.2);
        }

        /* ── Left panel: presentation ── */
        .login-left {
            background: linear-gradient(160deg, #0e2f52 0%, #0a2342 60%, #061a30 100%);
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 250px; height: 250px;
            background: radial-gradient(circle, rgba(201,162,39,0.15) 0%, transparent 70%);
            pointer-events: none;
        }
        .login-left::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(28,79,130,0.4) 0%, transparent 70%);
            pointer-events: none;
        }

        .court-badge {
            width: 90px; height: 90px;
            background: rgba(201,162,39,0.15);
            border: 2px solid rgba(201,162,39,0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }
        /* Logo institutionnel */
        .logos-block {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1.2rem;
            position: relative;
            z-index: 1;
        }
        .logos-block img {
            max-width: 280px;
            width: 100%;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.35));
        }
        .court-name {
            color: #fff;
            position: relative;
            z-index: 1;
        }
        .court-name .tgi-code {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gold-light);
            letter-spacing: 0.05em;
            line-height: 1;
        }
        .court-name .tgi-full {
            font-size: 0.85rem;
            opacity: 0.8;
            margin-top: 0.35rem;
            line-height: 1.4;
            max-width: 220px;
        }

        .gold-divider {
            height: 2px;
            background: linear-gradient(90deg, var(--gold), transparent);
            margin: 1.5rem 0;
        }

        .features-list {
            list-style: none;
            padding: 0;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        .features-list li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            color: rgba(255,255,255,0.75);
            font-size: 0.82rem;
            margin-bottom: 0.8rem;
            line-height: 1.4;
        }
        .features-list li .bi {
            color: var(--gold);
            font-size: 1rem;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }

        .left-footer {
            position: relative;
            z-index: 1;
        }
        .left-footer small {
            color: rgba(255,255,255,0.4);
            font-size: 0.72rem;
        }

        /* ── Right panel: form ── */
        .login-right {
            background: #fff;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-right h2 {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.35rem;
        }
        .login-right .subtitle {
            color: #6b7280;
            font-size: 0.85rem;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.82rem;
            color: #374151;
            margin-bottom: 0.35rem;
        }

        .form-control, .input-group .form-control {
            border: 1.5px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.65rem 0.9rem;
            font-size: 0.9rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(28,79,130,0.12);
        }
        .input-group-text {
            background: #f9fafb;
            border: 1.5px solid #d1d5db;
            border-radius: 0.5rem 0 0 0.5rem;
            color: #6b7280;
        }
        .input-group .form-control {
            border-radius: 0 0.5rem 0.5rem 0;
            border-left: 0;
        }
        .input-group .btn-outline-secondary {
            border: 1.5px solid #d1d5db;
            border-left: 0;
            border-radius: 0 0.5rem 0.5rem 0;
            color: #6b7280;
            background: #f9fafb;
        }
        .input-group .btn-outline-secondary:hover { background: #f3f4f6; }

        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            letter-spacing: 0.03em;
            transition: opacity .2s, transform .1s;
            width: 100%;
            margin-top: 0.5rem;
        }
        .btn-login:hover { opacity: 0.92; color: #fff; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }

        .gold-accent-bar {
            height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-light), var(--gold));
            border-radius: 3px;
            margin-bottom: 1.5rem;
        }

        .help-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: #9ca3af;
        }
        .help-link a { color: var(--secondary); text-decoration: none; }
        .help-link a:hover { text-decoration: underline; }

        /* ── Footer ── */
        .login-footer-bar {
            background: rgba(0,0,0,0.35);
            border-top: 1px solid rgba(201,162,39,0.2);
            padding: 0.75rem 1rem;
            text-align: center;
        }
        .login-footer-bar small {
            color: rgba(255,255,255,0.35);
            font-size: 0.72rem;
        }

        /* ── Alert ── */
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 0.5rem;
            font-size: 0.85rem;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .login-container { grid-template-columns: 1fr; max-width: 420px; }
            .login-left { padding: 2rem 1.5rem; }
            .features-list { display: none; }
            .gold-divider { margin: 1rem 0; }
            .court-name .tgi-full { font-size: 0.78rem; }
            .login-right { padding: 2rem 1.5rem; }
            .gov-bar-inner { flex-direction: column; text-align: center; gap: 0.5rem; }
        }
    </style>
</head>
<body>

<!-- Top institutional bar -->
<div class="gov-bar">
    <div class="gov-bar-inner">
        <div class="gov-title">
            République du Niger
            <strong>Ministère de la Justice — Garde des Sceaux</strong>
        </div>
        <div class="gov-emblem">
            <img src="<?= BASE_URL ?>/assets/img/logos_tgi.png"
                 alt="Logos"
                 style="width:44px;height:44px;object-fit:contain"
                 onerror="this.outerHTML='<i class=\'bi bi-shield-fill\' style=\'font-size:1.4rem;color:#c9a227;\'></i>'">
        </div>
        <div class="gov-title" style="text-align:right;">
            Système de Gestion Judiciaire
            <strong>Accès Sécurisé — Personnel Habilité</strong>
        </div>
    </div>
</div>

<!-- Main -->
<div class="login-wrapper">
    <div class="login-container">

        <!-- Left panel -->
        <div class="login-left">
            <div>
                <!-- Logos institutionnels -->
                <div class="logos-block">
                    <img src="<?= BASE_URL ?>/assets/img/logos_tgi.png"
                         alt="République du Niger — FAJ"
                         onerror="this.style.display='none';document.getElementById('fallbackBadge').style.display='flex'">
                </div>
                <!-- Fallback si image non chargée -->
                <div class="court-badge" id="fallbackBadge" style="display:none">
                    <i class="bi bi-balance-scale" style="font-size:2.4rem; color:#c9a227;"></i>
                </div>
                <div class="court-name">
                    <div class="tgi-code">TGI/HC</div>
                    <div class="tgi-full">Tribunal de Grande Instance<br>Hors Classe de Niamey</div>
                </div>
                <div class="gold-divider"></div>
                <ul class="features-list">
                    <li><i class="bi bi-folder2-open"></i>Gestion des dossiers judiciaires</li>
                    <li><i class="bi bi-file-text"></i>Suivi des procès-verbaux</li>
                    <li><i class="bi bi-calendar-week"></i>Planification des audiences</li>
                    <li><i class="bi bi-hammer"></i>Enregistrement des jugements</li>
                    <li><i class="bi bi-person-lock"></i>Population carcérale</li>
                    <li><i class="bi bi-file-ruled"></i>Émission des mandats</li>
                    <li><i class="bi bi-map"></i>Carte antiterroriste du Niger</li>
                </ul>
            </div>
            <div class="left-footer">
                <small>© <?= date('Y') ?> TGI/HC de Niamey — v<?= APP_VERSION ?></small>
            </div>
        </div>

        <!-- Right panel -->
        <div class="login-right">
            <div class="gold-accent-bar"></div>
            <h2><i class="bi bi-box-arrow-in-right me-2" style="color:#c9a227;"></i>Connexion</h2>
            <p class="subtitle">Accès réservé au personnel du tribunal habilité</p>

            <?php $flash = $flash ?? []; ?>
            <?php if (!empty($flash['error'])): foreach ($flash['error'] as $msg): ?>
            <div class="alert alert-danger py-2 small mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($msg) ?>
            </div>
            <?php endforeach; endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/login" novalidate>
                <?= CSRF::field() ?>

                <div class="mb-3">
                    <label class="form-label">Adresse email professionnelle</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                        <input type="email" name="email" class="form-control"
                               placeholder="prenom.nom@tgi-niamey.ne"
                               required autofocus
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password" class="form-control"
                               placeholder="••••••••" required id="pwdInput">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePwd()" tabindex="-1">
                            <i class="bi bi-eye" id="pwdIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter au système
                </button>
            </form>

            <div class="help-link">
                <i class="bi bi-info-circle me-1"></i>
                Problème de connexion ? Contactez
                <a href="mailto:admin@tgi-niamey.ne">l'administrateur système</a>
            </div>
        </div>

    </div>
</div>

<!-- Footer bar -->
<div class="login-footer-bar">
    <small>
        <i class="bi bi-shield-lock me-1"></i>
        Connexion sécurisée · Tribunal de Grande Instance Hors Classe de Niamey ·
        Tous droits réservés <?= date('Y') ?>
    </small>
</div>

<script>
function togglePwd() {
    const i  = document.getElementById('pwdInput');
    const ic = document.getElementById('pwdIcon');
    i.type = i.type === 'password' ? 'text' : 'password';
    ic.className = i.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
