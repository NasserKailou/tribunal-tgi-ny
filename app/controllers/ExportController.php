<?php
/**
 * TGI-NY — Export PDF
 * Utilise mPDF via Composer OU génère un HTML imprimable (fallback sans dépendances)
 * Route: /export/jugement/{id}, /export/pv/{id}, /export/dossier/{id}
 */

class ExportController extends Controller {

    /**
     * Export jugement en PDF (HTML imprimable)
     */
    public function jugement(string $id): void {
        Auth::requireLogin();

        $stmt = $this->db->prepare(
            "SELECT j.*, d.numero_rg, d.numero_rp, d.numero_ri, d.objet, d.type_affaire,
                    g.nom as greffier_nom, g.prenom as greffier_prenom,
                    u.nom as created_nom, u.prenom as created_prenom
             FROM jugements j
             JOIN dossiers d ON j.dossier_id = d.id
             LEFT JOIN users u ON j.created_by = u.id
             LEFT JOIN users g ON j.greffier_id = g.id
             WHERE j.id = ?"
        );
        $stmt->execute([(int)$id]);
        $jugement = $stmt->fetch();
        if (!$jugement) { $this->redirect('/jugements'); }

        // Parties du dossier
        $pStmt = $this->db->prepare("SELECT * FROM parties WHERE dossier_id = ? ORDER BY type_partie");
        $pStmt->execute([$jugement['dossier_id']]);
        $parties = $pStmt->fetchAll();

        header('Content-Type: text/html; charset=utf-8');
        echo $this->renderJugementHTML($jugement, $parties);
        exit;
    }

    /**
     * Export PV en HTML imprimable
     */
    public function pv(string $id): void {
        Auth::requireLogin();

        $stmt = $this->db->prepare(
            "SELECT p.*, ue.nom as unite_nom, ue.type as unite_type,
                    us.nom as substitut_nom, us.prenom as substitut_prenom,
                    r.nom as region_nom, dep.nom as dept_nom, c.nom as commune_nom,
                    cb.nom as created_nom, cb.prenom as created_prenom
             FROM pv p
             LEFT JOIN unites_enquete ue ON p.unite_enquete_id = ue.id
             LEFT JOIN users us ON p.substitut_id = us.id
             LEFT JOIN regions r ON p.region_id = r.id
             LEFT JOIN departements dep ON p.departement_id = dep.id
             LEFT JOIN communes c ON p.commune_id = c.id
             LEFT JOIN users cb ON p.created_by = cb.id
             WHERE p.id = ?"
        );
        $stmt->execute([(int)$id]);
        $pv = $stmt->fetch();
        if (!$pv) { $this->redirect('/pv'); }

        $piStmt = $this->db->prepare(
            "SELECT pi.nom FROM primo_intervenants pi
             JOIN pv_primo_intervenants ppi ON pi.id = ppi.primo_intervenant_id
             WHERE ppi.pv_id = ?"
        );
        $piStmt->execute([(int)$id]);
        $pv['primo_intervenants'] = $piStmt->fetchAll(PDO::FETCH_COLUMN);

        header('Content-Type: text/html; charset=utf-8');
        echo $this->renderPVHTML($pv);
        exit;
    }

    /**
     * Export dossier complet
     */
    public function dossier(string $id): void {
        Auth::requireLogin();

        $dStmt = $this->db->prepare(
            "SELECT d.*, us.nom as substitut_nom, us.prenom as substitut_prenom,
                    ci.numero as cabinet_num, ci.libelle as cabinet_lib,
                    ji.nom as juge_instr_nom, ji.prenom as juge_instr_prenom
             FROM dossiers d
             LEFT JOIN users us ON d.substitut_id = us.id
             LEFT JOIN cabinets_instruction ci ON d.cabinet_id = ci.id
             LEFT JOIN users ji ON ci.juge_id = ji.id
             WHERE d.id = ?"
        );
        $dStmt->execute([(int)$id]);
        $dossier = $dStmt->fetch();
        if (!$dossier) { $this->redirect('/dossiers'); }

        $pStmt = $this->db->prepare("SELECT * FROM parties WHERE dossier_id=? ORDER BY type_partie");
        $pStmt->execute([(int)$id]);
        $parties = $pStmt->fetchAll();

        $aStmt = $this->db->prepare("SELECT a.*, s.nom as salle_nom, u.nom as president_nom FROM audiences a LEFT JOIN salles_audience s ON a.salle_id=s.id LEFT JOIN users u ON a.president_id=u.id WHERE a.dossier_id=? ORDER BY a.date_audience");
        $aStmt->execute([(int)$id]);
        $audiences = $aStmt->fetchAll();

        $jStmt = $this->db->prepare("SELECT j.*, g.nom as greffier_nom FROM jugements j LEFT JOIN users g ON j.greffier_id=g.id WHERE j.dossier_id=? ORDER BY j.date_jugement DESC");
        $jStmt->execute([(int)$id]);
        $jugements = $jStmt->fetchAll();

        $mvtStmt = $this->db->prepare("SELECT m.*, u.nom, u.prenom FROM mouvements_dossier m LEFT JOIN users u ON m.user_id=u.id WHERE m.dossier_id=? ORDER BY m.created_at");
        $mvtStmt->execute([(int)$id]);
        $mouvements = $mvtStmt->fetchAll();

        header('Content-Type: text/html; charset=utf-8');
        echo $this->renderDossierHTML($dossier, $parties, $audiences, $jugements, $mouvements);
        exit;
    }

