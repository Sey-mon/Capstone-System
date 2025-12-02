<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nutritional Assessment Profile - {{ $patient->first_name }} {{ $patient->last_name }}</title>
    <style>
        {!! file_get_contents(public_path('css/nutritionist/assessment-pdf.css')) !!}
    </style>
</head>
<body>
    <div class="resume-container">
        <!-- Professional Header with Logo -->
        <div class="document-header">
            <div class="header-top">
                <div class="logo-section">
                    @if(file_exists(public_path('img/san-pedro-logo.png')))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/san-pedro-logo.png'))) }}" alt="San Pedro Logo" class="header-logo">
                    @else
                        <div class="logo-placeholder">
                            <div class="logo-icon">�️</div>
                        </div>
                    @endif
                </div>
                <div class="header-info">
                    <p class="republic-text">Republic of the Philippines</p>
                    <p class="province-text">Province of Laguna</p>
                    <h1 class="clinic-name">CITY OF SAN PEDRO</h1>
                    <h2 class="office-name">CITY HEALTH OFFICE</h2>
                    <p class="clinic-details">4F, New City Hall Bldg, Brgy. Poblacion, San Pedro, Laguna | (02) 808 – 2020 local 302 | CHOsanpedro@gmail.com</p>
                </div>
                <div class="logo-section">
                    @if(file_exists(public_path('img/bagong-pilipinas-logo.png')))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/bagong-pilipinas-logo.png'))) }}" alt="Bagong Pilipinas Logo" class="header-logo">
                    @else
                        <div class="logo-placeholder">
                            <div class="logo-icon">🇵🇭</div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="header-divider"></div>
        </div>

        <!-- Patient Header Card -->
        <div class="patient-header-card">
            <div class="patient-avatar">
                @if($patient->profile_picture && file_exists(public_path($patient->profile_picture)))
                    <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path($patient->profile_picture))) }}" alt="Patient Photo" class="patient-photo">
                @else
                    <div class="avatar-placeholder">
                        <span class="avatar-initials">{{ substr($patient->first_name, 0, 1) }}{{ substr($patient->last_name, 0, 1) }}</span>
                    </div>
                @endif
            </div>
            <div class="patient-title-section">
                <h1 class="patient-name">{{ $patient->first_name }} {{ $patient->last_name }}</h1>
                <div class="assessment-title">Nutritional Assessment Profile</div>
                <div class="assessment-date">Assessment Date: {{ $assessment->assessment_date->format('F d, Y') }}</div>
            </div>
            <div class="status-indicator status-{{ strtolower(str_replace(' ', '-', $assessment->diagnosis)) }}">
                {{ $assessment->diagnosis }}
            </div>
        </div>

        <div class="main-content">
            <div class="section">
                <h3 class="section-title">Patient Information</h3>
                <div class="experience-item">
                    <div class="experience-content">
                        <p><strong>Name:</strong> {{ $patient->first_name }} {{ $patient->last_name }}</p>
                        <p><strong>Age months:</strong> {{ $patient->age_months }}</p>
                        <p><strong>Diagnosis:</strong> {{ $assessment->diagnosis }}</p>
                        @if($treatmentPlan && isset($treatmentPlan['patient_info']['confidence_level']))
                        <p><strong>Confidence level:</strong> {{ $treatmentPlan['patient_info']['confidence_level'] }}</p>
                        @endif
                        <p><strong>Assessment date:</strong> {{ $assessment->assessment_date->format('Y-m-d') }}</p>
                        <p><strong>Plan created by:</strong> AI-Enhanced Malnutrition Assessment System</p>
                    </div>
                </div>
            </div>
            @if($treatmentPlan)

            <div class="section">
                <h3 class="section-title">Complete Treatment & Care Plan</h3>

                @if(isset($treatmentPlan['immediate_actions']))
                <div class="experience-item">
                    <div class="experience-title">Immediate Actions Required</div>
                    <div class="experience-content">
                        @if(is_array($treatmentPlan['immediate_actions']))
                            <ul>
                                @foreach($treatmentPlan['immediate_actions'] as $action)
                                <li>{{ is_array($action) ? implode(', ', $action) : $action }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>{{ $treatmentPlan['immediate_actions'] }}</p>
                        @endif
                    </div>
                </div>
                @endif

                @if(isset($treatmentPlan['monitoring_schedule']))
                <div class="experience-item">
                    <div class="experience-title">Monitoring Schedule</div>
                    <div class="experience-content">
                        @foreach($treatmentPlan['monitoring_schedule'] as $key => $value)
                            @if(is_array($value))
                                <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong></p>
                                <ul>
                                    @foreach($value as $item)
                                        <li>{{ is_array($item) ? implode(', ', $item) : $item }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
                            @endif
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
                                <li>{{ is_array($item) ? implode(', ', $item) : $item }}</li>
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
                            @if(is_array($value))
                                <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong></p>
                                <ul>
                                    @foreach($value as $item)
                                        <li>{{ is_array($item) ? implode(', ', $item) : $item }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
                            @endif
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
                                <li>{{ is_array($item) ? implode(', ', $item) : $item }}</li>
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
                                <li>{{ is_array($item) ? implode(', ', $item) : $item }}</li>
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

        <!-- Signature Section -->
        <div class="signature-section no-break">
            <div class="signature-box">
                <div style="height: 50px; margin-bottom: 10px;">
                    <!-- Space for digital signature or stamp -->
                </div>
                <div class="signature-line">
                    <div class="signature-name">{{ $nutritionist->first_name }} {{ $nutritionist->last_name }}</div>
                </div>
                <div class="signature-label">Licensed Nutritionist</div>
                <div class="signature-label">License No: _____________</div>
            </div>
            <div class="signature-box">
                <div style="height: 50px; margin-bottom: 10px;">
                    <!-- Space for date -->
                </div>
                <div class="signature-line">
                    <div class="signature-name">{{ now()->format('F d, Y') }}</div>
                </div>
                <div class="signature-label">Date Issued</div>
                <div class="signature-label">@ {{ now()->format('g:i A') }}</div>
            </div>
        </div>

        <!-- Professional Footer -->
        <div class="footer">
            <div class="footer-title">Professional Assessment Certification</div>
            <div class="footer-info">
                This comprehensive nutritional assessment profile was professionally conducted, analyzed, and documented by<br>
                <strong>{{ $nutritionist->first_name }} {{ $nutritionist->last_name }}</strong> - Licensed Nutritionist<br>
                <br>
                Document Generated: {{ now()->format('l, F d, Y \a\t g:i A') }}<br>
                Capstone Nutrition System | Professional Nutritional Care & Management<br>
                <small style="font-size: 10px; opacity: 0.8;">This is a computer-generated document. All information is confidential and protected under medical privacy laws.</small>
            </div>
        </div>
    </div>

    <!-- Optional Watermark -->
    <div class="watermark">CONFIDENTIAL</div>
</body>
</html>
