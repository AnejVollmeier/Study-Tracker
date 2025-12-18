<?php
require_once("config.php");
require_once("db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$predmetId = (int)($_GET['predmet_id'] ?? 0);
$userId = $_SESSION['user_id'];

$checkQuery = "SELECT 1 FROM predmet WHERE id_predmeta = ? AND TK_oseba = ?";
$checkStmt = $pdo->prepare($checkQuery);
$checkStmt->execute([$predmetId, $userId]);

if (!$checkStmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Predmet ne pripada uporabniku']);
    exit();
}

$query = "
    SELECT 
        p.ime as ime_poglavja,
        COALESCE(SUM(s.trajanje_min), 0) as cas_ucenja
    FROM poglavje p
    LEFT JOIN seja s ON s.TK_poglavje = p.id_poglavja AND s.TK_osebe = ?
    WHERE p.TK_predmet = ?
    GROUP BY p.id_poglavja, p.ime
    ORDER BY p.ime ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute([$userId, $predmetId]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => $data
]);
