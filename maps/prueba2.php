<?php
// Conexión a la base de datos
$host = 'localhost';
$db = 'packtracker_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query("SELECT id, origen, destino, lat_origen, lon_origen, latitud, longitud FROM envios WHERE estado != 'Cancelado'");
    $envios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}

// Función para geocodificar
function geocode($direccion) {
    $direccion = urlencode($direccion);
    $apiKey = 'TU_API_KEY_GOOGLE_MAPS';
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$direccion&key=$apiKey";

    $resp_json = file_get_contents($url);
    $resp = json_decode($resp_json, true);

    if ($resp['status'] == 'OK') {
        $lat = $resp['results'][0]['geometry']['location']['lat'];
        $lng = $resp['results'][0]['geometry']['location']['lng'];
        return [$lat, $lng];
    } else {
        return [null, null];
    }
}

$rutas = [];
foreach ($envios as $envio) {
    $origen = $envio['origen'];
    $destino = $envio['destino'];

    // Verificamos si hay coordenadas, si no geocodificamos
    $lat_origen = $envio['lat_origen'];
    $lon_origen = $envio['lon_origen'];
    $lat_destino = $envio['latitud'];
    $lon_destino = $envio['longitud'];

    if (!$lat_origen || !$lon_origen) {
        list($lat_origen, $lon_origen) = geocode($origen);
    }
    if (!$lat_destino || !$lon_destino) {
        list($lat_destino, $lon_destino) = geocode($destino);
    }

    // Solo agregamos rutas válidas
    if ($lat_origen && $lon_origen && $lat_destino && $lon_destino) {
        $rutas[] = [
            'origen' => ['lat' => $lat_origen, 'lng' => $lon_origen],
            'destino' => ['lat' => $lat_destino, 'lng' => $lon_destino],
            'envio_id' => $envio['id']
        ];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rutas de Envíos</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=TU_API_KEY_GOOGLE_MAPS"></script>
    <style>
        #mapa {
            height: 600px;
            width: 100%;
        }
    </style>
</head>
<body>
<h2>Rutas de Envíos</h2>
<div id="mapa"></div>

<script>
const rutas = <?php echo json_encode($rutas); ?>;

function initMap() {
    const map = new google.maps.Map(document.getElementById('mapa'), {
        zoom: 5,
        center: { lat: -32.95, lng: -60.66 } // Rosario como centro aproximado
    });

    rutas.forEach(ruta => {
        const directionsService = new google.maps.DirectionsService();
        const directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: false,
            preserveViewport: true
        });

        directionsService.route({
            origin: ruta.origen,
            destination: ruta.destino,
            travelMode: 'DRIVING'
        }, (response, status) => {
            if (status === 'OK') {
                directionsRenderer.setDirections(response);
            } else {
                console.error(`Error en envío ${ruta.envio_id}: ${status}`);
            }
        });
    });
}

window.onload = initMap;
</script>
</body>
</html>

