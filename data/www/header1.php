<?php
require_once("config.php");

if (isset($_SESSION['user_id'])) {
  header('Location: predmeti.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link
  href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
  rel="stylesheet"
/>
<link rel="stylesheet" href="./style.css" />
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
      <ul class="navbar-nav mx-auto d-flex flex-column flex-lg-row justify-content-center align-items-center">
        <li class="nav-item">
          <a class="nav-link active text-white fw-bold fs-5 px-3 my-2 my-lg-0" href="index.php">Domov</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active text-white fw-bold fs-5 px-3 my-2 my-lg-0" href="registration.php">Registracija</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active text-white fw-bold fs-5 px-3 my-2 my-lg-0" href="login.php">Prijava</a>
        </li>
      </ul>
    </div>
</nav>
</body>
</html>