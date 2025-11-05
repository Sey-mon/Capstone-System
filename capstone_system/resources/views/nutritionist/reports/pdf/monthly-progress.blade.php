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
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #8b5cf6;
        }
        .header h1 {
            font-size: 18px;
            color: #8b5cf6;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 14px;
            color: #7c3aed;
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
            color: #7c3aed;
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
            color: #8b5cf6;
        }
        .stat-label {
            font-size: 8px;
            color: #6b7280;
            margin-top: 2px;
        }
        .progress-summary {
            background: #faf5ff;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #8b5cf6;
        }
        .progress-summary h4 {
            font-size: 11px;
            color: #7c3aed;
            margin-bottom: 8px;
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
            background: #8b5cf6;
            color: white;
            padding: 6px 4px;
            font-size: 8px;
            font-weight: bold;
            text-align: left;
            border: 1px solid #7c3aed;
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
        .change-indicator {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
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
        <h2>Monthly Progress Report for Main Office</h2>
        <div style="font-size: 9px; color: #6b7280;">
            Report Period: {{ $month }} {{ $year }}
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

    <h3 style="font-size: 12px; margin-top: 15px; margin-bottom: 10px; color: #7c3aed;">Monthly Progress Details</h3>
    
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
                <td style="font-size: 7px;">
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
