<?php
session_start();
include 'db.php';

// COOKIE: si ya hay nombre guardado, entra directo
if (isset($_COOKIE['aeudj_nombre'])) {
    $nombre = $_COOKIE['aeudj_nombre'];
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nombre = ?");
    $stmt->execute([$nombre]);
    $user = $stmt->fetch();
    if (!$user) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
        $user_id = $pdo->lastInsertId();
    } else {
        $user_id = $user['id'];
    }
    $_SESSION['user_id'] = $user_id;
    $_SESSION['nombre']  = $nombre;
    header("Location: votar.php");
    exit;
}

// Procesar nuevo registro
if ($_POST) {
    $nombre = trim($_POST['nombre'] ?? '');

    // Verificar si nombre ya existe
    $check = $pdo->prepare("SELECT id FROM usuarios WHERE nombre = ?");
    $check->execute([$nombre]);
    if ($check->fetch()) {
        $error = "Este nombre ya está registrado. Solo se permite una vez.";
    } else {
        // Insertar
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
        $user_id = $pdo->lastInsertId();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['nombre']  = $nombre;
        setcookie('aeudj_nombre', $nombre, time() + 365 * 24 * 60 * 60, '/');
        header("Location: votar.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AEUDJ - Registro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>body{background:linear-gradient(135deg,#f0f4ff,#e0e7ff);font-family:'Segoe UI',sans-serif}</style>
</head>
<body class="h-full flex items-center justify-center">
  <main class="container mx-auto px-4 max-w-md">
    <div class="bg-white rounded-2xl shadow-xl p-8">
      <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">🚌 AEUDJ Transporte</h1>
        <h2 class="text-xl font-semibold text-gray-700">👤 Información del Pasajero</h2>
        <p class="text-gray-600 mt-2">Ingresa tu nombre para continuar</p>
      </div>

      <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 text-center">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_COOKIE['aeudj_nombre'])): ?>
        <div class="text-center">
          <p class="text-amber-600 mb-4">Ya estás registrado como <strong><?= htmlspecialchars($_COOKIE['aeudj_nombre']) ?></strong></p>
          <a href="votar.php" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold">Continuar</a>
        </div>
      <?php else: ?>
        <form method="post" class="space-y-4">
          <input type="text" name="nombre" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:outline-none" placeholder="Nombre completo">
          <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors">Registrar</button>
        </form>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>