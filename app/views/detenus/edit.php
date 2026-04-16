<?php $pageTitle = 'Modifier détenu — ' . $detenu['numero_ecrou']; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=BASE_URL?>/detenus">Population Carcérale</a></li>
        <li class="breadcrumb-item"><a href="<?=BASE_URL?>/detenus/show/<?=$detenu['id']?>"><?=htmlspecialchars($detenu['numero_ecrou'])?></a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol></nav>
    <h4 class="fw-bold"><i class="bi bi-pencil me-2 text-danger"></i>Modifier le détenu</h4>
</div>

<div class="row justify-content-center">
<div class="col-lg-9">
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex align-items-center gap-2">
    <i class="bi bi-person-lock text-danger fs-5"></i>
    <span class="fw-semibold"><?=htmlspecialchars($detenu['nom'].' '.$detenu['prenom'])?></span>
    <span class="badge bg-secondary ms-auto font-monospace"><?=htmlspecialchars($detenu['numero_ecrou'])?></span>
  </div>
  <div class="card-body">
    <form method="POST" action="<?=BASE_URL?>/detenus/update/<?=$detenu['id']?>"
          enctype="multipart/form-data" novalidate>
      <?=CSRF::field()?>

      <!-- Identité -->
      <h6 class="text-uppercase text-muted small fw-semibold mb-3 border-bottom pb-1">
        <i class="bi bi-person me-1"></i>Identité
      </h6>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label">Nom <span class="text-danger">*</span></label>
          <input type="text" name="nom" class="form-control" required
                 value="<?=htmlspecialchars($_POST['nom']??$detenu['nom'])?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Prénom <span class="text-danger">*</span></label>
          <input type="text" name="prenom" class="form-control" required
                 value="<?=htmlspecialchars($_POST['prenom']??$detenu['prenom'])?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Surnom / Alias</label>
          <input type="text" name="surnom_alias" class="form-control"
                 value="<?=htmlspecialchars($_POST['surnom_alias']??$detenu['surnom_alias']??'')?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Sexe <span class="text-danger">*</span></label>
          <select name="sexe" class="form-select" required>
            <option value="M" <?=($_POST['sexe']??$detenu['sexe']??'M')==='M'?'selected':''?>>Masculin</option>
            <option value="F" <?=($_POST['sexe']??$detenu['sexe']??'')==='F'?'selected':''?>>Féminin</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Date de naissance</label>
          <input type="date" name="date_naissance" class="form-control"
                 value="<?=htmlspecialchars($_POST['date_naissance']??$detenu['date_naissance']??'')?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Lieu de naissance</label>
          <input type="text" name="lieu_naissance" class="form-control"
                 value="<?=htmlspecialchars($_POST['lieu_naissance']??$detenu['lieu_naissance']??'')?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Nationalité</label>
          <input type="text" name="nationalite" class="form-control"
                 value="<?=htmlspecialchars($_POST['nationalite']??$detenu['nationalite']??'Nigérienne')?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Profession</label>
          <input type="text" name="profession" class="form-control"
                 value="<?=htmlspecialchars($_POST['profession']??$detenu['profession']??'')?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Nom de la mère</label>
          <input type="text" name="nom_mere" class="form-control"
                 value="<?=htmlspecialchars($_POST['nom_mere']??$detenu['nom_mere']??'')?>">
        </div>
      </div>

      <!-- Situation familiale -->
      <h6 class="text-uppercase text-muted small fw-semibold mb-3 border-bottom pb-1">
        <i class="bi bi-people me-1"></i>Situation familiale
      </h6>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label">Statut matrimonial</label>
          <select name="statut_matrimonial" id="statutMatrimonial" class="form-select"
                  onchange="toggleEnfants(this.value)">
            <?php
            $currentSM = $_POST['statut_matrimonial'] ?? $detenu['statut_matrimonial'] ?? 'celibataire';
            foreach(['celibataire'=>'Célibataire','marie'=>'Marié(e)','divorce'=>'Divorcé(e)','veuf'=>'Veuf/Veuve'] as $v=>$l): ?>
            <option value="<?=$v?>" <?=$currentSM===$v?'selected':''?>><?=$l?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6" id="rowNbEnfants"
             style="display:<?=$currentSM==='marie'?'block':'none'?>">
          <label class="form-label">Nombre d'enfants</label>
          <input type="number" name="nombre_enfants" class="form-control" min="0" max="99"
                 value="<?=(int)($_POST['nombre_enfants']??$detenu['nombre_enfants']??0)?>">
        </div>
      </div>

      <!-- Détention -->
      <h6 class="text-uppercase text-muted small fw-semibold mb-3 border-bottom pb-1">
        <i class="bi bi-lock me-1"></i>Détention
      </h6>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label">Type de détention <span class="text-danger">*</span></label>
          <select name="type_detention" class="form-select" required>
            <?php foreach(['prevenu'=>'Prévenu','inculpe'=>'Inculpé','condamne'=>'Condamné','detenu_provisoire'=>'Détenu provisoire','mis_en_examen'=>'Mis en examen','autre'=>'Autre'] as $v=>$l): ?>
            <option value="<?=$v?>" <?=($_POST['type_detention']??$detenu['type_detention'])===$v?'selected':''?>>
              <?=$l?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Cellule</label>
          <input type="text" name="cellule" class="form-control"
                 value="<?=htmlspecialchars($_POST['cellule']??$detenu['cellule']??'')?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Maison d'arrêt</label>
          <select name="maison_arret_id" class="form-select">
            <option value="">— Sélectionner —</option>
            <?php $currentMA = $_POST['maison_arret_id'] ?? $detenu['maison_arret_id'] ?? '';
            foreach($maisonArrets as $ma): ?>
            <option value="<?=$ma['id']?>" <?=(string)$currentMA===(string)$ma['id']?'selected':''?>>
              <?=htmlspecialchars($ma['nom'])?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Établissement <small class="text-muted">(saisie libre)</small></label>
          <input type="text" name="etablissement" class="form-control"
                 value="<?=htmlspecialchars($_POST['etablissement']??$detenu['etablissement']??'')?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Date de naissance</label>
          <input type="date" name="date_naissance" class="form-control"
                 value="<?=$_POST['date_naissance']??$detenu['date_naissance']??''?>" readonly>
          <small class="text-muted">Modifiable uniquement par un administrateur</small>
        </div>
      </div>

      <!-- Photo -->
      <h6 class="text-uppercase text-muted small fw-semibold mb-3 border-bottom pb-1">
        <i class="bi bi-camera me-1"></i>Photo d'identité
      </h6>
      <div class="row g-3 mb-4">
        <?php if(!empty($detenu['photo_identite'])): ?>
        <div class="col-md-3">
          <img src="<?=BASE_URL?>/<?=htmlspecialchars($detenu['photo_identite'])?>"
               class="img-thumbnail" style="max-height:120px;"
               alt="Photo actuelle">
          <div class="small text-muted mt-1">Photo actuelle</div>
        </div>
        <div class="col-md-9">
        <?php else: ?>
        <div class="col-12">
        <?php endif; ?>
          <label class="form-label">
            <?=!empty($detenu['photo_identite'])?'Remplacer la photo':'Ajouter une photo'?>
            <small class="text-muted">JPG/PNG, max 2 Mo</small>
          </label>
          <input type="file" name="photo_identite" class="form-control" accept=".jpg,.jpeg,.png">
        </div>
      </div>

      <!-- Notes -->
      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="3"><?=htmlspecialchars($_POST['notes']??$detenu['notes']??'')?></textarea>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-danger flex-fill">
          <i class="bi bi-save me-1"></i>Enregistrer les modifications
        </button>
        <a href="<?=BASE_URL?>/detenus/show/<?=$detenu['id']?>" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </form>
  </div>
</div>
</div>
</div>

<script>
function toggleEnfants(val) {
    document.getElementById('rowNbEnfants').style.display = (val === 'marie') ? 'block' : 'none';
}
</script>
