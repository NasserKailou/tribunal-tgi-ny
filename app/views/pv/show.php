<?php $pageTitle = 'Détail PV — ' . htmlspecialchars($pv['numero_rg']); ?>

<?php
/* ── Couleurs par catégorie ── */
$catColors = ['criminelle' => 'danger', 'correctionnelle' => 'warning', 'contraventionnelle' => 'secondary'];

/* ── Icônes MIME pour pièces jointes ── */
function pvDocIcon(string $mime): string {
    if (str_contains($mime, 'pdf'))  return 'bi-file-earmark-pdf text-danger';
    if (str_contains($mime, 'word') || str_contains($mime, 'odt')) return 'bi-file-earmark-word text-primary';
    if (str_contains($mime, 'sheet') || str_contains($mime, 'excel')) return 'bi-file-earmark-excel text-success';
    if (str_contains($mime, 'image')) return 'bi-file-earmark-image text-info';
    return 'bi-file-earmark text-secondary';
}

function pvDocSize(int $bytes): string {
    if ($bytes < 1024)       return $bytes . ' o';
    if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' Ko';
    return round($bytes / 1048576, 1) . ' Mo';
}
?>

<!-- ── Fil d'Ariane + entête ── -->
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pv">PV</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($pv['numero_rg']) ?></li>
    </ol></nav>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-file-text me-2 text-primary"></i><?= htmlspecialchars($pv['numero_rg']) ?>
            </h4>
            <p class="text-muted mb-0"><?= htmlspecialchars($pv['numero_pv']) ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php if (Auth::hasRole(['admin','greffier','procureur'])): ?>
            <a href="<?= BASE_URL ?>/pv/edit/<?= $pv['id'] ?>" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/export/pv/<?= $pv['id'] ?>" target="_blank" class="btn btn-outline-danger">
                <i class="bi bi-file-pdf me-1"></i>Imprimer / PDF
            </a>
        </div>
    </div>
</div>

