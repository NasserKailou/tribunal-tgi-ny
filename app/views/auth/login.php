<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a3c5e 0%, #0d2240 100%); min-height: 100vh; display:flex; align-items:center; justify-content:center; }
        .login-card { width: 100%; max-width: 440px; border-radius: 1rem; overflow:hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
        .login-header { background: #1a3c5e; color:#fff; padding: 2.5rem 2rem 1.5rem; text-align:center; }
        .login-header .logo-circle { width:80px;height:80px;border-radius:50%;background:rgba(201,162,39,0.2);display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem; }
        .login-header h1 { font-size:1.6rem;font-weight:700;margin:0; }
        .login-header p { font-size:0.8rem;opacity:0.7;margin:0.3rem 0 0; }
        .login-body { background:#fff; padding: 2rem; }
        .btn-login { background: linear-gradient(135deg,#c9a227,#a88020);border:none;color:#fff;font-weight:600;padding:0.7rem; }
        .btn-login:hover { background: linear-gradient(135deg,#a88020,#8a6a1a);color:#fff; }
        .gold-bar { height:4px;background:linear-gradient(90deg,#c9a227,#f0d060,#c9a227); }
    </style>
</head>
<body>
<div class="login-card">
    <div class="gold-bar"></div>
    <div class="login-header">
        <div class="logo-circle"><i class="bi bi-balance-scale" style="font-size:2.5rem;color:#c9a227"></i></div>
        <h1>TGI-NY</h1>
        <p>Tribunal de Grande Instance Hors Classe de Niamey</p>
    </div>
    <div class="login-body">
        <h5 class="mb-4 text-center text-secondary fw-normal">Connexion au système</h5>

        <?php $flash = $flash ?? []; ?>
        <?php if (!empty($flash['error'])): foreach ($flash['error'] as $msg): ?>
        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($msg) ?></div>
        <?php endforeach; endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login" novalidate>
            <?= CSRF::field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Adresse email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="votre@email.ne" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required id="pwdInput">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePwd()"><i class="bi bi-eye" id="pwdIcon"></i></button>
                </div>
            </div>
            <button type="submit" class="btn btn-login w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
            </button>
        </form>
    </div>
    <div class="gold-bar"></div>
</div>
<script>
function togglePwd(){
    const i=document.getElementById('pwdInput');
    const ic=document.getElementById('pwdIcon');
    i.type=i.type==='password'?'text':'password';
    ic.className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';
}
</script>
</body>
</html>
