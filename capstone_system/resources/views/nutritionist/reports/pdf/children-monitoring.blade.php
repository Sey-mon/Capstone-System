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
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #10b981;
        }
        .header h1 {
            font-size: 18px;
            color: #10b981;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 14px;
            color: #059669;
            margin-bottom: 10px;
        }
        .report-info {
            background: #f3f4f6;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .report-info table {
            width: 100%;
        }
        .report-info td {
            padding: 3px 5px;
            font-size: 9px;
        }
        .report-info strong {
            color: #059669;
        }
        .summary-stats {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 8px;
            text-align: center;
            border: 1px solid #d1d5db;
            background: #f9fafb;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #10b981;
        }
        .stat-label {
            font-size: 8px;
            color: #6b7280;
            margin-top: 2px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background: #10b981;
            color: white;
            padding: 6px 4px;
            font-size: 8px;
            font-weight: bold;
            text-align: left;
            border: 1px solid #059669;
        }
        table.data-table td {
            padding: 5px 4px;
            font-size: 8px;
            border: 1px solid #d1d5db;
        }
        table.data-table tr:nth-child(even) {
            background: #f9fafb;
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
            font-size: 8px;
            color: #6b7280;
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
        }
        .page-break {
            page-break-after: always;
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
    <div class="header">
        <h1>{{ $title }}</h1>
        <h2>Children Monitoring Report for Main Office</h2>
        <div style="font-size: 9px; color: #6b7280;">
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
                <td><strong>Total Children:</strong> {{ count($patients) }}</td>
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

    <h3 style="font-size: 12px; margin-top: 15px; margin-bottom: 10px; color: #059669;">Children Details</h3>
    
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
                <th style="width: 15%;">Remarks</th>
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
                        <span class="status-badge">Pending</span>
                    @endif
                </td>
                <td>{{ $patient->is_4ps_beneficiary ? 'Yes' : 'No' }}</td>
                <td style="font-size: 7px;">
                    @if($latestAssessment && $latestAssessment->notes)
                        {{ Str::limit($latestAssessment->notes, 50) }}
                    @else
                        -
                    @endif
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
            <div style="font-size: 9px; margin-bottom: 5px;"><strong>Prepared by:</strong></div>
            <div class="signature-line">
                <strong>{{ $nutritionist->name }}</strong><br>
                <span style="font-size: 8px;">Nutritionist</span>
            </div>
        </div>
        <div class="signature-box">
            <div style="font-size: 9px; margin-bottom: 5px;"><strong>Received by:</strong></div>
            <div class="signature-line">
                <strong>_______________________________</strong><br>
                <span style="font-size: 8px;">Main Office Representative</span>
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
