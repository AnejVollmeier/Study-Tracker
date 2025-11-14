<?php 
    session_start();
    include_once("db.php");
    $errors = [];
    $success = '';

    if($_SERVER["REQUEST_METHOD"] === 'POST'){
    
    $uporabnisko_ime_email = trim($_POST['username_email']);
    $geslo = trim($_POST['password']);
    
    if(empty($uporabnisko_ime_email)){
        $errors[] = 'Uporabniško ime ali email je obvezen.';
    }
    
    if(empty($geslo)){
        $errors[] = 'Geslo je obvezno.';
    }
    
    if(empty($errors)){
        
        $stmt = $pdo->prepare("SELECT id_osebe, uporabnisko_ime, email, geslo_hash FROM oseba WHERE uporabnisko_ime = ? OR email = ?");
        $stmt->execute([$uporabnisko_ime_email, $uporabnisko_ime_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$user){
            $errors[] = 'Uporabniško ime ali email ne obstaja.';
        } else {
            if(password_verify($geslo, $user['geslo_hash'])){

                $_SESSION['user_id'] = $user['id_osebe'];
                $_SESSION['username'] = $user['uporabnisko_ime'];
            
                header("Location: predmeti.php");
                exit();
            } else {
                $errors[] = 'Napačno geslo.';
            }
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
    <title>Prijava</title>
</head>
<body>
    <?php require_once("header1.html");?>
        <main class="mt-1 mb-1">
        <div class="container login-container">
            <div class="row justify-content-center align-items-center min-vh-100">
                <div class="col-md-6 col-lg-5">

                    <div class="card shadow-lg border-0 login-card">
                        <div class="card-header bg-primary text-white text-center py-4">
                            <div class="mb-3">
                                <i class="bi bi-person-circle fs-1"></i>
                            </div>
                            <h3 class="mb-0 fw-bold">Prijava</h3>
                            <p class="mb-0 opacity-75">Dobrodošli nazaj!</p>
                        </div>
                        
                        <div class="card-body p-5">

                            <!-- Error Messages -->
                            <?php if(!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Login Form -->
                            <form method="POST" action="login.php">

                                <div class="mb-4">
                                    <label for="username_email" class="form-label fw-semibold">
                                        <i class="bi bi-person-fill text-primary me-2"></i>Uporabniško ime ali email
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-envelope"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0 ps-0" 
                                               id="username_email"
                                               name="username_email"
                                               placeholder="Vnesite uporabniško ime ali email"
                                               required>
                                    </div>
                                </div>


                                <div class="mb-4">
                                    <label for="password" class="form-label fw-semibold">
                                        <i class="bi bi-lock-fill text-primary me-2"></i>Geslo
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-key"></i>
                                        </span>
                                        <input type="password" class="form-control border-start-0 border-end-0 ps-0" 
                                               id="password"
                                               name="password" 
                                               placeholder="Vnesite geslo"
                                               required>
                                    </div>
                                </div>


                                <div class="d-grid mb-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Prijavi se
                                    </button>
                                </div>


                                <div class="text-center mb-4">
                                    <hr class="my-4">
                                    <span class="bg-white px-3 text-muted">ali</span>
                                </div>


                                <div class="text-center">
                                    <p class="mb-0">
                                        Nimate računa? 
                                        <a href="registration.php" class="text-decoration-none fw-semibold">
                                            Registrirajte se
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

    <?php include_once("footer.html");?>
</body>
</html>