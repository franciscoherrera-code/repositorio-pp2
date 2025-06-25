<?php
require 'db.php';

// Ejecuta una consulta para traer latitud, longitud y dirección de cada paquete
$result = $conn->query("SELECT latitud, longitud, direccion FROM paquetes");
$coords = []; // Array para guardar coordenadas (formato [lon, lat])
$pops = []; // Array para guardar las direcciones (para mostrar en el mapa)

// Recorre los resultados y guarda coordenadas y dirección
while ($r = $result->fetch_assoc()) {
    $coords[] = [floatval($r['longitud']), floatval($r['latitud'])];
    $pops[] = $r['direccion'];
}
$conn->close();  //cierra la conexion
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf‑8">
  <title>Ruta Óptima</title>

    <!--hoja de estilos de Leaflet (mapas) -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>#map{height:600px;}</style>
</head>
<body>
  <h2>Ruta Óptima para Entregas</h2>
  <div id="map"></div>

    <!-- Carga la librería Leaflet (mapas) -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>

      // Recupera los arrays de PHP como variables JS, si hay menos de 2 direcciones no deja
    let coords = <?php echo json_encode($coords); ?>;
    let pops = <?php echo json_encode($pops); ?>;

    if (coords.length < 2) {
      alert("Agregá al menos 2 direcciones para calcular una ruta.");
    }

      // Crea el mapa centrado en el primer punto
    const map = L.map('map')
      .setView([coords[0][1], coords[0][0]], 13);

      // Carga el mapa base desde OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap'
    }).addTo(map);

      // Agrega marcadores con popups (etiquetas con dirección)
    coords.forEach((c,i) => {
       L.marker([c[1], c[0]]).addTo(map).bindPopup(pops[i]);
    });

      // Llama a la API de OpenRouteService para obtener la ruta óptima
    fetch("https://api.openrouteservice.org/v2/directions/driving-car/geojson", {
      method: "POST",
      headers: {
        "Authorization": "5b3ce3597851110001cf62482b3be8730d2d44f9813ec5c7ce789385", //API KEY
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ coordinates: coords }) // Envia las coordenadas en formato JSON
    })
    .then(r => r.json())  // Convierte la respuesta en objeto JS y despues dibuja la ruta sobre el mapa usando los datos devueltos
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
