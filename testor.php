<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Test ORS con Ruta</title>

  <!-- CSS de Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
  <style>
    #mapa-ruta {
      height: 400px;
      margin: 20px;
      border: 1px solid #ccc;
    }
    .info-box {
      margin: 20px;
      font-family: sans-serif;
      background: #e9f7fc;
      padding: 10px 15px;
      border-left: 5px solid #007bff;
      max-width: 400px;
    }
  </style>
</head>
<body>

  <div id="mapa-ruta"></div>
  <div id="info" class="info-box"></div>

  <!-- JS de Leaflet y plugin para decodificar polylines -->
  <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
  <script src="https://unpkg.com/@mapbox/polyline@1.1.1/src/polyline.js"></script>

  <script>
    const coordenadas = {
      origen: [-34.6037, -58.3816],   // Obelisco (lat, lon)
      destino: [-34.6500, -58.5237]   // Liniers
    };

    console.log("🛰️ Coordenadas de origen:", coordenadas.origen);
    console.log("🛰️ Coordenadas de destino:", coordenadas.destino);

    if (
      !Array.isArray(coordenadas.origen) || coordenadas.origen.length !== 2 ||
      !Array.isArray(coordenadas.destino) || coordenadas.destino.length !== 2
    ) {
      alert("⚠️ Coordenadas incompletas o inválidas. Verificá los valores de origen y destino.");
      document.getElementById("info").innerText = "❌ No se pudo calcular la ruta porque las coordenadas están mal definidas.";
      throw new Error("Coordenadas inválidas");
    }

    const map = L.map('mapa-ruta').setView(coordenadas.origen, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker(coordenadas.origen).addTo(map).bindPopup("📍 Origen").openPopup();
    L.marker(coordenadas.destino).addTo(map).bindPopup("🏁 Destino");

    const apiKey = '5b3ce3597851110001cf62489b684e22274d469c933570236c591413';

    fetch('https://api.openrouteservice.org/v2/directions/driving-car', {
      method: 'POST',
      headers: {
        'Authorization': apiKey,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        coordinates: [
          [coordenadas.origen[1], coordenadas.origen[0]],
          [coordenadas.destino[1], coordenadas.destino[0]]
        ]
      })
    })
    .then(res => res.json())
    .then(data => {
      if (!data.routes || !data.routes[0]) {
        document.getElementById("info").innerText = "❌ No se pudo calcular la ruta (respuesta sin rutas).";
        console.warn("🧩 Respuesta inesperada de ORS:", data);
        return;
      }

      const encoded = data.routes[0].geometry;
      const decoded = polyline.decode(encoded).map(c => [c[0], c[1]]); // [lat, lon]

      L.polyline(decoded, { color: 'blue', weight: 4 }).addTo(map);
      map.fitBounds(L.polyline(decoded).getBounds());

      const resumen = data.routes[0].summary;
      const distancia = (resumen.distance / 1000).toFixed(2);
      const duracion = Math.round(resumen.duration / 60);

      document.getElementById("info").innerHTML = `
        🛣️ <strong>Distancia:</strong> ${distancia} km<br>
        ⏱️ <strong>Duración estimada:</strong> ${duracion} min
      `;
    })
    .catch(error => {
      console.error("❌ Falló la solicitud a ORS:", error);
      document.getElementById("info").innerText = "❌ Hubo un problema con la solicitud a OpenRouteService.";
    });
  </script>
</body>
</html>