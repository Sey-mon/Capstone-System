<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Development - Email Verification Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-lg">
                    <div class="card-header bg-warning text-dark">
                        <h3 class="mb-0">
                            <i class="fas fa-tools"></i> Development Email Verification Panel
                        </h3>
                        <small>⚠️ This panel is only available in development mode</small>
                    </div>
                    <div class="card-body">
                        <!-- Success/Error Messages -->
                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                            </div>
                        @endif
                        
                        @if(session('info'))
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> {{ session('info') }}
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            </div>
                        @endif

                        <!-- Instructions -->
                        <div class="alert alert-info mb-4">
                            <h5><i class="fas fa-info-circle"></i> How to use this panel:</h5>
                            <ol class="mb-0">
                                <li>Register a new account on your app</li>
                                <li>Come back to this page from any device</li>
                                <li>Click "Verify" button for your email</li>
                                <li>Go back to your app and login normally</li>
                            </ol>
                        </div>

                        <!-- Panel Access Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6><i class="fas fa-link"></i> Panel URL</h6>
                                        <code>{{ url('/dev/verification-panel') }}</code>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6><i class="fas fa-mobile-alt"></i> Access from Phone</h6>
                                        <small class="text-muted">Replace 127.0.0.1 with your computer's IP</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Unverified Users List -->
                        @if($unverifiedUsers->count() > 0)
                            <h5><i class="fas fa-users"></i> Unverified Accounts ({{ $unverifiedUsers->count() }})</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Registered</th>
                                            <th>Role</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($unverifiedUsers as $user)
                                            <tr>
                                                <td>
                                                    <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>
                                                </td>
                                                <td>
                                                    <i class="fas fa-envelope text-muted"></i>
                                                    {{ $user->email }}
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $user->created_at->diffForHumans() }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        {{ $user->role->role_name ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('dev.verify', $user->email) }}" 
                                                       class="btn btn-success btn-sm"
                                                       onclick="return confirm('Verify email for {{ $user->email }}?')">
                                                        <i class="fas fa-check"></i> Verify Email
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle text-success" style="font-size: 60px;"></i>
                                <h4 class="mt-3">No Unverified Users</h4>
                                <p class="text-muted">All registered users have verified their email addresses!</p>
                            </div>
                        @endif

                        <!-- Quick Actions -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <a href="{{ url('/') }}" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-arrow-left"></i> Back to App
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button onclick="location.reload()" class="btn btn-outline-secondary btn-lg w-100">
                                    <i class="fas fa-refresh"></i> Refresh List
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
