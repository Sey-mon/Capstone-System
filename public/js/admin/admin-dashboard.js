/**
 * Admin Dashboard JavaScript
 * Handles dashboard animations, interactions, and map functionality
 */

// Global variables for map functionality
let adminMap;
let patientsLayer;
let assessmentsLayer;
let barangaysLayer;
let allBarangayMarkers = []; // Store all markers with metadata for filtering
let currentFilter = 'all'; // Track active filter
let highlightedMarker = null; // Track currently highlighted marker
let markerClusterGroup = null; // Marker clustering group

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

// Enhanced icon with hover effect
function createEnhancedIcon(color, size = 24) {
    return L.divIcon({
        className: 'custom-div-icon',
        html: `<div style="background:${color};width:${size}px;height:${size}px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.3);transition:all 0.3s ease;"></div>`,
        iconSize: [size, size],
        iconAnchor: [size/2, size]
    });
}

// Create pie chart icon for barangay markers
function createPieChartIcon(barangay, size = 35) {
    const total = (barangay.sam_count || 0) + (barangay.mam_count || 0) + 
                  (barangay.normal_count || 0) + (barangay.unknown_count || 0);
    
    // Fallback to simple gray icon if no patients
    if (total === 0) {
        return L.divIcon({
            className: 'custom-div-icon',
            html: `<div style="background:#9ca3af;width:${size}px;height:${size}px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>`,
            iconSize: [size, size],
            iconAnchor: [size/2, size]
        });
    }
    
    // Create off-screen canvas
    const canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;
    
    // Create Chart.js doughnut chart
    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ['SAM', 'MAM', 'Normal', 'Unknown'],
            datasets: [{
                data: [
                    barangay.sam_count || 0,
                    barangay.mam_count || 0,
                    barangay.normal_count || 0,
                    barangay.unknown_count || 0
                ],
                backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#6b7280'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: false,
            animation: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });
    
    // Convert canvas to image
    const iconHtml = `<img src="${canvas.toDataURL()}" style="width:${size}px;height:${size}px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.3);">`;
    
    return L.divIcon({
        className: 'pie-chart-marker',
        html: iconHtml,
        iconSize: [size, size],
        iconAnchor: [size/2, size]
    });
}

// Initialize the admin map with enhanced controls
function initializeAdminMap() {
    adminMap = L.map('admin-map', {
        zoomControl: false // We'll add custom zoom control
    }).setView([14.3589, 121.0576], 12);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(adminMap);

    // Add custom zoom control to top-right
    L.control.zoom({
        position: 'topright'
    }).addTo(adminMap);

    patientsLayer = L.layerGroup().addTo(adminMap);
    assessmentsLayer = L.layerGroup().addTo(adminMap);
    barangaysLayer = L.layerGroup().addTo(adminMap);

    // Setup event listeners for filter and search
    setupMapEventListeners();
}

// Setup event listeners for map interactions
function setupMapEventListeners() {
    // Listen for filter events from admin-dashboard-charts.js
    window.addEventListener('filterMapMarkers', function(event) {
        const filter = event.detail;
        currentFilter = filter;
        applyMarkerFilter(filter);
    });

    // Listen for search events from admin-dashboard-charts.js
    window.addEventListener('searchBarangays', function(event) {
        const query = event.detail;
        performBarangaySearch(query);
    });
}

// Apply filter to markers
function applyMarkerFilter(filter) {
    allBarangayMarkers.forEach(markerData => {
        const { marker, barangay } = markerData;
        let shouldShow = false;

        if (filter === 'all') {
            shouldShow = true;
        } else if (filter === 'sam') {
            shouldShow = barangay.sam_count > 0 && 
                        barangay.sam_count >= barangay.mam_count && 
                        barangay.sam_count >= barangay.normal_count;
        } else if (filter === 'mam') {
            shouldShow = barangay.mam_count > 0 && 
                        barangay.mam_count > barangay.sam_count && 
                        barangay.mam_count >= barangay.normal_count;
        } else if (filter === 'normal') {
            shouldShow = barangay.normal_count > 0 && 
                        barangay.normal_count > barangay.sam_count && 
                        barangay.normal_count > barangay.mam_count;
        } else if (filter === 'unknown') {
            shouldShow = (barangay.unknown_count || 0) > 0;
        }

        if (shouldShow) {
            if (!barangaysLayer.hasLayer(marker)) {
                barangaysLayer.addLayer(marker);
            }
            // Fade in animation
            const element = marker._icon;
            if (element) {
                element.style.opacity = '0';
                setTimeout(() => {
                    element.style.transition = 'opacity 0.3s ease';
                    element.style.opacity = '1';
                }, 10);
            }
        } else {
            if (barangaysLayer.hasLayer(marker)) {
                // Fade out animation
                const element = marker._icon;
                if (element) {
                    element.style.transition = 'opacity 0.3s ease';
                    element.style.opacity = '0';
                    setTimeout(() => {
                        barangaysLayer.removeLayer(marker);
                    }, 300);
                } else {
                    barangaysLayer.removeLayer(marker);
                }
            }
        }
    });
}

