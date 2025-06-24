<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: acceso.php");
    exit();
}
$page = $_GET["page"] ?? "bienvenida";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PackTracker | Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Barra lateral -->
        <div class="col-md-3 col-lg-2 sidebar">
            <h4 class="text-center my-4">
                <a href="principal.php" style="color: white; text-decoration: none;">PackTracker</a>
            </h4>
            <a href="principal.php?page=registro">📦 Registrar Envío</a>
            <a href="principal.php?page=rastrear">🔍 Rastrear Paquete</a>
            <a href="principal.php?page=historial">🕓 Historial</a>
            <a href="principal.php?page=gestion">📋 Gestión</a> <!-- NUEVO BOTÓN -->
            <a href="principal.php?page=perfil">👤 Perfil</a>
            <a href="logout.php" class="text-danger mt-4">🚪 Cerrar sesión</a>
        </div>

        <!-- Contenido principal -->
        <div class="col-md-9 col-lg-10 p-4">
            <?php
            if (isset($_SESSION["mensaje"])) {
                echo "<div class='toast'>{$_SESSION["mensaje"]}</div>";
                unset($_SESSION["mensaje"]);
            }

            switch ($page) {
                case 'registro':
                    include 'formularios/registro_envio.php';
                    break;
                case 'rastrear':
                    include 'formularios/rastrear_paquetes.php';
                    break;
                case 'historial':
                    include 'formularios/historial.php';
                    break;
                case 'gestion': // NUEVA SECCIÓN
                    include 'formularios/gestionar_envios.php';
                    break;
                case 'perfil':
                    include 'formularios/perfil.php';
                    break;
                default:
                    echo "<h2>Bienvenido, {$_SESSION["usuario"]} 👋</h2>
                          <p>Usá el menú lateral para comenzar a gestionar tus envíos.</p>";
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>