<?php
session_start();
require_once 'config/connexion.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php?redirect=1');
    exit;
}

$pdo      = getConnexion();
$clients  = $pdo->query("SELECT id_client, nom, prenom FROM client ORDER BY nom, prenom")->fetchAll();
$articles = $pdo->query("SELECT id_article, design, prix FROM article ORDER BY design")->fetchAll();

$erreurs = [];
$succes  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_client = (int)($_POST['id_client'] ?? 0);
    $date      = trim($_POST['date'] ?? '');
    $lignes    = $_POST['lignes'] ?? [];

    if ($id_client <= 0)
        $erreurs[] = "Veuillez sélectionner un client.";
    if (empty($date))
        $erreurs[] = "La date est obligatoire.";

    $lignesValides = [];
    foreach ($lignes as $lg) {
        $id_art = (int)($lg['id_article'] ?? 0);
        $qte    = (int)($lg['quantite']   ?? 0);
        if ($id_art > 0 && $qte > 0)
            $lignesValides[] = ['id_article' => $id_art, 'quantite' => $qte];
    }

    if (empty($lignesValides))
        $erreurs[] = "Ajoutez au moins un article avec une quantité valide.";

    if (empty($erreurs)) {
        try {
            $pdo->beginTransaction();

            $stmtCmd = $pdo->prepare("INSERT INTO commande (id_client, date) VALUES (?, ?)");
            $stmtCmd->execute([$id_client, $date]);
            $id_comm = $pdo->lastInsertId();

            $stmtPrix  = $pdo->prepare("SELECT prix FROM article WHERE id_article = ?");
            $stmtLigne = $pdo->prepare(
                "INSERT INTO ligne (id_comm, id_article, quantite, prix_unit) VALUES (?, ?, ?, ?)"
            );

            foreach ($lignesValides as $lg) {
                $stmtPrix->execute([$lg['id_article']]);
                $prix = $stmtPrix->fetchColumn();
                $stmtLigne->execute([$id_comm, $lg['id_article'], $lg['quantite'], $prix]);
            }

            $pdo->commit();
            $succes = "Commande #$id_comm enregistrée avec succès (" . count($lignesValides) . " article(s)).";

        } catch (Exception $e) {
            $pdo->rollBack();
            $erreurs[] = "Erreur : " . $e->getMessage();
        }
    }
}

$articlesJson = json_encode($articles);
$title = 'Nouvelle vente';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="page-title mb-0">
        <i class="bi bi-cart-plus me-1"></i> Nouvelle vente
    </div>
    <a href="listeventes.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-receipt me-1"></i> Voir les ventes
    </a>
</div>

<?php if ($succes): ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($succes) ?>
    <a href="listeventes.php" class="alert-link ms-2">→ Voir toutes les ventes</a>
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

<?php if (empty($clients)): ?>
<div class="alert alert-warning">
    <i class="bi bi-info-circle me-1"></i>
    Aucun client disponible. <a href="ajoutclient.php" class="alert-link">Ajouter un client d'abord.</a>
</div>
<?php elseif (empty($articles)): ?>
<div class="alert alert-warning">
    <i class="bi bi-info-circle me-1"></i>
    Aucun article disponible. <a href="ajoutarticle.php" class="alert-link">Ajouter un article d'abord.</a>
</div>
<?php else: ?>

