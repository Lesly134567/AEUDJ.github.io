<?php
$host = 'localhost'; 
$usuario = 'root';   
$contrasena = 'soveyda';    
$base_de_datos = 'aeudj'; 

try {
   
    $conexion = new PDO("mysql:host=$host;dbname=$base_de_datos;charset=utf8", $usuario, $contrasena);
    
    
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión a la base de datos exitosa.";

} catch (PDOException $e) {
  
    echo "Error de conexión: " . $e->getMessage();
    exit(); 
}

//  $conexion para hacer consultas SQL. 

?>