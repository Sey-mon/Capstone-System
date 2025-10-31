<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Meal Plan - {{ $plan->patient->first_name }} {{ $plan->patient->last_name }}</title>
    <link rel="stylesheet" href="{{ public_path('css/parent/meal-plan-pdf.css') }}">
</head>
<body>
    <div class="watermark">CAPSTONE NUTRITION SYSTEM</div>
    
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
