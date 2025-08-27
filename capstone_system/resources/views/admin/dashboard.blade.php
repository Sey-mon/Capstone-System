@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Welcome back, ' . Auth::user()->first_name . '! Here\'s what\'s happening today.')

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""/>
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
            align-items: start;
        }
        
        .dashboard-grid .content-card {
            height: 500px;
            display: flex;
            flex-direction: column;
        }
        
        .dashboard-grid .card-content {
            flex: 1;
            overflow-y: auto;
            max-height: 420px;
        }
        
        .map-controls {
            display: flex;
            gap: 0.5rem;
        }
        
        .map-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 0.75rem;
            background: var(--bg-secondary);
            border-radius: 6px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }
        
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .map-popup {
            font-family: inherit;
            font-size: 0.875rem;
        }
        
        .map-popup h6 {
            margin: 0 0 0.5rem 0;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .map-popup p {
            margin: 0.25rem 0;
            color: var(--text-secondary);
        }
        
        /* Custom leaflet popup styling */
        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .leaflet-popup-content {
            margin: 12px 16px;
            line-height: 1.4;
        }
        
        #admin-map {
            height: 320px !important;
            border-radius: 8px;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-grid .content-card {
                height: auto;
            }
            
            .map-controls {
                flex-wrap: wrap;
            }
        }
    </style>
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Users</div>
                <div class="stat-icon primary">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_users'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>+12% from last month</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Patients</div>
                <div class="stat-icon success">
                    <i class="fas fa-user-injured"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_patients'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>+8% from last month</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Assessments</div>
                <div class="stat-icon warning">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_assessments'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>+15% from last month</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Inventory Items</div>
                <div class="stat-icon info">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_inventory_items'] }}</div>
            <div class="stat-change negative">
                <i class="fas fa-arrow-down"></i>
                <span>-3% from last month</span>
            </div>
        </div>
    </div>

    <!-- Dashboard Grid Layout -->
    <div class="dashboard-grid">
        <!-- Recent Activity (Left Side) -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Recent Activity</h3>
                <a href="{{ route('admin.reports') }}" class="btn btn-secondary">
                    <i class="fas fa-chart-line"></i>
                    View All Reports
                </a>
            </div>
            <div class="card-content">
                @forelse($stats['recent_audit_logs'] as $log)
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <strong>{{ $log->user->first_name ?? 'System' }}</strong> {{ $log->description }}
                                </div>
                                <div class="activity-time">{{ $log->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <i class="fas fa-inbox text-gray-400 text-4xl mb-3"></i>
                        <p class="text-gray-500">No recent activity found.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Geographic Overview Map (Right Side) -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Geographic Overview</h3>
                <!-- Removed Barangays, Patients, and Assessments buttons -->
            </div>
            <div class="card-content">
                <div id="admin-map"></div>
                <div class="map-legend mt-3">
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: #ef4444;"></span>
                        <span>SAM Patients</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: #f59e0b;"></span>
                        <span>MAM Patients</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: #3b82f6;"></span>
                        <span>Normal Patients</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

    <script src="{{ asset('js/admin/admin-dashboard.js') }}"></script>

    <script>
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

    let adminMap;
    let patientsLayer;
    let assessmentsLayer;
    let barangaysLayer;

    document.addEventListener('DOMContentLoaded', function() {
        initializeAdminMap();
        loadMapData();
    });

    function initializeAdminMap() {
        adminMap = L.map('admin-map').setView([14.3589, 121.0576], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(adminMap);

        patientsLayer = L.layerGroup().addTo(adminMap);
        assessmentsLayer = L.layerGroup().addTo(adminMap);
        barangaysLayer = L.layerGroup().addTo(adminMap);

        const barangays = @json($barangays);
        const samIcon = safeIcon('/img/markers/marker-red.svg', '#ef4444');
        const mamIcon = safeIcon('/img/markers/marker-orange.svg', '#f59e0b');
        const normalIcon = safeIcon('/img/markers/marker-blue.svg', '#3b82f6');

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
        
        function loadMapData() {
            // Fetch geographic data from the server
            fetch('/admin/dashboard/map-data')
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
                // Define custom icons for patient status
                const samIcon = L.icon({
                    iconUrl: '/img/markers/marker-red.svg',
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -32]
                });
                const mamIcon = L.icon({
                    iconUrl: '/img/markers/marker-orange.svg',
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -32]
                });
                const normalIcon = L.icon({
                    iconUrl: '/img/markers/marker-blue.svg',
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
        
    </script>
@endpush
