<?php
$pageTitle = 'Dodaj predmet';
require_once("header2.php");
?>

<?php

$errors = [];
$success = '';

$tomorrow = date('Y-m-d', strtotime('+1 day'));

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ime_predmeta = trim($_POST['ime_predmeta']);
    $datum_izpita = trim($_POST['datum_izpita']);

    if(empty($ime_predmeta)) {
        $errors[] = 'Ime predmeta je obvezno.';
    }

    if(empty($datum_izpita)) {
        $errors[] = 'Datum izpita je obvezen.';
    } elseif(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datum_izpita)) {
        $errors[] = 'Datum izpita mora biti v formatu LLLL-MM-DD.';
    } else {
        if ($datum_izpita < $tomorrow) {
            $errors[] = 'Datum izpita ne sme biti v preteklosti; izberite vsaj naslednji dan.';
        }
    }

    if(empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO predmet (TK_oseba, ime, datum_zakljucka) VALUES (?,?,?)");
        $stmt->execute([$_SESSION['user_id'], $ime_predmeta, $datum_izpita]);

        $success = 'Predmet je bil uspešno dodan.';
        $ime_predmeta = '';
        $datum_izpita = '';
    }
}
?>

<main class="mt-1 mb-1">
        <div class="container login-container">
            <div class="row justify-content-center align-items-center min-vh-100">
                <div class="col-md-8 col-lg-6">

                    <!-- DodajPredmet Card -->
                    <div class="card shadow-lg border-0 login-card">
                        <div class="card-header bg-primary text-white text-center py-4">
                            <div class="mb-3">
                                <i class="bi bi-person-plus-fill fs-1"></i>
                            </div>
                            <h3 class="mb-0 fw-bold">Dodaj predmet</h3>
                            <p class="mb-0 opacity-75">Ustvarite nov predmet</p>
                        </div>
                        
                        <div class="card-body p-5">
                            <!-- DodajPredmet Form -->
                            <?php if(!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach($errors as $err): ?>
                                            <li><?php echo htmlspecialchars($err); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if($success): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                                <script>
                                    setTimeout(function() {
                                        window.location.href = 'predmeti.php';
                                    }, 3000);
                                </script>
                            <?php endif; ?>
                            <form id="dodajPredmetForm" method="post" action="dodajPredmet.php">
                                
                                <div class="row mb-3">

                                    <!-- ImePredmeta -->
                                    <div class="col-md-6">
                                        <label for="imePredmeta" class="form-label fw-semibold">
                                            Ime predmeta
                                        </label>
                                        <div class="input-group">
                                            <input type="text" class="form-control border-start-0 ps-0"
                                                id="imePredmeta" name="ime_predmeta"
                                                value="<?php echo isset($ime_predmeta) ? htmlspecialchars($ime_predmeta) : ''; ?>"
                                                placeholder="Vnesite ime predmeta"
                                                aria-label="Ime predmeta"
                                                required>
                                        </div>
                                    </div>

                                    <!-- DatumIzpita -->
                                    <div class="col-md-6">
                                        <label for="datumIzpita" class="form-label fw-semibold">
                                            Datum izpita
                                        </label>
                                        <input type="date" class="form-control" 
                                            id="datumIzpita" name="datum_izpita"
                                            value="<?php echo isset($datum_izpita) ? htmlspecialchars($datum_izpita) : ''; ?>"
                                            min="<?php echo $tomorrow; ?>"
                                            required>
                                    </div>
                                    
                                </div>

                                <!-- DodajPredmet Button -->
                                <div class="d-grid mb-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-person-plus me-2"></i>Dodaj predmet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                 </div>
            </div>
        </div>
    </main>


<?php require_once("footer.html"); ?>