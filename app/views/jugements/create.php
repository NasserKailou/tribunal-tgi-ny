<?php $pageTitle = 'Saisir un jugement'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/jugements">Jugements</a></li><li class="breadcrumb-item active">Saisir</li></ol></nav>
    <h4 class="fw-bold"><i class="bi bi-hammer me-2 text-primary"></i>Saisir un jugement</h4>
</div>
<div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i>Dossier : <strong><?=htmlspecialchars($dossier['numero_rg'])?></strong><?=$dossier['numero_rp']?' / <strong>'.htmlspecialchars($dossier['numero_rp']).'</strong>':''?> — <?=htmlspecialchars($dossier['objet']??'')?></div>

<div class="row justify-content-center"><div class="col-lg-9">
<form method="POST" action="<?=BASE_URL?>/jugements/store" novalidate>
    <?=CSRF::field()?>
    <input type="hidden" name="dossier_id" value="<?=$dossier['id']?>">
    <div class="card border-0 shadow-sm mb-4"><div class="card-body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label">Audience liée</label>
            <select name="audience_id" class="form-select">
                <option value="">— Aucune —</option>
                <?php foreach($audiences as $a): ?><option value="<?=$a['id']?>"><?=htmlspecialchars(date('d/m/Y',strtotime($a['date_audience'])).' — '.$a['type_audience'].' — '.$a['numero_audience'])?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">Date du jugement <span class="text-danger">*</span></label><input type="date" name="date_jugement" class="form-control" required value="<?=$_POST['date_jugement']??date('Y-m-d')?>"></div>
        <div class="col-md-6"><label class="form-label">Type de jugement <span class="text-danger">*</span></label>
            <select name="type_jugement" class="form-select" required onchange="togglePeine()">
                <?php foreach(['condamnation'=>'Condamnation','acquittement'=>'Acquittement','non_lieu'=>'Non-lieu','relaxe'=>'Relaxe','renvoi'=>'Renvoi','avant_droit'=>'Avant-droit','autre'=>'Autre'] as $v=>$l): ?><option value="<?=$v?>"><?=$l?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">Greffier</label>
            <select name="greffier_id" class="form-select"><option value="">— —</option><?php foreach($greffiers as $g): ?><option value="<?=$g['id']?>"><?=htmlspecialchars($g['prenom'].' '.$g['nom'])?></option><?php endforeach; ?></select>
        </div>
        <div class="col-12"><label class="form-label">Dispositif <span class="text-danger">*</span></label><textarea name="dispositif" class="form-control" rows="4" required placeholder="Le tribunal condamne / acquitte / renvoie..."><?=$_POST['dispositif']??''?></textarea></div>
    </div></div></div>

    <div class="card border-0 shadow-sm mb-4" id="peineBlock"><div class="card-header bg-white fw-semibold">Peine (condamnation)</div><div class="card-body"><div class="row g-3">
        <div class="col-12"><label class="form-label">Peine principale</label><input type="text" name="peine_principale" class="form-control" placeholder="ex: 5 ans de réclusion criminelle"></div>
        <div class="col-md-4"><label class="form-label">Durée peine (mois)</label><input type="number" name="duree_peine_mois" class="form-control" min="0"></div>
        <div class="col-md-4"><label class="form-label">Amende (FCFA)</label><input type="number" name="montant_amende" class="form-control" min="0"></div>
        <div class="col-md-4 d-flex align-items-end pb-1">
            <div class="form-check"><input class="form-check-input" type="checkbox" name="sursis" id="checkSursis" onchange="toggleSursis()"><label class="form-check-label" for="checkSursis">Sursis</label></div>
        </div>
        <div class="col-md-4" id="sursisBlock" style="display:none"><label class="form-label">Durée sursis (mois)</label><input type="number" name="duree_sursis_mois" class="form-control" min="0"></div>
    </div></div></div>

    <div class="card border-0 shadow-sm mb-4"><div class="card-body">
        <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="appel_possible" id="checkAppel" checked><label class="form-check-label" for="checkAppel">Appel possible (délai <?=DELAI_APPEL_JOURS?> jours)</label></div>
        <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?=$_POST['notes']??''?></textarea></div>
    </div></div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success flex-fill"><i class="bi bi-hammer me-1"></i>Enregistrer le jugement</button>
        <a href="<?=BASE_URL?>/dossiers/show/<?=$dossier['id']?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
</div></div>
<script>
function togglePeine(){const t=document.querySelector('[name=type_jugement]').value;document.getElementById('peineBlock').style.display=t==='condamnation'?'block':'none';}
function toggleSursis(){document.getElementById('sursisBlock').style.display=document.getElementById('checkSursis').checked?'block':'none';}
togglePeine();
</script>
