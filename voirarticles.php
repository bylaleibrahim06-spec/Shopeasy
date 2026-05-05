<?php
session_start();
require_once 'config/connexion.php';

$pdo = getConnexion();

$recherche = trim($_GET['q'] ?? '');
$params    = [];
$sql       = "SELECT * FROM article";

if ($recherche !== '') {
    $sql     .= " WHERE design LIKE ?";
    $params[] = "%$recherche%";
}
$sql .= " ORDER BY id_article DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$title = 'Articles';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="page-title mb-0">
        <i class="bi bi-box-seam me-1"></i> Liste des articles
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="ajoutarticle.php" class="btn btn-success btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Ajouter un article
    </a>
    <?php endif; ?>
</div>

<!-- Recherche -->
<form method="GET" action="" class="mb-3 d-flex gap-2">
    <input type="text" class="form-control" name="q"
           value="<?= htmlspecialchars($recherche) ?>"
           placeholder="Rechercher un article..." style="max-width:300px;">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-search"></i>
    </button>
    <?php if ($recherche): ?>
    <a href="voirarticles.php" class="btn btn-outline-secondary">
        <i class="bi bi-x"></i>
    </a>
    <?php endif; ?>
</form>

<?php if (empty($articles)): ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle me-1"></i>
    Aucun article trouvé.
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="ajoutarticle.php" class="alert-link">Ajouter le premier article.</a>
    <?php endif; ?>
</div>
<?php else: ?>

<div class="card">
    <div class="card-header">
        <i class="bi bi-table me-1"></i>
        <?= count($articles) ?> article(s) trouvé(s)
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Désignation</th>
                    <th>Catégorie</th>
                    <th>Prix unitaire</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $a): ?>
                <tr>
                    <td><?= $a['id_article'] ?></td>
                    <td><strong><?= htmlspecialchars($a['design']) ?></strong></td>
                    <td>
                        <?= $a['categorie']
                            ? '<span class="badge bg-secondary">' . htmlspecialchars($a['categorie']) . '</span>'
                            : '<span class="text-muted">—</span>' ?>
                    </td>
                    <td><strong><?= number_format($a['prix'],2,',',' ') ?> FCFA</strong></td>
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
