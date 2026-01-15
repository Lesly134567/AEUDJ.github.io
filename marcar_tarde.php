<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("UPDATE votos SET se_monto = 2 WHERE id = ? AND fecha = CURDATE()");
$stmt->execute([$id]);

header("Location: admin.php");
exit;
?>