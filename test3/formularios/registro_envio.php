<?php
require_once(__DIR__ . "/../conexion.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_id = $_SESSION['usuario_id'] ?? 1;
$codigo_generado = null;

if (isset($_POST['registrar_envio'])) {
    $destino = $conexion->real_escape_string($_POST['destino']);
    $peso = floatval($_POST['peso']);
    $nombre = $conexion->real_escape_string($_POST['nombre_destinatario']);
    $telefono = $conexion->real_escape_string($_POST['telefono']);
    $prioridad = $conexion->real_escape_string($_POST['prioridad']);
    $origen = $conexion->real_escape_string($_POST['origen']);
$lat_origen = floatval($_POST['lat_origen']);
$lon_origen = floatval($_POST['lon_origen']);
$latitud = floatval($_POST['latitud']);
$longitud = floatval($_POST['longitud']);

    $sql = "INSERT INTO envios (
    usuario_id,
    destino,
    peso,
    nombre_destinatario,
    telefono,
    prioridad,
    origen,
    lat_origen,
    lon_origen,
    latitud,
    longitud

    
) VALUES (
    '$usuario_id',
    '$destino',
    '$peso',
    '$nombre',
    '$telefono',
    '$prioridad',
    '$origen',
    $lat_origen,
    $lon_origen,
     $latitud,
    $longitud

)";
    if ($conexion->query($sql)) {
        $envio_id = $conexion->insert_id;

        $anio = date("Y");
        $codigo_generado = "PKG-" . $anio . "-" . str_pad($envio_id, 5, "0", STR_PAD_LEFT);
        $conexion->query("UPDATE envios SET codigo_seguimiento = '$codigo_generado' WHERE id = $envio_id");

        $conexion->query("INSERT INTO historial (envio_id, estado) VALUES ('$envio_id', 'En preparación')");
    } else {
        echo "<div class='alert alert-danger mt-3'>❌ Error: " . $conexion->error . "</div>";
    }
}
?>

<h3>Registrar nuevo envío</h3>
<form method="post" action="" autocomplete="off">
    <div class="mb-3">
        <label>Nombre del destinatario</label>
        <input type="text" name="nombre_destinatario" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Teléfono</label>
        <input type="text" name="telefono" class="form-control" required>
    </div>
<div class="mb-3 position-relative">
  <label for="origen" class="form-label">Origen</label>
  <input type="text" name="origen" id="origen" class="form-control" placeholder="Ciudad de origen" required>
  <div id="sugerencias_origen" class="list-group mt-1" style="position:absolute; z-index:999;"></div>
  <input type="hidden" name="lat_origen" id="lat_origen">
  <input type="hidden" name="lon_origen" id="lon_origen">
</div>

    <div class="mb-3 position-relative">
        <label>Destino</label>
        <input type="text" name="destino" id="destino" class="form-control" placeholder="Ej: Av. Córdoba 1234, CABA" required>
        <div id="sugerencias" class="list-group" style="position:absolute; z-index:999;"></div>
    </div>
    <input type="hidden" name="latitud" id="latitud">
<input type="hidden" name="longitud" id="longitud">
    <div class="mb-3">
        <label>Peso del paquete (kg)</label>
        <input type="number" name="peso" class="form-control" step="0.01" required>
    </div>
    <div class="mb-3">
        <label>Prioridad del envío</label>
        <select name="prioridad" class="form-control" required>
            <option value="Normal">Normal</option>
            <option value="Urgente">Urgente</option>
        </select>
    </div>
    <button type="submit" name="registrar_envio" class="btn btn-primary">Registrar</button>
</form>

<?php if ($codigo_generado): ?>
    <div class="alert alert-success mt-4">
        📦 Envío registrado correctamente.<br>
        Tu <strong>código de seguimiento</strong> es:
        <div class="input-group mt-2 mb-2">
            <input type="text" id="codigoSeguimiento" class="form-control fw-bold text-center" value="<?= htmlspecialchars($codigo_generado) ?>" readonly>
            <button class="btn btn-outline-secondary" type="button" onclick="copiarCodigo()">📋 Copiar</button>
        </div>
        <a href="principal.php?page=rastrear&codigo=<?= urlencode($codigo_generado) ?>" class="btn btn-outline-primary mt-2">
            🔍 Rastrear este paquete
        </a>
    </div>
<?php endif; ?>

<script>
const inputDestino = document.getElementById("destino");
const contenedor = document.getElementById("sugerencias");
const latDestino = document.getElementById("latitud");
const lonDestino = document.getElementById("longitud");

let delay;
inputDestino.addEventListener("input", () => {
    const valor = inputDestino.value.trim();
    clearTimeout(delay);

    if (valor.length < 3) {
        contenedor.innerHTML = "";
        return;
    }

    delay = setTimeout(() => {
        fetch("formularios/oproute.php?q=" + encodeURIComponent(valor))
            .then(res => res.json())
            .then(data => {
                contenedor.innerHTML = "";
                if (data.features) {
                    data.features.forEach(feature => {
                        const item = document.createElement("button");
                        item.className = "list-group-item list-group-item-action";
                        item.type = "button";
                        item.textContent = feature.properties.label;
                        item.onclick = () => {
                            inputDestino.value = feature.properties.label;
                            latDestino.value = feature.geometry.coordinates[1];
                            lonDestino.value = feature.geometry.coordinates[0];
                            contenedor.innerHTML = "";
                        };
                        contenedor.appendChild(item);
                    });
                }
            })
            .catch(() => {
                contenedor.innerHTML = "";
            });
    }, 400);
});
</script>

<script>
const inputOrigen = document.getElementById("origen");
const sugerenciasOrigen = document.getElementById("sugerencias_origen");
const latOrigen = document.getElementById("lat_origen");
const lonOrigen = document.getElementById("lon_origen");

let delayOrigen;
inputOrigen.addEventListener("input", () => {
    const texto = inputOrigen.value.trim();
    clearTimeout(delayOrigen);

    if (texto.length < 3) {
        sugerenciasOrigen.innerHTML = '';
        return;
    }

    delayOrigen = setTimeout(() => {
        fetch("formularios/oproute.php?q=" + encodeURIComponent(texto))
            .then(res => res.json())
            .then(data => {
                sugerenciasOrigen.innerHTML = '';
                if (data.features) {
                    data.features.forEach(feature => {
                        const btn = document.createElement("button");
                        btn.type = "button";
                        btn.className = "list-group-item list-group-item-action";
                        btn.textContent = feature.properties.label;
                        btn.onclick = () => {
                            inputOrigen.value = feature.properties.label;
                            latOrigen.value = feature.geometry.coordinates[1];
                            lonOrigen.value = feature.geometry.coordinates[0];
                            sugerenciasOrigen.innerHTML = '';
                        };
                        sugerenciasOrigen.appendChild(btn);
                    });
                }
            })
            .catch(() => {
                sugerenciasOrigen.innerHTML = '';
            });
    }, 350);
});
</script>