// Perform barangay search with highlighting
function performBarangaySearch(query) {
    // Clear previous highlight
    if (highlightedMarker) {
        resetMarkerHighlight(highlightedMarker);
        highlightedMarker = null;
    }

    // Empty query - reset view
    if (!query || query.trim().length === 0) {
        if (allBarangayMarkers.length > 0) {
            const bounds = L.latLngBounds(allBarangayMarkers.map(m => m.marker.getLatLng()));
            adminMap.fitBounds(bounds, { padding: [50, 50] });
        }
        return;
    }

    // Search for matching barangays
    const searchTerm = query.toLowerCase().trim();
    const matches = allBarangayMarkers.filter(markerData => 
        markerData.barangay.name.toLowerCase().includes(searchTerm)
    );

    if (matches.length === 0) {
        console.log('No barangays found matching:', query);
        return;
    }

    // If single match, zoom to it and highlight
    if (matches.length === 1) {
        const markerData = matches[0];
        highlightMarker(markerData);
        adminMap.setView(markerData.marker.getLatLng(), 15, {
            animate: true,
            duration: 0.5
        });
        markerData.marker.openPopup();
    } else {
        // Multiple matches - show all in view
        const bounds = L.latLngBounds(matches.map(m => m.marker.getLatLng()));
        adminMap.fitBounds(bounds, { padding: [50, 50] });
        
        // Highlight all matches temporarily
        matches.forEach(markerData => {
            highlightMarker(markerData, 2000); // Auto-unhighlight after 2s
        });
    }
}

// Highlight a marker
function highlightMarker(markerData, autoReset = null) {
    const element = markerData.marker._icon;
    if (element) {
        // Only change shadow and z-index, no transform
        element.style.zIndex = '1000';
        element.querySelector('div').style.boxShadow = '0 0 20px rgba(239, 68, 68, 0.8)';
        
        highlightedMarker = markerData;

        if (autoReset) {
            setTimeout(() => {
                resetMarkerHighlight(markerData);
                if (highlightedMarker === markerData) {
                    highlightedMarker = null;
                }
            }, autoReset);
        }
    }
}

// Reset marker highlight
function resetMarkerHighlight(markerData) {
    const element = markerData.marker._icon;
    if (element) {
        // Only reset shadow and z-index, no transform
        element.style.zIndex = '';
        element.querySelector('div').style.boxShadow = '0 2px 8px rgba(0,0,0,0.3)';
    }
}

// Load map data from server
function loadMapData() {
    // Use barangay data from blade template if available
    if (window.adminDashboard?.barangays) {
        setupBarangayMarkers(window.adminDashboard.barangays);
        return;
    }

    // Fallback: Try to fetch from server
    const mapDataUrl = window.adminDashboard?.mapDataUrl;
    if (mapDataUrl) {
        fetch(mapDataUrl)
            .then(response => response.json())
            .then(data => {
                if (data.barangays) {
                    setupBarangayMarkers(data.barangays);
                }
            })
            .catch(error => {
                console.error('Error loading map data:', error);
            });
    }
}



