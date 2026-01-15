<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

$matricula = $_POST['matricula'] ?? '';
$horarios = $_POST['horarios'] ?? [];

if ($matricula && count($horarios) == 4) {
    $user = $pdo->prepare("SELECT id FROM usuarios WHERE matricula = ?")->fetch();
    if ($user) {
        $userId = $user['id'];
        $fecha = date('Y-m-d');
        foreach ($horarios as $h) {
            $pdo->prepare("INSERT INTO votos (usuario_id, horario, fecha) VALUES (?,?,?)")->execute([$userId, $h, $fecha]);
        }
        header("Location: admin.php?extra=1");
        exit;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Votar extra - AEUDJ</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen py-8 bg-gradient-to-br from-blue-50 to-indigo-100">
  <main class="container mx-auto px-4 max-w-2xl">
    <h1 class="text-2xl font-bold mb-6 text-center">Votar 4 horarios por matrícula</h1>
    <form method="post" class="bg-white rounded-xl shadow p-6 space-y-4">
      <input type="text" name="matricula" placeholder="Matrícula" required class="w-full px-4 py-3 border rounded-xl">
      <p class="text-sm text-gray-600">Selecciona 2 ida y 2 vuelta (mañana y tarde)</p>
      <div class="grid grid-cols-2 gap-4">
        <?php
        $schedules = [
            "7:00 AM Jarabacoa → La Vega",
            "9:00 AM Jarabacoa → La Vega",
            "12:10 PM La Vega → Jarabacoa",
            "1:00 PM Jarabacoa → La Vega",
            "2:15 PM La Vega → Jarabacoa",
            "3:00 PM Jarabacoa → La Vega",
            "4:10 PM La Vega → Jarabacoa",
            "5:00 PM Jarabacoa → La Vega",
            "6:00 PM La Vega → Jarabacoa",
            "8:00 PM La Vega → Jarabacoa",
            "10:00 PM La Vega → Jarabacoa"
        ];
        foreach ($schedules as $s): ?>
          <label class="flex items-center space-x-2">
            <input type="checkbox" name="horarios[]" value="<?= $s ?>">
            <span><?= $s ?></span>
          </label>
        <?php endforeach; ?>
      </div>
      <button type="submit" class="w-full bg-purple-600 text-white py-3 rounded-xl font-semibold">Guardar</button>
    </form>
  </main>
</body>
</html>