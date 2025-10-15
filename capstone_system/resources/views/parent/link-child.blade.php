@extends('layouts.dashboard')

@section('title', 'Link Child')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/parent/link-child.css') }}">
@endpush

@section('page-title')
    <div class="modern-page-header">
        <div class="header-content">
            <h1 class="header-title">Link Your Child</h1>
            <p class="header-subtitle">Connect your account to your child using the registration code from your nutritionist</p>
        </div>
    </div>
@endsection

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')

<div class="page-container">
    <div class="container-fluid px-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Link Child Card -->
                <div class="link-child-card">
                    <div class="card-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    
                    <h3 class="card-title">Enter Registration Code</h3>
                    <p class="card-description">
                        Please enter the registration code provided by your nutritionist to link your child to this account.
                    </p>

                    <form method="POST" action="{{ route('parent.link.child') }}" class="link-child-form">
                        @csrf
                        
                        <div class="form-group">
                            <label for="registration_code" class="form-label">
                                <i class="fas fa-barcode"></i>
                                Registration Code
                            </label>
                            <input type="text" 
                                   class="form-control @error('registration_code') is-invalid @enderror" 
                                   id="registration_code" 
                                   name="registration_code" 
                                   placeholder="CHD-ABC123"
                                   value="{{ old('registration_code') }}"
                                   maxlength="10"
                                   pattern="CHD-[A-Z0-9]{6}"
                                   required>
                            <div class="form-help">
                                Format: CHD-ABC123 (provided by your nutritionist)
                            </div>
                            @error('registration_code')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-link"></i>
                                Link Child
                            </button>
                            <a href="{{ route('parent.children') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i>
                                Back to Children
                            </a>
                        </div>
                    </form>

                    <!-- QR Code Scanner Option -->
                    <div class="qr-scanner-section">
                        <div class="divider">
                            <span>OR</span>
                        </div>
                        
                        <div class="qr-option">
                            <div class="qr-icon">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <div class="qr-content">
                                <h4>Scan QR Code</h4>
                                <p>Use your phone camera to scan the QR code from your nutritionist</p>
                                <button type="button" class="btn btn-outline-success" id="scanQrBtn">
                                    <i class="fas fa-camera"></i>
                                    Scan QR Code
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="help-section">
                        <h5><i class="fas fa-question-circle"></i> Need Help?</h5>
                        <ul class="help-list">
                            <li>The registration code is provided by your child's nutritionist</li>
                            <li>Codes are in the format CHD-ABC123 (10 characters)</li>
                            <li>Each code can only be used once and expires after 30 days</li>
                            <li>Contact your nutritionist if you don't have a code or if it's expired</li>
                        </ul>
                    </div>
                </div>

                <!-- Already Linked Children -->
                @if(isset($linkedChildren) && count($linkedChildren) > 0)
                    <div class="linked-children-section">
                        <h4 class="section-title">
                            <i class="fas fa-users"></i>
                            Already Linked Children
                        </h4>
                        
                        <div class="children-grid">
                            @foreach($linkedChildren as $child)
                                <div class="child-card linked">
                                    <div class="child-info">
                                        <div class="child-avatar">
                                            <i class="fas fa-child"></i>
                                        </div>
                                        <div class="child-details">
                                            <h5 class="child-name">{{ $child->first_name }} {{ $child->last_name }}</h5>
                                            <p class="child-meta">
                                                <span class="age">{{ $child->age_months }} months old</span>
                                                <span class="status linked">
                                                    <i class="fas fa-check-circle"></i>
                                                    Linked
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="child-actions">
                                        <a href="{{ route('parent.children') }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<!-- QR Scanner Modal (for future implementation) -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scan QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="qr-scanner-placeholder">
                    <i class="fas fa-qrcode"></i>
                    <p>QR Scanner will be available in a future update</p>
                    <p class="text-muted">For now, please enter the code manually above</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // QR Scanner placeholder
    const scanQrBtn = document.getElementById('scanQrBtn');
    if (scanQrBtn) {
        scanQrBtn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('qrScannerModal'));
            modal.show();
        });
    }

    // Format registration code input
    const codeInput = document.getElementById('registration_code');
    if (codeInput) {
        codeInput.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();
            
            // Remove any non-alphanumeric characters except hyphens
            value = value.replace(/[^A-Z0-9-]/g, '');
            
            // Auto-format to CHD-XXXXXX pattern
            if (value.length > 0 && !value.startsWith('CHD-')) {
                if (value.startsWith('CHD')) {
                    value = 'CHD-' + value.substring(3);
                } else {
                    value = 'CHD-' + value;
                }
            }
            
            // Limit to 10 characters total
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            
            e.target.value = value;
        });
    }
});
</script>
@endpush