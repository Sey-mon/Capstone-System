@if($patients->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <a href="#" class="sort-link" data-sort="name">
                            Patient Name 
                            <i class="fas fa-sort"></i>
                        </a>
                    </th>
                    <th>Parent</th>
                    <th>Contact</th>
                    <th>
                        <a href="#" class="sort-link" data-sort="age">
                            Age 
                            <i class="fas fa-sort"></i>
                        </a>
                    </th>
                    <th>Sex</th>
                    <th>
                        <a href="#" class="sort-link" data-sort="barangay">
                            Barangay 
                            <i class="fas fa-sort"></i>
                        </a>
                    </th>
                    <th>
                        <a href="#" class="sort-link" data-sort="date_admitted">
                            Date Admitted 
                            <i class="fas fa-sort"></i>
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patients as $patient)
                    <tr>
                        <td>
                            <div class="user-info">
                                <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong>
                                @if($patient->middle_name)
                                    <small class="text-muted">{{ $patient->middle_name }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($patient->parent)
                                {{ $patient->parent->first_name }} {{ $patient->parent->last_name }}
                            @else
                                <span class="text-muted">Not assigned</span>
                            @endif
                        </td>
                        <td>{{ $patient->contact_number }}</td>
                        <td>{{ $patient->age_months }} months</td>
                        <td>{{ $patient->sex }}</td>
                        <td>{{ $patient->barangay->barangay_name ?? 'Unknown' }}</td>
                        <td>{{ $patient->date_of_admission->format('M d, Y') }}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-info" onclick="viewPatient({{ $patient->patient_id }})" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="editPatient({{ $patient->patient_id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deletePatient({{ $patient->patient_id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $patients->appends(request()->query())->links() }}
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-user-injured"></i>
        </div>
        <h3>No Patients Found</h3>
        <p>No patients match your current filters or search criteria.</p>
        <button class="btn btn-outline-secondary" onclick="clearFilters()">
            <i class="fas fa-times"></i>
            Clear Filters
        </button>
    </div>
@endif
