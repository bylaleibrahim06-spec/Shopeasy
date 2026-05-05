<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn  = isset($_SESSION['user_id']);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'ShopEasy') ?> - ShopEasy</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: Arial, sans-serif;
            color: #333;
        }

        /* NAVBAR */
        .navbar {
            background-color: #1a3c6e;
            padding: 8px 0;
        }
        .navbar-brand {
            font-size: 1.3rem;
            font-weight: bold;
            color: #fff !important;
            letter-spacing: 0.5px;
        }
        .navbar-brand span { color: #f0c040; }

        .nav-link {
            color: #cdd8e8 !important;
            font-size: 0.9rem;
            padding: 6px 12px !important;
            border-radius: 4px;
        }
        .nav-link:hover, .nav-link.active {
            background-color: #2a5298;
            color: #fff !important;
        }

        .logo-navbar {
            height: 45px;
            width: 45px;
            object-fit: contain;
            background: #fff;
            border-radius: 50%;
            padding: 2px;
        }

        /* CONTENU */
        .main-wrap {
            padding: 30px 0 50px;
        }

        .page-title {
            font-size: 1.4rem;
            font-weight: bold;
            color: #1a3c6e;
            border-left: 4px solid #1a3c6e;
            padding-left: 10px;
            margin-bottom: 20px;
        }

        /* CARDS */
        .card {
            border: 1px solid #dde3ec;
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        }
        .card-header {
            background-color: #1a3c6e;
            color: #fff;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 5px 5px 0 0 !important;
            padding: 10px 16px;
        }

        /* TABLES */
        .table thead th {
            background-color: #2a5298;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
        }
        .table td {
            font-size: 0.88rem;
            vertical-align: middle;
            border-color: #e8ecf1;
        }
        .table tbody tr:hover {
            background-color: #f0f4fb;
        }

        /* FORMS */
        .form-control, .form-select {
            border: 1px solid #c8d0dc;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #2a5298;
            box-shadow: 0 0 0 2px rgba(42,82,152,0.15);
        }
        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #444;
            margin-bottom: 4px;
        }

        /* BOUTONS */
        .btn {
            font-size: 0.875rem;
            border-radius: 4px;
            padding: 6px 14px;
        }
        .btn-primary {
            background-color: #1a3c6e;
            border-color: #1a3c6e;
        }
        .btn-primary:hover {
            background-color: #2a5298;
            border-color: #2a5298;
        }

        /* ALERTS */
        .alert { font-size: 0.875rem; border-radius: 5px; }

        /* STATS */
        .stat-box {
            background: #fff;
            border: 1px solid #dde3ec;
            border-radius: 6px;
            padding: 16px;
            text-align: center;
        }
        .stat-box .number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #1a3c6e;
        }
        .stat-box .label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .stat-box .icon {
            font-size: 1.5rem;
            color: #2a5298;
            margin-bottom: 5px;
        }

        /* FOOTER */
        .site-footer {
            background-color: #1a3c6e;
            color: #aabbd4;
            text-align: center;
            padding: 12px 0;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <img src="logo_uac.jpg" alt="UAC" class="logo-navbar me-2">

        <a class="navbar-brand me-3" href="accueil.php">
            Shop<span>Easy</span>
        </a>

        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav">
            <i class="bi bi-list text-white fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage==='accueil.php' ? 'active' : '' ?>"
                       href="accueil.php">
                        <i class="bi bi-house me-1"></i>Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage==='voirarticles.php' ? 'active' : '' ?>"
                       href="voirarticles.php">
                        <i class="bi bi-box-seam me-1"></i>Articles
                    </a>
                </li>
                <?php if ($isLoggedIn): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage==='ajoutarticle.php' ? 'active' : '' ?>"
                       href="ajoutarticle.php">
                        <i class="bi bi-plus-square me-1"></i>Ajouter article
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage==='listeclient.php' ? 'active' : '' ?>"
                       href="listeclient.php">
                        <i class="bi bi-people me-1"></i>Clients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage==='ajoutclient.php' ? 'active' : '' ?>"
                       href="ajoutclient.php">
                        <i class="bi bi-person-plus me-1"></i>Ajouter client
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage==='formulairevente.php' ? 'active' : '' ?>"
                       href="formulairevente.php">
                        <i class="bi bi-cart-plus me-1"></i>Nouvelle vente
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage==='listeventes.php' ? 'active' : '' ?>"
                       href="listeventes.php">
                        <i class="bi bi-receipt me-1"></i>Ventes
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav align-items-center gap-1">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <span class="nav-link" style="color:#7fc97f !important; font-size:.85rem;">
                            <i class="bi bi-person-check me-1"></i>
                            <?= htmlspecialchars($_SESSION['username']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="deconnexion.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage==='connexion.php' ? 'active' : '' ?>"
                           href="connexion.php">
                            <i class="bi bi-lock me-1"></i>Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm ms-1 fw-bold" href="inscription.php">
                            <i class="bi bi-person-plus me-1"></i>Inscription
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <img src="logo_eneam.jpg" alt="ENEAM" class="logo-navbar ms-3">
        </div>
    </div>
</nav>

<!-- CONTENU -->
<div class="main-wrap">
    <div class="container">
        <?= $content ?? '' ?>
    </div>
</div>

<!-- FOOTER -->
<footer class="site-footer">
    ShopEasy &mdash; Plateforme de gestion commerciale
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
