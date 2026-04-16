<?php $pageTitle = 'Planifier une audience'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=BASE_URL?>/audiences">Audiences</a></li>
        <li class="breadcrumb-item active">Planifier</li>
    </ol></nav>
    <h4 class="fw-bold"><i class="bi bi-calendar-plus me-2 text-primary"></i>Planifier une audience</h4>
</div>

<div class="row justify-content-center"><div class="col-lg-9">
<form method="POST" action="<?=BASE_URL?>/audiences/store" novalidate>
    <?=CSRF::field()?>

    <!-- ── Dossier & Date ── -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-primary text-white fw-semibold py-2">
            <i class="bi bi-folder2-open me-2"></i>Dossier & Calendrier
        </div>
        <div class="card-body"><div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Dossier <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-folder2"></i></span>
                    <select name="dossier_id" class="form-select" required>
                        <option value="">— Sélectionner un dossier —</option>
                        <?php foreach($dossiers as $d): ?>
                        <option value="<?=$d['id']?>"
                            <?=($dossierPreselect===$d['id']||($_POST['dossier_id']??'')==$d['id'])?'selected':''?>>
                            <?=htmlspecialchars($d['numero_rg'].' — '.$d['objet'])?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Date et heure <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                    <input type="datetime-local" name="date_audience" class="form-control" required
                           value="<?=$_POST['date_audience']??''?>">
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Type d'audience <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                    <select name="type_audience" class="form-select" required>
                        <?php foreach([
                            'correctionnelle'=>'Correctionnelle',
                            'criminelle'     =>'Criminelle',
                            'civile'         =>'Civile',
                            'commerciale'    =>'Commerciale',
                            'instruction'    =>'Instruction'
                        ] as $v=>$l): ?>
                        <option value="<?=$v?>" <?=($_POST['type_audience']??'')===$v?'selected':''?>><?=$l?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Salle d'audience</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-door-open"></i></span>
                    <select name="salle_id" class="form-select">
                        <option value="">— Sélectionner une salle —</option>
                        <?php foreach($salles as $s): ?>
                        <option value="<?=$s['id']?>"><?=htmlspecialchars($s['nom'])?> (<?=$s['capacite']?> places)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Notes / Observations</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                    <textarea name="notes" class="form-control" rows="1"><?=$_POST['notes']??''?></textarea>
                </div>
            </div>
        </div></div>
    </div>

    <!-- ── Composition du siège ── -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold py-2 border-bottom">
            <i class="bi bi-people-fill me-2 text-primary"></i>Composition du siège
        </div>
        <div class="card-body"><div class="row g-3">

            <!-- Président -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="bi bi-person-badge text-primary me-1"></i>Président
                </label>
                <select name="president_id" class="form-select">
                    <option value="">— Sélectionner —</option>
                    <?php foreach($juges as $j): ?>
                    <option value="<?=$j['id']?>"><?=htmlspecialchars($j['prenom'].' '.$j['nom'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Greffier -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="bi bi-person-lines-fill text-secondary me-1"></i>Greffier
                </label>
                <select name="greffier_id" class="form-select">
                    <option value="">— Sélectionner —</option>
                    <?php foreach($greffiers as $g): ?>
                    <option value="<?=$g['id']?>"><?=htmlspecialchars($g['prenom'].' '.$g['nom'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Assesseur 1 -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="bi bi-person text-info me-1"></i>Assesseur N°1
                </label>
                <div class="input-group">
                    <select name="assesseur1_id" class="form-select assesseur-select" id="assesseur1Id">
                        <option value="">— Juge du siège —</option>
                        <?php foreach($juges as $j): ?>
                        <option value="<?=$j['id']?>"><?=htmlspecialchars($j['prenom'].' '.$j['nom'])?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Saisir manuellement"
                        onclick="toggleManuel('assesseur1')"><i class="bi bi-pencil"></i></button>
                </div>
                <input type="text" name="assesseur1_nom" id="assesseur1Nom"
                       class="form-control mt-1 d-none" placeholder="Nom de l'assesseur (externe)">
            </div>

            <!-- Assesseur 2 -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="bi bi-person text-info me-1"></i>Assesseur N°2
                </label>
                <div class="input-group">
                    <select name="assesseur2_id" class="form-select assesseur-select" id="assesseur2Id">
                        <option value="">— Juge du siège —</option>
                        <?php foreach($juges as $j): ?>
                        <option value="<?=$j['id']?>"><?=htmlspecialchars($j['prenom'].' '.$j['nom'])?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Saisir manuellement"
                        onclick="toggleManuel('assesseur2')"><i class="bi bi-pencil"></i></button>
                </div>
                <input type="text" name="assesseur2_nom" id="assesseur2Nom"
                       class="form-control mt-1 d-none" placeholder="Nom de l'assesseur (externe)">
            </div>

            <!-- Juré 1 -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="bi bi-person-check text-warning me-1"></i>Juré N°1
                </label>
                <input type="text" name="jure1_nom" class="form-control"
                       placeholder="Nom et prénom du juré N°1" value="<?=$_POST['jure1_nom']??''?>">
            </div>

            <!-- Juré 2 -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="bi bi-person-check text-warning me-1"></i>Juré N°2
                </label>
                <input type="text" name="jure2_nom" class="form-control"
                       placeholder="Nom et prénom du juré N°2" value="<?=$_POST['jure2_nom']??''?>">
            </div>

            <!-- Représentant du Parquet -->
            <div class="col-12">
                <label class="form-label fw-semibold">
                    <i class="bi bi-building text-danger me-1"></i>Représentant du Parquet
                </label>
                <select name="parquet_id" class="form-select">
                    <option value="">— Sélectionner —</option>
                    <?php foreach($parquet as $p): ?>
                    <option value="<?=$p['id']?>"><?=htmlspecialchars($p['prenom'].' '.$p['nom'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div></div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-primary flex-fill">
            <i class="bi bi-calendar-check me-2"></i>Planifier l'audience
        </button>
        <a href="<?=BASE_URL?>/audiences" class="btn btn-outline-secondary px-4">
            <i class="bi bi-x-lg me-1"></i>Annuler
        </a>
    </div>
</form>
</div></div>

<script>
function toggleManuel(prefix) {
    const sel = document.getElementById(prefix + 'Id');
    const inp = document.getElementById(prefix + 'Nom');
    const hide = inp.classList.contains('d-none');
    inp.classList.toggle('d-none', !hide);
    sel.classList.toggle('d-none', hide);
    sel.value = '';
}
</script>
