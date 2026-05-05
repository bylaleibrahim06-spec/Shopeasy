<?php
session_start();
require_once 'config/connexion.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php?redirect=1');
    exit;
}

$pdo = getConnexion();

$ventes = $pdo->query("
    SELECT
        cmd.id_comm,
        cmd.date,
        cl.nom, cl.prenom, cl.ville,
        art.design      AS article,
        art.categorie,
        l.quantite,
        l.prix_unit,
        (l.quantite * l.prix_unit) AS sous_total
    FROM commande cmd
    INNER JOIN client  cl  ON cmd.id_client = cl.id_client
    INNER JOIN ligne   l   ON cmd.id_comm   = l.id_comm
    INNER JOIN article art ON l.id_article  = art.id_article
    ORDER BY cmd.date DESC, cmd.id_comm DESC, art.design ASC
")->fetchAll();

// Regrouper par commande
$commandes = [];
foreach ($ventes as $v) {
    $id = $v['id_comm'];
    if (!isset($commandes[$id])) {
        $commandes[$id] = [
            'id'     => $id,
            'date'   => $v['date'],
            'client' => $v['prenom'] . ' ' . $v['nom'],
            'ville'  => $v['ville'],
            'lignes' => [],
            'total'  => 0,
        ];
    }
    $commandes[$id]['lignes'][] = $v;
    $commandes[$id]['total']   += $v['sous_total'];
}

$totalGeneral = array_sum(array_column($commandes, 'total'));

$title = 'Liste des ventes';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="page-title mb-0">
        <i class="bi bi-receipt me-1"></i> Liste des ventes
    </div>
    <a href="formulairevente.php" class="btn btn-success btn-sm">
        <i class="bi bi-cart-plus me-1"></i> Nouvelle vente
    </a>
</div>

<?php if (empty($commandes)): ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle me-1"></i>
    Aucune vente enregistrée.
    <a href="formulairevente.php" class="alert-link">Créer la première vente.</a>
</div>
<?php else: ?>

<!-- Résumé -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-box">
            <div class="icon"><i class="bi bi-receipt"></i></div>
            <div class="number"><?= count($commandes) ?></div>
            <div class="label">Commandes</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-box">
            <div class="icon"><i class="bi bi-list-ul"></i></div>
            <div class="number"><?= count($ventes) ?></div>
            <div class="label">Lignes de vente</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-box">
            <div class="icon"><i class="bi bi-cash-stack"></i></div>
            <div class="number" style="font-size:1.1rem;">
                <?= number_format($totalGeneral,0,',',' ') ?>
            </div>
            <div class="label">CA Total (FCFA)</div>
        </div>
    </div>
</div>

<!-- Tableau détaillé -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table me-1"></i> Détail des ventes
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>N° Commande</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Article</th>
                    <th>Quantité</th>
                    <th>Prix unitaire</th>
                    <th>Sous-total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $cmd): ?>
                    <?php foreach ($cmd['lignes'] as $i => $lg): ?>
                    <tr <?= $i === 0 ? 'style="border-top:2px solid #2a5298;"' : '' ?>>
                        <td>
                            <?php if ($i === 0): ?>
                            <span class="badge bg-primary">#<?= $cmd['id'] ?></span>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:0.75rem;">↳</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.85rem;">
                            <?= $i === 0 ? date('d/m/Y', strtotime($cmd['date'])) : '' ?>
                        </td>
                        <td>
                            <?php if ($i === 0): ?>
                            <strong><?= htmlspecialchars($cmd['client']) ?></strong>
                            <?php if ($cmd['ville']): ?>
                            <div class="text-muted" style="font-size:0.75rem;">
                                <?= htmlspecialchars($cmd['ville']) ?>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($lg['article']) ?></td>
                        <td><?= (int)$lg['quantite'] ?></td>
                        <td><?= number_format($lg['prix_unit'],2,',',' ') ?> FCFA</td>
                        <td><strong><?= number_format($lg['sous_total'],2,',',' ') ?> FCFA</strong></td>
                    </tr>
                    <?php endforeach; ?>

                    <!-- Total commande -->
                    <tr class="table-light">
                        <td colspan="6" class="text-end" style="font-size:0.8rem; color:#555; font-weight:600;">
                            Total commande #<?= $cmd['id'] ?>
                        </td>
                        <td>
                            <strong style="color:#1a3c6e;">
                                <?= number_format($cmd['total'],2,',',' ') ?> FCFA
                            </strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#1a3c6e; color:#fff;">
                    <td colspan="6" class="text-end fw-bold">TOTAL GÉNÉRAL</td>
                    <td class="fw-bold"><?= number_format($totalGeneral,2,',',' ') ?> FCFA</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php endif; ?>

<?php
$content = ob_get_clean();
require '_base.php';
?>
