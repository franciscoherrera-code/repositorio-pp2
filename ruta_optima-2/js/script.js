let map;
let directionsService;
let directionsRenderer;
let waypointCount = 1; // Para controlar el número de campos de entrega
let html5QrCode; // Variable global para la instancia del escáner QR

// La función initMap es llamada por la API de Google Maps cuando se carga
function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 7,
        // Usamos la ubicación por defecto de San Nicolás de los Arroyos como centro.
        // Si el usuario habilita la geolocalización, esto se sobrescribirá.
        center: { lat: -33.3364, lng: -60.2224 },
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer();
    directionsRenderer.setMap(map);
    directionsRenderer.setPanel(document.getElementById("directions-panel"));

    // Opcional: Intentar obtener la ubicación actual del usuario como origen
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };
                // Centra el mapa en la ubicación del usuario
                map.setCenter(userLocation);
                map.setZoom(14); // Mayor zoom para la ubicación actual

                // Usar Geocoding para obtener la dirección legible y ponerla en el campo de origen
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: userLocation }, (results, status) => {
                    if (status === "OK" && results[0]) {
                        document.getElementById("origin").value = results[0].formatted_address;
                    } else {
                        console.error("Geocoding failed due to: " + status);
                    }
                });
            },
            () => {
                // Si la geolocalización falla o el usuario la deniega
                console.warn("Error: The Geolocation service failed.");
            }
        );
    } else {
        // El navegador no soporta Geolocation
        console.warn("Error: Your browser doesn't support Geolocation.");
    }
}

// Función para añadir un nuevo campo de dirección de entrega
// Puede recibir una dirección prellenada (si viene del QR) o estar vacío
function addWaypointInput(address = '') {
    if (waypointCount < 10) { // Límite de 10 entregas (más el origen)
        waypointCount++;
        const waypointsContainer = document.getElementById("waypoints-container");
        const waypointGroup = document.createElement("div");
        waypointGroup.classList.add("waypoint-group");
        waypointGroup.innerHTML = `
            <input type="text" class="waypoint-input" placeholder="Dirección de Entrega ${waypointCount}" required value="${address}">
            <button type="button" class="remove-waypoint">X</button>
        `;
        waypointsContainer.appendChild(waypointGroup);

        // Añadir evento para eliminar la parada
        waypointGroup.querySelector(".remove-waypoint").addEventListener("click", (event) => {
            event.target.closest(".waypoint-group").remove();
            // No decrementamos waypointCount aquí porque el índice ya no es secuencial.
            // Simplemente el `querySelectorAll` en el submit tomará las que queden.
        });

        // Enfocar el nuevo input si se ha añadido una dirección del QR
        if (address) {
            waypointGroup.querySelector(".waypoint-input").focus();
        }
    } else {
        alert("No puedes añadir más de 10 direcciones de entrega.");
    }
}

// Evento para añadir entrega manualmente
document.getElementById("addWaypoint").addEventListener("click", () => addWaypointInput());

// Lógica de Escaneo de QR
document.getElementById("scanQrButton").addEventListener("click", async () => {
    const qrReaderDiv = document.getElementById('reader');
    const stopButton = document.getElementById('stopScanner');
    
    // Ocultar botón de escanear y mostrar el de detener
    document.getElementById('scanQrButton').style.display = 'none';
    stopButton.style.display = 'inline-block';

    html5QrCode = new Html5Qrcode("reader");

    try {
        const cameras = await Html5Qrcode.getCameras();
        if (cameras && cameras.length) {
            // Usar la primera cámara disponible. Si hay varias, puedes dar una opción al usuario.
            const cameraId = cameras[0].id;
            
            html5QrCode.start(
                cameraId,
                {
                    fps: 10,    // Velocidad de fotogramas del escáner
                    qrbox: { width: 250, height: 250 } // Tamaño del recuadro de escaneo (centrado)
                },
                (decodedText, decodedResult) => {
                    // Función que se ejecuta cuando se detecta un QR
                    console.log(`Código QR detectado: ${decodedText}`);
                    
                    // Añadir la dirección escaneada a un nuevo campo de entrega
                    addWaypointInput(decodedText); 

                    // Detener el escáner automáticamente después de escanear uno para evitar reescaneos accidentales
                    stopQrScanner(); // Llama a la función para detener el escáner

                },
                (errorMessage) => {
                    // Mensajes de error de escaneo (ej. QR no encontrado en el encuadre)
                    // console.log(`No QR code detected or scanning error: ${errorMessage}`); // Descomentar para depuración
                }
            ).catch((err) => {
                alert(`Error al iniciar el escáner: ${err}. Asegúrate de permitir el acceso a la cámara.`);
                console.error("Error al iniciar el escáner:", err);
                stopQrScanner(); // Asegúrate de ocultar el botón de detener si falla el inicio
            });
        } else {
            alert("No se encontraron cámaras en tu dispositivo.");
            stopQrScanner();
        }
    } catch (err) {
        alert(`Error al acceder a las cámaras: ${err}. Asegúrate de permitir el acceso a la cámara.`);
        console.error("Error al obtener cámaras:", err);
        stopQrScanner();
    }
});

