<?php $pageTitle = 'Population Carcérale — Enregistrer'; ?>

<?php
// Ce fichier est rendu dans le contexte de la page liste (/detenus)
// Le formulaire s'ouvre en modal Bootstrap 5.
// La vue index.php doit inclure ce fichier ET déclencher le modal.
?>

<!-- Modal Nouveau Détenu -->
<div class="modal fade" id="modalNewDetenu" tabindex="-1" aria-labelledby="modalNewDetenuLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalNewDetenuLabel">
          <i class="bi bi-person-lock me-2"></i>Enregistrer un détenu
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="<?=BASE_URL?>/detenus/store" enctype="multipart/form-data" novalidate>
        <?=CSRF::field()?>
        <div class="modal-body">

          <!-- Identité -->
          <h6 class="text-uppercase text-muted small fw-semibold mb-3 border-bottom pb-1">
            <i class="bi bi-person me-1"></i>Identité
          </h6>
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Nom <span class="text-danger">*</span></label>
              <input type="text" name="nom" class="form-control" required
                     value="<?=htmlspecialchars($_POST['nom']??'')?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Prénom <span class="text-danger">*</span></label>
              <input type="text" name="prenom" class="form-control" required
                     value="<?=htmlspecialchars($_POST['prenom']??'')?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Surnom / Alias</label>
              <input type="text" name="surnom_alias" class="form-control"
                     value="<?=htmlspecialchars($_POST['surnom_alias']??'')?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Sexe <span class="text-danger">*</span></label>
              <select name="sexe" class="form-select" required>
                <option value="M" <?=($_POST['sexe']??'M')==='M'?'selected':''?>>Masculin</option>
                <option value="F" <?=($_POST['sexe']??'')==='F'?'selected':''?>>Féminin</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Date de naissance</label>
              <input type="date" name="date_naissance" class="form-control"
                     value="<?=$_POST['date_naissance']??''?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Lieu de naissance</label>
              <input type="text" name="lieu_naissance" class="form-control"
                     value="<?=htmlspecialchars($_POST['lieu_naissance']??'')?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Nationalité</label>
              <input type="text" name="nationalite" class="form-control"
                     value="<?=htmlspecialchars($_POST['nationalite']??'Nigérienne')?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Profession</label>
              <input type="text" name="profession" class="form-control"
                     value="<?=htmlspecialchars($_POST['profession']??'')?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Nom de la mère</label>
              <input type="text" name="nom_mere" class="form-control"
                     value="<?=htmlspecialchars($_POST['nom_mere']??'')?>">
            </div>
          </div>

          <!-- Situation familiale -->
          <h6 class="text-uppercase text-muted small fw-semibold mb-3 border-bottom pb-1">
            <i class="bi bi-people me-1"></i>Situation familiale
          </h6>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Statut matrimonial</label>
              <select name="statut_matrimonial" id="statutMatrimonial" class="form-select"
                      onchange="toggleEnfants(this.value)">
                <?php foreach(['celibataire'=>'Célibataire','marie'=>'Marié(e)','divorce'=>'Divorcé(e)','veuf'=>'Veuf/Veuve'] as $v=>$l): ?>
                <option value="<?=$v?>" <?=($_POST['statut_matrimonial']??'celibataire')===$v?'selected':''?>>
                  <?=$l?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6" id="rowNbEnfants"
                 style="display:<?=($_POST['statut_matrimonial']??'celibataire')==='marie'?'block':'none'?>">
              <label class="form-label">Nombre d'enfants</label>
              <input type="number" name="nombre_enfants" class="form-control" min="0" max="99"
                     value="<?=(int)($_POST['nombre_enfants']??0)?>">
            </div>
          </div>

          <!-- Détention -->
          <h6 class="text-uppercase text-muted small fw-semibold mb-3 border-bottom pb-1">
            <i class="bi bi-lock me-1"></i>Détention
          </h6>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Type de détention <span class="text-danger">*</span></label>
              <select name="type_detention" class="form-select" required>
                <?php foreach(['prevenu'=>'Prévenu','inculpe'=>'Inculpé','condamne'=>'Condamné','detenu_provisoire'=>'Détenu provisoire','mis_en_examen'=>'Mis en examen','autre'=>'Autre'] as $v=>$l): ?>
                <option value="<?=$v?>"><?=$l?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Date d'incarcération <span class="text-danger">*</span></label>
              <input type="date" name="date_incarceration" class="form-control" required
                     value="<?=$_POST['date_incarceration']??date('Y-m-d')?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Date de libération prévue</label>
              <input type="date" name="date_liberation_prevue" class="form-control"
                     value="<?=$_POST['date_liberation_prevue']??''?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Cellule</label>
              <input type="text" name="cellule" class="form-control"
                     value="<?=htmlspecialchars($_POST['cellule']??'')?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Maison d'arrêt</label>
              <select name="maison_arret_id" class="form-select">
                <option value="">— Sélectionner —</option>
                <?php foreach($maisonArrets as $ma): ?>
                <option value="<?=$ma['id']?>" <?=($_POST['maison_arret_id']??'')==$ma['id']?'selected':''?>>
                  <?=htmlspecialchars($ma['nom'])?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Établissement <small class="text-muted">(saisie libre)</small></label>
              <input type="text" name="etablissement" class="form-control"
                     value="<?=htmlspecialchars($_POST['etablissement']??'Maison d\'Arrêt de Niamey')?>">
            </div>
          </div>

          <!-- Dossier & Photo -->
          <h6 class="text-uppercase text-muted small fw-semibold mb-3 border-bottom pb-1">
            <i class="bi bi-folder me-1"></i>Dossier & Documents
          </h6>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Dossier lié</label>
              <select name="dossier_id" class="form-select">
                <option value="">— Aucun —</option>
                <?php $presel = (int)($_GET['dossier_id']??0);
                foreach($dossiers as $d): ?>
                <option value="<?=$d['id']?>" <?=$presel===(int)$d['id']?'selected':''?>>
                  <?=htmlspecialchars($d['numero_rg'].' — '.$d['objet'])?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Écrou (auto-généré)</label>
              <input type="text" class="form-control bg-light font-monospace"
                     value="<?=htmlspecialchars($suggestEcrou)?>" readonly>
            </div>
            <div class="col-12">
              <label class="form-label">Photo d'identité <small class="text-muted">JPG/PNG, max 2 Mo</small></label>
              <input type="file" name="photo_identite" class="form-control" accept=".jpg,.jpeg,.png">
            </div>
            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea name="notes" class="form-control" rows="3"><?=$_POST['notes']??''?></textarea>
            </div>
          </div>

        </div><!-- /.modal-body -->
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-danger">
            <i class="bi bi-person-lock me-1"></i>Enregistrer
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleEnfants(val) {
    document.getElementById('rowNbEnfants').style.display = (val === 'marie') ? 'block' : 'none';
}
</script>
