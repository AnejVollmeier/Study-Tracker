<?php
require_once("config.php");
include_once("db.php");

if (isset($_POST['odjava'])) {
  $_SESSION = array();
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params['path'], $params['domain'],
      $params['secure'], $params['httponly']
    );
  }
  session_destroy();
  header('Location: index.php');
  exit();
}

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

$name = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle ?? 'Study-Tracker'); ?></title>
        <link
  href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
  rel="stylesheet"
/>
    <link rel="stylesheet" href="<?php echo '/style.css?v=' . filemtime(__DIR__ . '/style.css'); ?>">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<header>
  <div class="bg-primary text-white py-3">
    <div class="container text-center">
      <h1 class="fw-bold">Study-Tracker</h1>
      <p class="mb-0">Vaš spremljevalec za učinkovito vodenje študija</p>
    </div>
  </div>
</header>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary position-relative">
  <div class="container">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

      <div class="collapse navbar-collapse" id="mainNavbar">
      <div class="w-100 d-flex justify-content-center align-items-center gap-4">
        <a class="nav-link active text-white fw-bold fs-5" aria-current="page" href="predmeti.php">Vsi predmeti</a>
        <a class="nav-link active text-white fw-bold fs-5" aria-current="page" href="dodajPredmet.php">Dodaj predmet</a>
      </div>

      <div class="d-lg-none w-100 text-center mt-2">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
          <button type="submit" name="odjava" class="btn btn-danger fw-bold text-white">Odjava</button>
        </form>
      </div>
    </div>
    <div class="position-absolute end-0 top-50 translate-middle-y me-3 d-none d-lg-flex align-items-center">
      <span class="text-white fw-bold me-3"><?php echo $name; ?></span>
      <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="m-0">
        <button type="submit" name="odjava" class="btn btn-danger fw-bold text-white">Odjava</button>
      </form>
    </div>
  </div>
</nav>