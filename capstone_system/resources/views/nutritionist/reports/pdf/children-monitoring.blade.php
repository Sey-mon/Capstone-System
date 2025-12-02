<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Children Monitoring Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1f2937;
            background: #ffffff;
        }
        
        /* Professional Document Header */
        .document-header {
            background: linear-gradient(to bottom, #ffffff 0%, #f9fafb 100%);
            color: #000;
            padding: 20px 30px 15px 30px;
            margin-bottom: 20px;
            border-bottom: 4px solid #10b981;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
            background-color: #10b981;
            margin-top: 12px;
        }
        
        .report-title-section {
            text-align: center;
            margin: 15px 0;
            padding: 18px;
            background-color: #ecfdf5;
            border: 2px solid #10b981;
        }
        .report-title-section h1 {
            font-size: 20px;
            color: #047857;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .report-title-section h2 {
            font-size: 14px;
            color: #065f46;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .report-info {
            background-color: #f0fdf4;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #a7f3d0;
        }
        .report-info table {
            width: 100%;
        }
        .report-info td {
            padding: 5px 8px;
            font-size: 11px;
        }
        .report-info strong {
            color: #047857;
            font-weight: bold;
        }
        .filter-badge {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 3px 10px;
            font-size: 9px;
            font-weight: bold;
            margin-left: 5px;
        }
        .summary-stats {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-spacing: 5px;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 6px;
            text-align: center;
            border: 1px solid #10b981;
            background-color: #ecfdf5;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #047857;
        }
        .stat-label {
            font-size: 8px;
            color: #065f46;
            margin-top: 2px;
            font-weight: bold;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #10b981;
            color: #ffffff;
            padding: 10px 5px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #059669;
        }
        table.data-table td {
            padding: 8px 5px;
            font-size: 10px;
            border: 1px solid #d1d5db;
            text-align: center;
        }
        table.data-table tbody tr:nth-child(even) {
            background-color: #f0fdf4;
        }
        table.data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .status-normal {
            background: #10b981;
            color: white;
        }
        .status-at-risk {
            background: #f59e0b;
            color: white;
        }
        .status-malnourished {
            background: #ef4444;
            color: white;
        }
        .status-pending {
            background: #6b7280;
            color: white;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
        }
        .page-break {
            page-break-after: always;
        }
        .section-title-bar {
            font-size: 11px;
            margin-top: 10px;
            margin-bottom: 8px;
            color: #047857;
            font-weight: bold;
            padding: 6px 10px;
            background-color: #d1fae5;
            border-left: 3px solid #10b981;
        }
        .signature-section {
            margin-top: 30px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 10px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Professional Header with Logo -->
    <div class="document-header">
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

    <div class="report-title-section">
        <h1>{{ $title }}</h1>
        <h2>Children Monitoring Report for Main Office</h2>
        <div style="font-size: 11px; color: #6b7280;">
            Report Period: {{ date('F d, Y', strtotime($startDate)) }} to {{ date('F d, Y', strtotime($endDate)) }}
        </div>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td style="width: 50%;"><strong>Nutritionist:</strong> {{ $nutritionist->first_name }} {{ $nutritionist->middle_name ? substr($nutritionist->middle_name, 0, 1) . '.' : '' }} {{ $nutritionist->last_name }}</td>
                <td style="width: 50%;"><strong>Professional ID:</strong> {{ $nutritionist->professional_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Email:</strong> {{ $nutritionist->email }}</td>
                <td><strong>Contact:</strong> {{ $nutritionist->contact_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Barangay Coverage:</strong> 
                    @if($selectedBarangay)
                        <span style="color: #047857; font-weight: 600;">{{ $selectedBarangay->name }}</span>
                    @elseif($barangays && $barangays->count() > 0)
                        <span style="color: #047857; font-weight: 600;">{{ $barangays->pluck('name')->unique()->sort()->join(', ') }}</span>
                    @elseif(count($patients) > 0)
                        <span style="color: #047857; font-weight: 600;">{{ $patients->pluck('barangay.name')->unique()->filter()->sort()->join(', ') }}</span>
                    @else
                        <span style="color: #6b7280;">All Barangays</span>
                    @endif
                </td>
                <td><strong>Total Children:</strong> {{ count($patients) }}</td>
            </tr>
            <tr>
                <td><strong>Report Generated:</strong> {{ $generatedDate }} at {{ $generatedTime }}</td>
                <td><strong>Filters Applied:</strong> 
                    @if($selectedBarangay)
                        <span class="filter-badge">{{ $selectedBarangay->name }}</span>
                    @endif
                    @if($filterStatus)
                        <span class="filter-badge">{{ $filterStatus }}</span>
                    @endif
                    @if(!$selectedBarangay && !$filterStatus)
                        None
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="summary-stats">
        <div class="stat-box">
            <div class="stat-value">{{ count($patients) }}</div>
            <div class="stat-label">Total Children</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $patients->where('sex', 'Male')->count() }}</div>
            <div class="stat-label">Male</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $patients->where('sex', 'Female')->count() }}</div>
            <div class="stat-label">Female</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $patients->where('is_4ps_beneficiary', true)->count() }}</div>
            <div class="stat-label">4Ps Beneficiaries</div>
        </div>
    </div>

    <div class="section-title-bar">📋 Children Details</div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 15%;">Name</th>
                <th style="width: 6%;">Age</th>
                <th style="width: 5%;">Sex</th>
                <th style="width: 12%;">Barangay</th>
                <th style="width: 7%;">Weight (kg)</th>
                <th style="width: 7%;">Height (cm)</th>
                <th style="width: 10%;">Latest Assessment</th>
                <th style="width: 13%;">Status</th>
                <th style="width: 7%;">4Ps</th>
                <th style="width: 15%;">Nutrition Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($patients as $index => $patient)
            @php
                $latestAssessment = $patient->assessments->first();
                $ageYears = floor($patient->age_months / 12);
                $ageMonths = $patient->age_months % 12;
                $statusClass = 'status-normal';
                if ($latestAssessment && $latestAssessment->recovery_status) {
                    if (str_contains($latestAssessment->recovery_status, 'Severely') || str_contains($latestAssessment->recovery_status, 'Moderately')) {
                        $statusClass = 'status-malnourished';
                    } elseif (str_contains($latestAssessment->recovery_status, 'At Risk')) {
                        $statusClass = 'status-at-risk';
                    }
                }
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong></td>
                <td>{{ $ageYears }}y {{ $ageMonths }}m</td>
                <td>{{ $patient->sex }}</td>
                <td>{{ $patient->barangay->name ?? 'N/A' }}</td>
                <td>{{ $patient->weight_kg ? number_format($patient->weight_kg, 2) : 'N/A' }}</td>
                <td>{{ $patient->height_cm ? number_format($patient->height_cm, 2) : 'N/A' }}</td>
                <td>{{ $latestAssessment ? $latestAssessment->assessment_date->format('M d, Y') : 'No assessment' }}</td>
                <td>
                    @if($latestAssessment && $latestAssessment->recovery_status)
                        <span class="status-badge {{ $statusClass }}">{{ $latestAssessment->recovery_status }}</span>
                    @else
                        <span class="status-badge status-pending">Pending</span>
                    @endif
                </td>
                <td>{{ $patient->is_4ps_beneficiary ? 'Yes' : 'No' }}</td>
                <td style="font-size: 7px;">
                    @php
                        $treatmentData = null;
                        $displayText = '-';
                        
                        if ($latestAssessment) {
                            if ($latestAssessment->treatment) {
                                $treatmentData = is_string($latestAssessment->treatment) ? json_decode($latestAssessment->treatment, true) : $latestAssessment->treatment;
                                
                                if (is_array($treatmentData)) {
                                    // Try to get notes from various possible locations in the treatment JSON
                                    if (isset($treatmentData['notes']) && !empty($treatmentData['notes'])) {
                                        $displayText = $treatmentData['notes'];
                                    } elseif (isset($treatmentData['treatment_notes']) && !empty($treatmentData['treatment_notes'])) {
                                        $displayText = $treatmentData['treatment_notes'];
                                    } elseif (isset($treatmentData['recommendations']) && !empty($treatmentData['recommendations'])) {
                                        $displayText = is_array($treatmentData['recommendations']) ? implode(', ', $treatmentData['recommendations']) : $treatmentData['recommendations'];
                                    } elseif (isset($treatmentData['patient_info']['diagnosis']) && !empty($treatmentData['patient_info']['diagnosis'])) {
                                        $displayText = $treatmentData['patient_info']['diagnosis'];
                                    }
                                }
                            } elseif ($latestAssessment->notes) {
                                $displayText = $latestAssessment->notes;
                            }
                        }
                    @endphp
                    {{ Str::limit($displayText, 50) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align: center; padding: 20px; color: #6b7280;">
                    No children found for the selected period.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(count($patients) > 0)
    <div class="signature-section">
        <div class="signature-box">
            <div style="font-size: 10px; margin-bottom: 5px; color: #065f46;"><strong>Prepared by:</strong></div>
            <div class="signature-line">
                <strong>{{ strtoupper($nutritionist->first_name . ' ' . ($nutritionist->middle_name ? substr($nutritionist->middle_name, 0, 1) . '. ' : '') . $nutritionist->last_name) }}</strong>
            </div>
        </div>
        <div class="signature-box">
            <div style="font-size: 10px; margin-bottom: 5px; color: #065f46;"><strong>Received by:</strong></div>
            <div class="signature-line">
                <strong>_______________________________</strong><br>
                <span style="font-size: 9px; color: #047857;">City Health Office Representative</span><br>
                <span style="font-size: 8px; color: #6b7280;">Date: _______________________</span>
            </div>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>This is a computer-generated report for official use only.</p>
        <p>Generated on {{ $generatedDate }} at {{ $generatedTime }}</p>
    </div>
</body>
</html>
