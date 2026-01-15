<?php
session_start();
?>
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmación - AEUDJ</title>
  <meta http-equiv="refresh" content="2;url=lista.php?visto=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="img/comite.jpg" type="image/jpg">
  <style>
    body {
      background: linear-gradient(135deg, #f0f4ff, #e0e7ff);
      font-family: 'Segoe UI', sans-serif;
    }
    .card {
      background: white;
      border-radius: 1.5rem;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    .btn-primary {
      background: linear-gradient(135deg, #10b981, #059669);
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
    }
  </style>
</head>
<body class="h-full flex items-center justify-center">
  <main class="container mx-auto px-4 max-w-md">
    <div class="card p-8 text-center">
      <div class="text-6xl mb-4">✅</div>

      <?php if (isset($_GET['ya_votado'])): ?>
        <p class="text-amber-600 mb-4">Ya habías confirmado tu asistencia hoy.</p>
      <?php endif; ?>
      
      <?php if (isset($_GET['bloqueado'])): ?>
  <p class="text-red-600 mb-4">⏰ El tiempo para confirmar ha terminado (22:00).</p>
<?php endif; ?>

      <h1 class="text-3xl font-bold text-gray-800 mb-2">Confirmación registrada</h1>
      <p class="text-lg text-gray-700 mb-6">
        Gracias, <span class="font-bold text-blue-600"><?= htmlspecialchars($_SESSION['nombre']) ?></span>.
      </p>
      <p class="text-base text-amber-700 bg-amber-50 px-4 py-3 rounded-lg border border-amber-200 mb-6">
        ⏰ <strong>Recuerda estar 10 min antes</strong>
      </p>

      <a href="lista.php?visto=1" class="btn-primary text-white px-8 py-3 rounded-xl font-semibold inline-block">
        Ver lista de pasajeros
      </a>

      <p class="text-sm text-gray-500 mt-4">Serás redirigido en 2 segundos...</p>
    </div>
  </main>
</body>
</html>