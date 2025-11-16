<?php
session_start();
include 'db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    if ($user === 'admin' && $pass === 'aeudj2025') {
        $_SESSION['admin'] = true;
        header("Location: admin.php");
        exit;
    }
    $error = "Usuario o clave incorrectos.";
}

if (!isset($_SESSION['admin'])) {
    
    exit('
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Comité - AEUDJ</title>
  <link rel="icon" href="img/comite.jpg" type="img/jpg">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: linear-gradient(135deg, #f0f4ff, #e0e7ff);
      font-family: "Segoe UI", sans-serif;
    }
    .card {
      background: white;
      border-radius: 1.5rem;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    .btn-primary {
      background: linear-gradient(135deg, #3b82f6, #1d4ed8);
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
    }
  </style>
</head>
<body class="h-full flex items-center justify-center">
  <main class="container mx-auto px-4 max-w-sm">
    <div class="card p-8">
      <div class="text-center mb-6">
        <img src="img/logo-comite.png" alt="Logo Comité AEUDJ" class="mx-auto mb-2 h-16 object-contain">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">🛠️ Acceso Comité AEUDJ</h1>
        <p class="text-gray-600">Ingresa tus credenciales</p>
      </div>

      <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 text-center">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" class="space-y-4">
        <input type="text" name="user" placeholder="Usuario" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:outline-none">
        <input type="password" name="pass" placeholder="Clave" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:outline-none">
        <button type="submit" class="btn-primary w-full text-white py-3 rounded-xl font-semibold">Entrar</button>
      </form>
    </div>
  </main>
</body>
</html>');
}

// Horarios y pasajeros de hoy
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
        SELECT v.id AS voto_id, u.nombre, v.se_monto
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
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8">
  <title>Panel Comité - AEUDJ</title>
  <link rel="icon" href="img/logo-comite.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: linear-gradient(135deg, #f0f4ff, #e0e7ff);
      font-family: 'Segoe UI', sans-serif;
    }
    .card {
      background: white;
      border-radius: 1.5rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .btn-si {
      background: linear-gradient(135deg, #10b981, #059669);
    }
    .btn-no {
      background: linear-gradient(135deg, #ef4444, #dc2626);
    }
  </style>
</head>
<body class="min-h-screen py-8">
  <main class="container mx-auto px-4 max-w-5xl">
    <div class="text-center mb-8">
      <img src="img/logo-comite.png" alt="Logo Comité AEUDJ" class="mx-auto mb-2 h-16 object-contain">
      <h1 class="text-3xl font-bold text-gray-800">🛠️ Panel Comité AEUDJ</h1>
    </div>

    <?php if (!$listado): ?>
      <p class="text-center text-gray-500">No hay votos hoy.</p>
    <?php else: ?>
      <div class="grid gap-8 md:gap-10">
        <?php foreach ($listado as $horario => $personas): ?>
          <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
            <h2 class="text-2xl font-bold text-blue-800 mb-6 text-center"><?= htmlspecialchars($horario) ?></h2>
            <?php if (!$personas): ?>
              <p class="text-center text-gray-500">Nadie seleccionó este horario.</p>
            <?php else: ?>
              <div class="grid gap-3 md:gap-4">
                <?php foreach ($personas as $p): ?>
                  <div class="flex items-center justify-between bg-gray-50 rounded-xl p-4">
                    <div class="flex items-center space-x-4">
                      <span class="bg-blue-600 text-white text-sm font-bold w-8 h-8 rounded-full flex items-center justify-center">👤</span>
                      <p class="font-semibold text-gray-800"><?= htmlspecialchars($p['nombre']) ?></p>
                    </div>
                    <div class="flex space-x-2">
                      <?php if ($p['se_monto'] === null): ?>
                        <a href="marcar.php?id=<?= $p['voto_id'] ?>&val=1" class="btn-si text-white px-4 py-2 rounded-lg font-semibold text-sm">Subió</a>
                        <a href="marcar.php?id=<?= $p['voto_id'] ?>&val=0" class="btn-no text-white px-4 py-2 rounded-lg font-semibold text-sm">No subió</a>
                      <?php elseif ($p['se_monto'] == 1): ?>
                        <span class="bg-green-100 text-green-800 px-4 py-2 rounded-lg font-semibold text-sm">✅ Subió</span>
                      <?php else: ?>
                        <span class="bg-red-100 text-red-800 px-4 py-2 rounded-lg font-semibold text-sm">❌ No subió</span>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mt-8">
      <a href="lista.php" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-xl font-semibold hover:bg-gray-300 transition-colors">Ver lista pública</a>
      <a href="no_subieron.php" class="bg-red-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-red-700 transition-colors">Ver quienes no subieron</a>
    </div>
  </main>
</body>

</html>
