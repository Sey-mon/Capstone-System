<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nutritional Assessment Profile - {{ $patient->first_name }} {{ $patient->last_name }}</title>
    <style>
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            margin: 0;
            padding: 30px;
            color: #2c3e50;
            line-height: 1.6;
            background: #ffffff;
        }
        .resume-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 0;
        }
        .header {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
            font-weight: 300;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .header .subtitle {
            font-size: 16px;
            font-weight: 300;
            opacity: 0.9;
            margin: 5px 0;
        }
        .header .date {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 15px;
        }
        .contact-info {
            background: #ecf0f1;
            padding: 25px;
            border-left: 5px solid #3498db;
        }
        .contact-info h2 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 20px;
            font-weight: 600;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .contact-item {
            display: flex;
            align-items: center;
        }
        .contact-label {
            font-weight: 600;
            color: #34495e;
            min-width: 120px;
            font-size: 14px;
        }
        .contact-value {
            color: #2c3e50;
            font-size: 14px;
        }
        .main-content {
            padding: 30px;
        }
        .section {
            margin-bottom: 35px;
            page-break-inside: avoid;
        }
        .section-title {
            color: #2c3e50;
            font-size: 22px;
            font-weight: 600;
            margin: 0 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 3px solid #3498db;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .summary-box {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        .summary-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .summary-diagnosis {
            font-size: 24px;
            font-weight: 700;
            margin: 10px 0;
        }
        .summary-subtitle {
            font-size: 14px;
            opacity: 0.9;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            border-left: 4px solid #3498db;
        }
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        .experience-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #27ae60;
        }
        .experience-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .experience-content {
            color: #4a5568;
            line-height: 1.7;
        }
        .experience-content ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .experience-content li {
            margin-bottom: 8px;
        }
        .skills-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .skill-category {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #e74c3c;
        }
        .skill-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .skill-item {
            background: white;
            padding: 10px 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .notes-section {
            background: linear-gradient(135deg, #f1f2f6 0%, #ddd5d0 100%);
            padding: 25px;
            border-radius: 8px;
            border-left: 4px solid #f39c12;
        }
        .footer {
            margin-top: 40px;
            padding: 25px;
            background: #2c3e50;
            color: white;
            text-align: center;
            border-radius: 0 0 8px 8px;
        }
        .footer-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .footer-info {
            font-size: 12px;
            opacity: 0.8;
            line-height: 1.4;
        }
        .status-excellent { color: #27ae60; font-weight: 600; }
        .status-good { color: #f39c12; font-weight: 600; }
        .status-concerning { color: #e74c3c; font-weight: 600; }
        .status-critical { color: #c0392b; font-weight: 600; }
        
        @media print {
            body { margin: 0; padding: 0; }
            .resume-container { box-shadow: none; }
        }
    </style>
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
