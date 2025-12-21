<?php
/**
 * Cron job script za pošiljanje opomnikov o izpitih
 * Preverja, kateri izpiti so čez 2 dni in pošilja opomnike uporabnikom
 */

// Uporabi obstoječo DB konfiguracijo
require_once('/var/www/html/db.php');
require_once('/var/www/html/sendEmail.php');

// Izračunaj datum čez 2 dni
$targetDate = date('Y-m-d', strtotime('+2 days'));

// Najdi vse predmete, ki imajo izpit čez 2 dni
// Uporabniki morajo imeti tip_osebe 'email_yes' in veljaven email
$query = "
    SELECT 
        p.id_predmeta,
        p.ime as ime_predmeta,
        p.datum_zakljucka,
        o.id_osebe,
        o.email,
        o.ime as ime_osebe,
        o.priimek as priimek_osebe
    FROM predmet p
    JOIN oseba o ON p.TK_oseba = o.id_osebe
    JOIN tip_osebe t ON o.TK_tip_osebe = t.id_tip_osebe
    WHERE DATE(p.datum_zakljucka) = ?
    AND t.naziv = 'email_yes'
    AND o.email IS NOT NULL
    AND o.email != ''
";

$stmt = $pdo->prepare($query);
$stmt->execute([$targetDate]);
$predmeti = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sentCount = 0;
$errorCount = 0;

foreach ($predmeti as $predmet) {
    // Pridobi podatke o poglavjih za ta predmet
    $grafQuery = "
        SELECT 
            p.ime as ime_poglavja,
            COALESCE(SUM(s.trajanje_min), 0) as cas_ucenja
        FROM poglavje p
        LEFT JOIN seja s ON s.TK_poglavje = p.id_poglavja AND s.TK_osebe = ?
        WHERE p.TK_predmet = ?
        GROUP BY p.id_poglavja, p.ime
        ORDER BY p.ime ASC
    ";
    
    $grafStmt = $pdo->prepare($grafQuery);
    $grafStmt->execute([$predmet['id_osebe'], $predmet['id_predmeta']]);
    $podatki = $grafStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generiraj in pošlji email
    $emailSubject = "Opomnik: Izpit iz predmeta " . $predmet['ime_predmeta'] . " čez 2 dni!";
    $emailBody = generateExamReminderEmail(
        $predmet['ime_predmeta'],
        $predmet['datum_zakljucka'],
        $podatki
    );
    
    $result = sendEmail($predmet['email'], $emailSubject, $emailBody);
    
    if ($result) {
        $sentCount++;
        error_log("Email reminder sent successfully to: " . $predmet['email'] . " for subject: " . $predmet['ime_predmeta']);
    } else {
        $errorCount++;
        error_log("Failed to send email reminder to: " . $predmet['email'] . " for subject: " . $predmet['ime_predmeta']);
    }
}

// Zapiši summary v log
$logMessage = sprintf(
    "[%s] Exam reminder cron job completed. Sent: %d, Errors: %d, Target date: %s",
    date('Y-m-d H:i:s'),
    $sentCount,
    $errorCount,
    $targetDate
);
error_log($logMessage);

echo $logMessage . "\n";
exit(0);
