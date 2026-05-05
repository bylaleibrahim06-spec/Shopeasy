<?php
session_start();
require_once 'config/connexion.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php?redirect=1');
    exit;
}

$erreurs = [];
$succes  = '';
$design = $prix = $categorie = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $design    = trim($_POST['design']    ?? '');
    $prix      = trim($_POST['prix']      ?? '');
    $categorie = trim($_POST['categorie'] ?? '');

    if (empty($design))
        $erreurs[] = "La désignation est obligatoire.";

    if (empty($prix) || !is_numeric($prix) || $prix <= 0)
        $erreurs[] = "Le prix doit être un nombre positif.";

    if (empty($erreurs)) {
        $pdo  = getConnexion();
        $stmt = $pdo->prepare(
            "INSERT INTO article (design, prix, categorie) VALUES (?, ?, ?)"
        );
        try {
            $stmt->execute([$design, $prix, $categorie ?: null]);
            $succes    = "Article ajouté avec succès.";
            $design = $prix = $categorie = '';
        } catch (PDOException $e) {
            $erreurs[] = "Erreur lors de l'ajout.";
        }
    }
}

$title = 'Ajouter un article';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="page-title mb-0">
                <i class="bi bi-plus-square me-1"></i> Ajouter un article
            </div>
            <a href="voirarticles.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Retour
            </a>
        </div>

        <?php if ($succes): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($succes) ?>
            <a href="voirarticles.php" class="alert-link ms-2">→ Voir les articles</a>
        </div>
        <?php endif; ?>

        <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-1"></i> <strong>Erreur(s) :</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($erreurs as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-box-seam me-1"></i> Informations de l'article
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label" for="design">Désignation *</label>
                        <input type="text" class="form-control" id="design" name="design"
                               value="<?= htmlspecialchars($design) ?>"
                               placeholder="Nom de l'article" maxlength="150" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="prix">Prix unitaire (FCFA) *</label>
                        <input type="number" class="form-control" id="prix" name="prix"
                               value="<?= htmlspecialchars($prix) ?>"
                               placeholder="ex : 5000" step="0.01" min="0" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="categorie">Catégorie</label>
                        <input type="text" class="form-control" id="categorie" name="categorie"
                               value="<?= htmlspecialchars($categorie) ?>"
                               placeholder="ex : Informatique, Vêtement..." maxlength="100">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Enregistrer
                        </button>
                        <a href="voirarticles.php" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();
require '_base.php';
?>
