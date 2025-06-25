<?php
require 'db.php';
// Verifica si el formulario fue enviado con método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtiene la dirección ingresada por el usuario y quita espacios en blanco, despues verifica que no este vacia
    $direccion = trim($_POST['direccion']);
    if (empty($direccion)) die("La dirección no puede estar vacía.");

    // Prepara la URL para llamar a la API de geocodificación (Nominatim)
    // urlencode transforma espacios y caracteres especiales para que funcione en una URL
    $url = "https://nominatim.openstreetmap.org/search?"
         . "q=" . urlencode($direccion) . "&format=json&limit=1";

    // Configura un "User-Agent" (requerido por la API de Nominatim)
    $opts = ['http' => ['header' => "User-Agent: Proyecto-QR-App"]];
    $context = stream_context_create($opts);

    // Hace la petición a la API y guarda la respuesta JSON
    $resp = file_get_contents($url, false, $context);

    // Convierte el JSON recibido a un array asociativo en PHP, si no encuentra nada termina elproceso
    $data = json_decode($resp, true);

    if (empty($data)) {
        die("Error: no se encontró esa dirección.");
    }

    // Extrae latitud y longitud 
    $lat = $data[0]['lat'];
    $lon = $data[0]['lon'];

    // Prepara una consulta SQL para insertar los datos en la base
    $stmt = $conn->prepare("INSERT INTO paquetes (direccion, latitud, longitud) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $direccion, $lat, $lon);
    $stmt->execute(); //ejecuta la consulta,y despues cierra la conexion
    $stmt->close();
    $conn->close();

    // Muestra un mensaje de éxito y links para volver o ver el mapa
    echo "✅ Dirección cargada: $direccion con coordenadas ($lat, $lon).";
    echo "<br><a href='cargar.php'>Cargar otra</a> | <a href='rutas.php'>Ver rutas</a>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head><meta charset="utf‑8"><title>Cargar Dirección</title></head>
<body>
  <h2>Cargar Dirección de Entrega</h2>
  <form method="post" action="cargar.php">
    Dirección:<br>
    <input type="text" name="direccion" style="width: 400px;" required>
    <button type="submit">Cargar</button>
  </form>
  <br><a href="rutas.php">Ver rutas</a>
</body>
</html>
