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

$errors_brisiPoglavje = [];
$success_brisiPoglavje = '';

$errors_dodajPoglavje = [];
$success_dodajPoglavje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'delete_predmet' && isset($_POST['predmet_id'])) {

        $predmetIdPost = (int)$_POST['predmet_id'];

        $checkPredmet = $pdo->prepare("SELECT 1 FROM predmet WHERE id_predmeta = ? AND TK_oseba = ?");
        $checkPredmet->execute([$predmetIdPost, $userId]);

        if ($checkPredmet->fetch()) {
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

                        <!-- TODO: GRAF -->
                        <div>
                            <h1>TUKAJ BO GRAF</h1>
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

                                            <form method="post" class="m-0">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="poglavje_id" value="<?php echo (int)$row['id_poglavja']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i> Odstrani
                                                </button>
                                            </form>
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

            window.history.replaceState({}, '', url);

        }, 1500);
    });
</script>