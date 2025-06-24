<?php
session_start();
session_unset();
session_destroy();

session_start(); // Para poder setear nuevo mensaje
$_SESSION["mensaje"] = "👋 Cerraste sesión correctamente.";
$_SESSION["tipo"] = "advertencia";

header("Location: acceso.php");
exit();