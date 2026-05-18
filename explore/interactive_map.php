<?php 
require_once '../includes/header.php'; 

// Fetch Destinations with route data
$stmt = $pdo->query("SELECT id, name, type, route_data, hero_image FROM destinations WHERE is_deleted = 0 AND route_data IS NOT NULL");
$destinations = $stmt->fetchAll();
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 700px;
        border-radius: 30px;
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        z-index: 1;
    }
    .map-container {
        position: relative;
    }
    .map-overlay {
        position: absolute;
        top: 2rem;
        right: 2rem;
        z-index: 1000;
        background: rgba(var(--bs-body-bg-rgb), 0.8);
        backdrop-filter: blur(15px);
        padding: 1.5rem;
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.1);
        width: 300px;
    }
    .poi-popup {
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
        border-radius: 10px;
        padding: 5px;
    }
    .leaflet-container {
        background: #111 !important;
    }
    .destination-item {
        cursor: pointer;
        padding: 10px;
        border-radius: 10px;
        transition: all 0.2s;
        margin-bottom: 5px;
        border-left: 3px solid transparent;
    }
    .destination-item:hover, .destination-item.active {
        background: rgba(var(--bs-primary-rgb), 0.1);
        border-left-color: var(--bs-primary);
    }
</style>

<div class="row">
    <div class="col-12 mb-4">
        <h2 class="fw-bold"><i class="bi bi-geo-fill me-2 text-primary"></i>Interactive Expedition Map</h2>
        <p class="text-muted">Visualize your flight paths and explore major points of interest across Pakistan.</p>
    </div>
</div>

<div class="map-container mb-5">
    <div id="map"></div>
    
    <div class="map-overlay shadow">
        <h6 class="text-uppercase small fw-bold text-primary mb-3">Select Expedition</h6>
        <div id="destinationList" style="max-height: 400px; overflow-y: auto;">
            <?php foreach($destinations as $d): ?>
                <div class="destination-item" onclick="viewRoute(<?= $d['id'] ?>, this)" 
                     data-json='<?= htmlspecialchars($d['route_data']) ?>'>
                    <div class="d-flex align-items-center">
                        <img src="<?= $d['hero_image'] ?>" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;" class="me-3">
                        <div>
                            <div class="fw-bold small"><?= $d['name'] ?></div>
                            <div class="text-muted extra-small" style="font-size: 0.7rem;"><?= $d['type'] ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([30.3753, 69.3451], 6); // Center of Pakistan

    // Dark Premium Map Tiles
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
    }).addTo(map);

    var currentRouteLayer = L.layerGroup().addTo(map);
    var currentMarkersLayer = L.layerGroup().addTo(map);

    function viewRoute(id, element) {
        // UI Highlight
        document.querySelectorAll('.destination-item').forEach(i => i.classList.remove('active'));
        element.classList.add('active');

        // Clear existing
        currentRouteLayer.clearLayers();
        currentMarkersLayer.clearLayers();

        const routeData = JSON.parse(element.getAttribute('data-json'));
        
        if (!routeData) return;

        // Draw Path (Flight Path)
        if (routeData.path && routeData.path.length > 0) {
            var polyline = L.polyline(routeData.path, {
                color: '#0d6efd',
                weight: 4,
                opacity: 0.7,
                dashArray: '10, 10',
                lineJoin: 'round'
            }).addTo(currentRouteLayer);
            
            map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
        }

        // Add POIs
        if (routeData.pois) {
            routeData.pois.forEach(poi => {
                var marker = L.circleMarker([poi.lat, poi.lng], {
                    radius: 8,
                    fillColor: "#0d6efd",
                    color: "#fff",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(currentMarkersLayer);

                marker.bindPopup(`
                    <div class="poi-popup">
                        <strong class="text-primary">${poi.name}</strong><br>
                        <small class="text-muted">${poi.desc}</small>
                    </div>
                `);

                // Pulsing effect simulation
                marker.on('mouseover', function() { this.setRadius(12); });
                marker.on('mouseout', function() { this.setRadius(8); });
            });
        }
    }

    // Auto-select first expedition
    const firstItem = document.querySelector('.destination-item');
    if (firstItem) firstItem.click();
</script>

<?php require_once '../includes/footer.php'; ?>
