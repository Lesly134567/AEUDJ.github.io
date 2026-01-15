<?php
session_start();
include 'db.php';

// Solo admin
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$val = (int)($_GET['val'] ?? 0);

// Valores permitidos: 0 = No subió, 1 = Subió, 2 = Llegó tarde
if ($val >= 0 && $val <= 2) {
    // Obtener información del voto antes de actualizar
    $stmt = $pdo->prepare("SELECT horario, fecha FROM votos WHERE id = ?");
    $stmt->execute([$id]);
    $voto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($voto) {
        // Actualizar el estado
        $stmt = $pdo->prepare("UPDATE votos SET se_monto = ? WHERE id = ? AND fecha = CURDATE()");
        $stmt->execute([$val, $id]);
        
        // Si se marcó como "No subió" (val = 0), pasar el primero de la lista de espera
        if ($val == 0) {
            // Buscar el primero en lista de espera del mismo horario y fecha
            // Primero intentar con en_espera = 1
            $stmt = $pdo->prepare("
                SELECT id FROM votos 
                WHERE horario = ? AND fecha = ? AND en_espera = 1
                ORDER BY created_at ASC 
                LIMIT 1
            ");
            $stmt->execute([$voto['horario'], $voto['fecha']]);
            $espera = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si no hay con en_espera = 1, buscar los que no están en la lista principal
            // (esto maneja el caso donde en_espera puede ser NULL)
            if (!$espera) {
                // Obtener todos los IDs de la lista principal de este horario
                $stmt = $pdo->prepare("
                    SELECT id FROM votos 
                    WHERE horario = ? AND fecha = ? AND (en_espera = 0 OR en_espera IS NULL)
                    ORDER BY created_at ASC
                ");
                $stmt->execute([$voto['horario'], $voto['fecha']]);
                $lista_principal = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Buscar el primero que no esté en la lista principal
                $stmt = $pdo->prepare("
                    SELECT id FROM votos 
                    WHERE horario = ? AND fecha = ? 
                    ORDER BY created_at ASC
                ");
                $stmt->execute([$voto['horario'], $voto['fecha']]);
                $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($todos as $t) {
                    if (!in_array($t['id'], $lista_principal)) {
                        $espera = $t;
                        break;
                    }
                }
            }
            
            // Si hay alguien en espera, pasarlo a la lista principal
            if ($espera) {
                $stmt = $pdo->prepare("UPDATE votos SET en_espera = 0 WHERE id = ?");
                $stmt->execute([$espera['id']]);
            }
        }
    }
}

header("Location: admin.php");
exit;
?>