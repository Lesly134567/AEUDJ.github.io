<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM notificaciones ORDER BY fecha DESC LIMIT 50");
$notis = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Notificaciones - AEUDJ</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen py-8 bg-gradient-to-br from-gray-50 to-gray-100">
  <main class="container mx-auto px-4 max-w-4xl">
    <h1 class="text-3xl font-bold mb-6 text-center">🔔 Notificaciones</h1>
    <?php if (!$notis): ?>
      <p class="text-center text-gray-600">Sin notificaciones.</p>
    <?php else: ?>
      <div class="bg-white rounded-2xl shadow-xl p-6 space-y-4">
        <?php foreach ($notis as $n): ?>
          <div class="border-l-4 border-blue-500 pl-4 py-2">
            <p class="text-gray-800"><?= htmlspecialchars($n['mensaje']) ?></p>
            <span class="text-xs text-gray-500"><?= $n['fecha'] ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="text-center mt-6">
      <a href="admin.php" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-xl font-semibold hover:bg-gray-300">Volver al panel</a>
    </div>
  </main>
</body>
</html>