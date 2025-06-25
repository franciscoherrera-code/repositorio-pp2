<?php
require_once(__DIR__ . "/../conexion.php");
if (session_status() === PHP_SESSION_NONE) session_start();

$codigo = '';

// Capturar código por GET o POST
if (isset($_GET['codigo'])) {
    $codigo = $conexion->real_escape_string($_GET['codigo']);
} elseif (isset($_POST['codigo'])) {
    $codigo = $conexion->real_escape_string($_POST['codigo']);
}
?>

<h3>Rastrear paquete</h3>
<form method="post" action="">
    <div class="mb-3">
        <label for="codigo">Código de seguimiento</label>
        <input type="text" name="codigo" id="codigo" class="form-control" placeholder="Ej: PKG-2025-00017" value="<?= htmlspecialchars($codigo) ?>" required>
    </div>
    <button type="submit" name="buscar_codigo" class="btn btn-secondary">Buscar</button>
</form>

<?php if (isset($_GET['codigo'])): ?>
    <div class="alert alert-success mt-4">
        🎉 <strong>¡Tu paquete ha sido registrado con éxito!</strong><br>
        A continuación se muestra la información del envío.
    </div>
<?php endif; ?>

<?php
if (!empty($codigo)) {
    $resultado = $conexion->query("SELECT * FROM envios WHERE codigo_seguimiento = '$codigo'");

    if ($resultado && $resultado->num_rows > 0) {
        $envio = $resultado->fetch_assoc();
        $lat_origen = floatval($envio['lat_origen']);
$lon_origen = floatval($envio['lon_origen']);
$lat_destino = floatval($envio['latitud']);
$lon_destino = floatval($envio['longitud']);
?>
        <div class="card mt-4" id="info-paquete">
            <div id="mapa-ruta" class="mt-4" style="height: 400px;"></div>

            <div class="card-header d-flex justify-content-between align-items-center">
                <span>📦 Código: <?= htmlspecialchars($envio['codigo_seguimiento']) ?></span>
                <button onclick="imprimirPaquete()" class="btn btn-sm btn-outline-primary">🖨️ Imprimir</button>
            </div>
            <div class="card-body">
                <p><strong>Nombre del destinatario:</strong> <?= htmlspecialchars($envio['nombre_destinatario']) ?></p>
                <p><strong>Teléfono:</strong> <?= htmlspecialchars($envio['telefono']) ?></p>
                <p><strong>Ruta:</strong> <?= htmlspecialchars($envio['origen']) ?> ➝ <?= htmlspecialchars($envio['destino']) ?></p>
                <p><strong>Destino:</strong> <?= htmlspecialchars($envio['destino']) ?></p>
                <p><strong>Peso:</strong> <?= $envio['peso']; ?> kg</p>
                <p><strong>Prioridad:</strong> <?= $envio['prioridad'] === 'Urgente' ? '🚨 Urgente' : 'Normal'; ?></p>
                <p><strong>Estado actual:</strong> <?= $envio['estado']; ?></p>
                <p><strong>Fecha de envío:</strong> <?= $envio['fecha_envio']; ?></p>
            </div>
        </div>

        <script>
            function imprimirPaquete() {
                const contenido = document.getElementById("info-paquete").innerHTML;
                const ventana = window.open("", "", "height=600,width=800");
                ventana.document.write("<html><head><title>Seguimiento de paquete</title>");
                ventana.document.write("<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>");
                ventana.document.write("</head><body class='p-4'>" + contenido + "</body></html>");
                ventana.document.close();
                ventana.print();
            }
        </script>
<?php
    } else {
        echo "<div class='alert alert-warning mt-4'>🚫 No se encontró ningún envío con ese código.</div>";
    }
}
?>
<script>
const coordenadas = {
  origen: [<?= $lat_origen ?>, <?= $lon_origen ?>],
  destino: [<?= $lat_destino ?>, <?= $lon_destino ?>]
};
</script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

<script>
const map = L.map('mapa-ruta').setView(coordenadas.origen, 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

const apiKey = '5b3ce3597851110001cf62489b684e22274d469c933570236c591413'; //  la key de la api de OpenRouteService no olvidar --

fetch('https://api.openrouteservice.org/v2/directions/driving-car', {
  method: 'POST',
  headers: {
    'Authorization': apiKey,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    coordinates: [
      [coordenadas.origen[1], coordenadas.origen[0]],
      [coordenadas.destino[1], coordenadas.destino[0]]
    ]
  })
})
.then(res => res.json())
.then(data => {
  const coords = data.features[0].geometry.coordinates.map(c => [c[1], c[0]]);
  L.polyline(coords, { color: 'blue', weight: 4 }).addTo(map);
  map.fitBounds(coords);

  // Mostrar distancia y duración
  const distancia = (data.features[0].properties.summary.distance / 1000).toFixed(2);
  const duracionMin = Math.round(data.features[0].properties.summary.duration / 60);
  const info = document.createElement('div');
  info.className = "mt-2 alert alert-info";
  info.innerHTML = `🛣️ Distancia estimada: <strong>${distancia} km</strong><br>⏱️ Tiempo estimado: <strong>${duracionMin} min</strong>`;
  document.getElementById('mapa-ruta').after(info);
})
.catch(error => {
  console.error("Falló la solicitud a ORS:", error);
});


</script>