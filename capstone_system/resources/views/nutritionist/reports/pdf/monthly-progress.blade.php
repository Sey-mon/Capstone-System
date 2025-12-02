<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Progress Report</title>
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
            margin-bottom: 25px;
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
            width: 25%;
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
        .progress-summary {
            background-color: #f0fdf4;
            padding: 10px;
            margin-bottom: 12px;
            border-left: 4px solid #10b981;
        }
        .progress-summary h4 {
            font-size: 10px;
            color: #047857;
            margin-bottom: 6px;
            font-weight: bold;
        }
        .progress-summary p {
            font-size: 9px;
            color: #374151;
            line-height: 1.5;
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
            font-size: 9px;
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
        .change-indicator {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .change-positive {
            background: #d1fae5;
            color: #065f46;
        }
        .change-negative {
            background: #fee2e2;
            color: #991b1b;
        }
        .change-neutral {
            background: #f3f4f6;
            color: #6b7280;
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
        <h2>Monthly Progress Report for Main Office</h2>
        <div style="font-size: 11px; color: #6b7280;">
            Report Period: {{ $month }} {{ $year }}
        </div>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td><strong>Nutritionist:</strong> {{ $nutritionist->first_name }} {{ $nutritionist->last_name }}</td>
                <td><strong>Professional ID:</strong> {{ $nutritionist->professional_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Email:</strong> {{ $nutritionist->email }}</td>
                <td><strong>Contact:</strong> {{ $nutritionist->contact_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Report Generated:</strong> {{ $generatedDate }} at {{ $generatedTime }}</td>
                <td><strong>Children Assessed:</strong> {{ $statistics['assessed_this_month'] }} of {{ $statistics['total_patients'] }}</td>
            </tr>
        </table>
    </div>

    <div class="summary-stats">
        <div class="stat-box">
            <div class="stat-value">{{ $statistics['total_patients'] }}</div>
            <div class="stat-label">Total Patients</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $statistics['improved'] }}</div>
            <div class="stat-label">Improved</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $statistics['stable'] }}</div>
            <div class="stat-label">Stable</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $statistics['declined'] }}</div>
            <div class="stat-label">Declined</div>
        </div>
    </div>

    <div class="progress-summary">
        <h4>Progress Summary for {{ $month }} {{ $year }}</h4>
        <p>
            <strong>Overall Progress:</strong> 
            Out of {{ $statistics['total_patients'] }} children under care, {{ $statistics['assessed_this_month'] }} were assessed this month.
            {{ $statistics['improved'] }} children showed improvement in their nutritional status,
            {{ $statistics['stable'] }} remained stable, and {{ $statistics['declined'] }} experienced a decline.
            @if($statistics['improved'] > 0)
                This represents a positive trend in the nutritional recovery program.
            @endif
        </p>
    </div>

    <h3 style="font-size: 12px; margin-top: 15px; margin-bottom: 10px; color: #047857; font-weight: bold;">Monthly Progress Details</h3>
    
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 15%;">Patient Name</th>
                <th style="width: 5%;">Age</th>
                <th style="width: 12%;">Barangay</th>
                <th style="width: 7%;">Prev. Weight</th>
                <th style="width: 7%;">Curr. Weight</th>
                <th style="width: 6%;">Change</th>
                <th style="width: 7%;">Prev. Height</th>
                <th style="width: 7%;">Curr. Height</th>
                <th style="width: 6%;">Change</th>
                <th style="width: 13%;">Current Status</th>
                <th style="width: 12%;">Progress</th>
            </tr>
        </thead>
        <tbody>
            @forelse($progressData as $index => $data)
            @php
                $ageYears = floor($data['patient']->age_months / 12);
                $ageMonths = $data['patient']->age_months % 12;
                $weightChange = $data['weight_change'];
                $heightChange = $data['height_change'];
                
                $weightClass = 'change-neutral';
                $weightIcon = '→';
                if ($weightChange > 0) {
                    $weightClass = 'change-positive';
                    $weightIcon = '↑';
                } elseif ($weightChange < 0) {
                    $weightClass = 'change-negative';
                    $weightIcon = '↓';
                }
                
                $heightClass = 'change-neutral';
                $heightIcon = '→';
                if ($heightChange > 0) {
                    $heightClass = 'change-positive';
                    $heightIcon = '↑';
                } elseif ($heightChange < 0) {
                    $heightClass = 'change-negative';
                    $heightIcon = '↓';
                }
                
                $statusClass = 'status-normal';
                if ($data['current']->recovery_status) {
                    if (str_contains($data['current']->recovery_status, 'Severely') || str_contains($data['current']->recovery_status, 'Moderately')) {
                        $statusClass = 'status-malnourished';
                    } elseif (str_contains($data['current']->recovery_status, 'At Risk')) {
                        $statusClass = 'status-at-risk';
                    }
                }
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $data['patient']->first_name }} {{ $data['patient']->last_name }}</strong></td>
                <td>{{ $ageYears }}y {{ $ageMonths }}m</td>
                <td>{{ $data['patient']->barangay->name ?? 'N/A' }}</td>
                <td>{{ $data['previous'] ? number_format($data['previous']->weight_kg, 2) : 'N/A' }}</td>
                <td>{{ number_format($data['current']->weight_kg, 2) }}</td>
                <td>
                    @if($weightChange !== null)
                        <span class="change-indicator {{ $weightClass }}">
                            {{ $weightIcon }} {{ abs($weightChange) }}
                        </span>
                    @else
                        <span class="change-indicator change-neutral">New</span>
                    @endif
                </td>
                <td>{{ $data['previous'] ? number_format($data['previous']->height_cm, 2) : 'N/A' }}</td>
                <td>{{ number_format($data['current']->height_cm, 2) }}</td>
                <td>
                    @if($heightChange !== null)
                        <span class="change-indicator {{ $heightClass }}">
                            {{ $heightIcon }} {{ abs($heightChange) }}
                        </span>
                    @else
                        <span class="change-indicator change-neutral">New</span>
                    @endif
                </td>
                <td>
                    @if($data['current']->recovery_status)
                        <span class="status-badge {{ $statusClass }}">{{ $data['current']->recovery_status }}</span>
                    @else
                        <span class="status-badge">Pending</span>
                    @endif
                </td>
                <td style="font-size: 9px;">
                    @if($weightChange > 0)
                        Improving
                    @elseif($weightChange < 0)
                        Needs attention
                    @elseif($weightChange === 0)
                        Stable
                    @else
                        First assessment
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" style="text-align: center; padding: 20px; color: #6b7280;">
                    No progress data available for the selected month.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(count($progressData) > 0)
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
