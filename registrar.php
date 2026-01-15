<?php
session_start();
require_once 'db.php';

$error = '';
$mostrarRegistro = true;

// ===== INICIO DE SESIÓN =====
if ($_POST && isset($_POST['matricula_login'])) {
    $matriculaLogin = trim($_POST['matricula_login']);
    $stmt = $pdo->prepare("SELECT id, nombre, matricula FROM usuarios WHERE matricula = ?");
    $stmt->execute([$matriculaLogin]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nombre']  = $user['nombre'];
        $_SESSION['matricula'] = $user['matricula'];
        header("Location: lista.php");
        exit;
    } else {
        $error = "Matrícula no registrada.";
    }
    $mostrarRegistro = false;
}

// ===== REGISTRO NUEVO =====
if ($_POST && isset($_POST['matricula'])) {
    $matricula   = trim($_POST['matricula'] ?? '');
    $nombre      = trim($_POST['nombre'] ?? '');
    $telefono    = trim($_POST['telefono'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $universidad = trim($_POST['universidad'] ?? '');

    if (strlen($matricula) < 6) {
        $error = "Matrícula muy corta.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Correo inválido.";
    } else {
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE matricula = ? OR email = ?");
        $check->execute([$matricula, $email]);
        if ($check->fetch()) {
            $error = "Matrícula o correo ya registrados.";
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (matricula, nombre, telefono, email, universidad) VALUES (?,?,?,?,?)"
            );
            $stmt->execute([$matricula, $nombre, $telefono, $email, $universidad]);
            $id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $id;
            $_SESSION['nombre']  = $nombre;
            $_SESSION['matricula'] = $matricula;
            setcookie('aeudj_nombre', $nombre, time() + 365 * 24 * 60 * 60, '/');
            header("Location: votar.php");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AEUDJ - Registro / Inicio</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="img/comite.jpg" type="image/jpg">
  <style>body{background:linear-gradient(135deg,#f0f4ff,#e0e7ff);font-family:'Segoe UI',sans-serif}</style>
  <style>
  @media (max-width: 428px) {
    .rounded-2xl { padding: 1rem !important; }
    h2 { font-size: 1.25rem !important; }
    button, select { font-size: .9rem; padding: .5rem .75rem; }
  }
</style>
</head>
<body class="h-full flex items-center justify-center">
  <main class="container mx-auto px-4 max-w-xl">
    <div class="bg-white rounded-2xl shadow-xl p-8">
      <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">🚌 AEUDJ Transporte</h1>
        <h2 class="text-xl font-semibold text-gray-700">👤 Registro / Inicio</h2>
        <p class="text-gray-600 mt-2">Datos reales para evitar duplicados</p>
      </div>

      <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 text-center">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- INICIO DE SESIÓN -->
      <form method="post" class="mb-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Iniciar sesión</h3>
        <input type="text" name="matricula_login" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:outline-none" placeholder="Matrícula (sin guiones)">
        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors mt-2">Entrar</button>
      </form>

      <!-- REGISTRO NUEVO -->
      <?php if ($mostrarRegistro): ?>
        <form method="post" class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-700 mb-2">¿Eres nuevo? Regístrate</h3>
          <input type="text" name="matricula" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:outline-none" placeholder="Matrícula (sin guiones)">
          <input type="text" name="nombre" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:outline-none" placeholder="Nombre completo">
          <input type="tel" name="telefono" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:outline-none" placeholder="Teléfono">
          <input type="email" name="email" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:outline-none" placeholder="Correo electrónico">
          <select name="universidad" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:outline-none">
            <option value="">-- Selecciona tu universidad --</option>
            <option value="UCATECI">UCATECI</option>
            <option value="UNEV">UNFU</option>
            <option value="UAPA">UAPA</option>
            <option value="Otra">Otra</option>
          </select>
          <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors">Registrar</button>
        </form>
      <?php endif; ?>

      <div class="text-center mt-4">
        <a href="lista.php" class="text-blue-600 hover:underline text-sm">Ver lista pública sin registrar</a>
        <div class="text-center mt-3">
  <a href="admin.php" class="text-sm text-gray-600 hover:underline">🛠️ Entrar como administrador</a>
</div>
      </div>
    </div>
  </main>
</body>
</html>