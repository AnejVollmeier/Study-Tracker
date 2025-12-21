<?php
$pageTitle = 'Predmeti';
require_once("header2.php");

require_once("lepe_misli.php");
$quote = fetchQuote();
?>

<main class="mt-1 mb-1">

    <!-- Lepe misli -->
    <?php if ($quote): ?>
        <div class="container mt-4 mb-4">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-lg border-0 bg-primary">
                        <div class="card-body text-white text-center py-5">
                            <i class="bi bi-lightbulb-fill fs-1 mb-3" style="opacity: 0.9;"></i>
                            <h4 class="fw-bold mb-3" style="font-style: italic;">"<?php echo htmlspecialchars($quote['quote']); ?>"</h4>
                            <p class="mb-0 opacity-75">- <?php echo htmlspecialchars($quote['author']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="container login-container pt-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">

                <!-- Predmeti Card -->
                <div class="card shadow-lg border-0 login-card">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <div class="mb-3">
                            <i class="bi bi-journal-bookmark-fill fs-1"></i>
                        </div>
                        <h3 class="mb-0 fw-bold">Vaši predmeti</h3>
                        <p class="mb-0 opacity-75">Seznam vaših predmetov</p>
                    </div>

                    <div class="card-body p-5">
                        <ul class="list-group list-group-flush">
                            <?php
                            require_once("db.php");

                            $userId = $_SESSION['user_id'];
                            $query = "SELECT * FROM predmet WHERE TK_oseba = ? ORDER BY datum_ustanovitve ASC";
                            $stmt = $pdo->prepare($query);
                            $stmt->execute([$userId]);
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if (count($result) > 0) {
                                foreach ($result as $row) {
                                    echo '<a href="podobnostiPredmeta.php?id=' . $row['id_predmeta'] . '" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">';
                                    echo '<div>';
                                    echo '<h5 class="mb-1">' . htmlspecialchars($row['ime']) . '</h5>';
                                    echo '<small class="text-muted">Zaključek: ' . date('d.m.Y', strtotime($row['datum_zakljucka'])) . '</small>';
                                    echo '</div>';
                                    echo '<i class="bi bi-chevron-right text-muted"></i>';
                                    echo '</a>';
                                }
                            } else {
                                echo '<li class="list-group-item text-center py-4">';
                                echo '<i class="bi bi-inbox fs-1 text-muted"></i>';
                                echo '<p class="mb-0 mt-2 text-muted">Nimate dodanih predmetov.</p>';
                                echo '</li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php require_once("footer.html"); ?>