<?php
$host = 'localhost'; // El servidor de la base de datos
$usuario = 'root';   // El usuario por defecto de Laragon/XAMPP
$contrasena = 'soveyda';    // La contraseña por defecto es vacía (¡NO usar en producción!)
$base_de_datos = 'aeudj'; // Reemplaza con el nombre de tu base de datos

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