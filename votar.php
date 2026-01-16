<?php
session_start();
require_once 'db.php';
require_once 'config.php';

// ✅ Cargar los horarios desde config.php
$transportSchedules = $transportSchedules ?? [];

// Si no hay sesión, redirigir al registro
if (!isset($_SESSION['user_id'])) {
  header("Location: registrar.php");
  exit;
}

// Fecha del ciclo: si ya pasaron las 22:00, permitir votar para MAÑANA
$ahora = new DateTime();
$hora = (int)$ahora->format('H');

if ($hora >= 22) {
    $cycleDateStr = (clone $ahora)->modify('+1 day')->format('Y-m-d');
} else {
    $cycleDateStr = $ahora->format('Y-m-d');
}

// ---- BLOQUEO: solo después de 21:50 (10 min antes de 22:00) ----


// Verificar si ya votó hoy
$check = $pdo->prepare("SELECT 1 FROM votos WHERE usuario_id = ? AND fecha = ? LIMIT 1");
$check->execute([$_SESSION['user_id'], $cycleDateStr]);
if ($check->fetch()) {
    header("Location: gracias.php?ya_votado=1");
    exit;
}
$transportSchedules = [
  ["time" => "7:00 AM",  "route" => "Jarabacoa → La Vega", "fullText" => "7:00 AM Jarabacoa → La Vega"],
  ["time" => "9:00 AM",  "route" => "Jarabacoa → La Vega", "fullText" => "9:00 AM Jarabacoa → La Vega"],
  ["time" => "12:10 PM", "route" => "La Vega → Jarabacoa", "fullText" => "12:10 PM La Vega → Jarabacoa"],
  ["time" => "1:00 PM",  "route" => "Jarabacoa → La Vega", "fullText" => "1:00 PM Jarabacoa → La Vega"],
  ["time" => "2:15 PM",  "route" => "La Vega → Jarabacoa", "fullText" => "2:15 PM La Vega → Jarabacoa"],
  ["time" => "3:00 PM",  "route" => "Jarabacoa → La Vega", "fullText" => "3:00 PM Jarabacoa → La Vega"],
  ["time" => "4:10 PM",  "route" => "La Vega → Jarabacoa", "fullText" => "4:10 PM La Vega → Jarabacoa"],
  ["time" => "5:00 PM",  "route" => "Jarabacoa → La Vega", "fullText" => "5:00 PM Jarabacoa → La Vega"],
  ["time" => "6:00 PM",  "route" => "La Vega → Jarabacoa", "fullText" => "6:00 PM La Vega → Jarabacoa"],
  ["time" => "8:00 PM",  "route" => "La Vega → Jarabacoa", "fullText" => "8:00 PM La Vega → Jarabacoa"],
  ["time" => "10:00 PM", "route" => "La Vega → Jarabacoa", "fullText" => "10:00 PM La Vega → Jarabacoa"]
];

// Procesar voto
if ($_POST) {
    $horarios = $_POST['horarios'] ?? [];
    $idas = 0;
    $vueltas = 0;

    foreach ($horarios as $h) {
        if (str_contains($h, 'Jarabacoa → La Vega')) $idas++;
        if (str_contains($h, 'La Vega → Jarabacoa')) $vueltas++;
    }

    if ($idas > 1 || $vueltas > 1) {
        header("Location: votar.php?error=max_1_por_direccion");
        exit;
    }

    foreach ($horarios as $horario) {
        $stmt = $pdo->prepare("INSERT INTO votos (usuario_id, horario, fecha) VALUES (?,?,?)");
        $stmt->execute([$_SESSION['user_id'], $horario, $cycleDateStr]);
    }

    header("Location: gracias.php");
    exit;
}
?>

