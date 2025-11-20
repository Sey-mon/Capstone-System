<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Meal Plan - {{ $plan->patient->first_name }} {{ $plan->patient->last_name }}</title>
    <link rel="stylesheet" href="{{ public_path('css/parent/meal-plan-pdf.css') }}">
    <style>
        /* Official Header Styles */
        .official-header {
            background: white;
            color: #000;
            padding: 20px 30px 15px 30px;
            margin-bottom: 25px;
            border-bottom: 4px solid #1e40af;
        }
        .header-top {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .logo-section {
            display: table-cell;
            width: 100px;
            vertical-align: middle;
            text-align: center;
        }
        .header-logo {
            width: 85px;
            height: 85px;
            object-fit: contain;
            vertical-align: middle;
        }
        .logo-placeholder {
            width: 85px;
            height: 85px;
            background: rgba(30, 64, 175, 0.1);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #1e40af;
            vertical-align: middle;
        }
        .logo-icon {
            font-size: 40px;
        }
        .header-info {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding: 0 20px;
        }
        .republic-text {
            font-size: 12px;
            margin: 0;
            font-weight: 400;
            color: #374151;
        }
        .province-text {
            font-size: 12px;
            margin: 0;
            font-weight: 400;
            color: #374151;
        }
        .clinic-name {
            font-size: 20px;
            font-weight: 700;
            margin: 2px 0;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #1e40af;
        }
        .office-name {
            font-size: 16px;
            font-weight: 600;
            margin: 2px 0 8px 0;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #374151;
        }
        .clinic-details {
            font-size: 10px;
            margin: 0;
            color: #6b7280;
            line-height: 1.4;
        }
        .header-divider {
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, #1e40af, #3b82f6, #1e40af);
            margin-top: 12px;
        }
    </style>
</head>
<body>
    <div class="watermark">CAPSTONE NUTRITION SYSTEM</div>
    
    <!-- Official Header with Logo -->
    <div class="official-header">
        <div class="header-top">
            <div class="logo-section">
                @if(file_exists(public_path('img/san-pedro-logo.png')))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/san-pedro-logo.png'))) }}" alt="San Pedro Logo" class="header-logo">
                @else
                    <div class="logo-placeholder">
                        <div class="logo-icon">🏛️</div>
                    </div>
                @endif
            </div>
            <div class="header-info">
                <p class="republic-text">Republic of the Philippines</p>
                <p class="province-text">Province of Laguna</p>
                <h1 class="clinic-name">CITY OF SAN PEDRO</h1>
                <h2 class="office-name">CITY HEALTH OFFICE</h2>
                <p class="clinic-details">📍 4F, New City Hall Bldg, Brgy. Poblacion, San Pedro, Laguna | ☎ (02) 808 – 2020 local 302 | ✉ CHOsanpedro@gmail.com</p>
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
    
    <div class="document-header">
        <h1>🍎 Personalized Meal Plan</h1>
        <div class="subtitle">Evidence-Based Nutritional Guidance for Optimal Health</div>
    </div>
    
    <div class="patient-info-card">
        <h3>👤 Patient Information</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Full Name:</div>
                <div class="info-value">{{ $plan->patient->first_name }} {{ $plan->patient->last_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Age:</div>
                <div class="info-value">{{ $plan->patient->age_months }} months ({{ floor($plan->patient->age_months / 12) }} years {{ $plan->patient->age_months % 12 }} months)</div>
            </div>
            <div class="info-row">
                <div class="info-label">Gender:</div>
                <div class="info-value">{{ $plan->patient->sex ?? 'Not specified' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Plan Generated:</div>
                <div class="info-value">{{ $plan->generated_at->format('l, F d, Y \a\t h:i A') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Document ID:</div>
                <div class="info-value">#MP-{{ str_pad($plan->plan_id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>
    </div>
    
    @if($plan->notes)
    <div class="notes-section">
        <h3>📝 Important Notes & Considerations</h3>
        <p>{{ $plan->notes }}</p>
    </div>
    @endif
    
    <div class="section-title">
        🍽️ Detailed Meal Plan
    </div>
    
    <div class="content-section">
        {!! $plan->plan_details !!}
    </div>
    
    <div class="tips-box">
        <strong>Remember:</strong> This meal plan is designed specifically for {{ $plan->patient->first_name }}'s nutritional needs. 
        Consistency is key to achieving the best results. Monitor progress and adjust portions as needed.
    </div>
    
    <div class="document-footer">
        <p class="footer-emphasis">⚕️ Medical Disclaimer</p>
        <p>This meal plan was generated on {{ $plan->generated_at->format('F d, Y') }} using AI-powered nutritional analysis based on current dietary guidelines and best practices.</p>
        <p><strong>Important:</strong> Please consult with a healthcare professional or registered dietitian before making significant dietary changes, especially if your child has any medical conditions or allergies.</p>
        <p>This document is for informational purposes only and does not constitute medical advice.</p>
        <p style="margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
            <strong>&copy; {{ date('Y') }} Capstone Nutrition System</strong><br>
            All rights reserved. Do not reproduce without permission.
        </p>
    </div>
</body>
</html>
