<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $direccion = trim($_POST['direccion']);
    if (empty($direccion)) die("La dirección no puede estar vacía.");

    $url = "https://nominatim.openstreetmap.org/search?"
         . "q=" . urlencode($direccion) . "&format=json&limit=1";

    $opts = ['http' => ['header' => "User-Agent: Proyecto-QR-App"]];
    $context = stream_context_create($opts);
    $resp = file_get_contents($url, false, $context);
    $data = json_decode($resp, true);

    if (empty($data)) {
        die("Error: no se encontró esa dirección.");
    }

    $lat = $data[0]['lat'];
    $lon = $data[0]['lon'];

    $stmt = $conn->prepare("INSERT INTO paquetes (direccion, latitud, longitud) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $direccion, $lat, $lon);
    $stmt->execute();
    $stmt->close();
    $conn->close();

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