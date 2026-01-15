<?php
session_start();
require_once 'db.php';
require_once 'config.php';

// ✅ Función para ordenar horarios cronológicamente
function horario_a_minutos($horario) {
    $hora = DateTime::createFromFormat('h:i A', $horario);
    return $hora ? (int)$hora->format('H') * 60 + (int)$hora->format('i') : 0;
}

// ✅ Función para verificar si votó 1 hora antes del horario
function votó_1h_antes($horario_seleccionado, $created_at, $fecha_voto) {
    if (empty($created_at)) return false;
    
    // Convertir horario seleccionado a DateTime del día del voto
    $horario_dt = DateTime::createFromFormat('h:i A', $horario_seleccionado);
    if (!$horario_dt) return false;
    
    // Establecer la fecha del día del voto
    $fecha_dt = new DateTime($fecha_voto);
    $horario_dt->setDate(
        (int)$fecha_dt->format('Y'),
        (int)$fecha_dt->format('m'),
        (int)$fecha_dt->format('d')
    );
    
    // Convertir created_at a DateTime
    $voto_dt = new DateTime($created_at);
    
    // Calcular diferencia en horas
    $diferencia = $horario_dt->diff($voto_dt);
    $horas_antes = $diferencia->h + ($diferencia->days * 24);
    
    // Si votó 1 hora o más antes del horario seleccionado
    return $horas_antes >= 1 && $voto_dt < $horario_dt;
}

$ahora = new DateTime();
$hora = (int)$ahora->format('H');

if ($hora >= 22) {
    $hoy = (clone $ahora)->modify('+1 day')->format('Y-m-d');
} else {
    $hoy = $ahora->format('Y-m-d');
}

$fecha_actual = (new DateTime($hoy))->format('d/m/Y');

