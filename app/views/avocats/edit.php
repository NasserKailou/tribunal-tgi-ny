<?php $pageTitle = 'Modifier avocat'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/avocats">Avocats</a></li><li class="breadcrumb-item"><a href="<?=BASE_URL?>/avocats/show/<?=$avocat['id']?>"><?=htmlspecialchars($avocat['nom'].' '.$avocat['prenom'])?></a></li><li class="breadcrumb-item active">Modifier</li></ol></nav>
    <h4 class="fw-bold">Modifier avocat — <?=htmlspecialchars($avocat['nom'].' '.$avocat['prenom'])?></h4>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?=BASE_URL?>/avocats/update/<?=$avocat['id']?>">
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Nom *</label><input type="text" name="nom" class="form-control" value="<?=htmlspecialchars($avocat['nom'])?>" required></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Prénom *</label><input type="text" name="prenom" class="form-control" value="<?=htmlspecialchars($avocat['prenom'])?>" required></div>
                <div class="col-md-4"><label class="form-label">Statut</label><select name="statut" class="form-select"><?php foreach(['actif','suspendu','radié','honoraire'] as $s): ?><option value="<?=$s?>" <?=$avocat['statut']===$s?'selected':''?>><?=ucfirst($s)?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Téléphone</label><input type="text" name="telephone" class="form-control" value="<?=htmlspecialchars($avocat['telephone']??'')?>"></div>
                <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?=htmlspecialchars($avocat['email']??'')?>"></div>
                <div class="col-md-4"><label class="form-label">Barreau</label><input type="text" name="barreau" class="form-control" value="<?=htmlspecialchars($avocat['barreau']??'Barreau de Niamey')?>"></div>
                <div class="col-md-4"><label class="form-label">N° ordre</label><input type="text" name="numero_ordre" class="form-control" value="<?=htmlspecialchars($avocat['numero_ordre']??'')?>"></div>
                <div class="col-md-4"><label class="form-label">Date inscription</label><input type="date" name="date_inscription" class="form-control" value="<?=htmlspecialchars($avocat['date_inscription']??'')?>"></div>
                <div class="col-md-4"><label class="form-label">Spécialité</label><input type="text" name="specialite" class="form-control" value="<?=htmlspecialchars($avocat['specialite']??'')?>"></div>
                <div class="col-12"><label class="form-label">Adresse</label><textarea name="adresse" class="form-control" rows="2"><?=htmlspecialchars($avocat['adresse']??'')?></textarea></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?=htmlspecialchars($avocat['notes']??'')?></textarea></div>
                <div class="col-md-4"><label class="form-label">Nationalité</label><input type="text" name="nationalite" class="form-control" value="<?=htmlspecialchars($avocat['nationalite']??'Nigérienne')?>"></div>
                <div class="col-md-4"><label class="form-label">Sexe</label><select name="sexe" class="form-select"><option value="M" <?=$avocat['sexe']==='M'?'selected':''?>>M</option><option value="F" <?=$avocat['sexe']==='F'?'selected':''?>>F</option></select></div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Enregistrer</button>
                <a href="<?=BASE_URL?>/avocats/show/<?=$avocat['id']?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
