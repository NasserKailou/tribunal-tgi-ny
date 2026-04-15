<?php $pageTitle = 'Enregistrer un détenu'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/detenus">Population Carcérale</a></li><li class="breadcrumb-item active">Enregistrer</li></ol></nav>
    <h4 class="fw-bold"><i class="bi bi-person-lock me-2 text-danger"></i>Enregistrer un détenu</h4>
</div>
<div class="row justify-content-center"><div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?=BASE_URL?>/detenus/store" novalidate>
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nom <span class="text-danger">*</span></label><input type="text" name="nom" class="form-control" required value="<?=htmlspecialchars($_POST['nom']??'')?>"></div>
                <div class="col-md-6"><label class="form-label">Prénom <span class="text-danger">*</span></label><input type="text" name="prenom" class="form-control" required value="<?=htmlspecialchars($_POST['prenom']??'')?>"></div>
                <div class="col-md-6"><label class="form-label">Date de naissance</label><input type="date" name="date_naissance" class="form-control" value="<?=$_POST['date_naissance']??''?>"></div>
                <div class="col-md-6"><label class="form-label">Lieu de naissance</label><input type="text" name="lieu_naissance" class="form-control" value="<?=htmlspecialchars($_POST['lieu_naissance']??'')?>"></div>
                <div class="col-md-6"><label class="form-label">Nationalité</label><input type="text" name="nationalite" class="form-control" value="<?=htmlspecialchars($_POST['nationalite']??'Nigérienne')?>"></div>
                <div class="col-md-6"><label class="form-label">Profession</label><input type="text" name="profession" class="form-control" value="<?=htmlspecialchars($_POST['profession']??'')?>"></div>
                <div class="col-md-6"><label class="form-label">Type de détention <span class="text-danger">*</span></label>
                    <select name="type_detention" class="form-select" required>
                        <?php foreach(['prevenu'=>'Prévenu','inculpe'=>'Inculpé','condamne'=>'Condamné','detenu_provisoire'=>'Détenu provisoire','mis_en_examen'=>'Mis en examen','autre'=>'Autre'] as $v=>$l): ?><option value="<?=$v?>"><?=$l?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Date d'incarcération <span class="text-danger">*</span></label><input type="date" name="date_incarceration" class="form-control" required value="<?=$_POST['date_incarceration']??date('Y-m-d')?>"></div>
                <div class="col-md-6"><label class="form-label">Date de libération prévue</label><input type="date" name="date_liberation_prevue" class="form-control" value="<?=$_POST['date_liberation_prevue']??''?>"></div>
                <div class="col-md-6"><label class="form-label">Cellule</label><input type="text" name="cellule" class="form-control" value="<?=htmlspecialchars($_POST['cellule']??'')?>"></div>
                <div class="col-12"><label class="form-label">Établissement</label><input type="text" name="etablissement" class="form-control" value="<?=htmlspecialchars($_POST['etablissement']??'Maison d\'Arrêt de Niamey')?>"></div>
                <div class="col-12"><label class="form-label">Dossier lié</label>
                    <select name="dossier_id" class="form-select">
                        <option value="">— Aucun —</option>
                        <?php $presel = (int)($_GET['dossier_id']??0); foreach($dossiers as $d): ?><option value="<?=$d['id']?>" <?=$presel===$d['id']?'selected':''?>><?=htmlspecialchars($d['numero_rg'].' — '.$d['objet'])?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12"><label class="form-label">Écrou (auto-généré)</label><input type="text" class="form-control bg-light" value="<?=htmlspecialchars($suggestEcrou)?>" readonly></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="3"><?=$_POST['notes']??''?></textarea></div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-danger flex-fill"><i class="bi bi-person-lock me-1"></i>Enregistrer</button>
                <a href="<?=BASE_URL?>/detenus" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div></div>
