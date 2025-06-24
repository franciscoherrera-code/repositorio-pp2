<?php
require_once(__DIR__ . "/../conexion.php");
if (session_status() === PHP_SESSION_NONE) session_start();

// Función segura para obtener la parada estimada
function obtener_parada_estimada($conexion, $destino, $estado) {
    switch ($estado) {
        case 'En preparación': $orden = 1; break;
        case 'En camino': $orden = 2; break;
        case 'Entregado': $orden = 99; break;
        default: return ['mensaje' => '❌ Cancelado'];
    }

    $stmt = $conexion->prepare("SELECT parada FROM rutas_virtuales WHERE ciudad_destino = ? AND orden = ? LIMIT 1");
    if (!$stmt) return ['mensaje' => '❌ Error de consulta'];
    $stmt->bind_param("si", $destino, $orden);
    $stmt->execute();
    $parada = null;
    $stmt->bind_result($parada);
    
    if ($stmt->fetch()) {
        return ['mensaje' => $parada];
    } else {
        return ['mensaje' => '⏳ Ruta desconocida'];
    }
}
?>

<h3>Rastrear paquete</h3>
<form method="post" action="">
    <div class="mb-3">
        <label for="codigo">Código de seguimiento</label>
        <input type="text" name="codigo" id="codigo" class="form-control" placeholder="Ej: 12" required>
    </div>
    <button type="submit" name="buscar_codigo" class="btn btn-secondary">Buscar</button>
</form>

<?php
if (isset($_POST['buscar_codigo'])) {
    $codigo = $conexion->real_escape_string($_POST['codigo']);
    $resultado = $conexion->query("SELECT * FROM envios WHERE id = '$codigo'");

    if ($resultado && $resultado->num_rows > 0) {
        $envio = $resultado->fetch_assoc();
        $ubicacion_data = obtener_parada_estimada($conexion, $envio['destino'], $envio['estado']);
        $ubicacion = $ubicacion_data['mensaje'];

        // Coordenadas fijas por parada estimada
        $coordenadas = [
            "Centro logístico" => [-34.6037, -58.3816],
            "Rosario" => [-32.9575, -60.6394],
            "Tucumán" => [-26.8083, -65.2176],
            "Salta" => [-24.7829, -65.4232],
            "Córdoba" => [-31.4201, -64.1888]
        ];
        ?>

        <div class="card mt-4">
            <div class="card-header">📦 Envío #<?= $envio['id']; ?></div>
            <div class="card-body">
                <p><strong>Destino:</strong> <?= htmlspecialchars($envio['destino']) ?></p>
                <p><strong>Peso:</strong> <?= $envio['peso']; ?> kg</p>
                <p><strong>Estado actual:</strong> <?= $envio['estado']; ?></p>
                <p><strong>Fecha de envío:</strong> <?= $envio['fecha_envio']; ?></p>
                <p><strong>Ubicación estimada:</strong> <?= $ubicacion ?></p>
            </div>
        </div>

        <?php if (array_key_exists($ubicacion, $coordenadas)):
            $coord = $coordenadas[$ubicacion];
        ?>
        <div id="map" style="height: 400px;" class="mt-4 mb-4"></div>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            const map = L.map('map').setView([<?= $coord[0] ?>, <?= $coord[1] ?>], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            L.marker([<?= $coord[0] ?>, <?= $coord[1] ?>])
                .addTo(map)
                .bindPopup("📦 Paquete en <?= addslashes($ubicacion) ?>")
                .openPopup();
        </script>
        <?php endif; ?>

    <?php } else {
        echo "<div class='alert alert-warning mt-4'>🚫 No se encontró ningún envío con ese código.</div>";
    }
}
?>