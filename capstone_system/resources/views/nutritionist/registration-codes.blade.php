@extends('layouts.dashboard')

@section('title', 'Registration Codes Management')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 text-gray-800 mb-0">Registration Codes Management</h1>
            <p class="text-muted">Generate and manage registration codes for patient-parent linking</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Codes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_codes'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-qrcode fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Available</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['unused_codes'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Used</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['used_codes'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-link fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Expired</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['expired_codes'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Patient Registration Codes</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="registrationCodesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Registration Code</th>
                            <th>Status</th>
                            <th>Generated</th>
                            <th>Expires</th>
                            <th>Linked Parent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patientsWithCodes as $patient)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <div class="font-weight-bold">{{ $patient->first_name }} {{ $patient->last_name }}</div>
                                        <div class="text-muted small">Age: {{ $patient->age }} • {{ $patient->gender }}</div>
                                        <div class="text-muted small">{{ $patient->barangay->barangay_name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="font-weight-bold text-primary">{{ $patient->registration_code }}</span>
                            </td>
                            <td>
                                @if($patient->parent_id)
                                    <span class="badge badge-success">Used</span>
                                @elseif($patient->code_expires_at && $patient->code_expires_at < now())
                                    <span class="badge badge-danger">Expired</span>
                                @else
                                    <span class="badge badge-warning">Available</span>
                                @endif
                            </td>
                            <td>
                                @if($patient->code_generated_at)
                                    {{ $patient->code_generated_at->format('M d, Y h:i A') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($patient->code_expires_at)
                                    {{ $patient->code_expires_at->format('M d, Y h:i A') }}
                                    @if($patient->code_expires_at < now())
                                        <br><small class="text-danger">Expired</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($patient->parent)
                                    <div class="font-weight-bold">{{ $patient->parent->first_name }} {{ $patient->parent->last_name }}</div>
                                    <div class="text-muted small">{{ $patient->parent->email }}</div>
                                @else
                                    <span class="text-muted">Not linked</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if(!$patient->parent_id)
                                        <!-- Generate/Regenerate Code -->
                                        @if($patient->registration_code)
                                            <button class="btn btn-sm btn-outline-primary regenerate-code-btn" 
                                                    data-patient-id="{{ $patient->patient_id }}"
                                                    data-patient-name="{{ $patient->first_name }} {{ $patient->last_name }}"
                                                    title="Regenerate Code">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-primary generate-code-btn" 
                                                    data-patient-id="{{ $patient->patient_id }}"
                                                    data-patient-name="{{ $patient->first_name }} {{ $patient->last_name }}"
                                                    title="Generate Code">
                                                <i class="fas fa-qrcode"></i>
                                            </button>
                                        @endif

                                        <!-- Download QR Code -->
                                        @if($patient->registration_code && $patient->qr_code_path)
                                            <a href="{{ route('nutritionist.registration-codes.qr-code', $patient->patient_id) }}" 
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Download QR Code">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @endif
                                    @else
                                        <span class="badge badge-success">Linked</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No registration codes found.</p>
                                    <p class="small">Registration codes are generated for patients who need to be linked to parents.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($patientsWithCodes->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $patientsWithCodes->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Help Section -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-question-circle"></i> How Registration Codes Work
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-qrcode fa-3x text-primary mb-2"></i>
                        <h6 class="font-weight-bold">1. Generate Code</h6>
                        <p class="text-muted small">Create a unique registration code for each patient that needs parent linking.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-share-alt fa-3x text-success mb-2"></i>
                        <h6 class="font-weight-bold">2. Share with Parent</h6>
                        <p class="text-muted small">Provide the registration code or QR code to the patient's parent for linking.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-link fa-3x text-info mb-2"></i>
                        <h6 class="font-weight-bold">3. Automatic Linking</h6>
                        <p class="text-muted small">Parents use the code to link their account with their child's patient record.</p>
                    </div>
                </div>
            </div>
            <div class="alert alert-info mt-3">
                <strong>Note:</strong> Registration codes expire after 30 days and can only be used once. 
                You can regenerate expired or unused codes as needed.
            </div>
        </div>
    </div>
</div>

<!-- Generate Code Modal -->
<div class="modal fade" id="generateCodeModal" tabindex="-1" role="dialog" aria-labelledby="generateCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generateCodeModalLabel">Generate Registration Code</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Generate a registration code for <strong id="generatePatientName"></strong>?</p>
                <p class="text-muted small">This will create a unique code that parents can use to link this patient to their account. The code will expire in 30 days.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmGenerateBtn">
                    <i class="fas fa-qrcode"></i> Generate Code
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Regenerate Code Modal -->
<div class="modal fade" id="regenerateCodeModal" tabindex="-1" role="dialog" aria-labelledby="regenerateCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="regenerateCodeModalLabel">Regenerate Registration Code</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Regenerate registration code for <strong id="regeneratePatientName"></strong>?</p>
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This will invalidate the current registration code and generate a new one. 
                    Any existing QR codes will no longer work.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmRegenerateBtn">
                    <i class="fas fa-redo"></i> Regenerate Code
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">
                    <i class="fas fa-check-circle"></i> Code Generated Successfully
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="alert alert-success">
                        <h4 class="font-weight-bold" id="generatedCode"></h4>
                    </div>
                    <p>Registration code has been generated successfully!</p>
                    <p class="text-muted">Expires: <span id="expirationDate"></span></p>
                    
                    <div class="mt-3">
                        <a href="#" id="downloadQRLink" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download QR Code
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#registrationCodesTable').DataTable({
        "order": [[ 3, "desc" ]], // Order by generated date
        "pageLength": 10,
        "searching": true,
        "lengthChange": false,
        "info": true,
        "columnDefs": [
            { "orderable": false, "targets": [6] } // Actions column not orderable
        ]
    });

    let currentPatientId = null;

    // Generate Code Button Click
    $(document).on('click', '.generate-code-btn', function() {
        currentPatientId = $(this).data('patient-id');
        const patientName = $(this).data('patient-name');
        $('#generatePatientName').text(patientName);
        $('#generateCodeModal').modal('show');
    });

    // Regenerate Code Button Click
    $(document).on('click', '.regenerate-code-btn', function() {
        currentPatientId = $(this).data('patient-id');
        const patientName = $(this).data('patient-name');
        $('#regeneratePatientName').text(patientName);
        $('#regenerateCodeModal').modal('show');
    });

    // Confirm Generate Code
    $('#confirmGenerateBtn').click(function() {
        if (!currentPatientId) return;

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');

        $.ajax({
            url: '{{ route("nutritionist.registration-codes.generate") }}',
            method: 'POST',
            data: {
                patient_id: currentPatientId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#generateCodeModal').modal('hide');
                    showSuccessModal(response.data);
                    setTimeout(() => location.reload(), 3000);
                } else {
                    showError(response.message || 'Failed to generate registration code');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to generate registration code';
                showError(message);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-qrcode"></i> Generate Code');
            }
        });
    });

    // Confirm Regenerate Code
    $('#confirmRegenerateBtn').click(function() {
        if (!currentPatientId) return;

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Regenerating...');

        $.ajax({
            url: '{{ route("nutritionist.registration-codes.regenerate") }}',
            method: 'POST',
            data: {
                patient_id: currentPatientId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#regenerateCodeModal').modal('hide');
                    showSuccessModal(response.data);
                    setTimeout(() => location.reload(), 3000);
                } else {
                    showError(response.message || 'Failed to regenerate registration code');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to regenerate registration code';
                showError(message);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-redo"></i> Regenerate Code');
            }
        });
    });

    // Show Success Modal
    function showSuccessModal(data) {
        $('#generatedCode').text(data.registration_code);
        $('#expirationDate').text(data.expires_at);
        $('#downloadQRLink').attr('href', '{{ route("nutritionist.registration-codes.qr-code", ":id") }}'.replace(':id', currentPatientId));
        $('#successModal').modal('show');
    }

    // Show Error Message
    function showError(message) {
        // You can customize this to show errors in your preferred way
        alert('Error: ' + message);
    }

    // Reset current patient ID when modals are closed
    $('.modal').on('hidden.bs.modal', function() {
        currentPatientId = null;
    });
});
</script>
@endpush