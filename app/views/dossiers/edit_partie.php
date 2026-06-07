<?php $pageTitle = 'Modifier la partie — ' . htmlspecialchars($partie['nom'] . ' ' . $partie['prenom']); ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=BASE_URL?>/dossiers">Dossiers</a></li>
        <li class="breadcrumb-item"><a href="<?=BASE_URL?>/dossiers/show/<?=$partie['dossier_id']?>">Dossier</a></li>
        <li class="breadcrumb-item active">Modifier partie</li>
    </ol></nav>
    <h4 class="fw-bold"><i class="bi bi-person-gear me-2 text-primary"></i>Modifier la partie</h4>
</div>

<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white fw-semibold">
    <i class="bi bi-person me-2"></i><?=htmlspecialchars($partie['nom'].' '.$partie['prenom'])?>
  </div>
  <div class="card-body">
    <form method="POST" action="<?=BASE_URL?>/dossiers/partie/update/<?=$partie['id']?>">
      <?=CSRF::field()?>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Type de partie <span class="text-danger">*</span></label>
          <select name="type_partie" class="form-select" required>
            <?php
            $types = [
                'plaignant'   => 'Plaignant',
                'defendeur'   => 'Défendeur',
                'prevenu'     => 'Prévenu',
                'victime'     => 'Victime',
                'avocat'      => 'Avocat',
                'temoin'      => 'Témoin',
                'mis_en_cause'=> 'Mis en cause',
            ];
            foreach($types as $v=>$l): ?>
            <option value="<?=$v?>" <?=$partie['type_partie']===$v?'selected':''?>><?=$l?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Nom <span class="text-danger">*</span></label>
          <input type="text" name="nom" class="form-control" required
                 value="<?=htmlspecialchars($partie['nom'])?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Prénom</label>
          <input type="text" name="prenom" class="form-control"
                 value="<?=htmlspecialchars($partie['prenom']??'')?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Date de naissance</label>
          <input type="date" name="date_naissance" class="form-control"
                 value="<?=htmlspecialchars($partie['date_naissance']??'')?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Nationalité</label>
          <input type="text" name="nationalite" class="form-control"
                 value="<?=htmlspecialchars($partie['nationalite']??'Nigérienne')?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Profession</label>
          <input type="text" name="profession" class="form-control"
                 value="<?=htmlspecialchars($partie['profession']??'')?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Téléphone</label>
          <input type="text" name="telephone" class="form-control"
                 value="<?=htmlspecialchars($partie['telephone']??'')?>">
        </div>
        <div class="col-12">
          <label class="form-label">Adresse</label>
          <textarea name="adresse" class="form-control" rows="2"><?=htmlspecialchars($partie['adresse']??'')?></textarea>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-1"></i>Enregistrer
        </button>
        <a href="<?=BASE_URL?>/dossiers/show/<?=$partie['dossier_id']?>" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </form>
  </div>
</div>
</div>
</div>
