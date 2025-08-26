<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nutritional Assessment Profile - {{ $patient->first_name }} {{ $patient->last_name }}</title>
    <link rel="stylesheet" href="{{ asset('css/nutritionist/assessment-pdf.css') }}">
</head>
<body>
    <div class="resume-container">
        <div class="header">
            <h1>{{ $patient->first_name }} {{ $patient->last_name }}</h1>
            <div class="subtitle">Nutritional Assessment Profile</div>
            <div class="date">Assessment Date: {{ $assessment->assessment_date->format('F d, Y') }}</div>
        </div>

        <div class="contact-info">
            <h2>Patient Information</h2>
            <div class="contact-grid">
                <div class="contact-item">
                    <span class="contact-label">Full Name:</span>
                    <span class="contact-value">{{ $patient->first_name }} {{ $patient->last_name }}</span>
                </div>
                <div class="contact-item">
                    <span class="contact-label">Age:</span>
                    <span class="contact-value">{{ $patient->age_months }} months</span>
                </div>
                <div class="contact-item">
                    <span class="contact-label">Assessment Date:</span>
                    <span class="contact-value">{{ $assessment->assessment_date->format('M d, Y') }}</span>
                </div>
                <div class="contact-item">
                    <span class="contact-label">Nutritionist:</span>
                    <span class="contact-value">{{ $nutritionist->first_name }} {{ $nutritionist->last_name }}</span>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="summary-box">
                <div class="summary-title">Primary Assessment</div>
                <div class="summary-diagnosis">{{ $assessment->diagnosis }}</div>
                <div class="summary-subtitle">Current Nutritional Status</div>
            </div>

            <div class="section">
                <h3 class="section-title">Key Metrics</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">{{ $assessment->weight_kg }}</div>
                        <div class="stat-label">Weight (kg)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $assessment->height_cm }}</div>
                        <div class="stat-label">Height (cm)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">
                            @if($assessment->muac_cm)
                                {{ $assessment->muac_cm }}
                            @else
                                N/A
                            @endif
                        </div>
                        <div class="stat-label">MUAC (cm)</div>
                    </div>
                </div>
            </div>
            @if($treatmentPlan)

            <div class="section">
                <h3 class="section-title">Complete Treatment & Care Plan</h3>
                @if(isset($treatmentPlan['patient_info']))
                <div class="experience-item">
                    <div class="experience-title">Patient Info</div>
                    <div class="experience-content">
                        @foreach($treatmentPlan['patient_info'] as $key => $value)
                            <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</p>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(isset($treatmentPlan['immediate_actions']))
                <div class="experience-item">
                    <div class="experience-title">Immediate Actions Required</div>
                    <div class="experience-content">
                        @if(is_array($treatmentPlan['immediate_actions']))
                            <ul>
                                @foreach($treatmentPlan['immediate_actions'] as $action)
                                <li>{{ is_array($action) ? json_encode($action) : $action }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>{{ $treatmentPlan['immediate_actions'] }}</p>
                        @endif
                    </div>
                </div>
                @endif

                @if(isset($treatmentPlan['nutrition_plan']))
                <div class="experience-item">
                    <div class="experience-title">Nutrition Plan</div>
                    <div class="experience-content">
                        @foreach($treatmentPlan['nutrition_plan'] as $key => $value)
                            <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</p>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(isset($treatmentPlan['medical_interventions']))
                <div class="experience-item">
                    <div class="experience-title">Medical Interventions</div>
                    <div class="experience-content">
                        @foreach($treatmentPlan['medical_interventions'] as $key => $value)
                            <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</p>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(isset($treatmentPlan['monitoring_schedule']))
                <div class="experience-item">
                    <div class="experience-title">Monitoring Schedule</div>
                    <div class="experience-content">
                        @foreach($treatmentPlan['monitoring_schedule'] as $key => $value)
                            <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</p>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(isset($treatmentPlan['follow_up_plan']))
                <div class="experience-item">
                    <div class="experience-title">Follow-up Plan</div>
                    <div class="experience-content">
                        @foreach($treatmentPlan['follow_up_plan'] as $key => $value)
                            <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</p>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(isset($treatmentPlan['family_education']))
                <div class="experience-item">
                    <div class="experience-title">Family Education</div>
                    <div class="experience-content">
                        @if(is_array($treatmentPlan['family_education']))
                            <ul>
                                @foreach($treatmentPlan['family_education'] as $item)
                                <li>{{ is_array($item) ? json_encode($item) : $item }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>{{ $treatmentPlan['family_education'] }}</p>
                        @endif
                    </div>
                </div>
                @endif

                @if(isset($treatmentPlan['success_criteria']))
                <div class="experience-item">
                    <div class="experience-title">Success Criteria</div>
                    <div class="experience-content">
                        @foreach($treatmentPlan['success_criteria'] as $key => $value)
                            <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</p>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(isset($treatmentPlan['discharge_criteria']))
                <div class="experience-item">
                    <div class="experience-title">Discharge Criteria</div>
                    <div class="experience-content">
                        @if(is_array($treatmentPlan['discharge_criteria']))
                            <ul>
                                @foreach($treatmentPlan['discharge_criteria'] as $item)
                                <li>{{ is_array($item) ? json_encode($item) : $item }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>{{ $treatmentPlan['discharge_criteria'] }}</p>
                        @endif
                    </div>
                </div>
                @endif

                @if(isset($treatmentPlan['emergency_signs']))
                <div class="experience-item">
                    <div class="experience-title">Emergency Warning Signs</div>
                    <div class="experience-content">
                        @if(is_array($treatmentPlan['emergency_signs']))
                            <ul>
                                @foreach($treatmentPlan['emergency_signs'] as $item)
                                <li>{{ is_array($item) ? json_encode($item) : $item }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>{{ $treatmentPlan['emergency_signs'] }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endif

            @if($assessment->notes)
            <div class="section">
                <h3 class="section-title">Clinical Notes & Observations</h3>
                <div class="notes-section">
                    {{ $assessment->notes }}
                </div>
            </div>
            @endif
        </div>

        <div class="footer">
            <div class="footer-title">Professional Assessment Certification</div>
            <div class="footer-info">
                This nutritional assessment profile was professionally conducted and documented by:<br>
                <strong>{{ $nutritionist->first_name }} {{ $nutritionist->last_name }}</strong> - Licensed Nutritionist<br>
                Generated on {{ now()->format('F d, Y \a\t g:i A') }} | Nutritional Assessment System
            </div>
        </div>
    </div>
</body>
</html>
