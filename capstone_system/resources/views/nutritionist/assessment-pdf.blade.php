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
                <h3 class="section-title">Treatment & Care Plan</h3>
                
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
                    <div class="experience-title">Nutritional Intervention Plan</div>
                    <div class="experience-content">
                        @if(is_array($treatmentPlan['nutrition_plan']))
                            @foreach($treatmentPlan['nutrition_plan'] as $key => $value)
                            <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</p>
                            @endforeach
                        @else
                            <p>{{ $treatmentPlan['nutrition_plan'] }}</p>
                        @endif
                    </div>
                </div>
                @endif

                <div class="skills-grid">
                    @if(isset($treatmentPlan['medical_interventions']))
                    <div class="skill-category">
                        <div class="skill-title">Medical Interventions</div>
                        @if(is_array($treatmentPlan['medical_interventions']))
                            @foreach($treatmentPlan['medical_interventions'] as $key => $value)
                            <div class="skill-item">
                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}
                            </div>
                            @endforeach
                        @else
                            <div class="skill-item">{{ $treatmentPlan['medical_interventions'] }}</div>
                        @endif
                    </div>
                    @endif

                    @if(isset($treatmentPlan['monitoring_schedule']))
                    <div class="skill-category">
                        <div class="skill-title">Monitoring Schedule</div>
                        @if(is_array($treatmentPlan['monitoring_schedule']))
                            @foreach($treatmentPlan['monitoring_schedule'] as $key => $value)
                            <div class="skill-item">
                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}
                            </div>
                            @endforeach
                        @else
                            <div class="skill-item">{{ $treatmentPlan['monitoring_schedule'] }}</div>
                        @endif
                    </div>
                    @endif
                </div>
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
