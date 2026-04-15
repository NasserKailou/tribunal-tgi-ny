<?php $pageTitle = 'Nouvel utilisateur'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/users">Utilisateurs</a></li><li class="breadcrumb-item active">Nouveau</li></ol></nav>
    <h4 class="fw-bold"><i class="bi bi-person-plus me-2 text-primary"></i>Créer un utilisateur</h4>
</div>
<div class="row justify-content-center">
<div class="col-md-8 col-lg-6">
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?=BASE_URL?>/users/store" novalidate>
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nom <span class="text-danger">*</span></label><input type="text" name="nom" class="form-control" required value="<?=htmlspecialchars($_POST['nom']??'')?>"></div>
                <div class="col-md-6"><label class="form-label">Prénom <span class="text-danger">*</span></label><input type="text" name="prenom" class="form-control" required value="<?=htmlspecialchars($_POST['prenom']??'')?>"></div>
                <div class="col-12"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required value="<?=htmlspecialchars($_POST['email']??'')?>"></div>
                <div class="col-12"><label class="form-label">Rôle <span class="text-danger">*</span></label>
                    <select name="role_id" class="form-select" required>
                        <option value="">— Sélectionner un rôle —</option>
                        <?php foreach($roles as $r): ?><option value="<?=$r['id']?>" <?=($_POST['role_id']??'')==$r['id']?'selected':''?>><?=htmlspecialchars($r['libelle'])?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Matricule</label><input type="text" name="matricule" class="form-control" value="<?=htmlspecialchars($_POST['matricule']??'')?>"></div>
                <div class="col-md-6"><label class="form-label">Téléphone</label><input type="text" name="telephone" class="form-control" value="<?=htmlspecialchars($_POST['telephone']??'')?>"></div>
                <div class="col-12"><label class="form-label">Mot de passe <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required minlength="8"></div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-save me-1"></i>Créer</button>
                <a href="<?=BASE_URL?>/users" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
