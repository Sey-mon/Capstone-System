/**
 * Admin Dashboard JavaScript
 * Handles dashboard animations, interactions, and map functionality
 */

// Global variables for map functionality
let adminMap;
let patientsLayer;
let assessmentsLayer;
let barangaysLayer;

// Helper to use SVG or fallback to colored circle
function safeIcon(url, color) {
    // Try to use SVG, fallback to colored divIcon
    const img = new Image();
    img.src = url;
    img.onerror = function() {};
    // Always use divIcon for reliability
    return L.divIcon({
        className: 'custom-div-icon',
        html: `<div style="background:${color};width:24px;height:24px;border-radius:50%;border:2px solid #fff;"></div>`,
        iconSize: [24, 24],
        iconAnchor: [12, 24]
    });
}

// Initialize the admin map
function initializeAdminMap() {
    adminMap = L.map('admin-map').setView([14.3589, 121.0576], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(adminMap);

    patientsLayer = L.layerGroup().addTo(adminMap);
    assessmentsLayer = L.layerGroup().addTo(adminMap);
    barangaysLayer = L.layerGroup().addTo(adminMap);
}

// Load map data from server
function loadMapData() {
    // Note: The route will need to be passed from the blade template
    const mapDataUrl = window.adminDashboard?.mapDataUrl;
    
    if (!mapDataUrl) {
        console.warn('Map data URL not provided, adding sample markers');
        addSampleMarkers();
        return;
    }

    // Fetch geographic data from the server
    fetch(mapDataUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateMapWithData(data.data);
            }
        })
        .catch(error => {
            console.error('Error loading map data:', error);
            // Add some sample markers for demonstration
            addSampleMarkers();
        });
}

// Fallback function for demo/sample markers
function addSampleMarkers() {
    // Clear layers if they exist
    if (patientsLayer) patientsLayer.clearLayers();
    if (assessmentsLayer) assessmentsLayer.clearLayers();
    if (barangaysLayer) barangaysLayer.clearLayers();

    // Add a sample marker to the map for demonstration
    const sampleMarker = L.marker([14.3589, 121.0576]).bindPopup('<strong>Sample Marker</strong><br>This is a demo marker.');
    patientsLayer.addLayer(sampleMarker);
}