$stmt = $pdo->prepare("
    SELECT v.horario, u.nombre, u.universidad, v.created_at, COALESCE(v.en_espera, 0) as en_espera
    FROM votos v
    JOIN usuarios u ON u.id = v.usuario_id
    WHERE v.fecha = ?
    ORDER BY v.horario ASC, v.en_espera ASC, v.created_at ASC
");
$stmt->execute([$hoy]);

$raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por horario (separar lista principal y lista de espera)
$listado = [];
$lista_espera = [];
foreach ($raw as $fila) {
    $votó_antes = votó_1h_antes($fila['horario'], $fila['created_at'], $hoy);
    $datos = [
        'nombre' => $fila['nombre'],
        'universidad' => $fila['universidad'],
        'created_at' => $fila['created_at'],
        'votó_1h_antes' => $votó_antes,
        'en_espera' => ($fila['en_espera'] == 1)
    ];
    
    if ($fila['en_espera'] == 1) {
        $lista_espera[$fila['horario']][] = $datos;
    } else {
        $listado[$fila['horario']][] = $datos;
    }
}

// ✅ Ordenar horarios de más temprano a más tarde
uksort($listado, function ($a, $b) {
    return horario_a_minutos($a) <=> horario_a_minutos($b);
});
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Lista por horarios - AEUDJ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="img/comite.jpg" type="image/jpg">
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
    .nombre-card.voto-antes {
      background: #fee2e2;
      border: 2px solid #ef4444;
    }
    .nombre-card.voto-antes .text-gray-800 {
      color: #dc2626;
    }
    .nombre-card.voto-antes .bg-blue-600 {
      background-color: #ef4444;
    }
    @media (max-width: 640px) {
      .card-horario {
        margin-left: 1rem;
        margin-right: 1rem;
      }
    }

    /* ✅ Ocultar menú sticky en móvil al bajar */
    @media (max-width: 640px) {
      .sticky-menu {
        transition: transform 0.3s ease;
      }
      .sticky-menu.hidden {
        transform: translateY(-100%);
      }
      <!--  CSS MÓVIL-FIRST: REDUCCIÓN AUTOMÁTICA  -->
<style>
  /* ========== BASE PARA MÓVILES PEQUEÑOS ========== */
  @media (max-width: 428px) {
    /* --- Contenedor general --- */
    body {
      overflow-x: hidden;          /* anula scroll horizontal */
      font-size: 13px;             /* base de texto más pequeña */
    }
    .container {
      padding-left: .75rem;
      padding-right: .75rem;
    }

    /* --- Títulos --- */
    h1 { font-size: 1.5rem !important; }
    h2 { font-size: 1.25rem !important; }
    h3 { font-size: 1rem !important; }

    /* --- Tarjetas de horario --- */
    .card-horario {
      padding: 1rem !important;           /* menos padding */
      margin-left: 0 !important;
      margin-right: 0 !important;
      border-radius: 1rem !important;
    }

    /* --- Filas de pasajeros --- */
    .nombre-card {
      padding: .5rem .75rem !important;
      font-size: .85rem;
    }
    .nombre-card span {
      font-size: .75rem;
    }

    /* --- Botones / badges --- */
    a.px-4, a.px-3, button.px-4, span.px-4 {
      padding-left: .5rem !important;
      padding-right: .5rem !important;
      font-size: .7rem !important;
    }

    /* --- Sticky menu --- */
    .sticky-menu a.px-3 {
      padding-left: .5rem !important;
      padding-right: .5rem !important;
      font-size: .7rem !important;
    }
    .sticky-menu select {
      font-size: .8rem;
      padding: .5rem .75rem;
    }

    /* --- Números dentro de círculos --- */
    .nombre-card span.w-10 {
      width: 1.5rem !important;
      height: 1.5rem !important;
      font-size: .75rem !important;
    }
  }
</style>
    }
  </style>
</head>
<body class="min-h-screen py-8">
  <main class="container mx-auto px-4 max-w-5xl">

    <!-- TÍTULO -->
    <div class="text-center mb-8">
      <h1 class="text-4xl font-bold text-gray-800 mb-2">📋 Lista de Pasajeros</h1>
      <p class="text-lg text-gray-600">Organizada por horario - <?= $fecha_actual ?></p>
    </div>

    <!-- MENÚ MÓVIL-FIRST CON TRANSICIONES -->
    <?php if (!empty($listado)): ?>
      <div class="sticky-menu sticky top-2 z-20 bg-white/90 backdrop-blur rounded-2xl p-4 mb-6 shadow-lg">
        <h3 class="text-center font-bold text-gray-700 mb-3">Ir al horario</h3>

        <!-- Desplegable para móviles -->
        <div class="md:hidden">
          <select onchange="irAHorario(this.value)" class="w-full px-4 py-3 rounded-xl border-2 border-blue-300 focus:border-blue-500 focus:outline-none transition">
            <option value="">-- Selecciona un horario --</option>
            <?php foreach (array_keys($listado) as $h): ?>
              <option value="#horario-<?= md5($h) ?>"><?= htmlspecialchars($h) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Botones para tablets y escritorio -->
        <div class="hidden md:flex flex-wrap justify-center gap-2">
          <?php foreach (array_keys($listado) as $h): ?>
            <a href="#horario-<?= md5($h) ?>"
               class="bg-blue-600 text-white px-4 py-2 rounded-xl font-medium hover:bg-blue-700 hover:scale-105 transform transition duration-200">
              <?= htmlspecialchars($h) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- LISTA POR HORARIO -->
    <?php if (!empty($listado)): ?>
      <div class="grid gap-8 md:gap-10">
        <?php foreach ($listado as $horario => $personas): ?>
          <div class="card-horario p-6 md:p-8" id="horario-<?= md5($horario) ?>">
            <h2 class="text-2xl font-bold text-blue-800 mb-6 text-center"><?= htmlspecialchars($horario) ?></h2>
            <?php if (empty($personas)): ?>
              <p class="text-center text-gray-500">Nadie seleccionó este horario.</p>
            <?php else: ?>
              <div class="grid gap-3 md:gap-4 max-h-96 overflow-y-auto pr-2">
                <?php foreach ($personas as $i => $p): ?>
                  <div class="nombre-card flex items-center justify-between p-4 <?= !empty($p['votó_1h_antes']) ? 'voto-antes' : '' ?>">
                    <div class="flex items-center space-x-4">
                      <span class="bg-blue-600 text-white text-lg font-bold w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0">
                        <?= $i + 1 ?>
                      </span>
                      <div class="flex items-center space-x-2">
                        <p class="font-semibold text-gray-800 text-lg"><?= htmlspecialchars($p['nombre'] ?? 'Nombre no disponible') ?></p>
                        <?php if (!empty($p['universidad'])): ?>
                          <span class="text-sm text-gray-600 font-medium">(<?= htmlspecialchars($p['universidad']) ?>)</span>
                        <?php endif; ?>
                      </div>
                    </div>
                    <span class="text-sm text-gray-500 font-medium">
                      <?= isset($p['created_at']) ? date('H:i', strtotime($p['created_at'])) : 'Hora no disponible' ?>
                    </span>
                  </div>
                <?php endforeach; ?>
              </div>
              
              <!-- LISTA DE ESPERA -->
              <?php if (!empty($lista_espera[$horario])): ?>
                <div class="mt-6 pt-6 border-t-2 border-yellow-300">
                  <h3 class="text-lg font-bold text-yellow-700 mb-4 text-center">⏳ Lista de Espera</h3>
                  <div class="grid gap-2 md:gap-3">
                    <?php foreach ($lista_espera[$horario] as $i => $p): ?>
                      <div class="nombre-card flex items-center justify-between p-3 bg-yellow-50 border-2 border-yellow-200">
                        <div class="flex items-center space-x-3">
                          <span class="bg-yellow-500 text-white text-sm font-bold w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0">
                            <?= $i + 1 ?>
                          </span>
                          <div class="flex items-center space-x-2">
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($p['nombre'] ?? 'Nombre no disponible') ?></p>
                            <span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded-full font-medium">En espera</span>
                            <?php if (!empty($p['universidad'])): ?>
                              <span class="text-sm text-gray-600 font-medium">(<?= htmlspecialchars($p['universidad']) ?>)</span>
                            <?php endif; ?>
                          </div>
                        </div>
                        <span class="text-xs text-gray-500 font-medium">
                          <?= isset($p['created_at']) ? date('H:i', strtotime($p['created_at'])) : 'Hora no disponible' ?>
                        </span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center">
        <p class="text-gray-600">No hay horarios disponibles para mostrar.</p>
      </div>
    <?php endif; ?>

    <!-- ✅ BOTONES DE CAMBIO -->
    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="bg-white rounded-xl shadow p-4 mb-6 mt-8">
        <h3 class="font-bold text-gray-800 mb-3 text-center">¿Cambios?</h3>
        <div class="flex flex-wrap gap-2 justify-center">
        <a href="cambios.php?tipo=antes" class="bg-orange-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-orange-600 transition-colors">Me fui antes</a>
        <a href="cambios.php?tipo=despues" class="bg-blue-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-blue-600 transition-colors">Me iré después</a>
        <a href="cambios.php?tipo=otros" class="bg-gray-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-gray-700 transition-colors">Voy por otros medios</a>
        </div>
      </div>
    <?php endif; ?>

    <!-- ✅ BOTÓN IMPRIMIR -->
    <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mt-6">
      <?php if (!isset($_GET['visto'])): ?>
        <button onclick="window.print()" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-xl font-semibold hover:bg-gray-300 transition-colors w-full sm:w-auto text-center cursor-pointer">
          🖨️ Imprimir lista
        </button>
      <?php endif; ?>
    </div>

  </main>

  <!-- ✅ Scroll suave con ajuste de desplazamiento -->
  <script>
    function irAHorario(ancla) {
      if (!ancla) return;
      const target = document.querySelector(ancla);
      if (target) {
        const offset = 100; // sube 100px más arriba
        const elementPosition = target.getBoundingClientRect().top + window.pageYOffset;
        window.scrollTo({ top: elementPosition - offset, behavior: 'smooth' });
      }
    }

    document.querySelectorAll('a[href^="#horario-"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        irAHorario(this.getAttribute('href'));
      });
    });

    // ✅ Ocultar menú sticky en móvil al bajar
    const stickyMenu = document.querySelector('.sticky-menu');
    let lastScrollY = window.scrollY;

    window.addEventListener('scroll', () => {
      if (window.innerWidth <= 640) {
        if (window.scrollY > lastScrollY && window.scrollY > 100) {
          stickyMenu.classList.add('hidden');
        } else {
          stickyMenu.classList.remove('hidden');
        }
        lastScrollY = window.scrollY;
      }
    });
  </script>

</body>
</html>