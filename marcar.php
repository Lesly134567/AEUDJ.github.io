<?php
session_start();
include 'db.php';

// Solo admin
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$val = (int)($_GET['val'] ?? 0);

$stmt = $pdo->prepare("UPDATE votos SET se_monto = ? WHERE id = ? AND fecha = CURDATE()");
$stmt->execute([$val, $id]);

header("Location: admin.php");
exit;
?>