@extends('layouts.dashboard')

@section('title', 'Parent Profile')
@section('page-title', 'My Profile')
@section('page-subtitle', 'View and update your profile information.')

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')
<div class="content-card">
    <div class="card-header">
        <h3 class="card-title">My Profile</h3>
    </div>
    <div class="card-content">
        <ul class="list-group">
            <li class="list-group-item"><strong>Name:</strong> {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</li>
            <li class="list-group-item"><strong>Email:</strong> {{ Auth::user()->email }}</li>
            <li class="list-group-item"><strong>Contact:</strong> {{ Auth::user()->contact_number ?? 'N/A' }}</li>
            <li class="list-group-item"><strong>Address:</strong> {{ Auth::user()->address ?? 'N/A' }}</li>
        </ul>
        <!-- Optional: Add Edit Profile button/modal here -->
    </div>
</div>
@endsection
