<?php
session_start();
require_once 'config/connexion.php';

if (isset($_SESSION['user_id'])) {
    header('Location: accueil.php');
    exit;
}

$erreurs = [];
$succes  = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (empty($username))
        $erreurs[] = "Le nom d'utilisateur est obligatoire.";
    elseif (strlen($username) < 3)
        $erreurs[] = "Minimum 3 caractères pour le nom d'utilisateur.";

    if (empty($password))
        $erreurs[] = "Le mot de passe est obligatoire.";
    elseif (strlen($password) < 6)
        $erreurs[] = "Le mot de passe doit contenir au moins 6 caractères.";

    if ($password !== $password2)
        $erreurs[] = "Les deux mots de passe ne correspondent pas.";

    if (empty($erreurs)) {
        $pdo   = getConnexion();
        $check = $pdo->prepare("SELECT id_user FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch())
            $erreurs[] = "Ce nom d'utilisateur est déjà utilisé.";
    }

    if (empty($erreurs)) {
        $pdo  = getConnexion();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        try {
            $stmt->execute([$username, $hash]);
            $succes   = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
            $username = '';
        } catch (PDOException $e) {
            $erreurs[] = "Erreur lors de la création du compte.";
        }
    }
}

$title = 'Inscription';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-5">

        <div class="page-title">
            <i class="bi bi-person-plus me-1"></i> Créer un compte
        </div>

        <?php if ($succes): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($succes) ?>
            <a href="connexion.php" class="alert-link ms-2">→ Se connecter</a>
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
                <i class="bi bi-person-plus me-1"></i> Formulaire d'inscription
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label" for="username">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?= htmlspecialchars($username) ?>"
                               placeholder="ex : johndoe" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Minimum 6 caractères" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="password2">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="password2" name="password2"
                               placeholder="Répétez le mot de passe" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-person-check me-1"></i> Créer le compte
                    </button>
                </form>
                <hr>
                <div class="text-center" style="font-size:0.875rem;">
                    Déjà inscrit ? <a href="connexion.php">Se connecter</a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();
require '_base.php';
?>
