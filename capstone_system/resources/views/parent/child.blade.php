@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Child Details</h2>
    @if(isset($child))
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">{{ $child->first_name }} {{ $child->last_name }}</h5>
                <p class="card-text"><strong>Age (months):</strong> {{ $child->age_months }}</p>
                <p class="card-text"><strong>Sex:</strong> {{ $child->sex }}</p>
                <p class="card-text"><strong>Barangay:</strong> {{ $child->barangay->name ?? 'N/A' }}</p>
                <p class="card-text"><strong>Nutritionist:</strong> {{ $child->nutritionist->first_name ?? 'N/A' }} {{ $child->nutritionist->last_name ?? '' }}</p>
                <p class="card-text"><strong>Contact Number:</strong> {{ $child->contact_number }}</p>
                <p class="card-text"><strong>Date of Admission:</strong> {{ $child->date_of_admission }}</p>
                <!-- Add more fields as needed -->
            </div>
        </div>
        <h4>Assessments</h4>
        @if($child->assessments->count())
            <ul class="list-group mb-3">
                @foreach($child->assessments as $assessment)
                    <li class="list-group-item">
                        <strong>Date:</strong> {{ $assessment->created_at->format('Y-m-d') }}<br>
                        <strong>Nutritionist:</strong> {{ $assessment->nutritionist->first_name ?? 'N/A' }} {{ $assessment->nutritionist->last_name ?? '' }}
                        <!-- Add more assessment details as needed -->
                    </li>
                @endforeach
            </ul>
        @else
            <p>No assessments found for this child.</p>
        @endif
    @else
        <p>Child not found or you do not have access.</p>
    @endif
</div>
@endsection
