<?php
/*previamente cree la base de datos llamada logistica. en sql:
CREATE TABLE paquetes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    direccion TEXT NOT NULL,
    latitud FLOAT,
    longitud FLOAT
); */

// Datos de conexión al servidor MySQL
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "logistica"; //nombre de la base de datos
// Crea una nueva conexión con MySQL usando mysqli
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
    // Si no hay errores, la conexión queda abierta y lista para usar
?>
