<?php
session_start();
include 'db.php';
require_once 'config.php';

if (!isset($_SESSION['cambio_tipo']) || !isset($_SESSION['horario_actual'])) {
    header("Location: lista.php");
    exit;
}

$tipo = $_SESSION['cambio_tipo'];
$horarioActual = $_SESSION['horario_actual'];

// Validar que el horario actual sea de "La Vega → Jarabacoa" (vuelta)
// Solo se pueden cambiar horarios de vuelta
if (strpos($horarioActual, 'La Vega → Jarabacoa') === false) {
    unset($_SESSION['cambio_tipo']);
    unset($_SESSION['horario_actual']);
    header("Location: lista.php?error=solo_vuelta");
    exit;
}

// Obtener fecha actual
$ahora = new DateTime();
$hora = (int)$ahora->format('H');
if ($hora >= 22) {
    $fechaHoy = (clone $ahora)->modify('+1 day')->format('Y-m-d');
} else {
    $fechaHoy = $ahora->format('Y-m-d');
}

// Función para convertir horario a minutos
function horario_a_minutos($horario) {
    // Extraer solo la hora del formato "7:00 AM Jarabacoa → La Vega" o "7:00 AM"
    $parts = explode(' ', trim($horario));
    if (count($parts) >= 2) {
        $timeStr = $parts[0] . ' ' . $parts[1];
        $hora = DateTime::createFromFormat('h:i A', $timeStr);
        if ($hora) {
            return (int)$hora->format('H') * 60 + (int)$hora->format('i');
        }
    }
    // Si no funciona, intentar solo con el formato de tiempo
    $hora = DateTime::createFromFormat('h:i A', trim($horario));
    return $hora ? (int)$hora->format('H') * 60 + (int)$hora->format('i') : 0;
}

// Obtener minutos del horario actual
$minutosActual = horario_a_minutos($horarioActual);

// Obtener minutos de la hora actual para validar horarios pasados
$horaActual = (int)$ahora->format('H') * 60 + (int)$ahora->format('i');

// Filtrar horarios según el tipo (excluyendo el mismo horario)
// SOLO mostrar horarios de "La Vega → Jarabacoa" para ambos casos
$horariosDisponibles = [];
foreach ($transportSchedules as $schedule) {
    // SOLO incluir horarios de La Vega → Jarabacoa (vuelta) para ambos casos
    if (strpos($schedule['route'], 'La Vega → Jarabacoa') === false) {
        continue;
    }
    
    $minutosHorario = horario_a_minutos($schedule['fullText']);
    
    // Para "antes": solo horarios estrictamente anteriores al actual Y que no hayan pasado ya
    // Validar que el horario no haya pasado (solo si es del mismo día)
    $horarioYaPaso = ($minutosHorario < $horaActual);
    
    if ($tipo === 'antes' && $minutosHorario < $minutosActual && !$horarioYaPaso) {
        $horariosDisponibles[] = $schedule['fullText'];
    } 
    // Para "despues": solo horarios estrictamente posteriores al actual
    elseif ($tipo === 'despues' && $minutosHorario > $minutosActual) {
        $horariosDisponibles[] = $schedule['fullText'];
    }
    // No incluir el mismo horario (minutosHorario == minutosActual)
}

// Ordenar horarios
usort($horariosDisponibles, function($a, $b) {
    return horario_a_minutos($a) <=> horario_a_minutos($b);
});

if ($_POST && !empty($_POST['nuevo_horario'])) {
    $nuevo = $_POST['nuevo_horario'];
    $userId = $_SESSION['user_id'];

    // Actualizar SOLO el voto de "La Vega → Jarabacoa" (vuelta) en la tabla votos
    // Actualizar el horario y resetear se_monto si estaba marcado
    $stmt = $pdo->prepare("UPDATE votos SET horario = ?, se_monto = NULL WHERE usuario_id = ? AND fecha = ? AND horario = ?");
    $stmt->execute([$nuevo, $userId, $fechaHoy, $horarioActual]);

    // Guardar en cambios para registro
    $pdo->prepare("INSERT INTO cambios (usuario_id, tipo, nuevo_horario) VALUES (?,?,?)")
        ->execute([$userId, $tipo, $nuevo]);

    $msg = "El estudiante {$_SESSION['matricula']} cambió para " . ($tipo === 'antes' ? 'antes' : 'después') . ": $nuevo";
    $pdo->prepare("INSERT INTO notificaciones (mensaje) VALUES (?)")->execute([$msg]);

    unset($_SESSION['cambio_tipo']);
    unset($_SESSION['horario_actual']);
    // Redirigir a la lista para que vea el cambio actualizado
    header("Location: lista.php?cambio=1");
    exit;
}
?>
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <title>Seleccionar nuevo horario</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>body{background:linear-gradient(135deg,#f0f4ff,#e0e7ff);font-family:'Segoe UI',sans-serif}</style>
</head>
<body class="h-full flex items-center justify-center">
  <main class="container mx-auto px-4 max-w-md">
    <div class="bg-white rounded-2xl shadow-xl p-8">
      <h2 class="text-2xl font-bold text-center mb-4">
        Selecciona un horario <?= $tipo === 'antes' ? 'anterior' : 'posterior' ?>
      </h2>
      <p class="text-center text-gray-600 mb-4">Tu horario actual: <strong><?= htmlspecialchars($horarioActual) ?></strong></p>
      
      <?php if (empty($horariosDisponibles)): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-xl mb-4 text-center">
          No hay horarios <?= $tipo === 'antes' ? 'anteriores' : 'posteriores' ?> disponibles.
        </div>
        <a href="lista.php" class="block w-full bg-gray-200 text-gray-800 py-3 rounded-xl font-semibold hover:bg-gray-300 transition-colors text-center">Volver a la lista</a>
      <?php else: ?>
        <form method="post" class="space-y-4">
          <select name="nuevo_horario" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:outline-none">
            <option value="">-- Selecciona un horario --</option>
            <?php foreach ($horariosDisponibles as $h): ?>
              <option value="<?= htmlspecialchars($h) ?>"><?= htmlspecialchars($h) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700">Guardar cambio</button>
        </form>
        <a href="lista.php" class="block w-full mt-4 bg-gray-200 text-gray-800 py-3 rounded-xl font-semibold hover:bg-gray-300 transition-colors text-center">Cancelar</a>
      <?php endif; ?>
      </div>
  </main>
</body>
</html>