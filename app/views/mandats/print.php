<?php
/* app/views/mandats/print.php — Document imprimable officiel avec QR code et en-têtes paramétrables */
$typeLabels = [
    'arret'       => 'MANDAT D\'ARRÊT',
    'depot'       => 'MANDAT DE DÉPÔT',
    'amener'      => 'MANDAT D\'AMENER',
    'comparution' => 'MANDAT DE COMPARUTION',
    'perquisition'=> 'MANDAT DE PERQUISITION',
    'liberation'  => 'MANDAT DE LIBÉRATION',
];
$tl = $typeLabels[$mandat['type_mandat']] ?? strtoupper($mandat['type_mandat']);
if ($mandat['detenu_label'])     $cible = $mandat['detenu_label'];
elseif ($mandat['partie_label']) $cible = $mandat['partie_label'];
elseif ($mandat['nouveau_nom'])  $cible = trim($mandat['nouveau_prenom'] . ' ' . $mandat['nouveau_nom']);
else                              $cible = 'INCONNU';

// Paramètres tribunal depuis la table (fallback si table non dispo)
try {
    $db = Database::getInstance()->getPDO();
    $prows = $db->query("SELECT cle, valeur FROM parametres_tribunal")->fetchAll(\PDO::FETCH_KEY_PAIR);
} catch (\Exception $e) {
    $prows = [];
}
$p = function(string $cle, string $default = '') use ($prows): string {
    return $prows[$cle] ?? $default;
};

