<?php
include 'db.php';

$horariosStmt = $pdo->query("
    SELECT DISTINCT horario
    FROM votos
    WHERE fecha = CURDATE()
    ORDER BY horario ASC
");
$horarios = $horariosStmt->fetchAll(PDO::FETCH_COLUMN);

$listado = [];
foreach ($horarios as $h) {
    $stmt = $pdo->prepare("
        SELECT u.nombre, v.created_at
        FROM votos v
        JOIN usuarios u ON u.id = v.usuario_id
        WHERE v.fecha = CURDATE() AND v.horario = ?
        ORDER BY v.created_at ASC
    ");
    $stmt->execute([$h]);
    $listado[$h] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Lista por horarios - AEUDJ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="img/comite.jpg" type="img/jpg">

  <style>
    body {
      background: linear-gradient(135deg, #f0f4ff, #e0e7ff);
      font-family: 'Segoe UI', sans-serif;
    }
    .card-horario {
      background: white;
      border-radius: 1.5rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .nombre-card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 1rem;
      transition: all 0.2s ease;
    }
    .nombre-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    @media (max-width: 640px) {
      .card-horario {
        margin-left: 1rem;
        margin-right: 1rem;
      }
    }
  </style>
</head>
<body class="min-h-screen py-8">
  <main class="container mx-auto px-4 max-w-5xl">
    <div class="text-center mb-8">
      <h1 class="text-4xl font-bold text-gray-800 mb-2">📋 Lista de Pasajeros</h1>
      <p class="text-lg text-gray-600">Organizada por horario - <?= date('d/m/Y') ?></p>
    </div>

    <?php if (!$listado): ?>
      <div class="text-center">
        <p class="text-gray-500 text-lg">No hay registros aún.</p>
        <?php if (!isset($_GET['visto'])): ?>
          <a href="votar.php" class="inline-block mt-4 bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors">Ir a votar</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="grid gap-8 md:gap-10">
        <?php foreach ($listado as $horario => $personas): ?>
          <div class="card-horario p-6 md:p-8">
            <h2 class="text-2xl font-bold text-blue-800 mb-6 text-center"><?= htmlspecialchars($horario) ?></h2>
            <?php if (!$personas): ?>
              <p class="text-center text-gray-500">Nadie seleccionó este horario.</p>
            <?php else: ?>
              <div class="grid gap-3 md:gap-4 max-h-96 overflow-y-auto pr-2">
                <?php foreach ($personas as $i => $p): ?>
                  <div class="nombre-card flex items-center justify-between p-4">
                    <div class="flex items-center space-x-4">
                      <span class="bg-blue-600 text-white text-lg font-bold w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0">
                        <?= $i + 1 ?>
                      </span>
                      <p class="font-semibold text-gray-800 text-lg"><?= htmlspecialchars($p['nombre']) ?></p>
                    </div>
                    <span class="text-sm text-gray-500 font-medium"><?= date('H:i', strtotime($p['created_at'])) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mt-10">
      <?php if (!isset($_GET['visto'])): ?>
        <a href="votar.php" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors w-full sm:w-auto text-center">Volver a votar</a>
      <?php endif; ?>
      <button onclick="window.print()" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-xl font-semibold hover:bg-gray-300 transition-colors w-full sm:w-auto text-center">Imprimir lista</button>
    </div>
  </main>
</body>
</html>