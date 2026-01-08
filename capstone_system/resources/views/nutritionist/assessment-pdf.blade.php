<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nutritional Screening Profile - {{ $patient->first_name }} {{ $patient->last_name }}</title>
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
                <div class="assessment-title">Nutritional Screening Profile</div>
                <div class="assessment-date">Screening Date: {{ $assessment->assessment_date->format('F d, Y') }}</div>
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
                        <p><strong>Age:</strong> {{ $patient->age_months }} months</p>
                        <p><strong>Sex:</strong> {{ $patient->sex }}</p>
                        <p><strong>Barangay:</strong> {{ $patient->barangay->barangay_name ?? 'N/A' }}</p>
                        <p><strong>Screening Date:</strong> {{ $assessment->assessment_date->format('F d, Y') }}</p>
                        <p><strong>Assessed By:</strong> {{ $nutritionist->first_name }} {{ $nutritionist->last_name }}</p>
                    </div>
                </div>
            </div>

            <div class="section">
                <h3 class="section-title">Essential Measurements</h3>
                <div class="experience-item">
                    <div class="experience-content">
                        <p><strong>Weight:</strong> {{ $assessment->weight_kg }} kg</p>
                        <p><strong>Height:</strong> {{ $assessment->height_cm }} cm</p>
                        @if($assessment->weight_kg && $assessment->height_cm)
                            @php
                                $heightInMeters = $assessment->height_cm / 100;
                                $bmi = round($assessment->weight_kg / ($heightInMeters * $heightInMeters), 2);
                            @endphp
                            <p><strong>BMI:</strong> {{ $bmi }}</p>
                        @endif
                    </div>
                </div>
            </div>

            @if($assessment->weight_for_age || $assessment->height_for_age || $assessment->bmi_for_age)
            <div class="section">
                <h3 class="section-title">Nutritional Indicators</h3>
                <div class="experience-item">
                    <div class="experience-content">
                        @if($assessment->weight_for_age)
                            <p><strong>Weight for Age:</strong> {{ $assessment->weight_for_age }}</p>
                        @endif
                        @if($assessment->height_for_age)
                            <p><strong>Height for Age:</strong> {{ $assessment->height_for_age }}</p>
                        @endif
                        @if($assessment->bmi_for_age)
                            <p><strong>BMI for Age:</strong> {{ $assessment->bmi_for_age }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <div class="section">
                <h3 class="section-title">Diagnosis</h3>
                <div class="experience-item">
                    <div class="experience-content">
                        <p><strong>Clinical Diagnosis:</strong> {{ $assessment->diagnosis }}</p>
                        @if($treatmentPlan && isset($treatmentPlan['patient_info']['confidence_level']))
                        <p><strong>Confidence Level:</strong> {{ number_format($treatmentPlan['patient_info']['confidence_level'] * 100, 1) }}%</p>
                        @endif
                    </div>
                </div>
            </div>

            @if($assessment->notes)
                @php
                    $clinicalData = null;
                    $additionalNotes = '';
                    try {
                        $parsedNotes = json_decode($assessment->notes, true);
                        if ($parsedNotes && isset($parsedNotes['clinical_symptoms'])) {
                            $clinicalData = $parsedNotes['clinical_symptoms'];
                            $additionalNotes = $parsedNotes['additional_notes'] ?? '';
                        } else {
                            $additionalNotes = $assessment->notes;
                        }
                    } catch (\Exception $e) {
                        $additionalNotes = $assessment->notes;
                    }
                @endphp

                @if($clinicalData)
                <div class="section">
                    <h3 class="section-title">Clinical Symptoms & Physical Signs</h3>
                    <div class="experience-item">
                        <div class="experience-content">
                            @if(!empty($clinicalData['appetite']))
                                <p><strong>Appetite:</strong> {{ ucfirst($clinicalData['appetite']) }}</p>
                            @endif
                            @if(!empty($clinicalData['edema']))
                                <p><strong>Edema:</strong> {{ ucfirst($clinicalData['edema']) }}</p>
                            @endif
                            @if(!empty($clinicalData['muac']))
                                <p><strong>MUAC:</strong> {{ $clinicalData['muac'] }} cm</p>
                            @endif
                            @if(!empty($clinicalData['diarrhea']) && $clinicalData['diarrhea'] !== '0')
                                <p><strong>Diarrhea:</strong> {{ $clinicalData['diarrhea'] }} day(s)</p>
                            @endif
                            @if(!empty($clinicalData['vomiting']) && $clinicalData['vomiting'] !== '0')
                                <p><strong>Vomiting:</strong> {{ $clinicalData['vomiting'] }} times/day</p>
                            @endif
                            @if(!empty($clinicalData['fever']) && $clinicalData['fever'] !== '0')
                                <p><strong>Fever:</strong> {{ $clinicalData['fever'] }} day(s)</p>
                            @endif
                            @if(!empty($clinicalData['breastfeeding_status']) && $clinicalData['breastfeeding_status'] !== 'not_applicable')
                                <p><strong>Breastfeeding Status:</strong> {{ ucwords(str_replace('_', ' ', $clinicalData['breastfeeding_status'])) }}</p>
                            @endif
                            @if(!empty($clinicalData['visible_signs']) && count($clinicalData['visible_signs']) > 0)
                                <p><strong>Visible Signs:</strong></p>
                                <ul>
                                    @foreach($clinicalData['visible_signs'] as $sign)
                                        <li>{{ ucfirst($sign) }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                @if($additionalNotes)
                <div class="section">
                    <h3 class="section-title">Additional Notes</h3>
                    <div class="notes-section">
                        {{ $additionalNotes }}
                    </div>
                </div>
                @endif
            @endif

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
                                    @php
                                        $cleanAction = is_array($action) ? implode(', ', $action) : $action;
                                        // Remove emoji and special characters
                                        $cleanAction = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $cleanAction);
                                        $cleanAction = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $cleanAction);
                                        $cleanAction = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $cleanAction);
                                        $cleanAction = trim($cleanAction);
                                    @endphp
                                <li>{{ $cleanAction }}</li>
                                @endforeach
                            </ul>
                        @else
                            @php
                                $cleanText = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $treatmentPlan['immediate_actions']);
                                $cleanText = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $cleanText);
                                $cleanText = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $cleanText);
                                $cleanText = trim($cleanText);
                            @endphp
                            <p>{{ $cleanText }}</p>
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
                                        @php
                                            $cleanItem = is_array($item) ? implode(', ', $item) : $item;
                                            $cleanItem = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $cleanItem);
                                            $cleanItem = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $cleanItem);
                                            $cleanItem = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $cleanItem);
                                            $cleanItem = trim($cleanItem);
                                        @endphp
                                        <li>{{ $cleanItem }}</li>
                                    @endforeach
                                </ul>
                            @else
                                @php
                                    $cleanValue = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $value);
                                    $cleanValue = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $cleanValue);
                                    $cleanValue = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $cleanValue);
                                    $cleanValue = trim($cleanValue);
                                @endphp
                                <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $cleanValue }}</p>
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
                                    @php
                                        $cleanItem = is_array($item) ? implode(', ', $item) : $item;
                                        $cleanItem = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $cleanItem);
                                        $cleanItem = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $cleanItem);
                                        $cleanItem = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $cleanItem);
                                        $cleanItem = trim($cleanItem);
                                    @endphp
                                <li>{{ $cleanItem }}</li>
                                @endforeach
                            </ul>
                        @else
                            @php
                                $cleanText = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $treatmentPlan['family_education']);
                                $cleanText = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $cleanText);
                                $cleanText = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $cleanText);
                                $cleanText = trim($cleanText);
                            @endphp
                            <p>{{ $cleanText }}</p>
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
                                        @php
                                            $cleanItem = is_array($item) ? implode(', ', $item) : $item;
                                            $cleanItem = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $cleanItem);
                                            $cleanItem = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $cleanItem);
                                            $cleanItem = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $cleanItem);
                                            $cleanItem = trim($cleanItem);
                                        @endphp
                                        <li>{{ $cleanItem }}</li>
                                    @endforeach
                                </ul>
                            @else
                                @php
                                    $cleanValue = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $value);
                                    $cleanValue = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $cleanValue);
                                    $cleanValue = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $cleanValue);
                                    $cleanValue = trim($cleanValue);
                                @endphp
                                <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $cleanValue }}</p>
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
                                    @php
                                        $cleanItem = is_array($item) ? implode(', ', $item) : $item;
                                        $cleanItem = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $cleanItem);
                                        $cleanItem = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $cleanItem);
                                        $cleanItem = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $cleanItem);
                                        $cleanItem = trim($cleanItem);
                                    @endphp
                                <li>{{ $cleanItem }}</li>
                                @endforeach
                            </ul>
                        @else
                            @php
                                $cleanText = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $treatmentPlan['discharge_criteria']);
                                $cleanText = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $cleanText);
                                $cleanText = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $cleanText);
                                $cleanText = trim($cleanText);
                            @endphp
                            <p>{{ $cleanText }}</p>
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
                                    @php
                                        $cleanItem = is_array($item) ? implode(', ', $item) : $item;
                                        $cleanItem = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $cleanItem);
                                        $cleanItem = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $cleanItem);
                                        $cleanItem = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $cleanItem);
                                        $cleanItem = trim($cleanItem);
                                    @endphp
                                <li>{{ $cleanItem }}</li>
                                @endforeach
                            </ul>
                        @else
                            @php
                                $cleanText = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $treatmentPlan['emergency_signs']);
                                $cleanText = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $cleanText);
                                $cleanText = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $cleanText);
                                $cleanText = trim($cleanText);
                            @endphp
                            <p>{{ $cleanText }}</p>
                        @endif
                    </div>
                </div>
                @endif
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
            <div class="footer-title">Professional Screening Certification</div>
            <div class="footer-info">
                This comprehensive nutritional screening profile was professionally conducted, analyzed, and documented by<br>
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
