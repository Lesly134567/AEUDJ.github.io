<?php
session_start();
require_once 'db.php';


$listado = obtenerListaPorHorario();
$antesDespues = obtenerHorariosAntesDespues();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Lista por horarios - AEUDJ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="img/comite.jpg" type="image/jpg">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #f0f4ff; font-family: 'Segoe UI', sans-serif; }
    .horario-btn { background: #3b82f6; color: white; }
    .horario-btn.active { background: #1d4ed8; }
    .lista { display: none; }
    .lista.active { display: block; }
  </style>
  <img src="img/comite.jpg" alt="Logo Comité AEUDJ" class="mx-auto mb-4 h-20 object-contain rounded-full">
</head>
<body class="min-h-screen py-4">
  <main class="container mx-auto px-4 max-w-3xl">

    <!-- BOTONES RÁPIDOS -->
    <div class="sticky top-0 bg-white/90 backdrop-blur rounded-xl p-3 mb-4 shadow">
      <h1 class="text-center font-bold text-gray-800 mb-3">📋 Lista de hoy - <?= date('d/m/Y') ?></h1>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
        <?php foreach ($listado as $h => $personas): ?>
          <?php $cant = count($personas); ?>
          <button class="horario-btn rounded-lg px-3 py-2 text-sm font-semibold" onclick="mostrar('<?= md5($h) ?>')">
            <?= htmlspecialchars($h) ?> <span class="ml-1 bg-white text-blue-700 rounded-full px-2 py-0.5 text-xs"><?= $cant ?></span>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- LISTAS POR HORARIO -->
    <?php foreach ($listado as $h => $personas): ?>
      <section id="<?= md5($h) ?>" class="lista bg-white rounded-xl shadow mb-4 p-4">
        <h2 class="text-lg font-bold text-blue-800 mb-3"><?= htmlspecialchars($h) ?></h2>
        <?php if (empty($personas)): ?>
          <p class="text-gray-500">Nadie seleccionó este horario.</p>
        <?php else: ?>
          <ol class="list-decimal list-inside space-y-1">
            <?php foreach ($personas as $i => $p): ?>
              <li class="flex justify-between items-center border-b pb-1">
                <span class="font-medium"><?= htmlspecialchars($p['nombre']) ?></span>
                <span class="text-xs text-gray-500"><?= date('H:i', strtotime($p['created_at'])) ?></span>
              </li>
            <?php endforeach; ?>
          </ol>
        <?php endif; ?>
      </section>
    <?php endforeach; ?>

  </main>

  <script>
    function mostrar(id) {
      document.querySelectorAll('.lista').forEach(s => s.classList.remove('active'));
      document.querySelectorAll('.horario-btn').forEach(b => b.classList.remove('active'));
      document.getElementById(id).classList.add('active');
      event.target.closest('button').classList.add('active');
    }
    // Mostrar el primer horario al cargar
    window.onload = () => {
      const primero = document.querySelector('.horario-btn');
      if (primero) primero.click();
    };
  </script>
</body>
</html>
