<?php $pageTitle = 'Nouvel avocat'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/avocats">Avocats</a></li><li class="breadcrumb-item active">Nouveau</li></ol></nav>
    <h4 class="fw-bold"><i class="bi bi-person-badge me-2 text-primary"></i>Enregistrer un avocat</h4>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?=BASE_URL?>/avocats/store">
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Nom *</label><input type="text" name="nom" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Prénom *</label><input type="text" name="prenom" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Matricule (auto si vide)</label><input type="text" name="matricule" class="form-control" placeholder="AV-2025-0001"></div>
                <div class="col-md-4"><label class="form-label">Date de naissance</label><input type="date" name="date_naissance" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Lieu de naissance</label><input type="text" name="lieu_naissance" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Sexe</label><select name="sexe" class="form-select"><option value="M">M</option><option value="F">F</option></select></div>
                <div class="col-md-2"><label class="form-label">Nationalité</label><input type="text" name="nationalite" class="form-control" value="Nigérienne"></div>
                <div class="col-md-4"><label class="form-label">Téléphone</label><input type="text" name="telephone" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Barreau</label><input type="text" name="barreau" class="form-control" value="Barreau de Niamey"></div>
                <div class="col-md-4"><label class="form-label">N° d'ordre</label><input type="text" name="numero_ordre" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Date d'inscription</label><input type="date" name="date_inscription" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Spécialité</label><input type="text" name="specialite" class="form-control" placeholder="Pénal, Civil, Commercial…"></div>
                <div class="col-12"><label class="form-label">Adresse</label><textarea name="adresse" class="form-control" rows="2"></textarea></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Enregistrer</button>
                <a href="<?=BASE_URL?>/avocats" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