    // ========================================================
    // Rendu HTML imprimable
    // ========================================================

    private function printHeader(): string {
        return '<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  @page { margin: 2cm; }
  * { box-sizing: border-box; }
  body { font-family: "Times New Roman", serif; font-size: 12pt; color: #111; margin: 0; padding: 20px; }
  .header-tribunal { text-align: center; border-bottom: 3px double #1a3c5e; padding-bottom: 16px; margin-bottom: 20px; }
  .header-tribunal h1 { font-size: 15pt; color: #1a3c5e; margin: 0 0 4px; text-transform: uppercase; letter-spacing: 1px; }
  .header-tribunal h2 { font-size: 11pt; color: #555; margin: 0 0 4px; font-weight: normal; }
  .header-tribunal .republique { font-size: 10pt; font-weight: bold; margin-bottom: 8px; color: #333; }
  .doc-title { text-align: center; font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin: 24px 0 20px; color: #1a3c5e; border: 2px solid #1a3c5e; padding: 10px; }
  .numero-doc { text-align: center; font-size: 12pt; margin-bottom: 20px; font-weight: bold; }
  table.info { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  table.info td { padding: 5px 10px; border: 1px solid #ccc; font-size: 11pt; }
  table.info td:first-child { font-weight: bold; background: #f5f5f5; width: 35%; }
  .section { margin: 20px 0 8px; font-size: 12pt; font-weight: bold; color: #1a3c5e; border-bottom: 1px solid #ccc; padding-bottom: 4px; text-transform: uppercase; }
  .dispositif { border: 2px solid #1a3c5e; padding: 16px; margin: 16px 0; background: #f9f9ff; font-size: 11pt; line-height: 1.8; }
  .badge-type { display: inline-block; border: 1px solid #333; padding: 2px 8px; font-size: 10pt; font-weight: bold; }
  .footer-doc { margin-top: 40px; border-top: 1px solid #999; padding-top: 12px; display: flex; justify-content: space-between; font-size: 10pt; color: #555; }
  .signature-block { margin-top: 50px; display: flex; justify-content: space-around; text-align: center; }
  .signature-block .sig { width: 200px; border-top: 1px solid #333; padding-top: 8px; font-size: 10pt; }
  .anti-badge { background: #8b0000; color: #fff; padding: 3px 10px; font-size: 10pt; border-radius: 3px; }
  .no-print { }
  @media print {
    .no-print { display: none !important; }
    body { padding: 0; }
  }
</style>
</head>
<body>';
    }

    private function printFooter(): string {
        return '
<div class="no-print" style="text-align:center;margin-top:20px">
  <button onclick="window.print()" style="background:#1a3c5e;color:#fff;border:none;padding:10px 24px;font-size:13pt;border-radius:4px;cursor:pointer">🖨 Imprimer / Enregistrer PDF</button>
  <button onclick="window.close()" style="background:#666;color:#fff;border:none;padding:10px 24px;font-size:13pt;border-radius:4px;cursor:pointer;margin-left:10px">✕ Fermer</button>
</div>
</body></html>';
    }

    private function printTribunalHeader(): string {
        return '<div class="header-tribunal">
  <div class="republique">RÉPUBLIQUE DU NIGER<br>FRATERNITÉ — TRAVAIL — PROGRÈS</div>
  <h1>Tribunal de Grande Instance Hors Classe de Niamey</h1>
  <h2>TGI-NY — Parquet du Procureur de la République</h2>
</div>';
    }

    private function renderJugementHTML(array $j, array $parties): string {
        $typeLabels = ['condamnation'=>'CONDAMNATION','acquittement'=>'ACQUITTEMENT','non_lieu'=>'NON-LIEU','relaxe'=>'RELAXE','renvoi'=>'RENVOI','avant_droit'=>'AVANT-DROIT','autre'=>'JUGEMENT'];
        $typeLabel = strtoupper($typeLabels[$j['type_jugement']] ?? $j['type_jugement']);
        $html = $this->printHeader();
        $html .= $this->printTribunalHeader();
        $html .= '<div class="doc-title">JUGEMENT — ' . htmlspecialchars($typeLabel) . '</div>';
        $html .= '<div class="numero-doc">' . htmlspecialchars($j['numero_jugement']) . '</div>';

        $html .= '<div class="section">Identification</div>';
        $html .= '<table class="info">
          <tr><td>Numéro de jugement</td><td>' . htmlspecialchars($j['numero_jugement']) . '</td></tr>
          <tr><td>Date du jugement</td><td>' . date('d/m/Y', strtotime($j['date_jugement'])) . '</td></tr>
          <tr><td>Dossier N° RG</td><td>' . htmlspecialchars($j['numero_rg']) . '</td></tr>
          ' . ($j['numero_rp'] ? '<tr><td>N° RP (Parquet)</td><td>' . htmlspecialchars($j['numero_rp']) . '</td></tr>' : '') . '
          ' . ($j['numero_ri'] ? '<tr><td>N° RI (Instruction)</td><td>' . htmlspecialchars($j['numero_ri']) . '</td></tr>' : '') . '
          <tr><td>Type d\'affaire</td><td>' . ucfirst(htmlspecialchars($j['type_affaire'])) . '</td></tr>
          <tr><td>Objet</td><td>' . htmlspecialchars($j['objet']) . '</td></tr>
          <tr><td>Type de décision</td><td><span class="badge-type">' . htmlspecialchars($typeLabel) . '</span></td></tr>
          <tr><td>Greffier</td><td>' . htmlspecialchars(($j['greffier_prenom'] ?? '') . ' ' . ($j['greffier_nom'] ?? '—')) . '</td></tr>
        </table>';

        if (!empty($parties)) {
            $html .= '<div class="section">Parties en cause</div><table class="info">';
            foreach ($parties as $p) {
                $html .= '<tr><td>' . htmlspecialchars(str_replace('_',' ',$p['type_partie'])) . '</td><td><strong>' . htmlspecialchars($p['nom'] . ' ' . $p['prenom']) . '</strong>' . ($p['nationalite'] ? ' — ' . htmlspecialchars($p['nationalite']) : '') . '</td></tr>';
            }
            $html .= '</table>';
        }

        $html .= '<div class="section">Dispositif du jugement</div>';
        $html .= '<div class="dispositif">' . nl2br(htmlspecialchars($j['dispositif'])) . '</div>';

        if ($j['type_jugement'] === 'condamnation' && $j['peine_principale']) {
            $html .= '<div class="section">Peine prononcée</div>';
            $html .= '<table class="info">
              <tr><td>Peine principale</td><td>' . htmlspecialchars($j['peine_principale']) . '</td></tr>';
            if ($j['duree_peine_mois']) $html .= '<tr><td>Durée</td><td>' . $j['duree_peine_mois'] . ' mois</td></tr>';
            if ($j['montant_amende'])   $html .= '<tr><td>Amende</td><td>' . number_format((float)$j['montant_amende'],0,',',' ') . ' FCFA</td></tr>';
            if ($j['sursis'])           $html .= '<tr><td>Sursis</td><td>Oui — ' . ($j['duree_sursis_mois'] ?? '') . ' mois</td></tr>';
            $html .= '</table>';
        }

        if ($j['appel_possible']) {
            $html .= '<div class="section">Voies de recours</div>';
            $html .= '<table class="info">
              <tr><td>Appel possible</td><td>Oui</td></tr>
              <tr><td>Délai limite d\'appel</td><td>' . ($j['date_limite_appel'] ? date('d/m/Y', strtotime($j['date_limite_appel'])) : '—') . '</td></tr>
            </table>';
        }

        if ($j['notes']) {
            $html .= '<div class="section">Notes</div><p>' . nl2br(htmlspecialchars($j['notes'])) . '</p>';
        }

        $html .= '<div class="signature-block">
          <div class="sig">Le Greffier<br><br><br>' . htmlspecialchars(($j['greffier_prenom']??'').(' '.$j['greffier_nom']??'')) . '</div>
          <div class="sig">Le Président du Tribunal<br><br><br>&nbsp;</div>
          <div class="sig">Le Procureur de la République<br><br><br>&nbsp;</div>
        </div>';
        $html .= '<div class="footer-doc"><span>Document généré le ' . date('d/m/Y à H:i') . '</span><span>TGI-NY — Niamey, Niger</span></div>';
        $html .= $this->printFooter();
        return $html;
    }

    private function renderPVHTML(array $pv): string {
        $html = $this->printHeader();
        $html .= $this->printTribunalHeader();
        $html .= '<div class="doc-title">PROCÈS-VERBAL D\'ENREGISTREMENT</div>';
        $html .= '<div class="numero-doc">' . htmlspecialchars($pv['numero_rg']) . '</div>';

        $html .= '<div class="section">Identification du PV</div>';
        $html .= '<table class="info">
          <tr><td>N° RG</td><td><strong>' . htmlspecialchars($pv['numero_rg']) . '</strong></td></tr>
          <tr><td>N° PV d\'origine</td><td>' . htmlspecialchars($pv['numero_pv']) . '</td></tr>
          <tr><td>Date du PV</td><td>' . date('d/m/Y', strtotime($pv['date_pv'])) . '</td></tr>
          <tr><td>Date de réception</td><td>' . date('d/m/Y', strtotime($pv['date_reception'])) . '</td></tr>
          <tr><td>Type d\'affaire</td><td>' . ucfirst(htmlspecialchars($pv['type_affaire'])) . '</td></tr>
          <tr><td>Unité d\'enquête</td><td>' . htmlspecialchars($pv['unite_nom'] ?? '—') . ' (' . htmlspecialchars($pv['unite_type'] ?? '') . ')</td></tr>
          <tr><td>Substitut assigné</td><td>' . htmlspecialchars(($pv['substitut_prenom']??'') . ' ' . ($pv['substitut_nom']??'—')) . '</td></tr>
          <tr><td>Statut</td><td>' . htmlspecialchars($pv['statut']) . '</td></tr>
        </table>';

        if ($pv['est_antiterroriste']) {
            $html .= '<div class="section">Informations antiterroristes <span class="anti-badge">CONFIDENTIEL</span></div>';
            $html .= '<table class="info">
              <tr><td>Région</td><td>' . htmlspecialchars($pv['region_nom']??'—') . '</td></tr>
              <tr><td>Département</td><td>' . htmlspecialchars($pv['dept_nom']??'—') . '</td></tr>
              <tr><td>Commune</td><td>' . htmlspecialchars($pv['commune_nom']??'—') . '</td></tr>
              <tr><td>Primo intervenants</td><td>' . implode(', ', array_map('htmlspecialchars', $pv['primo_intervenants']??[])) . '</td></tr>
            </table>';
        }

        if ($pv['description_faits']) {
            $html .= '<div class="section">Description des faits</div>';
            $html .= '<div class="dispositif">' . nl2br(htmlspecialchars($pv['description_faits'])) . '</div>';
        }

        if ($pv['statut'] === 'classe' && $pv['motif_classement']) {
            $html .= '<div class="section">Motif de classement sans suite</div>';
            $html .= '<div class="dispositif">' . nl2br(htmlspecialchars($pv['motif_classement'])) . '</div>';
        }

        $html .= '<div class="signature-block">
          <div class="sig">Le Greffier réceptionnaire<br><br><br>' . htmlspecialchars(($pv['created_prenom']??'').' '.($pv['created_nom']??'')) . '</div>
          <div class="sig">Le Substitut du Procureur<br><br><br>' . htmlspecialchars(($pv['substitut_prenom']??'').' '.($pv['substitut_nom']??'')) . '</div>
        </div>';
        $html .= '<div class="footer-doc"><span>Document généré le ' . date('d/m/Y à H:i') . '</span><span>TGI-NY — Niamey, Niger</span></div>';
        $html .= $this->printFooter();
        return $html;
    }

    private function renderDossierHTML(array $d, array $parties, array $audiences, array $jugements, array $mouvements): string {
        $html = $this->printHeader();
        $html .= $this->printTribunalHeader();
        $html .= '<div class="doc-title">FICHE DE DOSSIER JUDICIAIRE</div>';
        $html .= '<div class="numero-doc">' . htmlspecialchars($d['numero_rg']) . ($d['numero_rp'] ? ' / ' . htmlspecialchars($d['numero_rp']) : '') . ($d['numero_ri'] ? ' / ' . htmlspecialchars($d['numero_ri']) : '') . '</div>';

        $html .= '<div class="section">Identification</div>';
        $html .= '<table class="info">
          <tr><td>N° RG</td><td><strong>' . htmlspecialchars($d['numero_rg']) . '</strong></td></tr>
          ' . ($d['numero_rp'] ? '<tr><td>N° RP (Parquet)</td><td>' . htmlspecialchars($d['numero_rp']) . '</td></tr>' : '') . '
          ' . ($d['numero_ri'] ? '<tr><td>N° RI (Instruction)</td><td>' . htmlspecialchars($d['numero_ri']) . '</td></tr>' : '') . '
          <tr><td>Date enregistrement</td><td>' . date('d/m/Y', strtotime($d['date_enregistrement'])) . '</td></tr>
          <tr><td>Type d\'affaire</td><td>' . ucfirst(htmlspecialchars($d['type_affaire'])) . '</td></tr>
          <tr><td>Statut actuel</td><td><strong>' . htmlspecialchars($d['statut']) . '</strong></td></tr>
          <tr><td>Objet</td><td>' . htmlspecialchars($d['objet']) . '</td></tr>
          <tr><td>Substitut</td><td>' . htmlspecialchars(($d['substitut_prenom']??'').' '.($d['substitut_nom']??'—')) . '</td></tr>
          <tr><td>Cabinet</td><td>' . htmlspecialchars($d['cabinet_num'] ? $d['cabinet_num'].' — '.$d['cabinet_lib'] : '—') . '</td></tr>
          ' . ($d['date_instruction_debut'] ? '<tr><td>Début instruction</td><td>' . date('d/m/Y', strtotime($d['date_instruction_debut'])) . '</td></tr>' : '') . '
        </table>';

        if (!empty($parties)) {
            $html .= '<div class="section">Parties en cause (' . count($parties) . ')</div><table class="info">';
            foreach ($parties as $p) {
                $html .= '<tr><td>' . htmlspecialchars(str_replace('_',' ',$p['type_partie'])) . '</td><td><strong>' . htmlspecialchars($p['nom'].' '.($p['prenom']??'')) . '</strong>' . ($p['nationalite']?' — '.htmlspecialchars($p['nationalite']):'') . ($p['profession']?' — '.htmlspecialchars($p['profession']):'') . '</td></tr>';
            }
            $html .= '</table>';
        }

        if (!empty($audiences)) {
            $html .= '<div class="section">Audiences (' . count($audiences) . ')</div><table class="info"><tr><td>Date</td><td>Type</td><td>Salle</td><td>Statut</td></tr>';
            foreach ($audiences as $a) {
                $html .= '<tr><td>' . date('d/m/Y H:i', strtotime($a['date_audience'])) . '</td><td>' . htmlspecialchars($a['type_audience']) . '</td><td>' . htmlspecialchars($a['salle_nom']??'—') . '</td><td>' . htmlspecialchars($a['statut']) . '</td></tr>';
            }
            $html .= '</table>';
        }

        if (!empty($jugements)) {
            $html .= '<div class="section">Jugements (' . count($jugements) . ')</div>';
            foreach ($jugements as $jug) {
                $html .= '<table class="info">
                  <tr><td>N° Jugement</td><td><strong>' . htmlspecialchars($jug['numero_jugement']) . '</strong></td></tr>
                  <tr><td>Date</td><td>' . date('d/m/Y', strtotime($jug['date_jugement'])) . '</td></tr>
                  <tr><td>Type</td><td>' . htmlspecialchars($jug['type_jugement']) . '</td></tr>
                  <tr><td>Dispositif</td><td>' . nl2br(htmlspecialchars($jug['dispositif'])) . '</td></tr>
                  ' . ($jug['peine_principale'] ? '<tr><td>Peine</td><td>' . htmlspecialchars($jug['peine_principale']) . '</td></tr>' : '') . '
                </table><br>';
            }
        }

        if (!empty($mouvements)) {
            $html .= '<div class="section">Historique des mouvements</div><table class="info">';
            foreach ($mouvements as $m) {
                $html .= '<tr><td>' . date('d/m/Y H:i', strtotime($m['created_at'])) . '</td><td><strong>' . htmlspecialchars($m['type_mouvement']) . '</strong>' . ($m['nouveau_statut']?' → '.htmlspecialchars($m['nouveau_statut']):'') . ($m['description']?'<br><small>'.htmlspecialchars($m['description']).'</small>':'') . '</td></tr>';
            }
            $html .= '</table>';
        }

        $html .= '<div class="signature-block">
          <div class="sig">Le Greffier en chef<br><br><br>&nbsp;</div>
          <div class="sig">Le Procureur<br><br><br>&nbsp;</div>
        </div>';
        $html .= '<div class="footer-doc"><span>Fiche générée le ' . date('d/m/Y à H:i') . '</span><span>TGI-NY — Niamey, Niger</span></div>';
        $html .= $this->printFooter();
        return $html;
    }
}
