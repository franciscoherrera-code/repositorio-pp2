<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "packtracker_db");
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST["username"]);
    $clave = $_POST["password"];

    if (empty($usuario) || empty($clave)) {
        $mensaje = "⚠️ Todos los campos son obligatorios.";
        $tipo_mensaje = "advertencia";
    } elseif (isset($_POST["registro"])) {
        $existe = mysqli_query($conn, "SELECT id FROM usuarios WHERE nombre_usuario = '$usuario'");
        if (mysqli_num_rows($existe) > 0) {
            $mensaje = "❌ El nombre de usuario ya está en uso.";
            $tipo_mensaje = "error";
        } else {
            $hash = password_hash($clave, PASSWORD_DEFAULT);
    $sql = "INSERT INTO usuarios (nombre_usuario, contrasena_hash) VALUES ('$usuario', '$hash')";
            if (mysqli_query($conn, $sql)) {
                $_SESSION["mensaje"] = "✅ Registro exitoso. ¡Iniciá sesión!";
                $_SESSION["tipo"] = "exito";
                header("Location: acceso.php");
                exit();
            } else {
                $mensaje = "❌ Error al registrar.";
                $tipo_mensaje = "error";
            }
        }
    } elseif (isset($_POST["login"])) {
       $sql = "SELECT id, contrasena_hash FROM usuarios WHERE nombre_usuario = '$usuario'";
        $resultado = mysqli_query($conn, $sql);

        if ($fila = mysqli_fetch_assoc($resultado)) {
   if (password_verify($clave, $fila["contrasena_hash"])) {
                $_SESSION["usuario"] = $usuario;
                $_SESSION["usuario_id"] = $fila["id"];
                $_SESSION["mensaje"] = "✅ Sesión iniciada correctamente.";
                $_SESSION["tipo"] = "exito";
                header("Location: principal.php");
                exit();
            } else {
                $mensaje = "❌ Contraseña incorrecta.";
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = "❌ Usuario no encontrado.";
            $tipo_mensaje = "error";
        }
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<div class="form-container">
<?php
if (isset($_SESSION["mensaje"])) {
    echo "<div class='toast {$_SESSION["tipo"]}'>" . $_SESSION["mensaje"] . "</div>";
    unset($_SESSION["mensaje"]);
    unset($_SESSION["tipo"]);
} elseif (!empty($mensaje)) {
    echo "<div class='toast $tipo_mensaje'>$mensaje</div>";
}
?>

    <h2>Registro / Inicio de Sesión</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Usuario" required><br>
        <input type="password" name="password" placeholder="Contraseña" required><br><br>
        <button type="submit" name="login">Iniciar Sesión</button>
        <button type="submit" name="registro">Registrarse</button>
    </form>
</div>
</body>
</html>