<?php
require_once("config.php");

if (isset($_SESSION['user_id'])) {
    header('Location: predmeti.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="sl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study-Tracker - Domov</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
</head>

<body>
    <?php require_once("header1.html"); ?>

    <main class="mt-4">
        <div class="container main-narrow">
            <section class="hero text-white">
                <div class="container text-center py-5">
                    <h1 class="display-4 fw-bold">Dobrodošli v Study-Tracker!</h1>
                    <p class="lead mt-3">Vaš spremljevalec za učinkovito vodenje študija.</p>
                    <p class="mt-3 mb-4">Načrtujte naloge, sledite napredku in zgradite trajne navade za uspeh.</p>
                    <div class="mt-3">
                        <a class="btn btn-primary btn-lg mx-2" href="registration.php" role="button">Registracija</a>
                        <a class="btn btn-outline-light btn-lg mx-2" href="login.php" role="button">Prijava</a>
                    </div>
                </div>
            </section>

            <div class="my-5">
                <div class="card border-0 shadow-sm p-4">
                    <div class="text-center">
                        <h2 class="h3 fw-bold mb-3">Kaj je Study-Tracker</h2>
                        <p class="fs-5 text-secondary mx-auto" style="max-width:900px; line-height:1.6;">
                            Study-Tracker je vaš zvest sopotnik na poti do učinkovitega in zavestnega učenja.
                            Združuje discipliniran pristop z navdihujočim dizajnom — tu načrtujete cilje, sledite napredku
                            in gradite navade, ki trajno dvigujejo vašo produktivnost.
                        </p>
                    </div>
                </div>
            </div>
            <div class="carousel-wrapper" style="max-width: 65%; margin: 0 auto;">
                <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    </div>

                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="./images/Predmeti_img.png" class="d-block w-100" alt="..." style="object-fit: contain; max-height: 500px;">
                        </div>
                        <div class="carousel-item">
                            <img src="./images/Graf_vseg_poglavi_img.png" class="d-block w-100" alt="..." style="object-fit: contain; max-height: 500px;">
                        </div>
                        <div class="carousel-item">
                            <img src="./images/Graf_poglavja_img.png" class="d-block w-100" alt="..." style="object-fit: contain; max-height: 500px;">
                        </div>
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true" style="filter: invert(1);"></span>
                        <span class="visually-hidden">Prejšnja</span>
                    </button>

                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true" style="filter: invert(1);"></span>
                        <span class="visually-hidden">Naslednja</span>
                    </button>
                </div>
            </div>

            <section class="features py-5">
                <div class="container">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="feature-card p-4 text-center h-100 fade-in">
                                <div class="feature-icon mb-3">📈</div>
                                <h3 class="h5 fw-bold">Sledenje napredku</h3>
                                <p class="text-muted">Pregledni grafi in poročila vam pokažejo, kje ste napredovali.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-card p-4 text-center h-100 fade-in">
                                <div class="feature-icon mb-3">🗂️</div>
                                <h3 class="h5 fw-bold">Organizacija predmetov</h3>
                                <p class="text-muted">Urejajte predmete, lekcije in roke na enem mestu.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-card p-4 text-center h-100 fade-in">
                                <div class="feature-icon mb-3">🔔</div>
                                <h3 class="h5 fw-bold">Pametna obvestila</h3>
                                <p class="text-muted">Tedenska poročila in opomniki, ki vas spodbudijo k akciji.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <div class="my-5">
                <div class="card border-0 shadow-sm p-4">
                    <div class="text-center">
                        <h2 class="h3 fw-bold mb-3">Možnosti prijave</h2>
                        <p class="fs-5 text-secondary mx-auto" style="max-width:900px; line-height:1.6;">
                            Ob registraciji si lahko uporabnik sam izbere, ali želi prejemati tedenska e-poštna poročila o svojih predmetih in lekcijah.
                            Če to možnost omogoči, bo vsak teden prejel pregled svojega napredka, hkrati pa bo imel na voljo tudi prenos PDF in
                            Excel poročil z grafičnimi prikazi rezultatov. Če uporabnik možnosti e-poštnih obvestil ne izbere, poročil ne prejema
                            in prav tako nima dostopa do izvoza podatkov v PDF ali Excel, napredek pa lahko spremlja izključno znotraj aplikacije.
                        </p>
                    </div>
                </div>
            </div>
    </main>

    <?php require_once("footer.html"); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>