// Populate map with data from server
function populateMapWithData(data) {
    // Clear existing layers
    patientsLayer.clearLayers();
    assessmentsLayer.clearLayers();
    barangaysLayer.clearLayers();
    
    // Add barangay markers first (as base layer)
    if (data.barangays) {
        data.barangays.forEach(barangay => {
            const color = barangay.activity_level === 'high' ? '#ef4444' : 
                          barangay.activity_level === 'medium' ? '#f59e0b' : '#10b981';

            // Use barangay_name if name is missing or null
            const displayName = barangay.name || barangay.barangay_name || 'Unknown';

            // Show SAM, MAM, Normal counts if available, else fallback to patient_count
            let popupHtml = `<div class="map-popup">
                <h6>${displayName}</h6>`;
            if ('sam_count' in barangay && 'mam_count' in barangay && 'normal_count' in barangay) {
                popupHtml += `<p>SAM: ${barangay.sam_count}</p>
                    <p>MAM: ${barangay.mam_count}</p>
                    <p>Normal: ${barangay.normal_count}</p>`;
            }
            if ('patient_count' in barangay) {
                popupHtml += `<p>Patients: ${barangay.patient_count}</p>`;
            }
            if ('activity_level' in barangay) {
                popupHtml += `<p>Activity Level: ${barangay.activity_level.charAt(0).toUpperCase() + barangay.activity_level.slice(1)}</p>`;
            }
            popupHtml += `</div>`;

            const marker = L.circleMarker([barangay.lat, barangay.lng], {
                color: color,
                fillColor: color,
                fillOpacity: 0.3,
                radius: Math.max(10, ('patient_count' in barangay ? barangay.patient_count : 5) * 2),
                weight: 2
            }).bindPopup(popupHtml);
            barangaysLayer.addLayer(marker);
        });
    }
    
    // Add patient markers
    if (data.patients) {
        // Define custom icons for patient status using asset paths
        const assetPath = window.adminDashboard?.assetPath || '/img/markers/';
        const samIcon = L.icon({
            iconUrl: assetPath + "marker-red.svg",
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });
        const mamIcon = L.icon({
            iconUrl: assetPath + "marker-orange.svg",
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });
        const normalIcon = L.icon({
            iconUrl: assetPath + "marker-blue.svg",
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        data.patients.forEach(patient => {
            if (patient.barangay_lat && patient.barangay_lng) {
                let icon;
                if (patient.status === 'SAM') {
                    icon = samIcon;
                } else if (patient.status === 'MAM') {
                    icon = mamIcon;
                } else {
                    icon = normalIcon;
                }
                const marker = L.marker([patient.barangay_lat, patient.barangay_lng], { icon }).bindPopup(`
                    <div class="map-popup">
                        <h6>${patient.name}</h6>
                        <p>Barangay: ${patient.barangay}</p>
                        <p>Age: ${patient.age_months} months</p>
                        <p>Sex: ${patient.sex}</p>
                        <p>Status: ${patient.status}</p>
                    </div>
                `);
                patientsLayer.addLayer(marker);
            }
        });
    }
    
    // Add assessment markers
    if (data.assessments) {
        data.assessments.forEach(assessment => {
            if (assessment.lat && assessment.lng) {
                const marker = L.circleMarker([assessment.lat, assessment.lng], {
                    color: '#10b981',
                    fillColor: '#10b981',
                    fillOpacity: 0.8,
                    radius: 4,
                    weight: 2
                }).bindPopup(`
                    <div class="map-popup">
                        <h6>Assessment #${assessment.id}</h6>
                        <p>Patient: ${assessment.patient_name}</p>
                        <p>Date: ${assessment.date}</p>
                        <p>Status: ${assessment.status}</p>
                    </div>
                `);
                assessmentsLayer.addLayer(marker);
            }
        });
    }
}

// Setup barangay markers with data from blade template
function setupBarangayMarkers(barangays) {
    if (!barangays || !Array.isArray(barangays)) {
        console.warn('No barangay data provided');
        return;
    }

    const assetPath = window.adminDashboard?.assetPath || '/img/markers/';
    const samIcon = safeIcon(assetPath + "marker-red.svg", '#ef4444');
    const mamIcon = safeIcon(assetPath + "marker-orange.svg", '#f59e0b');
    const normalIcon = safeIcon(assetPath + "marker-blue.svg", '#3b82f6');

    barangays.forEach(function(barangay) {
        let icon = normalIcon;
        if (barangay.sam_count > barangay.mam_count && barangay.sam_count > barangay.normal_count) {
            icon = samIcon;
        } else if (barangay.mam_count > barangay.sam_count && barangay.mam_count > barangay.normal_count) {
            icon = mamIcon;
        }
        const marker = L.marker([barangay.lat, barangay.lng], { icon }).addTo(barangaysLayer);
        marker.on('mouseover', function(e) {
            marker.bindPopup(
                `<div>
                    <strong>${barangay.name}</strong><br>
                    SAM: ${barangay.sam_count}<br>
                    MAM: ${barangay.mam_count}<br>
                    Normal: ${barangay.normal_count}
                </div>`
            ).openPopup();
        });
        marker.on('mouseout', function(e) {
            marker.closePopup();
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Animate stat numbers when page loads
    const statValues = document.querySelectorAll('.stat-value');
    
    statValues.forEach(element => {
        const target = parseInt(element.textContent);
        if (window.DashboardUtils && window.DashboardUtils.animateCounter) {
            window.DashboardUtils.animateCounter(element, target, 1500);
        }
    });

    // Initialize map if the map container exists
    if (document.getElementById('admin-map')) {
        initializeAdminMap();
        loadMapData();
        
        // Setup barangay markers if data is available
        if (window.adminDashboard?.barangays) {
            setupBarangayMarkers(window.adminDashboard.barangays);
        }
    }
});