<!-- ── Flash messages ── -->
<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($flash['success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($flash['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($flash['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
<!-- ════════════════════════════ COLONNE GAUCHE ════════════════════════════ -->
<div class="col-lg-8">

    <!-- ── Informations générales ── -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-info-circle me-2"></i>Informations générales
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <small class="text-muted d-block">Date du PV</small>
                    <strong><?= date('d/m/Y', strtotime($pv['date_pv'])) ?></strong>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block">Date de réception</small>
                    <strong><?= date('d/m/Y', strtotime($pv['date_reception'])) ?></strong>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block">Type d'affaire</small>
                    <span class="badge <?= $pv['type_affaire']==='penale'?'bg-danger':($pv['type_affaire']==='civile'?'bg-primary':'bg-success') ?> fs-6">
                        <?= ucfirst($pv['type_affaire']) ?>
                    </span>
                    <?php if ($pv['est_antiterroriste']): ?>
                    <span class="badge bg-dark ms-1">
                        <i class="bi bi-shield-exclamation"></i> Anti-terroriste
                    </span>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block">Unité d'enquête</small>
                    <strong><?= htmlspecialchars($pv['unite_nom'] ?? '—') ?></strong>
                </div>
                <?php if (!empty($pv['created_by_nom'])): ?>
                <div class="col-md-6">
                    <small class="text-muted d-block">Saisi par</small>
                    <strong><?= htmlspecialchars(trim($pv['created_by_prenom'].' '.$pv['created_by_nom'])) ?></strong>
                </div>
                <?php endif; ?>
                <div class="col-12">
                    <small class="text-muted d-block">Description des faits</small>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($pv['description_faits'] ?? '—')) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         SECTION 1 — INFRACTIONS DÉCLARÉES (unité d'enquête)
    ══════════════════════════════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #1a3c5e !important;">
        <div class="card-header fw-semibold text-white" style="background:#1a3c5e;">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Infractions déclarées — <small class="fw-normal opacity-75">Unité d'enquête</small>
        </div>
        <div class="card-body">
            <?php if (!empty($pv['infractions_enquete'])): ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($pv['infractions_enquete'] as $inf): ?>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded"
                     style="background:#1a3c5e;color:#fff;font-size:.875rem;">
                    <?php if (!empty($inf['code'])): ?>
                    <code style="background:rgba(255,255,255,.18);padding:1px 6px;border-radius:3px;font-size:.78rem;color:#fff;">
                        <?= htmlspecialchars($inf['code']) ?>
                    </code>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($inf['libelle']) ?></span>
                    <span class="badge"
                          style="background:rgba(255,255,255,.2);color:#fff;font-size:.7rem;">
                        <?= ucfirst($inf['categorie']) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php elseif (!empty($pv['infraction_libelle'])): ?>
            <!-- Fallback : infraction unique de l'ancienne architecture -->
            <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded"
                 style="background:#1a3c5e;color:#fff;font-size:.875rem;">
                <?php if (!empty($pv['infraction_code'])): ?>
                <code style="background:rgba(255,255,255,.18);padding:1px 6px;border-radius:3px;font-size:.78rem;color:#fff;">
                    <?= htmlspecialchars($pv['infraction_code']) ?>
                </code>
                <?php endif; ?>
                <span><?= htmlspecialchars($pv['infraction_libelle']) ?></span>
                <span class="badge"
                      style="background:rgba(255,255,255,.2);color:#fff;font-size:.7rem;">
                    <?= ucfirst($pv['infraction_categorie'] ?? '') ?>
                </span>
            </div>
            <?php else: ?>
            <p class="text-muted mb-0">
                <i class="bi bi-dash-circle me-1"></i>
                Aucune infraction déclarée enregistrée.
            </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         SECTION 2 — QUALIFICATION RETENUE (substitut du procureur)
    ══════════════════════════════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #198754 !important;">
        <div class="card-header fw-semibold text-white d-flex justify-content-between align-items-center"
             style="background:#198754;">
            <span>
                <i class="bi bi-shield-check me-2"></i>
                Qualification retenue &amp; Lois applicables
                <small class="fw-normal opacity-75">— Réservé substitut du procureur</small>
            </span>
            <?php if (Auth::hasRole(['admin','procureur','substitut_procureur']) && in_array($pv['statut'], ['en_traitement','classe'])): ?>
            <button class="btn btn-sm btn-light" data-bs-toggle="collapse" data-bs-target="#panelQualifier">
                <i class="bi bi-pencil me-1"></i><?= empty($pv['qualifications_substitut']) ? 'Qualifier' : 'Modifier' ?>
            </button>
            <?php endif; ?>
        </div>

        <!-- Qualifications existantes -->
        <div class="card-body <?= empty($pv['qualifications_substitut']) ? 'py-3' : '' ?>">
            <?php if (!empty($pv['qualifications_substitut'])): ?>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <?php foreach ($pv['qualifications_substitut'] as $q): ?>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded border"
                     style="background:#f0fdf4;border-color:#198754 !important;font-size:.875rem;">
                    <?php if (!empty($q['code'])): ?>
                    <code style="background:#d1fae5;padding:1px 6px;border-radius:3px;font-size:.78rem;color:#065f46;">
                        <?= htmlspecialchars($q['code']) ?>
                    </code>
                    <?php endif; ?>
                    <span class="fw-semibold"><?= htmlspecialchars($q['libelle']) ?></span>
                    <span class="badge bg-<?= $catColors[$q['categorie']] ?? 'secondary' ?> opacity-75"
                          style="font-size:.7rem;">
                        <?= ucfirst($q['categorie']) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php $firstQ = $pv['qualifications_substitut'][0] ?? []; ?>
            <?php if (!empty($firstQ['loi_applicable'])): ?>
            <div class="mb-2">
                <small class="text-muted fw-semibold d-block mb-1">
                    <i class="bi bi-book me-1"></i>Lois applicables
                </small>
                <p class="mb-0 small"><?= nl2br(htmlspecialchars($firstQ['loi_applicable'])) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($firstQ['observations'])): ?>
            <div class="mb-0">
                <small class="text-muted fw-semibold d-block mb-1">
                    <i class="bi bi-chat-left-text me-1"></i>Observations
                </small>
                <p class="mb-0 small fst-italic"><?= nl2br(htmlspecialchars($firstQ['observations'])) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($firstQ['created_by_nom'])): ?>
            <div class="mt-2 text-end">
                <small class="text-muted">
                    <i class="bi bi-person-check me-1"></i>Par
                    <?= htmlspecialchars($firstQ['created_by_nom']) ?>
                </small>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <p class="text-muted mb-0">
                <i class="bi bi-hourglass me-1"></i>
                Aucune qualification retenue pour l'instant.
                <?php if (Auth::hasRole(['admin','procureur','substitut_procureur'])): ?>
                <a href="#panelQualifier" data-bs-toggle="collapse" class="ms-1 text-success">
                    Qualifier ce PV
                </a>
                <?php endif; ?>
            </p>
            <?php endif; ?>
        </div>

        <!-- Formulaire de qualification (collapsible, rôle restreint) -->
        <?php if (Auth::hasRole(['admin','procureur','substitut_procureur']) && in_array($pv['statut'], ['en_traitement','classe'])): ?>
        <div class="collapse" id="panelQualifier">
            <div class="card-footer bg-light border-top" style="border-left:none;">
                <form method="POST" action="<?= BASE_URL ?>/pv/qualifier/<?= $pv['id'] ?>">
                    <?= CSRF::field() ?>
                    <h6 class="fw-semibold mb-3 text-success">
                        <i class="bi bi-shield-check me-1"></i>
                        Définir / mettre à jour la qualification
                    </h6>

                    <!-- Sélection infractions qualifiées -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Infractions retenues <span class="text-danger">*</span>
                            <span class="badge bg-success ms-1" id="cntQualif">0 sélectionnée(s)</span>
                        </label>
                        <input type="text" id="filterQualif" class="form-control form-control-sm mb-2"
                               placeholder="Filtrer les infractions…">
                        <div id="listQualif"
                             style="max-height:240px;overflow-y:auto;border:1px solid #dee2e6;border-radius:.375rem;">
                            <?php
                            $qualifGroups = [];
                            foreach ($infractions as $inf) {
                                $qualifGroups[$inf['categorie']][] = $inf;
                            }
                            $qualifPreChecked = $pv['qualifications_ids'] ?? [];
                            foreach ($qualifGroups as $cat => $infList):
                            ?>
                            <div class="qualif-group mb-0" data-categorie="<?= htmlspecialchars($cat) ?>">
                                <div class="px-2 py-1 fw-semibold text-uppercase"
                                     style="font-size:.7rem;background:#f8f9fa;border-bottom:1px solid #dee2e6;color:#6c757d;">
                                    <?= htmlspecialchars($cat) ?>
                                </div>
                                <div class="qualif-items">
                                    <?php foreach ($infList as $inf):
                                        $checked = in_array($inf['id'], $qualifPreChecked);
                                    ?>
                                    <div class="qualif-row <?= $checked ? 'selected' : '' ?>"
                                         data-id="<?= $inf['id'] ?>"
                                         data-libelle="<?= htmlspecialchars(strtolower($inf['libelle'])) ?>"
                                         style="padding:6px 10px;cursor:pointer;border-bottom:1px solid #f0f0f0;
                                                <?= $checked ? 'background:#1a3c5e;color:#fff;' : 'background:#fff;color:#212529;' ?>">
                                        <label class="d-flex align-items-center gap-2 mb-0 w-100" style="cursor:pointer;">
                                            <input type="checkbox" name="qualification_ids[]"
                                                   value="<?= $inf['id'] ?>"
                                                   class="qualif-checkbox"
                                                   style="flex-shrink:0;"
                                                   <?= $checked ? 'checked' : '' ?>>
                                            <?php if (!empty($inf['code'])): ?>
                                            <code style="font-size:.75rem;<?= $checked ? 'color:#93c5fd;' : 'color:#6c757d;' ?>"><?= htmlspecialchars($inf['code']) ?></code>
                                            <?php endif; ?>
                                            <span style="font-size:.85rem;"><?= htmlspecialchars($inf['libelle']) ?></span>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Lois applicables -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-book me-1 text-success"></i>Lois applicables
                        </label>
                        <textarea name="loi_applicable" class="form-control" rows="3"
                                  placeholder="Ex: Art. 113 Code Pénal, Loi n°2016-003…"
                        ><?= htmlspecialchars($firstQ['loi_applicable'] ?? '') ?></textarea>
                        <div class="form-text">Références légales (articles, lois, ordonnances).</div>
                    </div>

                    <!-- Observations -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-chat-left-text me-1 text-success"></i>Observations
                        </label>
                        <textarea name="observations_qualification" class="form-control" rows="3"
                                  placeholder="Observations complémentaires sur la qualification retenue…"
                        ><?= htmlspecialchars($firstQ['observations'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i>Enregistrer la qualification
                        </button>
                        <button type="button" class="btn btn-outline-secondary"
                                data-bs-toggle="collapse" data-bs-target="#panelQualifier">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         SECTION 3 — PIÈCES JOINTES DU PV
    ══════════════════════════════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-paperclip me-2"></i>Pièces jointes du PV</span>
            <span class="badge bg-secondary" id="pjCount"><?= count($piecesJointes) ?></span>
        </div>
        <div class="card-body p-0">

            <!-- Liste des pièces jointes -->
            <?php if (!empty($piecesJointes)): ?>
            <ul class="list-group list-group-flush" id="pjList">
                <?php foreach ($piecesJointes as $doc): ?>
                <li class="list-group-item d-flex align-items-center justify-content-between gap-2 py-2 px-3"
                    id="pj-item-<?= $doc['id'] ?>">
                    <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                        <i class="bi <?= pvDocIcon($doc['mime_type']) ?> fs-5 flex-shrink-0"></i>
                        <div class="min-w-0">
                            <a href="<?= BASE_URL ?>/documents/view/<?= $doc['id'] ?>"
                               target="_blank" class="text-decoration-none fw-semibold text-truncate d-block"
                               title="<?= htmlspecialchars($doc['nom_original']) ?>">
                                <?= htmlspecialchars($doc['nom_original']) ?>
                            </a>
                            <small class="text-muted">
                                <?= pvDocSize((int)($doc['taille_octets'] ?? 0)) ?>
                                <?php if ($doc['description']): ?>
                                · <?= htmlspecialchars($doc['description']) ?>
                                <?php endif; ?>
                                · <?= date('d/m/Y H:i', strtotime($doc['created_at'])) ?>
                                <?php if ($doc['uploaded_by_nom']): ?>
                                · <em><?= htmlspecialchars($doc['uploaded_by_nom']) ?></em>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                    <?php
                    $canDeleteDoc = Auth::hasRole(['admin','procureur','greffier','president'])
                                 || (int)($doc['uploaded_by'] ?? 0) === Auth::userId();
                    ?>
                    <?php if ($canDeleteDoc): ?>
                    <button type="button"
                            class="btn btn-sm btn-outline-danger flex-shrink-0 btn-delete-pj"
                            data-doc-id="<?= $doc['id'] ?>"
                            title="Supprimer cette pièce jointe">
                        <i class="bi bi-trash"></i>
                    </button>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <div class="text-center text-muted py-3" id="pjEmpty">
                <i class="bi bi-folder2-open fs-4 d-block mb-1"></i>
                Aucune pièce jointe pour ce PV.
            </div>
            <?php endif; ?>

            <!-- Formulaire d'upload -->
            <div class="border-top p-3">
                <form id="formUploadPJ" enctype="multipart/form-data">
                    <?= CSRF::field() ?>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bi bi-upload me-1"></i>Ajouter un fichier
                            </label>
                            <input type="file" name="fichier" id="inputPJFile" class="form-control form-control-sm"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.odt" required>
                            <div class="form-text">PDF, DOC(X), JPG, PNG, XLSX, ODT — max 10 Mo</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold mb-1">Description (optionnel)</label>
                            <input type="text" name="description" id="inputPJDesc" class="form-control form-control-sm"
                                   placeholder="Ex: Rapport d'expertise, acte de poursuite…" maxlength="255">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100" id="btnUploadPJ">
                                <i class="bi bi-cloud-upload me-1"></i>Envoyer
                            </button>
                        </div>
                    </div>
                    <div id="uploadPJProgress" class="mt-2 d-none">
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                 style="width:100%"></div>
                        </div>
                    </div>
                    <div id="uploadPJMsg" class="mt-2"></div>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Section antiterroriste ── -->
    <?php if ($pv['est_antiterroriste']): ?>
    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #dc3545 !important;">
        <div class="card-header text-white fw-semibold" style="background:#dc3545;">
            <i class="bi bi-shield-exclamation me-2"></i>Informations antiterroristes
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block">Région</small>
                    <strong><?= htmlspecialchars($pv['region_nom'] ?? '—') ?></strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Département</small>
                    <strong><?= htmlspecialchars($pv['dept_nom'] ?? '—') ?></strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Commune</small>
                    <strong><?= htmlspecialchars($pv['commune_nom'] ?? '—') ?></strong>
                </div>
                <div class="col-12">
                    <small class="text-muted d-block mb-2">Primo intervenants</small>
                    <?php if (!empty($pv['primo_intervenants'])): ?>
                    <?php foreach ($pv['primo_intervenants'] as $pi): ?>
                    <span class="badge bg-dark me-1 mb-1"><?= htmlspecialchars($pi['nom']) ?></span>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <span class="text-muted">Aucun renseigné</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Dossier lié ── -->
    <?php if ($dossier): ?>
    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #198754 !important;">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-folder2 me-2 text-success"></i>Dossier lié
        </div>
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <strong><?= htmlspecialchars($dossier['numero_rg']) ?></strong>
                <?php if ($dossier['numero_rp']): ?>
                <span class="badge bg-secondary ms-1"><?= htmlspecialchars($dossier['numero_rp']) ?></span>
                <?php endif; ?>
                <?php if ($dossier['numero_ri']): ?>
                <span class="badge bg-info text-dark ms-1"><?= htmlspecialchars($dossier['numero_ri']) ?></span>
                <?php endif; ?>
                <div class="text-muted small mt-1"><?= htmlspecialchars($dossier['objet']) ?></div>
            </div>
            <a href="<?= BASE_URL ?>/dossiers/show/<?= $dossier['id'] ?>"
               class="btn btn-outline-success btn-sm">
                Voir le dossier <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /col-lg-8 -->

<!-- ════════════════════════════ COLONNE DROITE ════════════════════════════ -->
<div class="col-lg-4">

    <!-- ── Statut & Workflow ── -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-diagram-3 me-2"></i>Statut &amp; Workflow
        </div>
        <div class="card-body">
            <?php
            $statutMap = [
                'recu'                    => ['secondary', 'Reçu',             "Nouveau PV, en attente d'affectation"],
                'en_traitement'           => ['warning',   'En traitement',    'Affecté à un substitut'],
                'classe'                  => ['dark',      'Classé',           'Classé sans suite'],
                'transfere_instruction'   => ['info',      '→ Instruction',    "Transféré en cabinet d'instruction"],
                'transfere_jugement_direct'=>['success',   '→ Audience directe','Envoyé directement en audience'],
            ];
            [$cls, $lbl, $desc] = $statutMap[$pv['statut']] ?? ['secondary', $pv['statut'], ''];
            ?>
            <div class="text-center mb-3">
                <span class="badge bg-<?= $cls ?> p-3 fs-6"><?= $lbl ?></span>
                <p class="text-muted small mt-2"><?= $desc ?></p>
            </div>

            <?php if ($pv['substitut_id']): ?>
            <div class="mb-2">
                <small class="text-muted">Substitut assigné</small><br>
                <strong><?= htmlspecialchars($pv['substitut_prenom'].' '.$pv['substitut_nom']) ?></strong><br>
                <?php if ($pv['date_affectation_substitut']): ?>
                <small class="text-muted">Depuis le <?= date('d/m/Y', strtotime($pv['date_affectation_substitut'])) ?></small>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($pv['statut'] === 'classe' && $pv['motif_classement']): ?>
            <div class="alert alert-secondary small mt-2 py-2">
                <?= htmlspecialchars($pv['motif_classement']) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Déclasser ── -->
    <?php if ($pv['statut'] === 'classe' && Auth::hasRole(['admin','procureur'])): ?>
    <div class="card border-0 shadow-sm mb-3 border-warning">
        <div class="card-header bg-warning fw-semibold">
            <i class="bi bi-arrow-counterclockwise me-2"></i>PV classé sans suite
        </div>
        <div class="card-body">
            <p class="small text-muted mb-2">
                <strong>Classé le :</strong>
                <?= $pv['date_classement'] ? date('d/m/Y', strtotime($pv['date_classement'])) : '—' ?>
            </p>
            <button class="btn btn-warning btn-sm w-100"
                    data-bs-toggle="modal" data-bs-target="#modalDeclasser">
                <i class="bi bi-arrow-counterclockwise me-2"></i>Déclasser ce PV
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Actions ── -->
    <?php if (in_array($pv['statut'], ['recu','en_traitement'])): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-play-circle me-2 text-primary"></i>Actions
        </div>
        <div class="card-body d-grid gap-2">
            <?php if (Auth::hasRole(['admin','procureur','president']) && $pv['statut']==='recu'): ?>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalAffecter">
                <i class="bi bi-person-check me-2"></i>Affecter un substitut
            </button>
            <?php endif; ?>
            <?php if (Auth::hasRole(['admin','procureur','substitut_procureur']) && $pv['statut']==='en_traitement'): ?>
            <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalClasser">
                <i class="bi bi-archive me-2"></i>Classer sans suite
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTransferer">
                <i class="bi bi-send me-2"></i>Transférer
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Résumé qualifications ── -->
    <?php if (!empty($pv['qualifications_substitut'])): ?>
    <div class="card border-0 shadow-sm mb-3 border-success">
        <div class="card-header text-white fw-semibold" style="background:#198754;">
            <i class="bi bi-check2-circle me-1"></i>Qualifications retenues
        </div>
        <div class="card-body py-2">
            <?php foreach ($pv['qualifications_substitut'] as $q): ?>
            <div class="d-flex align-items-start gap-2 mb-1">
                <span class="badge bg-<?= $catColors[$q['categorie']] ?? 'secondary' ?> mt-1 flex-shrink-0">
                    <?= substr(ucfirst($q['categorie']),0,4) ?>
                </span>
                <small><?= htmlspecialchars($q['libelle']) ?></small>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /col-lg-4 -->
</div><!-- /row -->

<!-- ══════════════════════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════════════════════ -->

<!-- Modal Affecter -->
<div class="modal fade" id="modalAffecter" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Affecter un substitut</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/pv/affecter/<?= $pv['id'] ?>">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <label class="form-label">Substitut du procureur</label>
                    <select name="substitut_id" class="form-select" required id="selectSubstitut">
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($substituts as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['prenom'].' '.$s['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="substitutChargeInfo" class="small text-muted mt-1"></div>
                    <button type="button" class="btn btn-outline-success btn-sm mt-2"
                            onclick="suggererSubstitut()">
                        <i class="bi bi-magic me-1"></i>Suggérer le moins chargé
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Affecter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Classer -->
<div class="modal fade" id="modalClasser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Classer sans suite</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/pv/classer/<?= $pv['id'] ?>">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <label class="form-label">Motif de classement <span class="text-danger">*</span></label>
                    <textarea name="motif_classement" class="form-control" rows="4" required
                              placeholder="Indiquer le motif de classement…"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-dark">Classer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Transférer -->
<div class="modal fade" id="modalTransferer" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transférer le PV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/pv/transferer/<?= $pv['id'] ?>">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Destination <span class="text-danger">*</span></label>
                        <div class="d-grid gap-2">
                            <div class="form-check border rounded p-3">
                                <input class="form-check-input" type="radio" name="destination"
                                       value="instruction" id="destInstr" required onchange="toggleCabinet(true)">
                                <label class="form-check-label" for="destInstr">
                                    <strong>Cabinet d'instruction</strong> — Ouvre une instruction judiciaire (génère RP + RI)
                                </label>
                            </div>
                            <div class="form-check border rounded p-3">
                                <input class="form-check-input" type="radio" name="destination"
                                       value="audience_directe" id="destAud" onchange="toggleCabinet(false)">
                                <label class="form-check-label" for="destAud">
                                    <strong>Audience directe</strong> — Renvoie directement en audience de jugement (génère RP)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div id="cabinetBlock" style="display:none" class="mb-3">
                        <label class="form-label">Cabinet d'instruction</label>
                        <select name="cabinet_id" class="form-select" id="selectCabinet">
                            <option value="">— Sélectionner —</option>
                            <?php foreach ($cabinets as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['numero'] . ' — ' . $c['libelle']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="cabinetChargeInfo" class="mt-1 small text-muted"></div>
                        <button type="button" class="btn btn-outline-success btn-sm mt-1" onclick="suggererCabinet()">
                            <i class="bi bi-magic me-1"></i>Suggérer le moins chargé
                        </button>
                    </div>
                    <div id="modePoursuiteBlock" style="display:none" class="mb-3">
                        <label class="form-label fw-semibold">Mode de poursuite <span class="text-danger">*</span></label>
                        <select name="mode_poursuite" class="form-select" id="selectModePoursuite">
                            <option value="aucun">— AUCUN —</option>
                            <option value="CD">CD — Citation Directe</option>
                            <option value="FD">FD — Flagrant Délit</option>
                            <option value="CRCP">CRCP — Comparution sur Reconnaissance Préalable de Culpabilité</option>
                            <option value="RI">RI — Réquisitoire Introductif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Objet du dossier <span class="text-danger">*</span></label>
                        <textarea name="objet" class="form-control" rows="3" required><?= htmlspecialchars($pv['description_faits'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Transférer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Déclasser -->
<div class="modal fade" id="modalDeclasser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Déclasser le PV
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/pv/declasser/<?= $pv['id'] ?>">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        Le PV sera remis au statut <strong>En traitement</strong> pour reprise du dossier.
                    </div>
                    <?php if ($pv['motif_classement']): ?>
                    <div class="mb-3">
                        <small class="text-muted">Motif du classement initial :</small>
                        <p class="fst-italic small"><?= htmlspecialchars($pv['motif_classement']) ?></p>
                    </div>
                    <?php endif; ?>
                    <label class="form-label fw-bold">
                        Motif du déclassement <span class="text-danger">*</span>
                    </label>
                    <textarea name="motif_declassement" class="form-control" rows="4" required
                              placeholder="Exposez les raisons du déclassement (nouveaux éléments, erreur, …)"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Confirmer le déclassement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════════════════════ -->
<script>
(function () {
'use strict';

/* ── Récupération CSRF ── */
function getCsrfToken() {
    var f = document.querySelector('input[name="csrf_token"]');
    return f ? f.value : '';
}

/* ══════════════════════════════════════════════════════
   SECTION QUALIFICATION — couleur au clic
══════════════════════════════════════════════════════ */
var listQualif = document.getElementById('listQualif');
if (listQualif) {
    listQualif.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('qualif-checkbox')) {
            var row = e.target.closest('.qualif-row');
            if (row) {
                if (e.target.checked) {
                    row.classList.add('selected');
                    row.style.background = '#1a3c5e';
                    row.style.color      = '#fff';
                    row.querySelectorAll('code').forEach(function(c){ c.style.color='#93c5fd'; });
                } else {
                    row.classList.remove('selected');
                    row.style.background = '#fff';
                    row.style.color      = '#212529';
                    row.querySelectorAll('code').forEach(function(c){ c.style.color='#6c757d'; });
                }
                updateQualifCount();
            }
        }
    });

    /* Filtre texte qualification */
    var filterQualif = document.getElementById('filterQualif');
    if (filterQualif) {
        filterQualif.addEventListener('input', function () {
            var q = this.value.toLowerCase();
            listQualif.querySelectorAll('.qualif-row').forEach(function (row) {
                row.style.display = (row.dataset.libelle || '').indexOf(q) >= 0 ? '' : 'none';
            });
        });
    }

    function updateQualifCount() {
        var n = listQualif.querySelectorAll('.qualif-checkbox:checked').length;
        var cnt = document.getElementById('cntQualif');
        if (cnt) cnt.textContent = n + ' sélectionnée(s)';
    }
    updateQualifCount(); /* Init au chargement */
}

/* ══════════════════════════════════════════════════════
   PIÈCES JOINTES — Upload AJAX
══════════════════════════════════════════════════════ */
var formUploadPJ = document.getElementById('formUploadPJ');
if (formUploadPJ) {
    formUploadPJ.addEventListener('submit', function (e) {
        e.preventDefault();

        var fileInput = document.getElementById('inputPJFile');
        if (!fileInput.files.length) return;

        var fd = new FormData(formUploadPJ);
        /* On s'assure que le fichier est dans le FormData */
        fd.set('fichier', fileInput.files[0]);

        var btn  = document.getElementById('btnUploadPJ');
        var prog = document.getElementById('uploadPJProgress');
        var msg  = document.getElementById('uploadPJMsg');

        btn.disabled   = true;
        prog.classList.remove('d-none');
        msg.innerHTML  = '';

        fetch('<?= BASE_URL ?>/pv/upload/<?= $pv['id'] ?>', {
            method: 'POST',
            body:   fd,
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            prog.classList.add('d-none');
            btn.disabled = false;

            if (data.success) {
                msg.innerHTML = '<div class="alert alert-success py-2 small">' +
                    '<i class="bi bi-check-circle me-1"></i>' + data.message + '</div>';
                /* Ajouter dynamiquement la pièce jointe à la liste */
                addPJToList(data.document);
                formUploadPJ.reset();
            } else {
                msg.innerHTML = '<div class="alert alert-danger py-2 small">' +
                    '<i class="bi bi-exclamation-triangle me-1"></i>' +
                    (data.message || 'Erreur inconnue') + '</div>';
            }
        })
        .catch(function () {
            prog.classList.add('d-none');
            btn.disabled = false;
            msg.innerHTML = '<div class="alert alert-danger py-2 small">' +
                '<i class="bi bi-wifi-off me-1"></i>Erreur réseau, veuillez réessayer.</div>';
        });
    });
}

function addPJToList(doc) {
    /* Cacher message "aucune pièce" si présent */
    var emptyDiv = document.getElementById('pjEmpty');
    if (emptyDiv) emptyDiv.style.display = 'none';

    var ul = document.getElementById('pjList');
    if (!ul) {
        /* Créer la liste si elle n'existait pas */
        ul = document.createElement('ul');
        ul.id = 'pjList';
        ul.className = 'list-group list-group-flush';
        var cardBody = formUploadPJ.closest('.card-body');
        cardBody.parentNode.insertBefore(ul, cardBody);
    }

    var icon = doc.type && doc.type.includes('pdf')   ? 'bi-file-earmark-pdf text-danger'
             : doc.type && doc.type.includes('image') ? 'bi-file-earmark-image text-info'
             : 'bi-file-earmark text-secondary';

    var li = document.createElement('li');
    li.id        = 'pj-item-' + doc.id;
    li.className = 'list-group-item d-flex align-items-center justify-content-between gap-2 py-2 px-3';
    li.innerHTML =
        '<div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">' +
            '<i class="bi ' + icon + ' fs-5 flex-shrink-0"></i>' +
            '<div class="min-w-0">' +
                '<a href="<?= BASE_URL ?>/documents/view/' + doc.id + '" target="_blank" ' +
                   'class="text-decoration-none fw-semibold text-truncate d-block">' +
                    escHtml(doc.nom) +
                '</a>' +
                '<small class="text-muted">' + doc.date + '</small>' +
            '</div>' +
        '</div>' +
        '<button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0 btn-delete-pj" ' +
            'data-doc-id="' + doc.id + '" title="Supprimer">' +
            '<i class="bi bi-trash"></i>' +
        '</button>';
    ul.insertBefore(li, ul.firstChild);

    /* Mettre à jour le compteur */
    updatePJCount(1);
}

/* ══════════════════════════════════════════════════════
   PIÈCES JOINTES — Suppression AJAX
══════════════════════════════════════════════════════ */
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-delete-pj');
    if (!btn) return;

    var docId = btn.dataset.docId;
    if (!docId) return;

    if (!confirm('Supprimer définitivement cette pièce jointe ?\nCette action est irréversible.')) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    var fd = new FormData();
    fd.append('csrf_token', getCsrfToken());
    fd.append('_csrf',      getCsrfToken()); /* double champ CSRF */

    fetch('<?= BASE_URL ?>/pv/document/delete/' + docId, {
        method: 'POST',
        body:   fd,
        credentials: 'same-origin'
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) {
            var li = document.getElementById('pj-item-' + docId);
            if (li) li.remove();
            updatePJCount(-1);
        } else {
            alert(data.message || 'Erreur lors de la suppression.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash"></i>';
        }
    })
    .catch(function () {
        alert('Erreur réseau lors de la suppression.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash"></i>';
    });
});

function updatePJCount(delta) {
    var badge = document.getElementById('pjCount');
    if (!badge) return;
    var n = Math.max(0, parseInt(badge.textContent, 10) + delta);
    badge.textContent = n;
}

/* ══════════════════════════════════════════════════════
   HELPERS
══════════════════════════════════════════════════════ */
function escHtml(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ══════════════════════════════════════════════════════
   MODAL TRANSFERT
══════════════════════════════════════════════════════ */
window.toggleCabinet = function (show) {
    document.getElementById('cabinetBlock').style.display      = show ? 'block' : 'none';
    document.getElementById('modePoursuiteBlock').style.display = show ? 'block' : 'none';
};

window.suggererCabinet = function () {
    fetch('<?= BASE_URL ?>/api/cabinets/charge')
    .then(function(r){ return r.json(); })
    .then(function(data){
        if (data.success && data.data.length) {
            var best = data.data[0];
            var sel  = document.getElementById('selectCabinet');
            if (sel) {
                sel.value = best.id;
                document.getElementById('cabinetChargeInfo').innerHTML =
                    '<i class="bi bi-info-circle text-success me-1"></i>Suggéré : <strong>' +
                    best.numero + ' — ' + best.libelle + '</strong> (' +
                    best.nb_dossiers + ' dossier(s) actif(s))';
            }
        }
    }).catch(function(){});
};

window.suggererSubstitut = function () {
    fetch('<?= BASE_URL ?>/api/substituts/charge')
    .then(function(r){ return r.json(); })
    .then(function(data){
        if (data.success && data.data.length) {
            var best = data.data[0];
            var sel  = document.getElementById('selectSubstitut');
            if (sel) {
                sel.value = best.id;
                document.getElementById('substitutChargeInfo').innerHTML =
                    '<i class="bi bi-info-circle text-success me-1"></i>Suggéré : <strong>' +
                    best.prenom + ' ' + best.nom + '</strong> (' +
                    best.nb_pvs + ' PV(s) en cours)';
            }
        }
    }).catch(function(){});
};

})();
</script>
