<?php
session_start();
include 'db.php';

// Solo admin
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

$uid = (int)($_GET['uid'] ?? 0);

// Marcar como bloqueado (campo nuevo en usuarios)
$pdo->prepare("UPDATE usuarios SET bloqueado = 1 WHERE id = ?")->execute([$uid]);

header("Location: admin.php?bloqueado=1");
exit;
?>
