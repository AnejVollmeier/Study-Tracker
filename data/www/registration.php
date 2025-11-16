<?php
include_once("db.php");
require_once("config.php");

if (isset($_SESSION['user_id'])) {
    header('Location: predmeti.php');
    exit();
}

$errors = [];
$success = '';

if($_SERVER["REQUEST_METHOD"] === 'POST'){
    
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $wantsEmail = isset($_POST['contactMethod']);
    
    if($password === $confirmPassword){
        $hashed = password_hash($password, PASSWORD_BCRYPT);
    } else {
        $errors[] = 'Gesli se ne ujemata.';
    }
    
    if(empty($errors)){
        
        $stmt = $pdo->prepare("SELECT COUNT(*) AS sum FROM oseba WHERE uporabnisko_ime = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $result = $stmt->fetch();
        
        if($result['sum'] > 0){
            $errors[] = 'Uporabniško ime ali email že obstaja.';
        } else {
            
            //(1 = email_yes, 2 = email_no)
            $tipId = $wantsEmail ? 1 : 2;
            
            $insert = $pdo->prepare("INSERT INTO oseba (ime, priimek, email, uporabnisko_ime, geslo_hash, TK_tip_osebe) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->execute([$firstName, $lastName, $email, $username, $hashed, $tipId]);
            
            $success = 'Registracija uspešna. Sedaj se lahko prijavite.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <title>Registracija</title>
</head>
    <body>
    <?php require_once("header1.php");?>
        <main class="mt-1 mb-1">
        <div class="container login-container">
            <div class="row justify-content-center align-items-center min-vh-100">
                <div class="col-md-8 col-lg-6">
                    <!-- Register Card -->
                    <div class="card shadow-lg border-0 login-card">
                        <div class="card-header bg-primary text-white text-center py-4">
                            <div class="mb-3">
                                <i class="bi bi-person-plus-fill fs-1"></i>
                            </div>
                            <h3 class="mb-0 fw-bold">Registracija</h3>
                            <p class="mb-0 opacity-75">Ustvarite nov račun</p>
                        </div>
                        
                        <div class="card-body p-5">
                            <!-- Register Form -->
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
                                        window.location.href = 'login.php';
                                    }, 3000);
                                </script>
                            <?php endif; ?>
                            <form id="registerForm" method="post" action="registration.php">
                                <!-- Name Fields -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="firstName" class="form-label fw-semibold">
                                            <i class="bi bi-person-fill text-primary me-2"></i>Ime
                                        </label>
                         <input type="text" class="form-control" 
                             id="firstName" name="firstName"
                             value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>"
                             placeholder="Vnesite ime"
                             required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lastName" class="form-label fw-semibold">
                                            <i class="bi bi-person-fill text-primary me-2"></i>Priimek
                                        </label>
                         <input type="text" class="form-control" 
                             id="lastName" name="lastName"
                             value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>"
                             placeholder="Vnesite priimek"
                             required>
                                    </div>
                                </div>

                                <!-- Email Field -->
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">
                                        <i class="bi bi-envelope-fill text-primary me-2"></i>Email naslov
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-envelope"></i>
                                        </span>
                         <input type="email" class="form-control border-start-0 ps-0" 
                             id="email" name="email"
                             value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                             placeholder="vas@email.com"
                             required>
                                    </div>
                                </div>

                                <!-- Username Field -->
                                <div class="mb-3">
                                    <label for="username" class="form-label fw-semibold">
                                        <i class="bi bi-person-circle text-primary me-2"></i>Uporabniško ime
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-person"></i>
                                        </span>
                         <input type="text" class="form-control border-start-0 ps-0" 
                             id="username" name="username"
                             value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                             placeholder="Izberite uporabniško ime"
                             required>
                                    </div>
                                </div>

                                <!-- Password Fields -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="password" class="form-label fw-semibold">
                                            <i class="bi bi-lock-fill text-primary me-2"></i>Geslo
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-key"></i>
                                            </span>
                          <input type="password" class="form-control border-start-0 border-end-0 ps-0" 
                              id="password" name="password"
                              placeholder="Vnesite geslo"
                              required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirmPassword" class="form-label fw-semibold">
                                            <i class="bi bi-lock-fill text-primary me-2"></i>Ponovite geslo
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-key"></i>
                                            </span>
                          <input type="password" class="form-control border-start-0 border-end-0 ps-0" 
                              id="confirmPassword" name="confirmPassword"
                              placeholder="Ponovite geslo"
                              required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Terms and Conditions -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="contactMethod" id="contactEmail" value="email" <?php echo (!empty($wantsEmail) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="contactEmail">
                                            Želim biti obveščen/a o svojem napredku preko emaila
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="acceptTerms" required>
                                        <label class="form-check-label" for="acceptTerms">
                                            Strinjam se s <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#termsModal">pogoji uporabe</a>
                                        </label>
                                    </div>
                                </div>

                                <!-- Register Button -->
                                <div class="d-grid mb-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-person-plus me-2"></i>Registriraj se
                                    </button>
                                </div>

                                <!-- Login Link -->
                                <div class="text-center">
                                    <p class="mb-0">
                                        Že imate račun? 
                                        <a href="login.php" class="text-decoration-none fw-semibold">
                                            Prijavite se
                                        </a>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                 </div>
            </div>
        </div>
    </main>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-file-text me-2"></i>Pogoji uporabe
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <h6>1. Sprejemanje pogojev</h6>
                            <p class="text-muted">Z registracijo se strinjate s temi pogoji uporabe Study-Tracker platforme.</p>
                            
                            <h6>2. Račun in varnost</h6>
                            <p class="text-muted">Odgovorni ste za varnost svojega računa in gesla. Takoj sporočite vsako nepooblaščeno uporabo.</p>
                            
                            <h6>3. Zasebnost</h6>
                            <p class="text-muted">Vaši osebni podatki so zaščiteni v skladu z našo politiko zasebnosti.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        Razumem
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php include_once("footer.html");?>
</body>
</html>

