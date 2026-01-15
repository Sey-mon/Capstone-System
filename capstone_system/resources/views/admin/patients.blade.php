@extends('layouts.dashboard')

@section('title', 'Patients Management')

@section('page-title', 'Patients Management')
@section('page-subtitle', 'Manage and monitor all patients in the system.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-patients.css') }}">
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <div class="patients-management">
        <!-- Advanced Filters Panel -->
        <div class="filters-panel">
            <div class="filters-header">
                <h4><i class="fas fa-filter"></i> Filters & Search</h4>
                <div class="header-actions">
                    <button class="btn btn-sm btn-outline">
                        <i class="fas fa-times"></i>
                        Clear All
                    </button>
                    <button class="btn btn-sm btn-secondary">
                        <i class="fas fa-sync"></i>
                        Refresh
                    </button>
                </div>
            </div>
            <div class="filters-content">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="searchPatient">Search Patient</label>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchPatient" placeholder="Search by name, contact...">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label for="filterBarangay">Barangay</label>
                                                <select id="filterBarangay">
                            <option value="">All Barangays</option>
                            @foreach($barangays ?? [] as $barangay)
                                <option value="{{ $barangay->barangay_name }}">{{ $barangay->barangay_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterGender">Gender</label>
                        <select id="filterGender">
                            <option value="">All Genders</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterAgeRange">Age Range</label>
                        <select id="filterAgeRange">
                            <option value="">All Ages</option>
                            <option value="0-12">0-12 months</option>
                            <option value="13-24">13-24 months</option>
                            <option value="25-36">25-36 months</option>
                            <option value="37-48">37-48 months</option>
                            <option value="49+">49+ months</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterNutritionist">Nutritionist</label>
                                                <select id="filterNutritionist">
                            <option value="">All Nutritionists</option>
                            @foreach($nutritionists ?? [] as $nutritionist)
                                <option value="{{ $nutritionist->first_name }} {{ $nutritionist->last_name }}">
                                    {{ $nutritionist->first_name }} {{ $nutritionist->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Summary -->
        <div class="results-summary">
            <div class="summary-stats">
                <span class="total-count">Total: <strong id="totalPatients">{{ $patients->count() }}</strong> patients</span>
                <span class="filtered-count filtered-count-hidden" id="filteredCount">Showing: <strong id="visiblePatients">0</strong> patients</span>
            </div>
            <div class="view-options">
                <div class="view-toggle">
                    <button class="btn btn-sm view-btn active" data-view="table">
                        <i class="fas fa-table"></i> Table
                    </button>
                    <button class="btn btn-sm view-btn" data-view="grid">
                        <i class="fas fa-th-large"></i> Grid
                    </button>
                </div>
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Patient
                </button>
            </div>
        </div>

        <!-- Patients Content -->
        <div class="patients-content">
            @if($patients->count() > 0)
                <!-- Table View -->
                <div id="tableView" class="view-container active">
                    <div class="enhanced-table-container">
                        <table class="enhanced-patients-table" id="patientsTable">
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th class="sortable" data-sort="name">
                                        <span>Patient</span>
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="sortable" data-sort="age">
                                        <span>Age</span>
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="sortable" data-sort="gender">
                                        <span>Gender</span>
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="sortable" data-sort="barangay">
                                        <span>Barangay</span>
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="sortable" data-sort="parent">
                                        <span>Parent</span>
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="sortable" data-sort="nutritionist">
                                        <span>Nutritionist</span>
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="patientsTableBody">
                                @foreach($patients as $patient)
                                    <tr class="patient-row" 
                                        data-name="{{ strtolower($patient->first_name . ' ' . $patient->last_name) }}"
                                        data-age="{{ $patient->age_months }}"
                                        data-gender="{{ $patient->sex }}"
                                        data-barangay="{{ $patient->barangay ? $patient->barangay->barangay_name : '' }}"
                                        data-parent="{{ $patient->parent ? $patient->parent->first_name . ' ' . $patient->parent->last_name : '' }}"
                                        data-nutritionist="{{ $patient->nutritionist ? $patient->nutritionist->first_name . ' ' . $patient->nutritionist->last_name : '' }}"
                                        data-contact="{{ $patient->contact_number }}">
                                        <td>
                                            <span class="badge bg-primary">{{ $patient->custom_patient_id }}</span>
                                        </td>
                                        <td class="patient-info-cell">
                                            <div class="patient-details">
                                                <div class="patient-name">{{ $patient->first_name }} {{ $patient->last_name }}</div>
                                                @if($patient->middle_name)
                                                    <small class="text-muted">{{ $patient->middle_name }}</small>
                                                @endif
                                                <div class="patient-admission">Admitted: {{ $patient->date_of_admission->format('M d, Y') }}</div>
                                            </div>
                                        </td>
                                        <td class="age-cell">
                                            <span class="age-months">{{ $patient->age_months }} months</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $patient->sex === 'Male' ? 'primary' : 'secondary' }}">
                                                <i class="fas fa-{{ $patient->sex === 'Male' ? 'mars' : 'venus' }}"></i>
                                                {{ $patient->sex }}
                                            </span>
                                        </td>
                                        <td class="barangay-cell">
                                            @if($patient->barangay)
                                                <div class="barangay-name">{{ $patient->barangay->barangay_name }}</div>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </td>
                                        <td class="parent-cell">
                                            @if($patient->parent)
                                                <div class="parent-name">{{ $patient->parent->first_name }} {{ $patient->parent->last_name }}</div>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </td>
                                        <td class="nutritionist-cell">
                                            @if($patient->nutritionist)
                                                <div class="nutritionist-name">{{ $patient->nutritionist->first_name }} {{ $patient->nutritionist->last_name }}</div>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </td>
                                        <td class="actions-cell">
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary" data-patient-id="{{ $patient->patient_id }}" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" data-patient-id="{{ $patient->patient_id }}" title="Assessment History">
                                                    <i class="fas fa-chart-line"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" data-patient-id="{{ $patient->patient_id }}" title="Edit Patient">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" data-patient-id="{{ $patient->patient_id }}" title="Delete Patient">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <!-- Pagination Links -->
                        <div class="pagination-wrapper">
                            {{ $patients->links() }}
                        </div>
                    </div>
                </div>

                <!-- Grid View -->
                <div id="gridView" class="view-container grid-view-hidden">
                    <div class="patients-grid" id="patientsGrid">
                        @foreach($patients as $patient)
                            <div class="patient-card" 
                                data-name="{{ strtolower($patient->first_name . ' ' . $patient->last_name) }}"
                                data-age="{{ $patient->age_months }}"
                                data-gender="{{ $patient->sex }}"
                                data-barangay="{{ $patient->barangay ? $patient->barangay->barangay_name : '' }}"
                                data-parent="{{ $patient->parent ? $patient->parent->first_name . ' ' . $patient->parent->last_name : '' }}"
                                data-nutritionist="{{ $patient->nutritionist ? $patient->nutritionist->first_name . ' ' . $patient->nutritionist->last_name : '' }}"
                                data-contact="{{ $patient->contact_number }}">
                                <div class="card-header">
                                    <div class="patient-info">
                                        <h4 class="patient-name">{{ $patient->first_name }} {{ $patient->last_name }}</h4>
                                        @if($patient->middle_name)
                                            <p class="middle-name">{{ $patient->middle_name }}</p>
                                        @endif
                                        <div class="patient-meta">
                                            <span class="age">{{ $patient->age_months }} months</span>
                                            <span class="gender badge badge-{{ $patient->sex === 'Male' ? 'primary' : 'secondary' }}">
                                                <i class="fas fa-{{ $patient->sex === 'Male' ? 'mars' : 'venus' }}"></i>
                                                {{ $patient->sex }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="info-row">
                                        <span class="label">Barangay:</span>
                                        <span class="value">{{ $patient->barangay ? $patient->barangay->barangay_name : 'Not assigned' }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="label">Parent:</span>
                                        <span class="value">{{ $patient->parent ? $patient->parent->first_name . ' ' . $patient->parent->last_name : 'Not assigned' }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="label">Nutritionist:</span>
                                        <span class="value">{{ $patient->nutritionist ? $patient->nutritionist->first_name . ' ' . $patient->nutritionist->last_name : 'Not assigned' }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="label">Contact:</span>
                                        <span class="value">{{ $patient->contact_number }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="label">Admitted:</span>
                                        <span class="value">{{ $patient->date_of_admission->format('M d, Y') }}</span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-primary" data-patient-id="{{ $patient->patient_id }}" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-info" data-patient-id="{{ $patient->patient_id }}" title="Assessment History">
                                            <i class="fas fa-chart-line"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" data-patient-id="{{ $patient->patient_id }}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" data-patient-id="{{ $patient->patient_id }}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- No Results Message -->
                <div id="noResults" class="no-results no-results-hidden">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No patients found</h3>
                    <p>Try adjusting your filters or search terms</p>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="empty-state-title">No Patients Found</h3>
                    <p class="empty-state-description">
                        No patients have been registered yet. Click "Add Patient" to register the first patient.
                    </p>
                    <button class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add First Patient
                    </button>
                </div>
            @endif
            </div>
        </div>
    </div>

    <!-- Form Data Storage (Hidden) -->
    <script id="parentsData" type="application/json">
        {!! json_encode($parents ?? []) !!}
    </script>
    <script id="nutritionistsData" type="application/json">
        {!! json_encode($nutritionists ?? []) !!}
    </script>
    <script id="barangaysData" type="application/json">
        {!! json_encode($barangays ?? []) !!}
    </script>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/admin-patients-swal.js') }}"></script>
@endpush
