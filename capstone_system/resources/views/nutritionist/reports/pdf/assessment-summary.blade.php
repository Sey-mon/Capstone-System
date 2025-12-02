<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Assessment Summary Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        /* Professional Document Header */
        .document-header {
            background: white;
            color: #000;
            padding: 20px 30px 15px 30px;
            margin-bottom: 20px;
            border-bottom: 4px solid #10b981;
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
            background: rgba(16, 185, 129, 0.1);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #10b981;
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
            color: #10b981;
        }
        .office-name {
            font-size: 18px;
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
            padding: 12px;
            background-color: #ecfdf5;
            border: 2px solid #10b981;
        }
        .report-title-section h1 {
            font-size: 16px;
            color: #047857;
            margin-bottom: 4px;
            font-weight: bold;
        }
        .report-title-section h2 {
            font-size: 12px;
            color: #065f46;
            margin-bottom: 6px;
            font-weight: bold;
        }
        
        .report-info {
            background-color: #f0fdf4;
            padding: 8px 10px;
            margin-bottom: 12px;
            border: 1px solid #a7f3d0;
        }
        .report-info table {
            width: 100%;
        }
        .report-info td {
            padding: 3px 5px;
            font-size: 10px;
        }
        .report-info strong {
            color: #047857;
            font-weight: bold;
        }
        .summary-stats {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-spacing: 5px;
        }
        .stat-box {
            display: table-cell;
            width: 20%;
            padding: 6px;
            text-align: center;
            border: 1px solid #10b981;
            background-color: #ecfdf5;
        }
        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #047857;
        }
        .stat-label {
            font-size: 8px;
            color: #065f46;
            margin-top: 2px;
            font-weight: bold;
        }
        .statistics-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-column {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
        }
        .stat-section {
            background-color: #f0fdf4;
            padding: 8px;
            border: 1px solid #a7f3d0;
            margin-bottom: 8px;
        }
        .stat-section h4 {
            font-size: 9px;
            color: #047857;
            margin-bottom: 6px;
            border-bottom: 1px solid #10b981;
            padding-bottom: 4px;
            font-weight: bold;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 9px;
        }
        .stat-item-label {
            color: #6b7280;
        }
        .stat-item-value {
            font-weight: bold;
            color: #111827;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #10b981;
            color: #ffffff;
            padding: 8px 5px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #059669;
        }
        table.data-table td {
            padding: 6px 4px;
            font-size: 9px;
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
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
        }
        .status-normal {
            background: #d1fae5;
            color: #065f46;
        }
        .status-at-risk {
            background: #fed7aa;
            color: #92400e;
        }
        .status-malnourished {
            background: #fee2e2;
            color: #991b1b;
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
        <h2>Assessment Summary for Main Office</h2>
        <div style="font-size: 11px; color: #6b7280;">
            Report Period: {{ date('F d, Y', strtotime($startDate)) }} to {{ date('F d, Y', strtotime($endDate)) }}
        </div>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td><strong>Nutritionist:</strong> {{ $nutritionist->name }}</td>
                <td><strong>Professional ID:</strong> {{ $nutritionist->professional_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Email:</strong> {{ $nutritionist->email }}</td>
                <td><strong>Contact:</strong> {{ $nutritionist->contact_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Report Generated:</strong> {{ $generatedDate }} at {{ $generatedTime }}</td>
                <td><strong>Total Assessments:</strong> {{ $statistics['total_assessments'] }}</td>
            </tr>
        </table>
    </div>

    <div class="summary-stats">
        <div class="stat-box">
            <div class="stat-value">{{ $statistics['total_assessments'] }}</div>
            <div class="stat-label">Total Assessments</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $statistics['completed_assessments'] }}</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $statistics['average_weight'] ?? 'N/A' }}</div>
            <div class="stat-label">Avg. Weight (kg)</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $statistics['average_height'] ?? 'N/A' }}</div>
            <div class="stat-label">Avg. Height (cm)</div>
        </div>
    </div>

    <div class="statistics-grid">
        <div class="stat-column">
            <div class="stat-section">
                <h4>Assessment Status Distribution</h4>
                @if(isset($statistics['by_status']) && count($statistics['by_status']) > 0)
                    @foreach($statistics['by_status'] as $status => $count)
                        <div class="stat-item">
                            <span class="stat-item-label">{{ $status }}</span>
                            <span class="stat-item-value">{{ $count }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="stat-item" style="justify-content: center;">
                        <span class="stat-item-label">No status data available</span>
                    </div>
                @endif
            </div>
        </div>
        <div class="stat-column">
            <div class="stat-section">
                <h4>Assessments by Barangay</h4>
                @if(isset($statistics['by_barangay']) && count($statistics['by_barangay']) > 0)
                    @foreach($statistics['by_barangay']->take(8) as $barangay => $count)
                        <div class="stat-item">
                            <span class="stat-item-label">{{ $barangay ?: 'Not Specified' }}</span>
                            <span class="stat-item-value">{{ $count }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="stat-item" style="justify-content: center;">
                        <span class="stat-item-label">No barangay data available</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <h3 style="font-size: 12px; margin-top: 15px; margin-bottom: 10px; color: #2563eb;">Assessment Details</h3>
    
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 10%;">Date</th>
                <th style="width: 18%;">Patient Name</th>
                <th style="width: 5%;">Age</th>
                <th style="width: 5%;">Sex</th>
                <th style="width: 12%;">Barangay</th>
                <th style="width: 7%;">Weight (kg)</th>
                <th style="width: 7%;">Height (cm)</th>
                <th style="width: 13%;">Recovery Status</th>
                <th style="width: 20%;">Nutrition Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assessments as $index => $assessment)
            @php
                $ageYears = floor($assessment->patient->age_months / 12);
                $ageMonths = $assessment->patient->age_months % 12;
                $statusClass = 'status-normal';
                if ($assessment->recovery_status) {
                    if (str_contains($assessment->recovery_status, 'Severely') || str_contains($assessment->recovery_status, 'Moderately')) {
                        $statusClass = 'status-malnourished';
                    } elseif (str_contains($assessment->recovery_status, 'At Risk')) {
                        $statusClass = 'status-at-risk';
                    }
                }
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $assessment->assessment_date ? $assessment->assessment_date->format('M d, Y') : 'N/A' }}</td>
                <td><strong>{{ $assessment->patient->first_name }} {{ $assessment->patient->last_name }}</strong></td>
                <td>{{ $ageYears }}y {{ $ageMonths }}m</td>
                <td>{{ $assessment->patient->sex }}</td>
                <td>{{ $assessment->patient->barangay->name ?? 'N/A' }}</td>
                <td>{{ number_format($assessment->weight_kg, 2) }}</td>
                <td>{{ number_format($assessment->height_cm, 2) }}</td>
                <td>
                    @if($assessment->recovery_status)
                        <span class="status-badge {{ $statusClass }}">{{ $assessment->recovery_status }}</span>
                    @else
                        <span class="status-badge">Pending</span>
                    @endif
                </td>
                <td style="font-size: 7px;">
                    @php
                        $treatmentData = null;
                        $displayText = '-';
                        
                        if ($assessment->treatment) {
                            $treatmentData = is_string($assessment->treatment) ? json_decode($assessment->treatment, true) : $assessment->treatment;
                            
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
                        } elseif ($assessment->notes) {
                            $displayText = $assessment->notes;
                        }
                    @endphp
                    {{ Str::limit($displayText, 60) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px; color: #6b7280;">
                    No assessments found for the selected period.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(count($assessments) > 0)
    <div class="signature-section">
        <div class="signature-box">
            <div style="font-size: 9px; margin-bottom: 5px;"><strong>Prepared by:</strong></div>
            <div class="signature-line">
                <strong>{{ $nutritionist->first_name }} {{ $nutritionist->last_name }}</strong><br>
                <span style="font-size: 10px;">Nutritionist</span>
            </div>
        </div>
        <div class="signature-box">
            <div style="font-size: 9px; margin-bottom: 5px;"><strong>Received by:</strong></div>
            <div class="signature-line">
                <strong>_______________________________</strong><br>
                <span style="font-size: 10px;">Main Office Representative</span>
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