// Función para detener el escáner
document.getElementById("stopScanner").addEventListener("click", stopQrScanner);

async function stopQrScanner() {
    // La propiedad 'is' no es estándar o confiable. Es mejor usar un estado interno o isScanning si la librería lo ofrece.
    // html5-qrcode tiene un método .is
    if (html5QrCode && html5QrCode.isScanning) { // Mejor usar isScanning si está disponible, o un flag booleano
        try {
            await html5QrCode.stop();
            console.log("Escáner QR detenido.");
        } catch (err) {
            console.error("Error al detener el escáner QR:", err);
        }
    } else if (html5QrCode) { // Si no es isScanning, verificar si la instancia existe y detenerla por si acaso.
         try {
            await html5QrCode.stop();
            console.log("Escáner QR detenido (vía fallback).");
        } catch (err) {
            console.error("Error al detener el escáner QR (vía fallback):", err);
        }
    }
    document.getElementById('reader').innerHTML = ''; // Limpia el área del video
    document.getElementById('scanQrButton').style.display = 'inline-block';
    document.getElementById('stopScanner').style.display = 'none';
}


// Manejar el Envío del Formulario
document.getElementById("routeForm").addEventListener("submit", (event) => {
    event.preventDefault(); // Evitar el envío normal del formulario

    const origin = document.getElementById("origin").value.trim();
    const waypointInputs = document.querySelectorAll(".waypoint-input");

    const waypoints = [];
    waypointInputs.forEach(input => {
        if (input.value.trim() !== "") { // Asegurarse de que el campo no esté vacío
            waypoints.push({
                location: input.value.trim(),
                stopover: true // Indica que es una parada real
            });
        }
    });

    if (origin === "") {
        alert("Por favor, ingresa tu Punto de Partida.");
        return;
    }

    if (waypoints.length === 0) {
        alert("Por favor, ingresa al menos una Dirección de Entrega.");
        return;
    }

    // El destino de la ruta será el mismo origen, creando un recorrido que vuelve al inicio
    const destination = origin;

    // Construir la solicitud a la API de Directions
    const request = {
        origin: origin,
        destination: destination, // El destino ahora es el punto de partida (ruta circular)
        waypoints: waypoints,     // TODAS las entregas son waypoints a optimizar
        optimizeWaypoints: true,  // Esto es clave para la ruta "óptima"
        travelMode: google.maps.TravelMode.DRIVING
    };

    // Realizar la solicitud
    directionsService.route(request, (response, status) => {
        if (status === "OK") {
            directionsRenderer.setDirections(response);
            
            const route = response.routes[0];
            const summaryPanel = document.getElementById("directions-panel");

            // --- Código para mostrar el orden optimizado de las entregas ---
            // Capturamos las direcciones de las entradas del formulario para referencia
            const originalDeliveryAddresses = Array.from(waypointInputs)
                .map(input => input.value.trim())
                .filter(val => val !== "");

            let optimizedOrderHtml = '<h3>Orden Optimizada de Entregas:</h3><ol>';
            optimizedOrderHtml += `<li><strong>Punto de Partida:</strong> ${origin}</li>`;

            // Verificamos si hay waypoints y si el API devolvió un orden optimizado
            if (route.waypoint_order && route.waypoint_order.length > 0) {
                // Iteramos sobre el orden optimizado que nos da Google
                route.waypoint_order.forEach(originalIndex => {
                    optimizedOrderHtml += `<li>${originalDeliveryAddresses[originalIndex]}</li>`;
                });
            } else if (originalDeliveryAddresses.length === 1) {
                // Si solo hay una entrega, el orden es simplemente Origen -> Entrega 1 -> Origen
                optimizedOrderHtml += `<li>${originalDeliveryAddresses[0]}</li>`;
            } else {
                // Caso en que no hay un orden optimizado explícito de Google (ej. 0 o más de 10 waypoints)
                optimizedOrderHtml += `<li>No se pudo determinar el orden detallado de las entregas, pero la ruta está optimizada.</li>`;
            }
            optimizedOrderHtml += `<li><strong>Regreso a Punto de Partida:</strong> ${origin}</li>`; // Último punto del ciclo
            optimizedOrderHtml += '</ol>';
            // --- Fin: Código para mostrar el orden optimizado de las entregas ---

            summaryPanel.innerHTML = `
                <h3>Resumen de la Ruta Optimizada:</h3>
                <p><strong>Número de Entregas (excluyendo origen/destino si es circular):</strong> ${originalDeliveryAddresses.length}</p>
                <p><strong>Distancia total:</strong> ${route.legs.reduce((acc, leg) => acc + leg.distance.text, '')}</p>
                <p><strong>Tiempo estimado:</strong> ${route.legs.reduce((acc, leg) => acc + leg.duration.text, '')}</p>
                ${optimizedOrderHtml}
                <h3>Instrucciones Detalladas:</h3>
            `;
        } else {
            alert("No se pudo calcular la ruta: " + status + ". Verifica las direcciones ingresadas.");
            console.error("Error al calcular ruta:", status, response);
        }
    });
});