<?php
session_start();
require_once 'config/connexion.php';

$pdo = getConnexion();

$nbArticles  = $pdo->query("SELECT COUNT(*) FROM article")->fetchColumn();
$nbClients   = $pdo->query("SELECT COUNT(*) FROM client")->fetchColumn();
$nbCommandes = $pdo->query("SELECT COUNT(*) FROM commande")->fetchColumn();
$caTotal     = $pdo->query("SELECT COALESCE(SUM(quantite * prix_unit), 0) FROM ligne")->fetchColumn();

$dernierArticles = $pdo->query("SELECT * FROM article ORDER BY id_article DESC LIMIT 6")->fetchAll();

$title = 'Accueil';
ob_start();
?>

<!-- Bienvenue -->
<div class="bg-white border rounded p-4 mb-4" style="border-color:#dde3ec !important;">
    <h4 style="color:#1a3c6e; font-weight:bold;">
        <i class="bi bi-shop me-2"></i>Bienvenue sur ShopEasy
    </h4>
    <p class="text-muted mb-0" style="font-size:0.9rem;">
        Plateforme de gestion commerciale — articles, clients et ventes.
    </p>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-box">
            <div class="icon"><i class="bi bi-box-seam"></i></div>
            <div class="number"><?= $nbArticles ?></div>
            <div class="label">Articles</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-box">
            <div class="icon"><i class="bi bi-people"></i></div>
            <div class="number"><?= $nbClients ?></div>
            <div class="label">Clients</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-box">
            <div class="icon"><i class="bi bi-receipt"></i></div>
            <div class="number"><?= $nbCommandes ?></div>
            <div class="label">Commandes</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-box">
            <div class="icon"><i class="bi bi-cash-stack"></i></div>
            <div class="number" style="font-size:1.2rem;"><?= number_format($caTotal,0,',',' ') ?></div>
            <div class="label">CA Total (FCFA)</div>
        </div>
    </div>
</div>

<!-- Accès rapides si connecté -->
<?php if (isset($_SESSION['user_id'])): ?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="page-title">Accès rapides</div>
    </div>
    <div class="col-md-3 col-6">
        <a href="ajoutarticle.php" class="text-decoration-none">
            <div class="bg-white border rounded p-3 text-center" style="border-color:#dde3ec !important;">
                <i class="bi bi-plus-square fs-3" style="color:#1a3c6e;"></i>
                <div style="font-size:0.85rem; font-weight:600; margin-top:6px; color:#333;">Ajouter article</div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="ajoutclient.php" class="text-decoration-none">
            <div class="bg-white border rounded p-3 text-center" style="border-color:#dde3ec !important;">
                <i class="bi bi-person-plus fs-3" style="color:#1a3c6e;"></i>
                <div style="font-size:0.85rem; font-weight:600; margin-top:6px; color:#333;">Ajouter client</div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="formulairevente.php" class="text-decoration-none">
            <div class="bg-white border rounded p-3 text-center" style="border-color:#dde3ec !important;">
                <i class="bi bi-cart-plus fs-3" style="color:#1a3c6e;"></i>
                <div style="font-size:0.85rem; font-weight:600; margin-top:6px; color:#333;">Nouvelle vente</div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="listeventes.php" class="text-decoration-none">
            <div class="bg-white border rounded p-3 text-center" style="border-color:#dde3ec !important;">
                <i class="bi bi-list-ul fs-3" style="color:#1a3c6e;"></i>
                <div style="font-size:0.85rem; font-weight:600; margin-top:6px; color:#333;">Liste des ventes</div>
            </div>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Derniers articles -->
<?php if (!empty($dernierArticles)): ?>
<div class="page-title">Derniers articles</div>
<div class="card">
    <div class="card-header"><i class="bi bi-box-seam me-1"></i> Articles récents</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Désignation</th>
                    <th>Catégorie</th>
                    <th>Prix unitaire</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dernierArticles as $a): ?>
                <tr>
                    <td><?= $a['id_article'] ?></td>
                    <td><?= htmlspecialchars($a['design']) ?></td>
                    <td><?= $a['categorie'] ? htmlspecialchars($a['categorie']) : '<span class="text-muted">—</span>' ?></td>
                    <td><strong><?= number_format($a['prix'],2,',',' ') ?> FCFA</strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">
    <a href="voirarticles.php" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-arrow-right me-1"></i> Voir tous les articles
    </a>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require '_base.php';
?>
