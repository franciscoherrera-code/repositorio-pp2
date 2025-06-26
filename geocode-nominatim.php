<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (!isset($_GET['q']) || empty($_GET['q'])) {
  echo json_encode(["error" => "Falta parámetro 'q'"]);
  exit;
}

$direccion = urlencode($_GET['q']);
$url = "https://nominatim.openstreetmap.org/search?format=json&q=$direccion&limit=1";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'MiApp-Tobi/1.0');
$respuesta = curl_exec($ch);

if (curl_errno($ch)) {
  echo json_encode(["error" => "Error en cURL: " . curl_error($ch)]);
} else {
  echo $respuesta;
}
curl_close($ch);