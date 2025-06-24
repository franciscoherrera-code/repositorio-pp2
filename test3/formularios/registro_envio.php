<?php
require_once(__DIR__ . "/../conexion.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_id = $_SESSION['usuario_id'] ?? 1;

if (isset($_POST['registrar_envio'])) {
    $destino = $conexion->real_escape_string($_POST['destino']);
    $peso = floatval($_POST['peso']);
    $nombre = $conexion->real_escape_string($_POST['nombre_destinatario']);
    $telefono = $conexion->real_escape_string($_POST['telefono']);
    $prioridad = $conexion->real_escape_string($_POST['prioridad']);

    $sql = "INSERT INTO envios (usuario_id, destino, peso, nombre_destinatario, telefono, prioridad)
            VALUES ('$usuario_id', '$destino', '$peso', '$nombre', '$telefono', '$prioridad')";

    if ($conexion->query($sql)) {
        $envio_id = $conexion->insert_id;

        // Generar código de seguimiento tipo PKG-2025-00001
        $anio = date("Y");
        $codigo = "PKG-" . $anio . "-" . str_pad($envio_id, 5, "0", STR_PAD_LEFT);
        $conexion->query("UPDATE envios SET codigo_seguimiento = '$codigo' WHERE id = $envio_id");

        // Registrar estado inicial
        $sql_historial = "INSERT INTO historial (envio_id, estado)
                          VALUES ('$envio_id', 'En preparación')";
        $conexion->query($sql_historial);

        echo "<div class='alert alert-success mt-3'>📦 Envío registrado correctamente.<br>Código de seguimiento: <strong>$codigo</strong></div>";
    } else {
        echo "<div class='alert alert-danger mt-3'>❌ Error: " . $conexion->error . "</div>";
    }
}
?>

<h3>Registrar nuevo envío</h3>
<form method="post" action="">
    <div class="mb-3">
        <label>Nombre del destinatario</label>
        <input type="text" name="nombre_destinatario" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Teléfono</label>
        <input type="text" name="telefono" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Destino</label>
        <input type="text" name="destino" class="form-control" placeholder="Dirección de entrega" required>
    </div>
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