<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Selector de Horarios - AEUDJ</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="img/comite.jpg" type="image/jpg">
  <style>
    .time-slot { transition: all 0.3s ease; cursor: pointer; }
    .time-slot:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
    .time-slot.selected { background: linear-gradient(135deg, #10b981, #059669); border-color: #059669; }
    .checkmark { transition: all 0.3s ease; }
    .checkmark.selected { transform: scale(1.2); }
    .day-header { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .save-btn { background: linear-gradient(135deg, #8b5cf6, #7c3aed); transition: all 0.3s ease; }
    .save-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(139,92,246,0.4); }
    .disabled { opacity: 0.4; cursor: not-allowed; }
  </style>
</head>
<body class="h-full bg-gradient-to-br from-blue-50 to-indigo-100 font-sans">
<main class="container mx-auto px-4 py-8 max-w-6xl">
  <header class="text-center mb-8">
    <h1 class="text-4xl font-bold text-gray-800 mb-2">Selector de Horarios</h1>
    <p class="text-lg text-gray-600">Selecciona <strong>un viaje de ida</strong> y <strong>un viaje de vuelta</strong></p>
  </header>

  <form method="post" class="bg-white rounded-2xl shadow-xl p-6 mb-6" id="horarioForm">
    <div class="day-header text-white text-center py-4 rounded-lg font-bold text-lg mb-6">
      <div class="text-2xl mb-1">📅</div>
      <div>Horarios de Hoy - <?= date('l') ?></div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($transportSchedules as $s): ?>
        <?php
          $routeIcon = str_contains($s['route'], 'Jarabacoa → La Vega') ? '🚌➡️' : '⬅️🚌';
          $direction = str_contains($s['route'], 'Jarabacoa → La Vega') ? 'ida' : 'vuelta';
        ?>
        <div class="time-slot bg-gray-50 border-2 border-gray-200 rounded-lg p-4 text-center hover:shadow-lg"
             data-direction="<?= $direction ?>"
             onclick="toggleSlot(this, '<?= $s['fullText'] ?>', '<?= $direction ?>')">
          <div class="text-lg mb-2"><?= $routeIcon ?></div>
          <div class="text-lg font-bold text-gray-800 mb-1"><?= $s['time'] ?></div>
          <div class="text-sm text-gray-600 mb-3"><?= $s['route'] ?></div>
          <div class="checkmark text-3xl">⭕</div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-6">
      <button type="submit" class="save-btn text-white px-8 py-3 rounded-xl font-semibold text-lg shadow-lg">
        Confirmar Selección
      </button>
    </div>
    <div id="horariosSeleccionados"></div>
  </form>

  <div id="status-message" class="text-center text-sm font-medium mt-4"></div>
</main>

<script>
  let seleccionados = [];

  function toggleSlot(el, fullText, direction) {
    seleccionados = seleccionados.filter(h => !h.includes(direction === 'ida' ? 'Jarabacoa → La Vega' : 'La Vega → Jarabacoa'));
    document.querySelectorAll('.time-slot').forEach(slot => {
      if (slot.dataset.direction === direction) {
        slot.classList.remove('selected');
        slot.querySelector('.checkmark').textContent = '⭕';
        slot.classList.remove('disabled');
        slot.style.opacity = '1';
        slot.style.cursor = 'pointer';
      }
    });

    el.classList.add('selected');
    el.querySelector('.checkmark').textContent = '✅';
    seleccionados.push(fullText);
    actualizarCamposOcultos();

    document.querySelectorAll('.time-slot').forEach(slot => {
      if (slot.dataset.direction === direction && !slot.classList.contains('selected')) {
        slot.classList.add('disabled');
        slot.style.opacity = '0.4';
        slot.style.cursor = 'not-allowed';
      }
    });

    const statusEl = document.getElementById('status-message');
    statusEl.textContent = `✅ Viaje de ${direction === 'ida' ? 'ida' : 'vuelta'} seleccionado`;
    statusEl.className = 'mt-4 text-sm font-medium text-green-600';
    setTimeout(() => { statusEl.textContent = ''; statusEl.className = ''; }, 2000);
  }

  function actualizarCamposOcultos() {
    const container = document.getElementById('horariosSeleccionados');
    container.innerHTML = '';
    seleccionados.forEach(horario => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'horarios[]';
      input.value = horario;
      container.appendChild(input);
    });
  }
</script>
</body>
</html>
