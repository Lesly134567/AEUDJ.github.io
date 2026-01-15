<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

$stmt = $pdo->query("
    SELECT u.nombre, v.horario, v.created_at
    FROM votos v
    JOIN usuarios u ON u.id = v.usuario_id
    WHERE v.fecha = CURDATE() AND v.se_monto = 0
    ORDER BY v.horario ASC, v.created_at ASC
");
$personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8">
  <title>No subieron - AEUDJ</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: linear-gradient(135deg, #fef2f2, #fecaca);
      font-family: 'Segoe UI', sans-serif;
    }
    .card {
      background: white;
      border-radius: 1.5rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="min-h-screen py-8">
  <main class="container mx-auto px-4 max-w-4xl">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-red-800 mb-2">📵 Personas que NO subieron</h1>
      <p class="text-red-600">Día <?= date('d/m/Y') ?> - para contactar</p>
    </div>

    <?php if (!$personas): ?>
      <div class="text-center">
        <p class="text-green-700 text-lg">¡Todos subieron! 🎉</p>
      </div>
    <?php else: ?>
      <div class="card p-6 md:p-8">
        <div class="grid gap-3 md:gap-4">
          <?php foreach ($personas as $p): ?>
            <div class="flex items-center justify-between bg-red-50 border border-red-200 rounded-xl p-4">
              <div>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($p['nombre']) ?></p>
                <p class="text-sm text-gray-600"><?= $p['horario'] ?></p>
              </div>
              <span class="text-sm text-gray-500"><?= date('H:i', strtotime($p['created_at'])) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="text-center mt-8">
      <a href="admin.php" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-xl font-semibold hover:bg-gray-300 transition-colors">Volver al panel</a>
    </div>
  </main>
</body>
</html>