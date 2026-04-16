<?php
// ─── État d'écrou — Fiche imprimable après enregistrement d'un détenu ─────────
$pageTitle = 'État d\'écrou — ' . $detenu['numero_ecrou'];
$typeLabels = [
    'prevenu'          => 'Prévenu',
    'inculpe'          => 'Inculpé',
    'condamne'         => 'Condamné',
    'detenu_provisoire'=> 'Détenu provisoire',
    'mis_en_examen'    => 'Mis en examen',
    'autre'            => 'Autre',
];
$sexeLabel = $detenu['sexe'] === 'F' ? 'Féminin' : 'Masculin';
$matrimonialLabels = ['celibataire'=>'Célibataire','marie'=>'Marié(e)','divorce'=>'Divorcé(e)','veuf'=>'Veuf/Veuve'];
?>

<style>
@media print {
    .no-print { display:none!important; }
    .card { box-shadow:none!important; border:1px solid #dee2e6!important; }
    body { font-size:11pt; }
    .etat-header { border-bottom:3px solid #000; }
}
.etat-header { border-bottom:3px solid #dc3545; padding-bottom:12px; margin-bottom:24px; }
.etat-label  { font-size:0.78rem; text-transform:uppercase; color:#6c757d; font-weight:600; letter-spacing:.5px; }
.etat-value  { font-size:1rem; font-weight:600; color:#212529; }
.seal-box    { border:2px dashed #adb5bd; min-height:80px; min-width:80px; display:flex;
               align-items:center; justify-content:center; color:#adb5bd; font-size:0.75rem; }
</style>

<!-- Barre d'actions (masquée à l'impression) -->
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <a href="<?=BASE_URL?>/detenus" class="btn btn-outline-secondary btn-sm me-2">
            <i class="bi bi-arrow-left me-1"></i>Retour à la liste
        </a>
        <a href="<?=BASE_URL?>/detenus/show/<?=$detenu['id']?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-eye me-1"></i>Fiche complète
        </a>
    </div>
    <button onclick="window.print()" class="btn btn-danger">
        <i class="bi bi-printer me-2"></i>Imprimer l'état d'écrou
    </button>
</div>

<!-- ── Document officiel ───────────────────────────────────────────────────── -->
<div class="card border-0 shadow" style="max-width:860px; margin:0 auto;">
    <div class="card-body p-5">

        <!-- En-tête officiel -->
        <div class="etat-header">
            <div class="row align-items-start">
                <div class="col-8">
                    <div class="text-center text-uppercase fw-bold" style="font-size:0.85rem;letter-spacing:1px;color:#555;">
                        RÉPUBLIQUE DU NIGER<br>
                        <small style="font-size:0.75rem;">Fraternité – Travail – Progrès</small>
                    </div>
                    <div class="text-center mt-2">
                        <div class="fw-bold" style="font-size:1rem;">TRIBUNAL DE GRANDE INSTANCE HORS CLASSE DE NIAMEY</div>
                        <div class="text-muted small">Service du Greffe — Population Carcérale</div>
                    </div>
                </div>
                <div class="col-4 text-end">
                    <div class="seal-box mx-auto ms-auto" style="width:90px;height:90px;">
                        <span style="font-size:0.65rem;text-align:center;line-height:1.2">CACHET<br>OFFICIEL</span>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3 py-2" style="background:#dc3545; color:#fff; border-radius:4px;">
                <span class="fw-bold fs-5 text-uppercase">ÉTAT D'ÉCROU</span>
            </div>
        </div>

        <!-- Numéro d'écrou -->
        <div class="text-center mb-4">
            <div class="d-inline-block border border-danger px-4 py-2 rounded">
                <div class="etat-label">N° d'écrou</div>
                <div class="fw-bold font-monospace" style="font-size:1.6rem; color:#dc3545;">
                    <?=htmlspecialchars($detenu['numero_ecrou'])?>
                </div>
            </div>
        </div>

        <!-- Photo + Identité -->
        <div class="row mb-4">
            <div class="col-md-3 text-center">
                <?php if($detenu['photo_identite']): ?>
                <img src="<?=BASE_URL?>/uploads/photos_detenus/<?=htmlspecialchars(basename($detenu['photo_identite']))?>"
                     class="img-thumbnail mb-2" style="width:110px;height:140px;object-fit:cover;">
                <?php else: ?>
                <div class="border d-flex align-items-center justify-content-center"
                     style="width:110px;height:140px;margin:0 auto;background:#f8f9fa;color:#adb5bd;">
                    <i class="bi bi-person fs-1"></i>
                </div>
                <?php endif; ?>
                <div class="small text-muted">Photo d'identité</div>
            </div>
            <div class="col-md-9">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="etat-label">Nom</div>
                        <div class="etat-value"><?=htmlspecialchars(strtoupper($detenu['nom']))?></div>
                    </div>
                    <div class="col-6">
                        <div class="etat-label">Prénom(s)</div>
                        <div class="etat-value"><?=htmlspecialchars($detenu['prenom'])?></div>
                    </div>
                    <?php if($detenu['surnom_alias']): ?>
                    <div class="col-6">
                        <div class="etat-label">Alias / Surnom</div>
                        <div class="etat-value"><?=htmlspecialchars($detenu['surnom_alias'])?></div>
                    </div>
                    <?php endif; ?>
                    <div class="col-6">
                        <div class="etat-label">Sexe</div>
                        <div class="etat-value"><?=$sexeLabel?></div>
                    </div>
                    <div class="col-6">
                        <div class="etat-label">Date de naissance</div>
                        <div class="etat-value">
                            <?=$detenu['date_naissance'] ? date('d/m/Y', strtotime($detenu['date_naissance'])) : '—'?>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="etat-label">Lieu de naissance</div>
                        <div class="etat-value"><?=htmlspecialchars($detenu['lieu_naissance'] ?: '—')?></div>
                    </div>
                    <div class="col-6">
                        <div class="etat-label">Nationalité</div>
                        <div class="etat-value"><?=htmlspecialchars($detenu['nationalite'] ?: '—')?></div>
                    </div>
                    <div class="col-6">
                        <div class="etat-label">Profession</div>
                        <div class="etat-value"><?=htmlspecialchars($detenu['profession'] ?: '—')?></div>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <!-- Détention -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="fw-bold text-uppercase mb-2" style="color:#dc3545; font-size:0.85rem; letter-spacing:.5px;">
                    <i class="bi bi-lock me-1"></i>Informations sur la détention
                </div>
            </div>
            <div class="col-md-4">
                <div class="etat-label">Type de détention</div>
                <div class="etat-value">
                    <span class="badge bg-danger fs-6">
                        <?=$typeLabels[$detenu['type_detention']] ?? ucfirst($detenu['type_detention'])?>
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="etat-label">Date d'incarcération</div>
                <div class="etat-value">
                    <?=$detenu['date_incarceration'] ? date('d/m/Y', strtotime($detenu['date_incarceration'])) : '—'?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="etat-label">Date libération prévue</div>
                <div class="etat-value">
                    <?=$detenu['date_liberation_prevue'] ? date('d/m/Y', strtotime($detenu['date_liberation_prevue'])) : 'Non déterminée'?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="etat-label">Maison d'arrêt</div>
                <div class="etat-value"><?=htmlspecialchars($detenu['maison_arret_nom'] ?? $detenu['etablissement'] ?? '—')?></div>
            </div>
            <div class="col-md-4">
                <div class="etat-label">Cellule / Quartier</div>
                <div class="etat-value"><?=htmlspecialchars($detenu['cellule'] ?: '—')?></div>
            </div>
            <div class="col-md-4">
                <div class="etat-label">Dossier judiciaire</div>
                <div class="etat-value">
                    <?php if($detenu['numero_rg']): ?>
                    <a href="<?=BASE_URL?>/dossiers/show/<?=$detenu['dossier_id']?>"
                       class="text-decoration-none"><?=htmlspecialchars($detenu['numero_rg'])?></a>
                    <?php else: ?>—<?php endif; ?>
                </div>
            </div>
        </div>

        <hr>

        <!-- Signatures -->
        <div class="row mt-4 pt-2">
            <div class="col-4 text-center">
                <div class="etat-label mb-4">Le Greffier en Chef</div>
                <div style="border-bottom:1px solid #000; height:60px;"></div>
                <div class="small mt-2">Signature et cachet</div>
            </div>
            <div class="col-4 text-center">
                <div class="etat-label mb-1">Date d'établissement</div>
                <div class="fw-bold"><?=date('d/m/Y')?></div>
                <div class="seal-box mx-auto mt-3" style="width:80px;height:70px;">
                    <span style="font-size:0.6rem;text-align:center;">CACHET<br>GREFFE</span>
                </div>
            </div>
            <div class="col-4 text-center">
                <div class="etat-label mb-4">Le Président du Tribunal</div>
                <div style="border-bottom:1px solid #000; height:60px;"></div>
                <div class="small mt-2">Signature</div>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="text-center mt-4 pt-3 border-top">
            <small class="text-muted">
                Document généré le <?=date('d/m/Y à H:i')?> — 
                TGI Hors Classe de Niamey — Système de gestion judiciaire
            </small>
        </div>

    </div>
</div>
