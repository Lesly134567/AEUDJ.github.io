<?php
session_start();
require_once 'db.php';
require_once 'config.php';   // ← para usar ADMIN_USER y ADMIN_PASS

/* ---------- LOGIN ---------- */
if (!isset($_SESSION['admin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user = $_POST['user'] ?? '';
        $pass = $_POST['pass'] ?? '';
        if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
            $_SESSION['admin'] = true;
            header("Location: admin.php");
            exit;
        }
        $error = "Usuario o clave incorrectos.";
    }
    // Mostramos el formulario sin cortar el HTML
    ?>
    <!doctype html>
    <html lang="es" class="h-full">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Login Comité - AEUDJ</title>
      <script src="https://cdn.tailwindcss.com"></script>
      <style>
        body{background:linear-gradient(135deg,#f0f4ff,#e0e7ff);font-family:"Segoe UI",sans-serif}
        .card{background:white;border-radius:1.5rem;box-shadow:0 20px 40px rgba(0,0,0,.1)}
        .btn-primary{background:linear-gradient(135deg,#3b82f6,#1d4ed8);transition:all 0.3s ease}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 10px 25px rgba(59,130,246,.4)}
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
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 text-center"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <form method="post" class="space-y-4">
            <input type="text" name="user" placeholder="Usuario" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:outline-none">
            <input type="password" name="pass" placeholder="Clave" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:outline-none">
            <button type="submit" class="btn-primary w-full text-white py-3 rounded-xl font-semibold">Entrar</button>
          </form>
        </div>
      </main>
    </body>
    </html>
    <?php
    exit;
}

/* ---------- PANEL ---------- */
$fechaHoy = date('Y-m-d');
$horarios = $pdo->query("SELECT DISTINCT horario FROM votos WHERE fecha = '$fechaHoy' ORDER BY horario ASC")->fetchAll(PDO::FETCH_COLUMN);

$listado = [];
foreach ($horarios as $h) {
    $stmt = $pdo->prepare("
        SELECT v.id AS voto_id, u.matricula, u.nombre, u.telefono, u.email, u.universidad, v.horario, v.se_monto, v.created_at, v.en_espera,
               (SELECT COUNT(*) FROM cambios c WHERE c.usuario_id = u.id AND DATE(c.created_at) = ? AND c.tipo IN ('antes', 'despues')) as tiene_cambios
        FROM votos v
        JOIN usuarios u ON u.id = v.usuario_id
        WHERE v.fecha = ? AND v.horario = ? AND (v.en_espera = 0 OR v.en_espera IS NULL)
        ORDER BY v.created_at ASC
    ");
    $stmt->execute([$fechaHoy, $fechaHoy, $h]);
    $listado[$h] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8">
  <title>Panel Comité - AEUDJ</title>
  <link rel="icon" href="img/comite.jpg" type="image/jpg">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{background:linear-gradient(135deg,#f0f4ff,#e0e7ff);font-family:'Segoe UI',sans-serif}
    .card{background:white;border-radius:1.5rem;box-shadow:0 10px 30px rgba(0,0,0,.1)}
    .btn-si{background:linear-gradient(135deg,#10b981,#059669)}
    .btn-no{background:linear-gradient(135deg,#ef4444,#dc2626)}
  </style>
</head>
<body class="min-h-screen py-8">
  <main class="container mx-auto px-4 max-w-5xl">
    <div class="text-center mb-8">
      <img src="img/comite.jpg" alt="Logo Comité AEUDJ" class="mx-auto mb-2 h-16 object-contain">
      <h1 class="text-3xl font-bold text-gray-800"> Panel Comité AEUDJ</h1>
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
              <div class="grid gap-3 md:gap-4 max-h-96 overflow-y-auto pr-2">
                <?php foreach ($personas as $p): ?>
                  <div class="flex items-center justify-between bg-gray-50 rounded-xl p-4 <?= !empty($p['tiene_cambios']) ? 'border-l-4 border-orange-500' : '' ?>">
                    <div class="flex items-center space-x-4">
                      <span class="bg-blue-600 text-white text-sm font-bold w-8 h-8 rounded-full flex items-center justify-center">👤</span>
                      <div>
                        <p class="font-semibold text-gray-800">
                          <?= htmlspecialchars($p['nombre']) ?>
                          <?php if (!empty($p['tiene_cambios'])): ?>
                            <span class="ml-2 text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">🔄 Cambió horario</span>
                          <?php endif; ?>
                        </p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($p['matricula']) ?> · <?= htmlspecialchars($p['telefono']) ?> · <?= htmlspecialchars($p['email']) ?></p>
                      </div>
                    </div>
                    <div class="flex space-x-2">
                      <?php if ($p['se_monto'] === null): ?>
                        <a href="marcar.php?id=<?= $p['voto_id'] ?>&val=1" class="btn-si text-white px-4 py-2 rounded-lg font-semibold text-sm">Subió</a>
                        <a href="marcar.php?id=<?= $p['voto_id'] ?>&val=0" class="btn-no text-white px-4 py-2 rounded-lg font-semibold text-sm">No subió</a>
                      <?php elseif ($p['se_monto'] == 1): ?>
                        <span class="bg-green-100 text-green-800 px-4 py-2 rounded-lg font-semibold text-sm">✅ Subió</span>
                      <?php elseif ($p['se_monto'] == 2): ?>
                        <span class="bg-orange-100 text-orange-800 px-4 py-2 rounded-lg font-semibold text-sm">⏰ Llegó tarde</span>
                      <?php else: ?>
                        <span class="bg-red-100 text-red-800 px-4 py-2 rounded-lg font-semibold text-sm">❌ No subió</span>
                        <a href="marcar_tarde.php?id=<?= $p['voto_id'] ?>" class="bg-orange-500 text-white px-4 py-2 rounded-lg font-semibold text-sm hover:bg-orange-600 transition">⏰ Llegó tarde</a>
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

    <!-- LISTA DE ESPERA -->
    <?php
    $stmt_espera = $pdo->prepare("
        SELECT v.id AS voto_id, u.matricula, u.nombre, u.telefono, u.email, u.universidad, v.horario, v.created_at
        FROM votos v
        JOIN usuarios u ON u.id = v.usuario_id
        WHERE v.fecha = ? AND v.en_espera = 1
        ORDER BY v.horario ASC, v.created_at ASC
    ");
    $stmt_espera->execute([$fechaHoy]);
    $lista_espera = $stmt_espera->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <?php if (!empty($lista_espera)): ?>
      <div class="bg-yellow-50 border-2 border-yellow-300 rounded-2xl shadow-xl p-6 md:p-8 mt-8">
        <h2 class="text-2xl font-bold text-yellow-800 mb-6 text-center">⏳ Lista de Espera</h2>
        <div class="grid gap-3 md:gap-4 max-h-96 overflow-y-auto pr-2">
          <?php foreach ($lista_espera as $p): ?>
            <div class="flex items-center justify-between bg-yellow-100 border border-yellow-300 rounded-xl p-4">
              <div class="flex items-center space-x-4">
                <span class="bg-yellow-500 text-white text-sm font-bold w-8 h-8 rounded-full flex items-center justify-center">⏳</span>
                <div>
                  <p class="font-semibold text-gray-800">
                    <?= htmlspecialchars($p['nombre']) ?>
                    <span class="ml-2 text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded-full">En espera</span>
                  </p>
                  <p class="text-xs text-gray-500"><?= htmlspecialchars($p['matricula']) ?> · <?= htmlspecialchars($p['telefono']) ?> · <?= htmlspecialchars($p['email']) ?></p>
                  <p class="text-xs text-yellow-700 font-medium mt-1">Horario: <?= htmlspecialchars($p['horario']) ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mt-8">
      <a href="lista.php" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-xl font-semibold hover:bg-gray-300 transition-colors">Ver lista pública</a>
      <a href="no_subieron.php" class="bg-red-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-red-700 transition-colors">Ver quienes no subieron</a>
    </div>
  </main>
</body>
</html>
