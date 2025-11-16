<?php
session_start();
include 'db.php';


if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

$uid = (int)($_GET['uid'] ?? 0);


$pdo->prepare("UPDATE usuarios SET bloqueado = 1 WHERE id = ?")->execute([$uid]);

header("Location: admin.php?bloqueado=1");
exit;

?>
