<?php
$conexion = new mysqli("localhost", "root", "", "packtracker_db");

if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}
?>