// Setup barangay markers with data from blade template
function setupBarangayMarkers(barangays) {
    if (!barangays || !Array.isArray(barangays)) {
        console.warn('No barangay data provided');
        return;
    }

    // Clear existing markers
    allBarangayMarkers = [];
    barangaysLayer.clearLayers();

    const assetPath = window.adminDashboard?.assetPath || '/img/markers/';

    barangays.forEach(function(barangay) {
        // Determine dominant status
        let color = '#3b82f6'; // Default blue (normal)
        let status = 'Normal';
        
        if (barangay.sam_count > barangay.mam_count && barangay.sam_count > barangay.normal_count) {
            color = '#ef4444'; // Red for SAM
            status = 'SAM';
        } else if (barangay.mam_count > barangay.sam_count && barangay.mam_count > barangay.normal_count) {
            color = '#f59e0b'; // Orange for MAM
            status = 'MAM';
        }

        // Create pie chart icon
        const icon = createPieChartIcon(barangay, 35);
        
        // Create marker
        const marker = L.marker([barangay.lat, barangay.lng], { 
            icon: icon,
            title: barangay.name
        }).addTo(barangaysLayer);

        // Enhanced popup with better formatting
        const unknownCount = barangay.unknown_count || 0;
        const totalCases = barangay.sam_count + barangay.mam_count + barangay.normal_count + unknownCount;
        
        let popupContent = `
            <div class="barangay-popup" style="min-width:200px;font-family:system-ui,-apple-system,sans-serif;">
                <h6 style="margin:0 0 10px 0;font-size:16px;font-weight:600;color:#1f2937;border-bottom:2px solid ${color};padding-bottom:8px;">
                    ${barangay.name}
                </h6>
                <div style="margin-bottom:8px;">
                    <strong style="color:#6b7280;font-size:12px;">Dominant Status:</strong>
                    <span style="background:${color};color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;margin-left:4px;">
                        ${status}
                    </span>
                </div>
                <div style="background:#f9fafb;padding:10px;border-radius:6px;margin:8px 0;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="color:#ef4444;font-weight:500;">
                            <span style="display:inline-block;width:8px;height:8px;background:#ef4444;border-radius:50%;margin-right:6px;"></span>
                            SAM:
                        </span>
                        <strong style="color:#1f2937;">${barangay.sam_count}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="color:#f59e0b;font-weight:500;">
                            <span style="display:inline-block;width:8px;height:8px;background:#f59e0b;border-radius:50%;margin-right:6px;"></span>
                            MAM:
                        </span>
                        <strong style="color:#1f2937;">${barangay.mam_count}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;${unknownCount > 0 ? 'margin-bottom:6px;' : ''}">
                        <span style="color:#3b82f6;font-weight:500;">
                            <span style="display:inline-block;width:8px;height:8px;background:#3b82f6;border-radius:50%;margin-right:6px;"></span>
                            Normal:
                        </span>
                        <strong style="color:#1f2937;">${barangay.normal_count}</strong>
                    </div>`;
        
        // Add unknown row if there are any unknown cases
        if (unknownCount > 0) {
            popupContent += `
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:#6b7280;font-weight:500;">
                            <span style="display:inline-block;width:8px;height:8px;background:#6b7280;border-radius:50%;margin-right:6px;"></span>
                            Unknown:
                        </span>
                        <strong style="color:#1f2937;">${unknownCount}</strong>
                    </div>`;
        }
        
        popupContent += `
                </div>
                <div style="border-top:1px solid #e5e7eb;padding-top:8px;margin-top:8px;">
                    <strong style="color:#6b7280;font-size:13px;">Total Patients:</strong>
                    <strong style="color:#1f2937;font-size:16px;margin-left:8px;">${totalCases}</strong>
                </div>
            </div>
        `;
        
        marker.bindPopup(popupContent, {
            maxWidth: 300,
            minWidth: 200,
            maxHeight: 400,
            closeButton: true,
            autoClose: true,
            closeOnEscapeKey: true,
            className: 'custom-popup'
        });

        // Enhanced hover interaction - immediate popup
        marker.on('mouseover', function(e) {
            const element = e.target._icon;
            if (element && markerData !== highlightedMarker) {
                // Only change shadow, keep marker static (no transform)
                if (element.querySelector('div')) {
                    element.querySelector('div').style.transition = 'box-shadow 0.2s ease';
                    element.querySelector('div').style.boxShadow = '0 4px 12px rgba(0,0,0,0.5)';
                }
            }
            
            // Show popup immediately - autoClose ensures only one popup at a time
            this.openPopup();
        });

        marker.on('mouseout', function(e) {
            const element = e.target._icon;
            if (element && markerData !== highlightedMarker) {
                // Reset shadow only
                if (element.querySelector('div')) {
                    element.querySelector('div').style.boxShadow = '0 2px 8px rgba(0,0,0,0.3)';
                }
            }
            
            // Don't close popup on mouseout - let autoClose handle it
            // This prevents flickering when moving between marker and popup
        });

        // Click to highlight (no zoom, no pan)
        marker.on('click', function(e) {
            if (highlightedMarker && highlightedMarker !== markerData) {
                resetMarkerHighlight(highlightedMarker);
            }
            highlightMarker(markerData);
            
            // Open popup immediately
            this.openPopup();
        });

        // Store marker with metadata
        const markerData = {
            marker: marker,
            barangay: barangay,
            status: status,
            color: color
        };
        
        allBarangayMarkers.push(markerData);
    });

    // Fit map to show all barangays initially, then zoom to preferred level
    if (allBarangayMarkers.length > 0) {
        const bounds = L.latLngBounds(allBarangayMarkers.map(m => m.marker.getLatLng()));
        const center = bounds.getCenter();
        
        // Set the center to the middle of all markers and zoom to 12
        adminMap.setView(center, 13);
    }
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
        
        // Load map data - this will call setupBarangayMarkers internally
        // This prevents double marker creation
        loadMapData();
    }
});
