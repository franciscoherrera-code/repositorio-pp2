<?php
require 'db.php';
$result = $conn->query("SELECT latitud, longitud, direccion FROM paquetes");
$coords = [];
$pops = [];

while ($r = $result->fetch_assoc()) {
    $coords[] = [floatval($r['longitud']), floatval($r['latitud'])];
    $pops[] = $r['direccion'];
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf‑8">
  <title>Ruta Óptima</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>#map{height:600px;}</style>
</head>
<body>
  <h2>Ruta Óptima para Entregas</h2>
  <div id="map"></div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    let coords = <?php echo json_encode($coords); ?>;
    let pops = <?php echo json_encode($pops); ?>;

    if (coords.length < 2) {
      alert("Agregá al menos 2 direcciones para calcular una ruta.");
    }

    const map = L.map('map')
      .setView([coords[0][1], coords[0][0]], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap'
    }).addTo(map);

    coords.forEach((c,i) => {
       L.marker([c[1], c[0]]).addTo(map).bindPopup(pops[i]);
    });

    fetch("https://api.openrouteservice.org/v2/directions/driving-car/geojson", {
      method: "POST",
      headers: {
        "Authorization": "5b3ce3597851110001cf62482b3be8730d2d44f9813ec5c7ce789385",
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ coordinates: coords })
    })
    .then(r => r.json())
    .then(data => {
      L.geoJSON(data, {
        style: { color: 'blue', weight: 5 }
      }).addTo(map);
    })
    .catch(e => alert("Error de ruta: " + e));
  </script>
  <p><a href="cargar.php">Cargar más direcciones</a></p>
</body>
</html>