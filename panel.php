<?php
include 'db.php';
$stmt = $pdo->query("SELECT v.id, u.nombre, v.horario, v.se_monto FROM votos v JOIN usuarios u ON u.id = v.usuario_id WHERE v.fecha = CURDATE() ORDER BY v.horario");
$filas = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<link rel="icon" href="img/comite.jpg" type="img/jpg">
<title>Panel AEUDJ</title>
</head>
<body>
<h1>Panel AEUDJ - Lista de hoy</h1>
<table border="1" cellpadding="6">
<tr><th>Nombre</th><th>Horario</th><th>¿Montó?</th><th>Acción</th></tr>
<?php foreach ($filas as $f): ?>
<tr>
<td><?= htmlspecialchars($f['nombre']) ?></td>
<td><?= $f['horario'] ?></td>
<td><?= $f['se_monto'] === null ? '-' : ($f['se_monto'] ? 'Sí' : 'No') ?></td>
<td>
  <a href="marcar.php?id=<?= $f['id'] ?>&val=1">Marcar Sí</a> |
  <a href="marcar.php?id=<?= $f['id'] ?>&val=0">Marcar No</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>