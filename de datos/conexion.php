<?php
$host = 'localhost'; 
$usuario = 'root';   
$contrasena = 'soveyda';  
$base_de_datos = 'aeudj'; 

try {
    // 1. Crear el objeto de conexión PDO (DSN)
    $conexion = new PDO("mysql:host=$host;dbname=$base_de_datos;charset=utf8", $usuario, $contrasena);
    
    // 2. Establecer atributos para manejar errores (recomendado)
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión a la base de datos exitosa.";

} catch (PDOException $e) {
    // 3. Mostrar el error si la conexión falla
    echo "Error de conexión: " . $e->getMessage();
    exit(); // Detiene la ejecución si hay un error
}

// Ahora puedes usar la variable $conexion para hacer consultas SQL.
?>