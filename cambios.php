<?php
session_start();
include 'db.php';
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$tipo = $_GET['tipo'] ?? '';
$userId = $_SESSION['user_id'];

// Obtener fecha actual
$ahora = new DateTime();
$hora = (int)$ahora->format('H');
if ($hora >= 22) {
    $fechaHoy = (clone $ahora)->modify('+1 day')->format('Y-m-d');
} else {
    $fechaHoy = $ahora->format('Y-m-d');
}

// Obtener todos los votos del usuario para la fecha
$stmt = $pdo->prepare("SELECT horario FROM votos WHERE usuario_id = ? AND fecha = ?");
$stmt->execute([$userId, $fechaHoy]);
$votos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($votos)) {
    header("Location: lista.php?error=no_voto");
    exit;
}

// Buscar el horario de "La Vega → Jarabacoa" (vuelta)
$horarioVuelta = null;
foreach ($votos as $voto) {
    if (strpos($voto['horario'], 'La Vega → Jarabacoa') !== false) {
        $horarioVuelta = $voto['horario'];
        break;
    }
}

// Si eligió "antes" o "despues" → validar y redirigir a selector
if ($tipo === 'despues' || $tipo === 'antes') {
    // Solo se puede cambiar si hay un horario de vuelta
    if (!$horarioVuelta) {
        header("Location: lista.php?error=no_horario_vuelta");
        exit;
    }
    
    // Función para convertir horario a minutos
    function horario_a_minutos($horario) {
        $parts = explode(' ', trim($horario));
        if (count($parts) >= 2) {
            $timeStr = $parts[0] . ' ' . $parts[1];
            $hora = DateTime::createFromFormat('h:i A', $timeStr);
            if ($hora) {
                return (int)$hora->format('H') * 60 + (int)$hora->format('i');
            }
        }
        return 0;
    }
    
    // Validar que solo se puede cambiar si es al menos 1 hora antes del horario
    $minutosHorario = horario_a_minutos($horarioVuelta);
    $horaActual = (int)$ahora->format('H') * 60 + (int)$ahora->format('i');
    $diferenciaMinutos = $minutosHorario - $horaActual;
    
    // Si faltan menos de 60 minutos (1 hora), no se puede cambiar
    // Solo validar si el horario es del mismo día (no del día siguiente)
    if ($diferenciaMinutos < 60 && $diferenciaMinutos >= 0) {
        header("Location: lista.php?error=ya_no_se_puede");
        exit;
    }
    
    // Si el horario ya pasó, permitir el cambio (puede ser del día siguiente)
    // Si la diferencia es negativa, significa que el horario ya pasó hoy, pero puede ser válido para mañana
    
    $_SESSION['cambio_tipo'] = $tipo;
    $_SESSION['horario_actual'] = $horarioVuelta;
    header("Location: selector_cambios.php");
    exit;
}

// Si eligió "otros" → eliminar SOLO el horario de "La Vega → Jarabacoa"
if ($tipo === 'otros') {
    if ($horarioVuelta) {
        // Eliminar solo el voto de vuelta
        $pdo->prepare("DELETE FROM votos WHERE usuario_id = ? AND fecha = ? AND horario = ?")
            ->execute([$userId, $fechaHoy, $horarioVuelta]);
    } else {
        // Si no hay horario de vuelta, no hacer nada
        header("Location: lista.php?error=no_horario_vuelta");
        exit;
    }
    
    // Guardar en cambios para registro
    $stmt = $pdo->prepare("INSERT INTO cambios (usuario_id, tipo, nuevo_horario) VALUES (?,?,?)");
    $stmt->execute([$userId, $tipo, null]);
    
    // Notificación a admin
    $msg = "El estudiante {$_SESSION['matricula']} reportó: " . strtoupper($tipo);
    $pdo->prepare("INSERT INTO notificaciones (mensaje) VALUES (?)")->execute([$msg]);
    
    header("Location: gracias.php?cambio=1");
    exit;
}

header("Location: lista.php");
exit;