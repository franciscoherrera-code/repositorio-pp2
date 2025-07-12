<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Optimizador de Ruta de Entregas con QR</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        #reader {
            width: 100%;
            max-width: 600px; /* Limita el tamaño del video de la cámara */
            margin: 20px auto;
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden; /* Asegura que el video no se salga */
        }
        .scanner-controls {
            text-align: center;
            margin-top: 10px;
        }
        .scanner-controls button {
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Optimizador de Ruta de Entregas</h1>

        <form id="routeForm">
            <div class="form-group">
                <label for="origin">Punto de Partida (Origen):</label>
                <input type="text" id="origin" placeholder="Ej: Tu ubicación, Calle Falsa 123" required>
            </div>

            <div id="waypoints-container">
                <p>Direcciones de Entrega (Puedes escanear QR o añadir manualmente, hasta 10 para optimización):</p>
                <div class="waypoint-group">
                    <input type="text" class="waypoint-input" placeholder="Dirección de Entrega 1" required>
                    <button type="button" class="remove-waypoint">X</button>
                </div>
            </div>
            <button type="button" id="addWaypoint">Añadir Otra Entrega Manualmente</button>
            
            <button type="button" id="scanQrButton" style="background-color: #008CBA;">Escanear QR de Paquete</button>
            <div id="reader"></div> <div class="scanner-controls">
                <button type="button" id="stopScanner" style="background-color: #f44336; display: none;">Detener Escáner</button>
            </div>
            <button type="submit">Calcular Ruta Óptima</button>
        </form>

        <div id="map" style="height: 500px; width: 100%; margin-top: 20px;"></div>
        <div id="directions-panel" style="margin-top: 10px;"></div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB-K0XVJe_jjPe3xgssEbUVRZb9AjxlY00&libraries=routes&callback=initMap"></script>
    <script src="js/script.js"></script>
</body>
</html>