<?php
$host = 'podatkovna-baza';
$dbname = 'StudyTracker';
$user = 'root';
$pass = 'superVarnoGeslo';

try {
	$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	die("Napaka pri povezavi na bazo: " . $e->getMessage());
}
