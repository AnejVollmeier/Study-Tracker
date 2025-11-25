<?php
$pageTitle = 'Podrobnosti predmeta';
require_once("header2.php");
require_once("db.php");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: predmeti.php");
    exit();
}

$predmetId = $_GET['id'];
$userId = $_SESSION['user_id'];

$query = "SELECT * FROM predmet WHERE id_predmeta = ? AND TK_oseba = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$predmetId, $userId]);
$predmet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$predmet) {
    header("Location: predmeti.php");
    exit();
}
?>

<main class="mt-1 mb-1">
    <div class="container pt-4">
        <div class="row justify-content-center">
            <div class="col-12">

                <!-- Podrobnosti Card -->
                <div class="card shadow-lg border-0 login-card">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <div class="mb-3">
                            <i class="bi bi-journal-text fs-1"></i>
                        </div>
                        <h3 class="mb-0 fw-bold"><?php echo htmlspecialchars($predmet['ime']); ?></h3>
                        <p class="mb-0 opacity-75">Podrobnosti predmeta</p>
                    </div>

                    <div class="card-body p-5">
                        <div class="mb-4">
                            <a href="predmeti.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Nazaj na predmete
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php require_once("footer.html"); ?>