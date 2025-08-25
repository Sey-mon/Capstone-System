<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Assessment Trends Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #FF9800;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #FF9800;
            margin: 0;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-section h2 {
            color: #FF9800;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .stat-box {
            display: inline-block;
            background: #f8f9fa;
            padding: 15px;
            margin: 10px;
            border-radius: 5px;
            text-align: center;
            min-width: 150px;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #FF9800;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #FF9800;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Assessment Trends Report</h1>
        <p>Generated on: {{ $generated_at }}</p>
    </div>

    <div class="info-section">
        <h2>Summary Statistics</h2>
        <div class="stat-box">
            <div class="stat-number">{{ $data['total_assessments'] ?? 0 }}</div>
            <div class="stat-label">Total Assessments</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $data['assessments_this_month'] ?? 0 }}</div>
            <div class="stat-label">This Month</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $data['avg_assessments_per_day'] ?? 0 }}</div>
            <div class="stat-label">Average Per Day</div>
        </div>
    </div>

    @if(isset($data['monthly_trends']) && count($data['monthly_trends']) > 0)
    <div class="info-section">
        <h2>Monthly Assessment Trends</h2>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Assessments</th>
                    <th>Growth</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['monthly_trends'] as $trend)
                <tr>
                    <td>{{ $trend['month'] ?? 'N/A' }}</td>
                    <td>{{ $trend['count'] ?? 0 }}</td>
                    <td>{{ isset($trend['growth']) ? ($trend['growth'] >= 0 ? '+' : '') . $trend['growth'] . '%' : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['assessments_by_nutritionist']) && count($data['assessments_by_nutritionist']) > 0)
    <div class="info-section">
        <h2>Assessments by Nutritionist</h2>
        <table>
            <thead>
                <tr>
                    <th>Nutritionist</th>
                    <th>Assessments Completed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['assessments_by_nutritionist'] as $nutritionist => $count)
                <tr>
                    <td>{{ $nutritionist }}</td>
                    <td>{{ $count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['assessment_outcomes']) && count($data['assessment_outcomes']) > 0)
    <div class="info-section">
        <h2>Assessment Outcomes</h2>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['assessment_outcomes'] as $status => $count)
                <tr>
                    <td>{{ ucfirst($status) }}</td>
                    <td>{{ $count }}</td>
                    <td>{{ isset($data['total_assessments']) && $data['total_assessments'] > 0 ? round(($count / $data['total_assessments']) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Capstone System</p>
    </div>
</body>
</html>
