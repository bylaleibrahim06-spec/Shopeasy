<?php
session_start();
require_once 'config/connexion.php';

if (isset($_SESSION['user_id'])) {
    header('Location: accueil.php');
    exit;
}

$erreur   = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        $pdo  = getConnexion();
        $stmt = $pdo->prepare("SELECT id_user, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            header('Location: accueil.php');
            exit;
        } else {
            $erreur   = "Nom d'utilisateur ou mot de passe incorrect.";
            $username = '';
        }
    }
}

$title = 'Connexion';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-5">

        <div class="page-title">
            <i class="bi bi-lock me-1"></i> Connexion
        </div>

        <?php if ($erreur): ?>
        <div class="alert alert-danger">
            <i class="bi bi-x-circle me-1"></i> <?= htmlspecialchars($erreur) ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['redirect'])): ?>
        <div class="alert alert-warning">
            <i class="bi bi-shield-lock me-1"></i> Vous devez être connecté pour accéder à cette page.
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-box-arrow-in-right me-1"></i> Accès à votre espace
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label" for="username">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?= htmlspecialchars($username) ?>"
                               placeholder="Votre identifiant" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="password">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Votre mot de passe" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
                    </button>
                </form>
                <hr>
                <div class="text-center" style="font-size:0.875rem;">
                    Pas encore de compte ? <a href="inscription.php">S'inscrire</a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();
require '_base.php';
?>
