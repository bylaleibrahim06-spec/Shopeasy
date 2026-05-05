<?php
session_start();
require_once 'config/connexion.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php?redirect=1');
    exit;
}

$pdo = getConnexion();

$clients = $pdo->query("
    SELECT
        c.id_client, c.nom, c.prenom, c.age, c.adresse, c.ville, c.mail,
        COUNT(DISTINCT cmd.id_comm)                AS nb_commandes,
        COALESCE(SUM(l.quantite * l.prix_unit), 0) AS total_achats
    FROM client c
    LEFT JOIN commande cmd ON c.id_client = cmd.id_client
    LEFT JOIN ligne    l   ON cmd.id_comm = l.id_comm
    GROUP BY c.id_client
    ORDER BY c.nom, c.prenom
")->fetchAll();

$title = 'Liste des clients';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="page-title mb-0">
        <i class="bi bi-people me-1"></i> Liste des clients
    </div>
    <a href="ajoutclient.php" class="btn btn-success btn-sm">
        <i class="bi bi-person-plus me-1"></i> Ajouter un client
    </a>
</div>

<?php if (empty($clients)): ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle me-1"></i>
    Aucun client enregistré.
    <a href="ajoutclient.php" class="alert-link">Ajouter le premier client.</a>
</div>
<?php else: ?>

<div class="card">
    <div class="card-header">
        <i class="bi bi-table me-1"></i> <?= count($clients) ?> client(s)
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Nom complet</th>
                    <th>Âge</th>
                    <th>Ville</th>
                    <th>Email</th>
                    <th>Commandes</th>
                    <th>Total achats</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $c): ?>
                <tr>
                    <td><?= $c['id_client'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></strong>
                        <?php if ($c['adresse']): ?>
                        <div class="text-muted" style="font-size:0.78rem;">
                            <?= htmlspecialchars($c['adresse']) ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td><?= $c['age'] ? $c['age'] . ' ans' : '<span class="text-muted">—</span>' ?></td>
                    <td><?= $c['ville'] ? htmlspecialchars($c['ville']) : '<span class="text-muted">—</span>' ?></td>
                    <td>
                        <?php if ($c['mail']): ?>
                        <a href="mailto:<?= htmlspecialchars($c['mail']) ?>" style="font-size:0.85rem;">
                            <?= htmlspecialchars($c['mail']) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-primary"><?= $c['nb_commandes'] ?></span>
                    </td>
                    <td>
                        <strong><?= number_format($c['total_achats'],2,',',' ') ?> FCFA</strong>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?php
$content = ob_get_clean();
require '_base.php';
?>
