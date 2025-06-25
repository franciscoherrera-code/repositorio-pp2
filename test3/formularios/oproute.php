<?php
define("ORS_API_KEY", "5b3ce3597851110001cf62489b684e22274d469c933570236c591413");

$texto = $_GET['q'] ?? '';
if (empty($texto)) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetro 'q' requerido."]);
    exit;
}

$url = "https://api.openrouteservice.org/geocode/search?" . http_build_query([
    'api_key' => ORS_API_KEY,
    'text' => $texto,
    'boundary.country' => 'AR'
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$respuesta = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json');
echo $respuesta;