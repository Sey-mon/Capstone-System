@extends('layouts.dashboard')

@section('title', 'My Children')
@section('page-title', 'My Children')
@section('page-subtitle', 'View all your registered children.')

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')
<div class="content-card">
    <div class="card-header">
        <h3 class="card-title">My Children</h3>
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#bindChildModal">
            Bind a Child
        </button>
    </div>
    <div class="card-content">
        @if(isset($children) && count($children) > 0)
                <ul class="list-group">
                    @foreach($children as $child)
                        <li class="list-group-item">
                            <strong>{{ $child->first_name }} {{ $child->last_name }}</strong><br>
                            Age: {{ $child->age }}<br>
                            Nutritionist: {{ $child->nutritionist->first_name ?? 'Not assigned' }} {{ $child->nutritionist->last_name ?? '' }}<br>
                            Assessments: {{ $child->assessments->count() }}<br>
                                <!-- Assessment details moved to assessments tab -->
                                    <!-- Assessment details are now only in the assessments tab -->
                        </li>
                    @endforeach
                </ul>
        @else
            <div class="text-center py-4">
                <i class="fas fa-child text-gray-400 text-2xl mb-2"></i>
                <p class="text-gray-500">No children registered yet.</p>
            </div>
        @endif
    </div>
</div>

<!-- Bind Child Modal -->
<div class="modal fade" id="bindChildModal" tabindex="-1" aria-labelledby="bindChildModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bindChildModalLabel">Bind Child to Your Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if ($errors->any())
                        <div class="alert alert-danger">
                                <ul>
                                        @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                        @endforeach
                                </ul>
                        </div>
                @endif
                <form method="POST" action="{{ route('parent.bindChild') }}">
                        @csrf
                        <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="mb-3">
                                <label for="age_months" class="form-label">Age (months)</label>
                                <input type="number" class="form-control" id="age_months" name="age_months" required>
                        </div>
                        <div class="mb-3">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Bind Child</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
