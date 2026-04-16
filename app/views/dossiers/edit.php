<?php $pageTitle = 'Modifier le dossier'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=BASE_URL?>/dossiers">Dossiers</a></li>
        <li class="breadcrumb-item"><a href="<?=BASE_URL?>/dossiers/show/<?=$dossier['id']?>"><?=htmlspecialchars($dossier['numero_rg'])?></a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol></nav>
    <h4 class="fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Modifier le dossier</h4>
</div>

<div class="row justify-content-center"><div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <span class="fw-semibold text-primary"><?=htmlspecialchars($dossier['numero_rg'])?></span>
        <?php if($dossier['numero_rp']): ?><span class="mx-1 text-muted">·</span><span class="fw-semibold text-secondary"><?=htmlspecialchars($dossier['numero_rp'])?></span><?php endif; ?>
    </div>
    <div class="card-body">
        <form method="POST" action="<?=BASE_URL?>/dossiers/update/<?=$dossier['id']?>" novalidate>
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Type d'affaire</label>
                    <select name="type_affaire" class="form-select">
                        <option value="penale"     <?=$dossier['type_affaire']==='penale'    ?'selected':''?>>Pénale</option>
                        <option value="civile"     <?=$dossier['type_affaire']==='civile'    ?'selected':''?>>Civile</option>
                        <option value="commerciale"<?=$dossier['type_affaire']==='commerciale'?'selected':''?>>Commerciale</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Statut</label>
                    <select name="statut" class="form-select">
                        <?php
                        $statuts = ['parquet'=>'Parquet','instruction'=>'En instruction','en_audience'=>'En audience','juge'=>'Jugé','classe'=>'Classé','appel'=>'En appel','archive'=>'Archivé'];
                        foreach($statuts as $val=>$lib):
                        ?>
                        <option value="<?=$val?>" <?=$dossier['statut']===$val?'selected':''?>><?=$lib?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Objet <span class="text-danger">*</span></label>
                    <textarea name="objet" class="form-control" rows="3" required><?=htmlspecialchars($dossier['objet'])?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Substitut assigné</label>
                    <select name="substitut_id" class="form-select">
                        <option value="">— Aucun —</option>
                        <?php foreach($substituts as $s): ?>
                        <option value="<?=$s['id']?>" <?=$dossier['substitut_id']==$s['id']?'selected':''?>>
                            <?=htmlspecialchars($s['prenom'].' '.$s['nom'])?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Cabinet d'instruction</label>
                    <select name="cabinet_id" class="form-select">
                        <option value="">— Aucun —</option>
                        <?php foreach($cabinets as $c): ?>
                        <option value="<?=$c['id']?>" <?=$dossier['cabinet_id']==$c['id']?'selected':''?>>
                            <?=htmlspecialchars($c['numero'].' — '.$c['libelle'])?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bi bi-save me-1"></i>Enregistrer les modifications
                </button>
                <a href="<?=BASE_URL?>/dossiers/show/<?=$dossier['id']?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div></div>