$entete1  = $p('doc_entete_ligne1', 'REPUBLIQUE DU NIGER');
$entete2  = $p('doc_entete_ligne2', 'MINISTÈRE DE LA JUSTICE');
$entete3  = $p('doc_entete_ligne3', 'Tribunal de Grande Instance Hors Classe de Niamey');
$piedPage = $p('doc_pied_page', 'Document officiel — TGI-NY — Niamey — République du Niger');
$devise   = $p('tribunal_devise', 'Fraternité — Travail — Progrès');
$adresse  = $p('tribunal_adresse', 'Avenue de la Mairie — B.P. 466 — Niamey, République du Niger');
$qrActif  = $p('doc_qr_code_actif', '1') === '1';
$qrBase   = rtrim($p('doc_qr_code_base_url', BASE_URL), '/');
$qrUrl    = $qrBase . '/mandats/show/' . $mandat['id'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= $tl ?> — <?= htmlspecialchars($mandat['numero']) ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Times New Roman',serif;font-size:12pt;color:#000;background:#fff;padding:15mm 20mm}
.header{text-align:center;border-bottom:3px double #000;padding-bottom:12px;margin-bottom:20px}
.republic{font-size:12pt;font-weight:bold;letter-spacing:2px;text-transform:uppercase}
.devise{font-size:9pt;font-style:italic;margin:4px 0}
.tribunal{font-size:13pt;font-weight:bold;margin:8px 0}
.pole{font-size:10pt;color:#555;margin-bottom:6px}
.doc-title{text-align:center;margin:20px 0 18px}
.doc-title h1{font-size:18pt;font-weight:bold;text-transform:uppercase;border:3px solid #000;padding:8px 24px;display:inline-block;letter-spacing:3px}
.doc-title .numero{font-size:12pt;margin-top:6px;font-weight:bold}
.section{margin:12px 0}
.section h3{font-size:10pt;font-weight:bold;text-transform:uppercase;border-bottom:1px solid #000;padding-bottom:3px;margin-bottom:8px;letter-spacing:1px}
.field-row{display:flex;margin:5px 0;line-height:1.6}
.field-label{font-weight:bold;min-width:180px;flex-shrink:0}
.field-value{flex:1;border-bottom:1px dotted #999;padding-left:5px}
.motif-box{border:1px solid #000;padding:10px;margin:8px 0;min-height:70px;line-height:1.8;text-align:justify}
.signatures{margin-top:35px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px}
.sig-block{text-align:center}
.sig-block .sig-title{font-weight:bold;text-transform:uppercase;font-size:9.5pt;border-top:1px solid #000;padding-top:8px;margin-top:55px}
.sig-block .sig-name{font-size:8pt;color:#555;margin-top:4px}
.footer{margin-top:25px;padding-top:8px;border-top:1px solid #000;font-size:8pt;color:#555;text-align:center}
.validity{background:#f5f5f5;border:1px solid #ccc;padding:8px;margin:12px 0;font-size:9.5pt}
.qr-block{float:right;margin:0 0 10px 20px;text-align:center}
.qr-block canvas, .qr-block img{display:block}
.qr-block small{font-size:7pt;color:#888;display:block;margin-top:3px}
.header-logos{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.header-logos img{height:60px;width:auto;object-fit:contain}
.clearfix::after{content:'';display:block;clear:both}
@media print{
    body{padding:10mm 15mm}
    @page{margin:8mm 12mm;size:A4}
    .no-print{display:none}
}
</style>
</head>
<body onload="initQR();window.setTimeout(function(){window.print();},400)">

<div class="header">
    <!-- Logos + République -->
    <?php if (file_exists(ROOT_PATH . '/public/assets/img/logos_tgi.png')): ?>
    <div class="header-logos">
        <img src="<?= BASE_URL ?>/assets/img/logos_tgi.png" alt="Logos TGI" style="height:65px">
        <div>
            <div class="republic"><?= htmlspecialchars($entete1) ?></div>
            <div class="devise"><?= htmlspecialchars($devise) ?></div>
        </div>
        <div style="width:65px"></div>
    </div>
    <?php else: ?>
    <div class="republic"><?= htmlspecialchars($entete1) ?></div>
    <div class="devise"><?= htmlspecialchars($devise) ?></div>
    <?php endif; ?>
    <div style="margin:6px 0;font-size:8pt"><?= htmlspecialchars($entete2) ?></div>
    <div class="tribunal"><?= htmlspecialchars($entete3) ?></div>
    <div class="pole">Pôle Judiciaire — TGI-NY</div>
    <div style="font-size:8pt;margin-top:4px"><?= htmlspecialchars($adresse) ?></div>
</div>

<!-- QR code -->
<?php if ($qrActif): ?>
<div class="qr-block" id="qrBlock">
    <canvas id="qrCanvas" width="80" height="80"></canvas>
    <small>Vérifier en ligne</small>
</div>
<?php endif; ?>

<div class="doc-title clearfix">
    <h1><?= $tl ?></h1>
    <div class="numero"><?= htmlspecialchars($mandat['numero']) ?></div>
</div>

<p style="text-align:center;font-style:italic;margin-bottom:15px">
    Nous, <strong><?= htmlspecialchars($mandat['emetteur_nom']) ?></strong>,
    <?= htmlspecialchars($mandat['emetteur_role'] ?? 'Magistrat') ?> auprès du Tribunal de Grande Instance Hors Classe de Niamey,
</p>

<div class="section">
    <h3>I — Identification de la personne concernée</h3>
    <div class="field-row"><span class="field-label">Nom et Prénom(s) :</span><span class="field-value"><strong><?= htmlspecialchars($cible) ?></strong></span></div>
    <?php if ($mandat['nouveau_ddn']): ?>
    <div class="field-row"><span class="field-label">Date de naissance :</span><span class="field-value"><?= date('d/m/Y', strtotime($mandat['nouveau_ddn'])) ?></span></div>
    <?php endif; ?>
    <?php if ($mandat['nouveau_nationalite'] ?? $mandat['detenu_label']): ?>
    <div class="field-row"><span class="field-label">Nationalité :</span><span class="field-value"><?= htmlspecialchars($mandat['nouveau_nationalite'] ?? 'Nigérienne') ?></span></div>
    <?php endif; ?>
    <?php if ($mandat['nouveau_profession']): ?>
    <div class="field-row"><span class="field-label">Profession :</span><span class="field-value"><?= htmlspecialchars($mandat['nouveau_profession']) ?></span></div>
    <?php endif; ?>
    <?php if ($mandat['nouveau_adresse']): ?>
    <div class="field-row"><span class="field-label">Adresse :</span><span class="field-value"><?= htmlspecialchars($mandat['nouveau_adresse']) ?></span></div>
    <?php endif; ?>
    <?php if ($mandat['numero_ecrou']): ?>
    <div class="field-row"><span class="field-label">N° d'écrou :</span><span class="field-value"><?= htmlspecialchars($mandat['numero_ecrou']) ?></span></div>
    <?php endif; ?>
</div>

<?php if ($mandat['infraction_libelle']): ?>
<div class="section">
    <h3>II — Infractions retenues</h3>
    <div class="motif-box"><?= nl2br(htmlspecialchars($mandat['infraction_libelle'])) ?></div>
</div>
<?php endif; ?>

<div class="section">
    <h3>III — Motif du mandat</h3>
    <div class="motif-box"><?= nl2br(htmlspecialchars($mandat['motif'])) ?></div>
</div>

<?php if ($mandat['lieu_execution']): ?>
<div class="section">
    <h3>IV — Lieu d'exécution</h3>
    <div class="field-row"><span class="field-label">Lieu prévu :</span><span class="field-value"><?= htmlspecialchars($mandat['lieu_execution']) ?></span></div>
</div>
<?php endif; ?>

<div class="validity">
    <strong>📅 Date d'émission :</strong> <?= date('d/m/Y', strtotime($mandat['date_emission'])) ?>
    <?php if ($mandat['date_expiration']): ?>
    &nbsp;&nbsp;|&nbsp;&nbsp; <strong>⏳ Valable jusqu'au :</strong> <?= date('d/m/Y', strtotime($mandat['date_expiration'])) ?>
    <?php else: ?>
    &nbsp;&nbsp;|&nbsp;&nbsp; <strong>⏳ Validité :</strong> Illimitée
    <?php endif; ?>
    <?php if ($mandat['numero_rg']): ?>
    &nbsp;&nbsp;|&nbsp;&nbsp; <strong>📁 Dossier :</strong> <?= htmlspecialchars($mandat['numero_rg']) ?>
    <?php endif; ?>
</div>

<p style="margin:15px 0;text-align:justify">
    En conséquence, nous ordonnons à tout Officier de Police Judiciaire et à tout agent de la force publique de
    <?php if ($mandat['type_mandat'] === 'arret'): ?>procéder à l'arrestation immédiate de la personne sus-désignée et de la conduire devant nous.
    <?php elseif ($mandat['type_mandat'] === 'depot'): ?>conduire la personne sus-désignée à <?= htmlspecialchars($mandat['lieu_execution'] ?? 'la maison d\'arrêt compétente') ?> pour y être incarcérée.
    <?php elseif ($mandat['type_mandat'] === 'amener'): ?>amener devant nous la personne sus-désignée pour être entendue.
    <?php elseif ($mandat['type_mandat'] === 'comparution'): ?>notifier à la personne sus-désignée l'ordre de comparaître devant ce tribunal.
    <?php elseif ($mandat['type_mandat'] === 'perquisition'): ?>procéder aux perquisitions et saisies nécessaires au lieu indiqué.
    <?php elseif ($mandat['type_mandat'] === 'liberation'): ?>procéder à la mise en liberté immédiate de la personne sus-désignée.
    <?php endif; ?>
</p>

<div class="signatures">
    <div class="sig-block">
        <div style="height:50px"></div>
        <div class="sig-title">Le Magistrat Émetteur</div>
        <div class="sig-name"><?= htmlspecialchars($mandat['emetteur_nom']) ?><br><em><?= htmlspecialchars($mandat['emetteur_role'] ?? '') ?></em></div>
    </div>
    <div class="sig-block">
        <div style="height:50px"></div>
        <div class="sig-title">Le Procureur de la République</div>
        <div class="sig-name">Tribunal de Grande Instance<br>Hors Classe de Niamey</div>
    </div>
    <div class="sig-block">
        <div style="height:50px"></div>
        <div class="sig-title">Le Greffier en Chef</div>
        <div class="sig-name">Tribunal de Grande Instance<br>Hors Classe de Niamey</div>
    </div>
</div>

<div class="footer">
    <?= htmlspecialchars($piedPage) ?> — <?= htmlspecialchars($mandat['numero']) ?> — Généré le <?= date('d/m/Y à H:i') ?>
    — Ce document est valable uniquement avec le cachet et la signature originaux du tribunal.
</div>

<?php if ($qrActif): ?>
<!-- QR Code via bibliothèque JS (pas de dépendance serveur) -->
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
function initQR() {
    var canvas = document.getElementById('qrCanvas');
    if (!canvas) return;
    QRCode.toCanvas(canvas, <?= json_encode($qrUrl) ?>, {
        width: 80,
        margin: 1,
        color: { dark: '#000000', light: '#ffffff' }
    }, function(err) {
        if (err) console.error('QR error:', err);
    });
}
</script>
<?php else: ?>
<script>function initQR(){}</script>
<?php endif; ?>

</body>
</html>
