<?php
require_once(__DIR__ . "/../conexion.php");
if (session_status() === PHP_SESSION_NONE) session_start();

// Parámetros GET
$busqueda = $_GET['busqueda'] ?? '';
$filtro_estado = $_GET['f_estado'] ?? '';
$filtro_prioridad = $_GET['f_prioridad'] ?? '';
$orden = $_GET['orden'] ?? 'fecha_envio';
$sentido = $_GET['sentido'] ?? 'DESC';
$pagina = max(1, intval($_GET['pagina'] ?? 1));
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

// Procesar actualización
if (isset($_POST['actualizar_envio'])) {
    $id = intval($_POST['envio_id']);
    $destino = $conexion->real_escape_string($_POST['destino']);
    $peso = floatval($_POST['peso']);
    $nombre = $conexion->real_escape_string($_POST['nombre_destinatario']);
    $telefono = $conexion->real_escape_string($_POST['telefono']);
    $estado = $conexion->real_escape_string($_POST['estado']);
    $prioridad = $conexion->real_escape_string($_POST['prioridad']);

    $sql = "UPDATE envios SET destino='$destino', peso=$peso, nombre_destinatario='$nombre',
            telefono='$telefono', estado='$estado', prioridad='$prioridad' WHERE id=$id";

    if ($conexion->query($sql)) {
        echo "<div class='alert alert-success'>✅ Envío #$id actualizado correctamente.</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Error al actualizar: {$conexion->error}</div>";
    }
}

// Construir WHERE
$sql_base = "FROM envios WHERE 1";
if ($busqueda) {
    $b = $conexion->real_escape_string($busqueda);
    $sql_base .= " AND (id LIKE '%$b%' OR nombre_destinatario LIKE '%$b%')";
}
if ($filtro_estado) {
    $e = $conexion->real_escape_string($filtro_estado);
    $sql_base .= " AND estado = '$e'";
}
if ($filtro_prioridad) {
    $p = $conexion->real_escape_string($filtro_prioridad);
    $sql_base .= " AND prioridad = '$p'";
}

// Total y datos
$total = $conexion->query("SELECT COUNT(*) AS cantidad $sql_base")->fetch_assoc()['cantidad'];
$paginas = ceil($total / $por_pagina);
$sql = "SELECT * $sql_base ORDER BY $orden $sentido LIMIT $offset, $por_pagina";
$resultado = $conexion->query($sql);
?>

<h3>Gestión de Envíos</h3>

<form method="get" action="principal.php" class="row mb-3">
    <input type="hidden" name="page" value="gestion">
    <div class="col-md-3">
        <input type="text" name="busqueda" class="form-control" placeholder="Buscar por ID o nombre" value="<?= htmlspecialchars($busqueda) ?>">
    </div>
    <div class="col-md-3">
        <select name="f_estado" class="form-control">
            <option value="">-- Estado --</option>
            <?php
            $estados = ['En preparación', 'En camino', 'Entregado', 'Cancelado'];
            foreach ($estados as $estado) {
                $sel = ($estado === $filtro_estado) ? 'selected' : '';
                echo "<option value=\"$estado\" $sel>$estado</option>";
            }
            ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="f_prioridad" class="form-control">
            <option value="">-- Prioridad --</option>
            <option value="Normal" <?= ($filtro_prioridad === 'Normal') ? 'selected' : '' ?>>Normal</option>
            <option value="Urgente" <?= ($filtro_prioridad === 'Urgente') ? 'selected' : '' ?>>Urgente</option>
        </select>
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100">🔍 Aplicar filtros</button>
    </div>
</form>

<?php if ($resultado && $resultado->num_rows > 0): ?>
<table class="table table-bordered table-sm align-middle">
    <thead>
        <tr>
            <?php
            $columnas = [
                'id' => 'ID',
                'nombre_destinatario' => 'Nombre',
                'telefono' => 'Teléfono',
                'destino' => 'Destino',
                'peso' => 'Peso (kg)',
                'estado' => 'Estado',
                'prioridad' => 'Prioridad',
                'fecha_envio' => 'Fecha'
            ];
            foreach ($columnas as $col => $label) {
                $dir = ($orden === $col && $sentido === 'ASC') ? 'DESC' : 'ASC';
                $icono = ($orden === $col) ? ($sentido === 'ASC' ? '↑' : '↓') : '';
                $params = http_build_query([
                    'page' => 'gestion',
                    'busqueda' => $busqueda,
                    'f_estado' => $filtro_estado,
                    'f_prioridad' => $filtro_prioridad,
                    'orden' => $col,
                    'sentido' => $dir,
                    'pagina' => 1
                ]);
                echo "<th><a href='principal.php?$params' class='text-dark text-decoration-none'>{$label} $icono</a></th>";
            }
            ?>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($envio = $resultado->fetch_assoc()): ?>
        <tr>
        <form method="post" action="principal.php?page=gestion&<?= http_build_query($_GET) ?>">
            <td><?= $envio['id']; ?></td>
            <td><input type="text" name="nombre_destinatario" value="<?= $envio['nombre_destinatario']; ?>" class="form-control" required></td>
            <td><input type="text" name="telefono" value="<?= $envio['telefono']; ?>" class="form-control" required></td>
            <td><input type="text" name="destino" value="<?= $envio['destino']; ?>" class="form-control" required></td>
            <td><input type="number" name="peso" value="<?= $envio['peso']; ?>" step="0.01" class="form-control" required></td>
            <td>
                <select name="estado" class="form-control" required>
                    <?php foreach ($estados as $estado): ?>
                        <option value="<?= $estado ?>" <?= ($envio['estado'] === $estado) ? 'selected' : '' ?>><?= $estado ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select name="prioridad" class="form-control" required>
                    <option value="Normal" <?= ($envio['prioridad'] === 'Normal') ? 'selected' : '' ?>>Normal</option>
                    <option value="Urgente" <?= ($envio['prioridad'] === 'Urgente') ? 'selected' : '' ?>>Urgente</option>
                </select>
            </td>
            <td><?= $envio['fecha_envio']; ?></td>
            <td>
                <input type="hidden" name="envio_id" value="<?= $envio['id']; ?>">
                <button type="submit" name="actualizar_envio" class="btn btn-sm btn-success">Actualizar</button>
            </td>
        </form>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<nav>
  <ul class="pagination">
    <?php for ($i = 1; $i <= $paginas; $i++): ?>
        <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
            <a class="page-link" href="principal.php?<?= http_build_query([
                'page' => 'gestion',
                'pagina' => $i,
                'busqueda' => $busqueda,
                'f_estado' => $filtro_estado,
                'f_prioridad' => $filtro_prioridad,
                'orden' => $orden,
                'sentido' => $sentido
            ]) ?>"><?= $i ?></a>
        </li>
    <?php endfor; ?>
  </ul>
</nav>

<?php else: ?>
    <div class="alert alert-info">No se encontraron envíos registrados.</div>
<?php endif; ?>