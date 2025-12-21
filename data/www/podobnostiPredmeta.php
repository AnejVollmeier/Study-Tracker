<?php
$pageTitle = 'Podrobnosti predmeta';

require_once("config.php");
require_once("db.php");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: predmeti.php");
    exit();
}

$predmetId = (int)$_GET['id'];
$userId = $_SESSION['user_id'] ?? null;

$query = "SELECT * FROM predmet WHERE id_predmeta = ? AND TK_oseba = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$predmetId, $userId]);
$predmet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$predmet) {
    header("Location: predmeti.php");
    exit();
}

// PREVERI TIP OSEBE ZA IZVOZ
$queryUser = "SELECT TK_tip_osebe FROM oseba WHERE id_osebe = ?";
$stmtUser = $pdo->prepare($queryUser);
$stmtUser->execute([$userId]);
$userType = $stmtUser->fetch(PDO::FETCH_ASSOC);
$canExport = ($userType && $userType['TK_tip_osebe'] == 1);

$errors_brisiPoglavje = [];
$success_brisiPoglavje = '';
$errors_dodajPoglavje = [];
$success_dodajPoglavje = '';
$errors_casUcenja = [];
$success_casUcenja = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'add_cas_ucenja' && isset($_POST['poglavje_id'])) {
        $poglavjeId = (int)$_POST['poglavje_id'];
        $datum = $_POST['datum'] ?? '';
        $casUcenja = $_POST['cas_ucenja'] ?? '';

        $checkQuery = "SELECT 1 FROM poglavje WHERE id_poglavja = ? AND TK_predmet = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$poglavjeId, $predmetId]);

        if (!$checkStmt->fetch()) {
            $errors_casUcenja[] = 'Poglavje ne obstaja ali ne pripada temu predmetu.';
        } elseif (empty($datum)) {
            $errors_casUcenja[] = 'Prosimo, izberite datum.';
        } elseif (empty($casUcenja) || (int)$casUcenja < 1) {
            $errors_casUcenja[] = 'Čas učenja mora biti vsaj 1 minuta.';
        } else {
            $zacetekDatetime = $datum . ' ' . date('H:i:s');

            $insertQuery = "INSERT INTO seja (zacetek, trajanje_min, TK_osebe, TK_predmeta, TK_poglavje) VALUES (?, ?, ?, ?, ?)";
            $insertStmt = $pdo->prepare($insertQuery);

            if ($insertStmt->execute([$zacetekDatetime, (int)$casUcenja, $userId, $predmetId, $poglavjeId])) {
                header("Location: podobnostiPredmeta.php?id=" . $predmetId . "&cas_added=1");
                exit();
            } else {
                $errors_casUcenja[] = 'Napaka pri shranjevanju časa učenja.';
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete_predmet' && isset($_POST['predmet_id'])) {

        $predmetIdPost = (int)$_POST['predmet_id'];

        $checkPredmet = $pdo->prepare("SELECT 1 FROM predmet WHERE id_predmeta = ? AND TK_oseba = ?");
        $checkPredmet->execute([$predmetIdPost, $userId]);

        if ($checkPredmet->fetch()) {

            $delSeje = $pdo->prepare("DELETE FROM seja WHERE TK_predmeta = ?");
            $delSeje->execute([$predmetIdPost]);

            $delPoglavja = $pdo->prepare("DELETE FROM poglavje WHERE TK_predmet = ?");
            $delPoglavja->execute([$predmetIdPost]);

            $delPredmet = $pdo->prepare("DELETE FROM predmet WHERE id_predmeta = ? AND TK_oseba = ?");
            $delPredmet->execute([$predmetIdPost, $userId]);

            header("Location: predmeti.php?deleted=1");
            exit();
        } else {
            header("Location: predmeti.php");
            exit();
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['poglavje_id'])) {

        $poglavjeId = (int)$_POST['poglavje_id'];

        $checkQuery = "SELECT 1 FROM poglavje WHERE id_poglavja = ? AND TK_predmet = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$poglavjeId, $predmetId]);

        if ($checkStmt->fetch()) {
            $deleteStmt = $pdo->prepare("DELETE FROM poglavje WHERE id_poglavja = ?");
            $deleteStmt->execute([$poglavjeId]);

            header("Location: podobnostiPredmeta.php?id=" . $predmetId . "&del=1");
            exit();
        } else {
            $errors_brisiPoglavje[] = 'Poglavje ne obstaja ali ne pripada temu predmetu.';
        }
    } elseif (isset($_POST['ime_poglavja'])) {

        $imePoglavja = trim($_POST['ime_poglavja']);

        if ($imePoglavja === '') {
            $errors_dodajPoglavje[] = 'Prosimo, vnesite ime poglavja.';
        } else {
            $insertQuery = "INSERT INTO poglavje (ime, TK_predmet, datum_ustanovitve) VALUES (?, ?, NOW())";
            $insertStmt = $pdo->prepare($insertQuery);
            $insertStmt->execute([$imePoglavja, $predmetId]);

            header("Location: podobnostiPredmeta.php?id=" . $predmetId . "&add=1");
            exit();
        }
    }
}

if (isset($_GET['add']) && $_GET['add'] == '1') {
    $success_dodajPoglavje = 'Poglavje je bilo uspešno dodano.';
}

if (isset($_GET['del']) && $_GET['del'] == '1') {
    $success_brisiPoglavje = 'Poglavje je bilo uspešno odstranjeno.';
}

if (isset($_GET['cas_added']) && $_GET['cas_added'] == '1') {
    $success_casUcenja = 'Čas učenja je bil uspešno dodan.';
}

$query1 = "SELECT * FROM poglavje WHERE TK_predmet = ? ORDER BY datum_ustanovitve ASC";
$stmtPoglavja = $pdo->prepare($query1);
$stmtPoglavja->execute([$predmetId]);
$poglavja = $stmtPoglavja->fetchAll(PDO::FETCH_ASSOC);

require_once("header2.php");
?>

<main class="mt-1 mb-1">
    <div class="container pt-4">
        <div class="row justify-content-center">
            <div class="col-12">

                <div class="card shadow-lg border-0 login-card">

                    <div class="card-header bg-primary text-white text-center py-4">
                        <div class="mb-3">
                            <i class="bi bi-journal-text fs-1"></i>
                        </div>
                        <h3 class="mb-0 fw-bold">
                            <?php echo htmlspecialchars($predmet['ime']); ?>
                        </h3>
                        <p class="mb-0 opacity-75">Podrobnosti predmeta</p>
                    </div>
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <a href="predmeti.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Nazaj na predmete
                            </a>
                        </div>

                        <!-- GRAF -->
                        <div class="mt-4 d-flex justify-content-center mb-4">
                            <div class="border rounded p-4 shadow-sm" style="width: 90%;">
                                <h2 class="fw-bold text-center text-primary mb-3">Graf učenja po poglavjih</h2>
                                <div style="position: relative; height: 400px;">
                                    <canvas id="studyChart" data-predmet="<?php echo $predmetId; ?>"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- SEZNAM POGLAVIJ -->
                        <div class="mt-4 d-flex justify-content-center mb-4">
                            <div class="border rounded p-4 shadow-sm" style="width: 50%;">

                                <h2 class="fw-bold text-center text-primary mb-5">Poglavja</h2>

                                <?php if (!empty($errors_brisiPoglavje)): ?>
                                    <div class="alert alert-danger alert-dismissible mb-3" id="errorAlert">
                                        <ul class="mb-0">
                                            <?php foreach ($errors_brisiPoglavje as $err): ?>
                                                <li><?php echo htmlspecialchars($err); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <?php if ($success_brisiPoglavje): ?>
                                    <div class="alert alert-danger alert-dismissible mb-3" id="successAlert">
                                        <?php echo htmlspecialchars($success_brisiPoglavje); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (count($poglavja) > 0): ?>

                                    <?php foreach ($poglavja as $row): ?>
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="fs-5">
                                                <?php echo htmlspecialchars($row['ime']); ?>
                                            </span>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#grafPoglavjeModal<?php echo (int)$row['id_poglavja']; ?>">
                                                    <i class="bi bi-graph-up"></i> Graf
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#casUcenjaModal<?php echo (int)$row['id_poglavja']; ?>">
                                                    <i class="bi bi-alarm"></i> Čas učenja
                                                </button>
                                                <form method="post" class="m-0">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="poglavje_id" value="<?php echo (int)$row['id_poglavja']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="bi bi-trash"></i> Odstrani
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Čas učenja -->
                                        <div class="modal fade" id="casUcenjaModal<?php echo (int)$row['id_poglavja']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">Dodaj čas učenja</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="post" class="modal-form">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="add_cas_ucenja">
                                                            <input type="hidden" name="poglavje_id" value="<?php echo (int)$row['id_poglavja']; ?>">

                                                            <?php if (!empty($errors_casUcenja)): ?>
                                                                <div class="alert alert-danger alert-dismissible mb-3">
                                                                    <ul class="mb-0">
                                                                        <?php foreach ($errors_casUcenja as $err): ?>
                                                                            <li><?php echo htmlspecialchars($err); ?></li>
                                                                        <?php endforeach; ?>
                                                                    </ul>
                                                                </div>
                                                            <?php endif; ?>

                                                            <?php if ($success_casUcenja): ?>
                                                                <div class="alert alert-success alert-dismissible mb-3">
                                                                    <?php echo htmlspecialchars($success_casUcenja); ?>
                                                                </div>
                                                            <?php endif; ?>

                                                            <div class="mb-3">
                                                                <label for="datum_<?php echo (int)$row['id_poglavja']; ?>" class="form-label">Datum:</label>
                                                                <input type="date" class="form-control" id="datum_<?php echo (int)$row['id_poglavja']; ?>" name="datum" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="cas_<?php echo (int)$row['id_poglavja']; ?>" class="form-label">Čas učenja (minutah):</label>
                                                                <input type="number" class="form-control" id="cas_<?php echo (int)$row['id_poglavja']; ?>" name="cas_ucenja" min="1" placeholder="0" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Prekliči</button>
                                                            <button type="submit" class="btn btn-primary">Dodaj</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal za graf poglavja -->
                                        <div class="modal fade" id="grafPoglavjeModal<?php echo (int)$row['id_poglavja']; ?>" tabindex="-1" aria-hidden="true" data-poglavje-id="<?php echo (int)$row['id_poglavja']; ?>">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title">Graf učenja - <?php echo htmlspecialchars($row['ime']); ?></h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div style="position: relative; height: 400px;">
                                                            <canvas id="poglavjeChart<?php echo (int)$row['id_poglavja']; ?>"></canvas>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zapri</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <div class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="mb-0 mt-2 text-muted">Nimate dodanih poglavij.</p>
                                    </div>

                                <?php endif; ?>

                            </div>
                        </div>

                        <!-- DODAJ POGLAVJE -->
                        <div class="d-flex justify-content-center align-items-center mb-4">
                            <div class="border rounded p-4 shadow-sm" style="width: 50%;">

                                <h2 class="fw-bold text-center text-primary mb-4">Dodaj poglavje</h2>

                                <?php if (!empty($errors_dodajPoglavje)): ?>
                                    <div class="alert alert-danger alert-dismissible mb-3" id="errorAlert">
                                        <ul class="mb-0">
                                            <?php foreach ($errors_dodajPoglavje as $err): ?>
                                                <li><?php echo htmlspecialchars($err); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <?php if ($success_dodajPoglavje): ?>
                                    <div class="alert alert-success alert-dismissible mb-3" id="successAlert">
                                        <?php echo htmlspecialchars($success_dodajPoglavje); ?>
                                    </div>
                                <?php endif; ?>

                                <form id="dodajPoglavjeForm" method="post" class="mt-3">
                                    <input type="hidden" name="predmet_id" value="<?php echo (int)$predmetId; ?>">

                                    <label for="ime_poglavja" class="form-label fw-semibold mb-2 ps-2">
                                        Ime poglavja:
                                    </label>

                                    <div class="input-group mb-3">
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="ime_poglavja"
                                            name="ime_poglavja"
                                            placeholder="Vnesite ime poglavja"
                                            required>
                                        <button class="btn btn-primary px-4" type="submit">
                                            <i class="bi bi-plus-circle me-2"></i>Dodaj
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>

                        <!-- IZVOZI PREDMETA -->
                        <?php if ($canExport): ?>
                            <div class="d-flex justify-content-center align-items-center mb-4">
                                <div class="border rounded p-4 shadow-sm" style="width: 50%;">

                                    <h2 class="fw-bold text-center text-primary mb-4">
                                        Izvezi predmeta
                                    </h2>

                                    <div class="d-flex gap-3 justify-content-center">
                                        <form method="post" action="izvozi.php" style="display:inline;">
                                            <input type="hidden" name="predmet_id" value="<?php echo (int)$predmetId; ?>">
                                            <input type="hidden" name="format" value="excel">
                                            <button type="submit" class="btn btn-danger text-white fw-bold">
                                                <i class="bi bi-file-earmark-spreadsheet me-2"></i>Excel
                                            </button>
                                        </form>

                                        <form method="post" action="izvozi.php" style="display:inline;">
                                            <input type="hidden" name="predmet_id" value="<?php echo (int)$predmetId; ?>">
                                            <input type="hidden" name="format" value="pdf">
                                            <button type="submit" class="btn btn-warning text-white fw-bold">
                                                <i class="bi bi-file-earmark-pdf me-2"></i>PDF
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- IZBRIŠI PREDMET -->
                        <div class="text-center fs-5 m-5">
                            <form method="post" class="m-0">
                                <input type="hidden" name="action" value="delete_predmet">
                                <input type="hidden" name="predmet_id" value="<?php echo (int)$predmetId; ?>">
                                <button type="submit" class="btn btn-danger btn-xxl">
                                    <i class="bi bi-trash"></i> Odstrani predmet
                                </button>
                            </form>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
</main>

<?php require_once("footer.html"); ?>

<!-- Chart.js Kniznica -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Graf  -->
<script src="graf.js"></script>

<!-- Graf Poglavje  -->
<script src="graf_poglavje.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const alerts = document.querySelectorAll('#errorAlert, #successAlert');

        if (alerts.length === 0) return;

        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";

                setTimeout(() => alert.remove(), 500);
            });

            const url = new URL(window.location.href);
            url.searchParams.delete('add');
            url.searchParams.delete('del');
            url.searchParams.delete('cas_added');

            window.history.replaceState({}, '', url);

        }, 1500);
    });
</script>