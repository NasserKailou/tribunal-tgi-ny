<?php $pageTitle = 'Population Carcérale — Enregistrer'; ?>

<!-- Modal Nouveau Détenu -->
<div class="modal fade" id="modalNewDetenu" tabindex="-1" aria-labelledby="modalNewDetenuLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-danger text-white py-2">
        <h5 class="modal-title" id="modalNewDetenuLabel">
          <i class="bi bi-person-lock me-2"></i>Enregistrer un détenu
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form method="POST" action="<?=BASE_URL?>/detenus/store" enctype="multipart/form-data" novalidate>
        <?=CSRF::field()?>
        <div class="modal-body px-4 py-3">

          <!-- ── Identité ───────────────────────────────────────────────────── -->
          <div class="section-title d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">
              <i class="bi bi-person-vcard me-1"></i>Identité
            </span>
          </div>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-danger text-white"><i class="bi bi-person"></i></span>
                <input type="text" name="nom" class="form-control" required placeholder="NOM DE FAMILLE"
                       value="<?=htmlspecialchars($_POST['nom']??'')?>" style="text-transform:uppercase">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-danger text-white"><i class="bi bi-person"></i></span>
                <input type="text" name="prenom" class="form-control" required placeholder="Prénom(s)"
                       value="<?=htmlspecialchars($_POST['prenom']??'')?>">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Surnom / Alias</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-incognito"></i></span>
                <input type="text" name="surnom_alias" class="form-control" placeholder="Alias ou surnom"
                       value="<?=htmlspecialchars($_POST['surnom_alias']??'')?>">
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Sexe <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-gender-ambiguous"></i></span>
                <select name="sexe" class="form-select" required>
                  <option value="M" <?=($_POST['sexe']??'M')==='M'?'selected':''?>>Masculin</option>
                  <option value="F" <?=($_POST['sexe']??'')==='F'?'selected':''?>>Féminin</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Date de naissance</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                <input type="date" name="date_naissance" class="form-control"
                       value="<?=$_POST['date_naissance']??''?>">
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Lieu de naissance</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                <input type="text" name="lieu_naissance" class="form-control" placeholder="Ville / Village"
                       value="<?=htmlspecialchars($_POST['lieu_naissance']??'')?>">
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Nationalité</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-flag"></i></span>
                <input type="text" name="nationalite" class="form-control"
                       value="<?=htmlspecialchars($_POST['nationalite']??'Nigérienne')?>">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Profession</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                <input type="text" name="profession" class="form-control" placeholder="Profession"
                       value="<?=htmlspecialchars($_POST['profession']??'')?>">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Nom de la mère</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person-heart"></i></span>
                <input type="text" name="nom_mere" class="form-control" placeholder="Prénom et nom de la mère"
                       value="<?=htmlspecialchars($_POST['nom_mere']??'')?>">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Adresse domicile</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-house"></i></span>
                <input type="text" name="adresse" class="form-control" placeholder="Quartier / Rue"
                       value="<?=htmlspecialchars($_POST['adresse']??'')?>">
              </div>
            </div>
          </div>

          <!-- ── Situation familiale ────────────────────────────────────────── -->
          <div class="section-title d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-secondary rounded-pill px-3 py-2 fs-6">
              <i class="bi bi-people me-1"></i>Situation familiale
            </span>
          </div>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Statut matrimonial</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-heart"></i></span>
                <select name="statut_matrimonial" id="statutMatrimonial" class="form-select"
                        onchange="toggleEnfants(this.value)">
                  <?php foreach(['celibataire'=>'Célibataire','marie'=>'Marié(e)','divorce'=>'Divorcé(e)','veuf'=>'Veuf / Veuve'] as $v=>$l): ?>
                  <option value="<?=$v?>" <?=($_POST['statut_matrimonial']??'celibataire')===$v?'selected':''?>><?=$l?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6" id="rowNbEnfants"
                 style="display:<?=($_POST['statut_matrimonial']??'')==='marie'?'block':'none'?>">
              <label class="form-label fw-semibold">Nombre d'enfants</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-people-fill"></i></span>
                <input type="number" name="nombre_enfants" class="form-control" min="0" max="99"
                       value="<?=(int)($_POST['nombre_enfants']??0)?>">
              </div>
            </div>
          </div>

          <!-- ── Détention ──────────────────────────────────────────────────── -->
          <div class="section-title d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-dark rounded-pill px-3 py-2 fs-6">
              <i class="bi bi-lock me-1"></i>Détention
            </span>
          </div>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Type de détention <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-dark text-white"><i class="bi bi-shield-lock"></i></span>
                <select name="type_detention" class="form-select" required>
                  <?php foreach([
                    'prevenu'          =>'Prévenu',
                    'inculpe'          =>'Inculpé',
                    'condamne'         =>'Condamné',
                    'detenu_provisoire'=>'Détenu provisoire',
                    'mis_en_examen'    =>'Mis en examen',
                    'autre'            =>'Autre'
                  ] as $v=>$l): ?>
                  <option value="<?=$v?>" <?=($_POST['type_detention']??'')===$v?'selected':''?>><?=$l?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Maison d'arrêt</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-building"></i></span>
                <select name="maison_arret_id" class="form-select">
                  <option value="">— Sélectionner —</option>
                  <?php foreach($maisonArrets as $ma): ?>
                  <option value="<?=$ma['id']?>" <?=($_POST['maison_arret_id']??'')==$ma['id']?'selected':''?>>
                    <?=htmlspecialchars($ma['nom'])?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Date d'incarcération <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-calendar-x"></i></span>
                <input type="date" name="date_incarceration" class="form-control" required
                       value="<?=$_POST['date_incarceration']??date('Y-m-d')?>">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Date libération prévue</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                <input type="date" name="date_liberation_prevue" class="form-control"
                       value="<?=$_POST['date_liberation_prevue']??''?>">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Cellule / Quartier</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-grid-1x2"></i></span>
                <input type="text" name="cellule" class="form-control" placeholder="Ex: B-12"
                       value="<?=htmlspecialchars($_POST['cellule']??'')?>">
              </div>
            </div>
          </div>

          <!-- ── Dossier & Photo ────────────────────────────────────────────── -->
          <div class="section-title d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">
              <i class="bi bi-folder2-open me-1"></i>Dossier judiciaire
            </span>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Dossier lié</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
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
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">N° Écrou <small class="text-muted">(auto-généré)</small></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-hash"></i></span>
                <input type="text" class="form-control bg-light font-monospace"
                       value="<?=htmlspecialchars($suggestEcrou)?>" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Photo d'identité <small class="text-muted">JPG/PNG ≤ 2 Mo</small></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-camera"></i></span>
                <input type="file" name="photo_identite" class="form-control" accept=".jpg,.jpeg,.png">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Infractions reprochées</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-exclamation-triangle"></i></span>
                <input type="text" name="infractions_resumé" class="form-control"
                       placeholder="Résumé des infractions"
                       value="<?=htmlspecialchars($_POST['infractions_resumé']??'')?>">
              </div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Notes / Observations</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                <textarea name="notes" class="form-control" rows="2"
                          placeholder="Informations complémentaires..."><?=$_POST['notes']??''?></textarea>
              </div>
            </div>
          </div>

        </div><!-- /.modal-body -->

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i>Annuler
          </button>
          <button type="submit" class="btn btn-danger px-4">
            <i class="bi bi-person-lock me-2"></i>Enregistrer le détenu
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
