<?php
require_once(__DIR__ . "/../conexion.php");

if (session_status() === PHP_SESSION_NONE) session_start();

$usuario_id = $_SESSION['usuario_id'] ?? 0;

// Traemos todos los envíos del usuario ordenados por fecha
$sql = "SELECT * FROM envios 
        WHERE usuario_id = $usuario_id 
        ORDER BY fecha_envio DESC";

$resultado = $conexion->query($sql);
?>

<h3>Historial de Envíos</h3>

<?php if ($resultado && $resultado->num_rows > 0): ?>
    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>Código</th>
                <th>Destino</th>
                <th>Peso (kg)</th>
                <th>Estado</th>
                <th>Fecha de Envío</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($fila = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($fila['codigo_seguimiento']); ?></td>
                <td><?= htmlspecialchars($fila['destino']); ?></td>
                <td><?= $fila['peso']; ?></td>
                <td><?= $fila['estado']; ?></td>
                <td><?= $fila['fecha_envio']; ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-info">No se encontraron envíos registrados.</div>
<?php endif; ?>