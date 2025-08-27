@extends('layouts.dashboard')

@section('title', 'My Child Assessments')
@section('page-title', 'My Child Assessments')
@section('page-subtitle', 'View all child assessments for your children.')

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/parent/parent-assessments.css') }}">

    @if(isset($children) && count($children) > 0)
        @php
            $count = count($children);
            $empty = (3 - ($count % 3)) % 3;
        @endphp
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
            @foreach($children as $child)
                <div class="modern-assessment-card" style="background: #f3ffe6; border: 2px solid #43ea7b; border-radius: 18px; min-height: 400px; display: flex; flex-direction: column; justify-content: flex-start;">
                    <div class="modern-assessment-title">{{ $child->first_name }} {{ $child->last_name }}</div>
                    @php
                        $latestAssessment = $child->assessments->sortByDesc('created_at')->first();
                    @endphp
                    @if($latestAssessment)
                        <div class="mb-2">
                            <span class="modern-assessment-label">Latest Child Assessment:</span><br>
                            <span class="text-gray-500">Date:</span> {{ $latestAssessment->created_at->format('M d, Y') }}<br>
                            <span class="text-gray-500">Diagnostic:</span>
                            @php
                                $diagnosis = null;
                                if (!empty($latestAssessment->treatment)) {
                                    $treatmentData = json_decode($latestAssessment->treatment, true);
                                    $diagnosis = $treatmentData['patient_info']['diagnosis'] ?? null;
                                }
                            @endphp
                            @if($diagnosis == 'Severe Acute Malnutrition (SAM)')
                                <span class="modern-badge-sam">Severe Acute Malnutrition (SAM)</span>
                            @elseif($diagnosis == 'Moderate Acute Malnutrition (MAM)')
                                <span class="modern-badge-mam">Moderate Acute Malnutrition (MAM)</span>
                            @elseif($diagnosis == 'Normal')
                                <span class="modern-badge-normal">Normal</span>
                            @elseif($diagnosis)
                                <span>{{ $diagnosis }}</span>
                            @else
                                <span>diagnostic</span>
                            @endif<br>
                            <span class="text-gray-500">Weight:</span> {{ $latestAssessment->weight ?? $child->weight_kg ?? 'N/A' }}<br>
                            <span class="text-gray-500">Height:</span> {{ $latestAssessment->height ?? $child->height_cm ?? 'N/A' }}<br>
                            <span class="text-gray-500">Age:</span> {{ $child->age_months ? ($child->age_months . ' months') : 'N/A' }}<br>
                            <span class="text-gray-500">Sex:</span> {{ $child->sex ?? 'N/A' }}<br>
                            <span class="text-gray-500">Nutritionist:</span> {{ $latestAssessment->nutritionist->first_name ?? 'N/A' }} {{ $latestAssessment->nutritionist->last_name ?? '' }}<br>
                            <span class="text-gray-500">Remarks:</span> {{ $latestAssessment->remarks ?? 'N/A' }}<br>
                        </div>
                    @else
                        <div class="mb-2 text-gray-400">No child assessments yet.</div>
                    @endif
                    @if($child->assessments->count() > 1)
                        <button class="modern-showall-btn" type="button" onclick="toggleOldAssessments('{{ $child->id }}')">
                            Show All Child Assessments
                        </button>
                        <div id="old-assessments-{{ $child->id }}" class="d-none mt-2">
                            <ul class="pl-2">
                                @foreach($child->assessments->sortByDesc('created_at')->skip(1) as $assessment)
                                    <li class="mb-3">
                                        <span class="text-gray-500">Date:</span> {{ $assessment->created_at->format('M d, Y') }}<br>
                                        <span class="text-gray-500">Diagnostic:</span>
                                        @php
                                            $diagnosis = null;
                                            if (!empty($assessment->treatment)) {
                                                $treatmentData = json_decode($assessment->treatment, true);
                                                $diagnosis = $treatmentData['patient_info']['diagnosis'] ?? null;
                                            }
                                        @endphp
                                        @if($diagnosis == 'Severe Acute Malnutrition (SAM)')
                                            <span class="modern-badge-sam">Severe Acute Malnutrition (SAM)</span>
                                        @elseif($diagnosis == 'Moderate Acute Malnutrition (MAM)')
                                            <span class="modern-badge-mam">Moderate Acute Malnutrition (MAM)</span>
                                        @elseif($diagnosis == 'Normal')
                                            <span class="modern-badge-normal">Normal</span>
                                        @elseif($diagnosis)
                                            <span>{{ $diagnosis }}</span>
                                        @else
                                            <span>diagnostic</span>
                                        @endif<br>
                                        <span class="text-gray-500">Weight:</span> {{ $assessment->weight ?? 'N/A' }}<br>
                                        <span class="text-gray-500">Height:</span> {{ $assessment->height ?? 'N/A' }}<br>
                                        <span class="text-gray-500">Nutritionist:</span> {{ $assessment->nutritionist->first_name ?? 'N/A' }} {{ $assessment->nutritionist->last_name ?? '' }}<br>
                                        <span class="text-gray-500">Remarks:</span> {{ $assessment->remarks ?? 'N/A' }}<br>
                                        <hr>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
            @for($i = 0; $i < $empty; $i++)
                <div style="background: transparent; border: none;"></div>
            @endfor

    @else
        <div class="text-center py-4">
            <i class="fas fa-clipboard text-gray-400 text-2xl mb-2"></i>
            <p class="text-gray-500">No child assessments found.</p>
        </div>
    @endif
</div>
@endsection
