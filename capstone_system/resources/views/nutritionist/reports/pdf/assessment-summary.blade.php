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
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #3b82f6;
        }
        .header h1 {
            font-size: 18px;
            color: #3b82f6;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 14px;
            color: #2563eb;
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
            color: #2563eb;
        }
        .summary-stats {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-box {
            display: table-cell;
            width: 20%;
            padding: 8px;
            text-align: center;
            border: 1px solid #d1d5db;
            background: #f9fafb;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #3b82f6;
        }
        .stat-label {
            font-size: 8px;
            color: #6b7280;
            margin-top: 2px;
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
            background: #f9fafb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .stat-section h4 {
            font-size: 10px;
            color: #2563eb;
            margin-bottom: 8px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 5px;
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
            background: #3b82f6;
            color: white;
            padding: 6px 4px;
            font-size: 8px;
            font-weight: bold;
            text-align: left;
            border: 1px solid #2563eb;
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
        <h2>Assessment Summary for Main Office</h2>
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
                <th style="width: 20%;">Treatment/Notes</th>
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
                    @if($assessment->treatment)
                        {{ Str::limit($assessment->treatment, 60) }}
                    @elseif($assessment->notes)
                        {{ Str::limit($assessment->notes, 60) }}
                    @else
                        -
                    @endif
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
