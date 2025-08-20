@extends('layouts.dashboard')

@section('title', 'WHO Standards')

@section('page-title', 'WHO Growth Standards')
@section('page-subtitle', 'WHO Reference Data for Child Growth Assessment')

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- Error Display -->
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('admin.api.management') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to API Management
        </a>
    </div>

    <!-- Standards Overview -->
    <div class="content-section">
        <div class="section-header">
            <h2>WHO Growth Standards Overview</h2>
            <p>Reference data used for malnutrition assessment calculations</p>
        </div>

        <div class="standards-grid">
            @if(isset($maleWfa))
            <div class="standard-card">
                <h3>Male - Weight for Age</h3>
                <div class="standard-info">
                    <p><strong>Records:</strong> {{ count($maleWfa['data'] ?? []) }}</p>
                    <p><strong>Age Range:</strong> 0-60 months</p>
                    <p><strong>Last Updated:</strong> {{ $maleWfa['last_updated'] ?? 'N/A' }}</p>
                </div>
                <button onclick="showStandardData('male-wfa', {{ json_encode($maleWfa) }})" class="btn btn-outline-primary btn-sm">
                    View Data
                </button>
            </div>
            @endif

            @if(isset($femaleWfa))
            <div class="standard-card">
                <h3>Female - Weight for Age</h3>
                <div class="standard-info">
                    <p><strong>Records:</strong> {{ count($femaleWfa['data'] ?? []) }}</p>
                    <p><strong>Age Range:</strong> 0-60 months</p>
                    <p><strong>Last Updated:</strong> {{ $femaleWfa['last_updated'] ?? 'N/A' }}</p>
                </div>
                <button onclick="showStandardData('female-wfa', {{ json_encode($femaleWfa) }})" class="btn btn-outline-primary btn-sm">
                    View Data
                </button>
            </div>
            @endif

            @if(isset($maleLhfa))
            <div class="standard-card">
                <h3>Male - Length/Height for Age</h3>
                <div class="standard-info">
                    <p><strong>Records:</strong> {{ count($maleLhfa['data'] ?? []) }}</p>
                    <p><strong>Age Range:</strong> 0-60 months</p>
                    <p><strong>Last Updated:</strong> {{ $maleLhfa['last_updated'] ?? 'N/A' }}</p>
                </div>
                <button onclick="showStandardData('male-lhfa', {{ json_encode($maleLhfa) }})" class="btn btn-outline-primary btn-sm">
                    View Data
                </button>
            </div>
            @endif

            @if(isset($femaleLhfa))
            <div class="standard-card">
                <h3>Female - Length/Height for Age</h3>
                <div class="standard-info">
                    <p><strong>Records:</strong> {{ count($femaleLhfa['data'] ?? []) }}</p>
                    <p><strong>Age Range:</strong> 0-60 months</p>
                    <p><strong>Last Updated:</strong> {{ $femaleLhfa['last_updated'] ?? 'N/A' }}</p>
                </div>
                <button onclick="showStandardData('female-lhfa', {{ json_encode($femaleLhfa) }})" class="btn btn-outline-primary btn-sm">
                    View Data
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Information Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>About WHO Standards</h2>
            <p>Understanding the reference data used in assessments</p>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <h4>Weight for Age (WFA)</h4>
                <p>Used to assess underweight status. Reflects body mass relative to chronological age.</p>
                <ul>
                    <li>Below -2 SD: Underweight</li>
                    <li>Below -3 SD: Severely underweight</li>
                </ul>
            </div>

            <div class="info-item">
                <h4>Length/Height for Age (LFA/HFA)</h4>
                <p>Used to assess stunting. Reflects achieved growth in length/height relative to age.</p>
                <ul>
                    <li>Below -2 SD: Stunted</li>
                    <li>Below -3 SD: Severely stunted</li>
                </ul>
            </div>

            <div class="info-item">
                <h4>Z-Score Calculation</h4>
                <p>Standard deviations from the median of the reference population.</p>
                <ul>
                    <li>Z-score = (Observed value - Median) / SD</li>
                    <li>Indicates how many standard deviations away from normal</li>
                </ul>
            </div>

            <div class="info-item">
                <h4>Age Ranges</h4>
                <p>Different standards apply to different age groups:</p>
                <ul>
                    <li>0-24 months: Length-based measurements</li>
                    <li>24-60 months: Height-based measurements</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

<!-- Modal for displaying standard data -->
<div class="modal fade" id="standardModal" tabindex="-1" aria-labelledby="standardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="standardModalLabel">WHO Standard Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="standardDataContent"></div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function showStandardData(type, data) {
    const modal = new bootstrap.Modal(document.getElementById('standardModal'));
    const content = document.getElementById('standardDataContent');
    
    let title = type.replace('-', ' ').toUpperCase();
    document.getElementById('standardModalLabel').textContent = `WHO Standard: ${title}`;
    
    // Create table with data
    let html = '<div class="table-responsive"><table class="table table-striped table-sm">';
    html += '<thead><tr>';
    
    if (data.data && data.data.length > 0) {
        // Get headers from first row
        Object.keys(data.data[0]).forEach(key => {
            html += `<th>${key.replace('_', ' ').toUpperCase()}</th>`;
        });
        html += '</tr></thead><tbody>';
        
        // Add data rows (limit to first 50 for performance)
        data.data.slice(0, 50).forEach(row => {
            html += '<tr>';
            Object.values(row).forEach(value => {
                html += `<td>${value}</td>`;
            });
            html += '</tr>';
        });
        
        if (data.data.length > 50) {
            html += `<tr><td colspan="${Object.keys(data.data[0]).length}" class="text-center text-muted">
                <em>Showing first 50 of ${data.data.length} records</em>
            </td></tr>`;
        }
    } else {
        html += '<th>No Data</th></tr></thead><tbody>';
        html += '<tr><td>No standard data available</td></tr>';
    }
    
    html += '</tbody></table></div>';
    
    content.innerHTML = html;
    modal.show();
}
</script>

<style>
.standards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.standard-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.standard-card h3 {
    color: #1f2937;
    margin-bottom: 1rem;
    font-size: 1.2rem;
    font-weight: 600;
}

.standard-info p {
    margin-bottom: 0.5rem;
    color: #6b7280;
    font-size: 0.9rem;
}

.standard-info strong {
    color: #374151;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.info-item {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.info-item h4 {
    color: #1f2937;
    margin-bottom: 1rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.info-item p {
    color: #6b7280;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.info-item ul {
    margin: 0;
    padding-left: 1.2rem;
}

.info-item li {
    color: #6b7280;
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

.modal-xl .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.table-responsive {
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,.02);
}
</style>
@endsection
