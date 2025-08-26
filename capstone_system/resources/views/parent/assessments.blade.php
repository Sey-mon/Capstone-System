@extends('layouts.dashboard')

@section('title', 'My Child Assessments')
@section('page-title', 'My Child Assessments')
@section('page-subtitle', 'View all child assessments for your children.')

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')
<div class="content-card">
    <div class="card-header">
    <h3 class="card-title">Child Assessments</h3>
    </div>
    <div class="card-content">
        @if(isset($children) && count($children) > 0)
            @foreach($children as $child)
                <div class="mb-4">
                    <h5>{{ $child->first_name }} {{ $child->last_name }}</h5>
                    @php
                        $latestAssessment = $child->assessments->sortByDesc('created_at')->first();
                    @endphp
                    @if($latestAssessment)
                        <div class="mt-2">
                            <strong>Latest Child Assessment:</strong><br>
                            Date: {{ $latestAssessment->created_at->format('M d, Y') }}<br>
                            Diagnostic:
                            @php
                                $diagnosis = null;
                                if (!empty($latestAssessment->treatment)) {
                                    $treatmentData = json_decode($latestAssessment->treatment, true);
                                    $diagnosis = $treatmentData['patient_info']['diagnosis'] ?? null;
                                }
                            @endphp
                            @if($diagnosis == 'Severe Acute Malnutrition (SAM)')
                                <span style="background:#f8d7da;color:#721c24;padding:8px 16px;border-radius:20px;display:inline-block;">Severe Acute Malnutrition (SAM)</span>
                            @elseif($diagnosis == 'Moderate Acute Malnutrition (MAM)')
                                <span style="background:#fff3cd;color:#856404;padding:8px 16px;border-radius:20px;display:inline-block;">Moderate Acute Malnutrition (MAM)</span>
                            @elseif($diagnosis == 'Normal')
                                <span style="background:#d4edda;color:#155724;padding:8px 16px;border-radius:20px;display:inline-block;">Normal</span>
                            @elseif($diagnosis)
                                <span>{{ $diagnosis }}</span>
                            @else
                                <span>diagnostic</span>
                            @endif<br>
                            Weight: {{ $latestAssessment->weight ?? $child->weight_kg ?? 'N/A' }}<br>
                            Height: {{ $latestAssessment->height ?? $child->height_cm ?? 'N/A' }}<br>
                            Age: {{ $child->age_months ? ($child->age_months . ' months') : 'N/A' }}<br>
                            Sex: {{ $child->sex ?? 'N/A' }}<br>
                            Nutritionist: {{ $latestAssessment->nutritionist->first_name ?? 'N/A' }} {{ $latestAssessment->nutritionist->last_name ?? '' }}<br>
                            Remarks: {{ $latestAssessment->remarks ?? 'N/A' }}<br>
                        </div>
                    @else
                        <div class="mt-2 text-muted">No child assessments yet.</div>
                    @endif
                    @if($child->assessments->count() > 1)
                        <button class="btn btn-link p-0" type="button" onclick="document.getElementById('old-assessments-{{ $child->id }}').classList.toggle('d-none')">
                            Show All Child Assessments
                        </button>
                        <div id="old-assessments-{{ $child->id }}" class="d-none mt-2">
                            <ul>
                                @foreach($child->assessments->sortByDesc('created_at')->skip(1) as $assessment)
                                    <li>
                                        <strong>Date:</strong> {{ $assessment->created_at->format('M d, Y') }}<br>
                                        <strong>Diagnostic:</strong>
                                        @php
                                            $diagnosis = null;
                                            if (!empty($assessment->treatment)) {
                                                $treatmentData = json_decode($assessment->treatment, true);
                                                $diagnosis = $treatmentData['patient_info']['diagnosis'] ?? null;
                                            }
                                        @endphp
                                        @if($diagnosis == 'Severe Acute Malnutrition (SAM)')
                                            <span style="background:#f8d7da;color:#721c24;padding:8px 16px;border-radius:20px;display:inline-block;">Severe Acute Malnutrition (SAM)</span>
                                        @elseif($diagnosis == 'Moderate Acute Malnutrition (MAM)')
                                            <span style="background:#fff3cd;color:#856404;padding:8px 16px;border-radius:20px;display:inline-block;">Moderate Acute Malnutrition (MAM)</span>
                                        @elseif($diagnosis == 'Normal')
                                            <span style="background:#d4edda;color:#155724;padding:8px 16px;border-radius:20px;display:inline-block;">Normal</span>
                                        @elseif($diagnosis)
                                            <span>{{ $diagnosis }}</span>
                                        @else
                                            <span>diagnostic</span>
                                        @endif<br>
                                        <strong>Weight:</strong> {{ $assessment->weight ?? 'N/A' }}<br>
                                        <strong>Height:</strong> {{ $assessment->height ?? 'N/A' }}<br>
                                        <strong>Nutritionist:</strong> {{ $assessment->nutritionist->first_name ?? 'N/A' }} {{ $assessment->nutritionist->last_name ?? '' }}<br>
                                        <strong>Remarks:</strong> {{ $assessment->remarks ?? 'N/A' }}<br>
                                        <hr>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="text-center py-4">
                <i class="fas fa-clipboard text-gray-400 text-2xl mb-2"></i>
                <p class="text-gray-500">No child assessments found.</p>
            </div>
        @endif
    </div>
</div>
@endsection
