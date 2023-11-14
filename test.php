<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leaflet Map with IRIS Zones</title>

    <!-- Leaflet CSS and JavaScript -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo" crossorigin=""></script>
    
    <link rel="stylesheet" type="text/css" href="test.css">

    <!-- Leaflet Geocoding Plugin CSS and JavaScript -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@1.13.0/dist/Control.Geocoder.css" />
    <script src="https://unpkg.com/leaflet-control-geocoder@1.13.0/dist/Control.Geocoder.js"></script>
</head>
<body>
<div id="map"></div>
<div id="geocoder" style="text-align: center;">
    <input type="text" id="addressInput" placeholder="Enter an address">
    <button id="geocodeButton">Go to Address</button>
</div>

<!-- Recommendation popup content -->
<div id="recommendationPopup" style="display: none;">
    <p id="recommendationText"></p>
    <ul>
        <li>Est-ce que l'adresse fournie est bien l'adresse d'entrée ?</li>
        <li>Est-ce que plusieurs codes sont nécessaires pour entrer ?</li>
        <li>Est-ce que le point d'entrée est protégé par des barrières ?</li>
    </ul>
    <button id="closeRecommendation">Fermer</button>
</div>

<script>
    const map = L.map('map').setView([48.807, 2.378], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const geocoder = L.Control.Geocoder.nominatim();
    const geocoderControl = L.Control.geocoder({
        geocoder: geocoder,
    }).addTo(map);

    // Define the coordinates for the Ivry Sur Seine polygon
    const ivryPolygonCoordinates = [
        [48.807, 2.378],
        [48.808, 2.384],
        [48.805, 2.387],
        [48.813055, 2.38822],
    ];

    // Create a polygon for Ivry Sur Seine
    const ivryPolygon = L.polygon(ivryPolygonCoordinates, { color: 'blue', fillColor: 'lightblue', fillOpacity: 0.5 }).addTo(map);

    // Define the coordinates for the Alfortville polygon
    const alfortvillePolygonCoordinates = [
        [48.799117, 2.412366],
        [48.800697, 2.414812],
        [48.801510, 2.412566],
        [48.799835, 2.410201],
        [48.80575, 2.4204]
    ];

    // Create a polygon for Alfortville
    const alfortvillePolygon = L.polygon(alfortvillePolygonCoordinates, { color: 'red', fillColor: 'lightblue', fillOpacity: 0.5 }).addTo(map);

    // Define the zone type for each polygon
    const irisZoneTypes = {
        'IRIS3': 'zone très difficile d accès',
        'IRIS4': 'zone extrêmement difficiel d accès',
    };

    // Function to set the map view to the user's current location
    function setDefaultLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const latlng = [position.coords.latitude, position.coords.longitude];
                map.setView(latlng, 13);

                // Add a marker at the user's current location
                const userMarker = L.marker(latlng).addTo(map)
                    .bindPopup('<b>Your Current Location</b><br />This is where you are.').openPopup();
            });
        }
    }

    setDefaultLocation();

    // Function to show the recommendation popup with IRIS number and zone type
    function showRecommendation(irisNumber) {
        const irisZoneType = irisZoneTypes[irisNumber];
        document.getElementById('recommendationText').textContent = `${irisNumber} : ${irisZoneType}`;
        document.getElementById('recommendationPopup').style.display = 'block';
    }

    // Event listener to close the recommendation popup
    document.getElementById('closeRecommendation').addEventListener('click', function () {
        document.getElementById('recommendationPopup').style.display = 'none';
    });

    // Function to move the map to the entered address and add a marker
    document.getElementById('geocodeButton').addEventListener('click', function () {
        const address = document.getElementById('addressInput').value;

        // Clear previous markers
        map.eachLayer(function (layer) {
            if (layer instanceof L.Marker) {
                map.removeLayer(layer);
            }
        });

        geocoder.geocode(address, function (results) {
            if (results.length > 0) {
                const latlng = results[0].center;

                // Create a marker and bind the simplified address as a popup
                const currentMarker = L.marker(latlng).addTo(map);
                const addressDetails = results[0].name || results[0].properties.display_name;
                currentMarker.bindPopup(addressDetails);
                currentMarker.openPopup();

                // Check if the marker is in a polygon and show the related recommendation
                if (ivryPolygon.getBounds().contains(currentMarker.getLatLng())) {
                    showRecommendation('IRIS3');
                } else if (alfortvillePolygon.getBounds().contains(currentMarker.getLatLng())) {
                    showRecommendation('IRIS4');
                }
                
                map.setView(latlng, 13);
            } else {
                alert('Address not found');
            }
        });
    });
</script>
</body>
</html>
