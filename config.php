<?php
// Configuración global
date_default_timezone_set('America/Santo_Domingo');

// Base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', 3316);
define('DB_NAME', 'aeudj');
define('DB_USER', 'root');
define('DB_PASS', 'soveyda');
define('DB_CHARSET', 'utf8mb4');

// Admin credentials
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'aeudj2025');

// Horarios
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
?>