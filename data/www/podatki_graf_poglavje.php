<?php
require_once("config.php");
require_once("db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$poglavjeId = (int)($_GET['poglavje_id'] ?? 0);
$days = (int)($_GET['days'] ?? 7);
$userId = $_SESSION['user_id'];

$checkQuery = "
    SELECT 1 
    FROM poglavje p
    JOIN predmet pr ON p.TK_predmet = pr.id_predmeta
    WHERE p.id_poglavja = ? AND pr.TK_oseba = ?
";
$checkStmt = $pdo->prepare($checkQuery);
$checkStmt->execute([$poglavjeId, $userId]);

if (!$checkStmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Poglavje ne pripada uporabniku']);
    exit();
}

$query = "
    SELECT 
        DATE(zacetek) as datum,
        SUM(trajanje_min) as cas_ucenja
    FROM seja
    WHERE TK_poglavje = ? 
    AND TK_osebe = ?
    AND zacetek >= DATE_SUB(NOW(), INTERVAL ? DAY)
    GROUP BY DATE(zacetek)
    ORDER BY datum ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute([$poglavjeId, $userId, $days]);
$dbData = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($dbData)) {
    echo json_encode([
        'success' => true,
        'data' => []
    ]);
    exit();
}

$firstDate = $dbData[0]['datum'];
$lastDate = $dbData[count($dbData) - 1]['datum'];

$allDates = [];
$currentDate = strtotime($firstDate);
$endDate = strtotime($lastDate);

while ($currentDate <= $endDate) {
    $dateStr = date('Y-m-d', $currentDate);
    $allDates[$dateStr] = 0;
    $currentDate = strtotime('+1 day', $currentDate);
}

foreach ($dbData as $row) {
    $allDates[$row['datum']] = (int)$row['cas_ucenja'];
}

$data = [];
foreach ($allDates as $datum => $cas_ucenja) {
    $data[] = [
        'datum' => $datum,
        'cas_ucenja' => $cas_ucenja
    ];
}

echo json_encode([
    'success' => true,
    'data' => $data
]);