<form method="POST" action="" id="formVente">
    <div class="row g-4">

        <!-- Infos commande -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-info-circle me-1"></i> Informations
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label" for="id_client">Client *</label>
                        <select class="form-select" id="id_client" name="id_client" required>
                            <option value="">— Choisir un client —</option>
                            <?php foreach ($clients as $cl): ?>
                            <option value="<?= $cl['id_client'] ?>"
                                <?= (isset($_POST['id_client']) && $_POST['id_client'] == $cl['id_client']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cl['prenom'] . ' ' . $cl['nom']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="date">Date *</label>
                        <input type="date" class="form-control" id="date" name="date"
                               value="<?= htmlspecialchars($_POST['date'] ?? date('Y-m-d')) ?>" required>
                    </div>

                    <!-- Récap -->
                    <div class="bg-light rounded p-3 border">
                        <div style="font-size:0.8rem; font-weight:600; color:#555; margin-bottom:8px;">
                            RÉCAPITULATIF
                        </div>
                        <div class="d-flex justify-content-between">
                            <span style="font-size:0.85rem;">Articles :</span>
                            <strong id="recap-nb">0</strong>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span style="font-size:0.85rem;">Total :</span>
                            <strong id="recap-total" style="color:#1a3c6e;">0 FCFA</strong>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-check-lg me-1"></i> Valider la commande
                    </button>
                </div>
            </div>
        </div>

        <!-- Lignes articles -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-ul me-1"></i> Articles commandés</span>
                    <button type="button" class="btn btn-success btn-sm" id="btnAjouter">
                        <i class="bi bi-plus-lg me-1"></i> Ajouter un article
                    </button>
                </div>
                <div class="card-body p-3">
                    <div id="lignesContainer"></div>
                    <div id="emptyMsg" class="text-center text-muted py-3" style="font-size:0.875rem;">
                        <i class="bi bi-cart-x fs-4 d-block mb-1"></i>
                        Cliquez sur "Ajouter un article" pour commencer
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
const articles = <?= $articlesJson ?>;
let idx = 0;

function formatPrix(n) {
    return n.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
}

function updateRecap() {
    const rows = document.querySelectorAll('.ligne-row');
    let total = 0, nb = 0;
    rows.forEach(row => {
        const sel = row.querySelector('.sel-article');
        const qte = parseInt(row.querySelector('.inp-qte').value) || 0;
        if (sel.value && qte > 0) {
            const art = articles.find(a => a.id_article == sel.value);
            if (art) { total += parseFloat(art.prix) * qte; nb++; }
        }
    });
    document.getElementById('recap-nb').textContent    = nb;
    document.getElementById('recap-total').textContent = formatPrix(total);
    document.getElementById('emptyMsg').style.display  = rows.length === 0 ? 'block' : 'none';
}

function ajouterLigne() {
    const i   = idx++;
    const div = document.createElement('div');
    div.className = 'ligne-row';
    div.style.cssText = 'display:flex;gap:8px;align-items:center;padding:8px;margin-bottom:6px;background:#f8f9fa;border:1px solid #dde3ec;border-radius:5px;flex-wrap:wrap;';

    let opts = '<option value="">— Article —</option>';
    articles.forEach(a => {
        opts += `<option value="${a.id_article}" data-prix="${a.prix}">
            ${a.design.replace(/</g,'&lt;')} — ${parseFloat(a.prix).toFixed(2).replace('.',',')} FCFA
        </option>`;
    });

    div.innerHTML = `
        <select class="form-select sel-article" name="lignes[${i}][id_article]" style="flex:3;min-width:180px;" required>
            ${opts}
        </select>
        <input type="number" class="form-control inp-qte" name="lignes[${i}][quantite]"
               value="1" min="1" placeholder="Qté" style="flex:1;min-width:70px;max-width:90px;" required>
        <span class="prix-ligne fw-bold" style="min-width:110px;font-size:0.875rem;color:#1a3c6e;">—</span>
        <button type="button" class="btn btn-outline-danger btn-sm btn-suppr">
            <i class="bi bi-trash"></i>
        </button>
    `;

    const sel    = div.querySelector('.sel-article');
    const qteEl  = div.querySelector('.inp-qte');
    const prixEl = div.querySelector('.prix-ligne');

    function majPrix() {
        const art = articles.find(a => a.id_article == sel.value);
        const q   = parseInt(qteEl.value) || 0;
        prixEl.textContent = (art && q > 0) ? formatPrix(parseFloat(art.prix) * q) : '—';
        updateRecap();
    }

    sel.addEventListener('change', majPrix);
    qteEl.addEventListener('input', majPrix);
    div.querySelector('.btn-suppr').addEventListener('click', () => { div.remove(); updateRecap(); });

    document.getElementById('lignesContainer').appendChild(div);
    document.getElementById('emptyMsg').style.display = 'none';
    updateRecap();
}

document.getElementById('btnAjouter').addEventListener('click', ajouterLigne);
ajouterLigne();
</script>

<?php endif; ?>

<?php
$content = ob_get_clean();
require '_base.php';
?>
