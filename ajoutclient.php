<?php
session_start();
require_once 'config/connexion.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php?redirect=1');
    exit;
}

$erreurs = [];
$succes  = '';
$nom = $prenom = $age = $adresse = $ville = $mail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom']     ?? '');
    $prenom  = trim($_POST['prenom']  ?? '');
    $age     = trim($_POST['age']     ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $ville   = trim($_POST['ville']   ?? '');
    $mail    = trim($_POST['mail']    ?? '');

    if (empty($nom))    $erreurs[] = "Le nom est obligatoire.";
    if (empty($prenom)) $erreurs[] = "Le prénom est obligatoire.";
    if ($age !== '' && (!is_numeric($age) || $age < 0 || $age > 120))
        $erreurs[] = "L'âge doit être un nombre valide.";
    if ($mail !== '' && !filter_var($mail, FILTER_VALIDATE_EMAIL))
        $erreurs[] = "L'adresse email n'est pas valide.";

    if (empty($erreurs)) {
        $pdo  = getConnexion();
        $stmt = $pdo->prepare(
            "INSERT INTO client (nom, prenom, age, adresse, ville, mail)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        try {
            $stmt->execute([
                $nom, $prenom,
                $age !== '' ? (int)$age : null,
                $adresse ?: null,
                $ville   ?: null,
                $mail    ?: null
            ]);
            $succes = "Client ajouté avec succès.";
            $nom = $prenom = $age = $adresse = $ville = $mail = '';
        } catch (PDOException $e) {
            $erreurs[] = "Erreur lors de l'ajout du client.";
        }
    }
}

$title = 'Ajouter un client';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="page-title mb-0">
                <i class="bi bi-person-plus me-1"></i> Ajouter un client
            </div>
            <a href="listeclient.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Retour
            </a>
        </div>

        <?php if ($succes): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($succes) ?>
            <a href="listeclient.php" class="alert-link ms-2">→ Voir les clients</a>
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
                <i class="bi bi-person me-1"></i> Informations du client
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="nom">Nom *</label>
                            <input type="text" class="form-control" id="nom" name="nom"
                                   value="<?= htmlspecialchars($nom) ?>"
                                   placeholder="Nom de famille" maxlength="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="prenom">Prénom *</label>
                            <input type="text" class="form-control" id="prenom" name="prenom"
                                   value="<?= htmlspecialchars($prenom) ?>"
                                   placeholder="Prénom" maxlength="100" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="age">Âge</label>
                            <input type="number" class="form-control" id="age" name="age"
                                   value="<?= htmlspecialchars($age) ?>"
                                   placeholder="ex : 30" min="0" max="120">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="ville">Ville</label>
                            <input type="text" class="form-control" id="ville" name="ville"
                                   value="<?= htmlspecialchars($ville) ?>"
                                   placeholder="ex : Cotonou" maxlength="100">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="adresse">Adresse</label>
                            <input type="text" class="form-control" id="adresse" name="adresse"
                                   value="<?= htmlspecialchars($adresse) ?>"
                                   placeholder="ex : Rue 123, Quartier..." maxlength="255">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="mail">Email</label>
                            <input type="email" class="form-control" id="mail" name="mail"
                                   value="<?= htmlspecialchars($mail) ?>"
                                   placeholder="ex : client@email.com" maxlength="150">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Enregistrer
                        </button>
                        <a href="listeclient.php" class="btn btn-outline-secondary">Annuler</a>
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
