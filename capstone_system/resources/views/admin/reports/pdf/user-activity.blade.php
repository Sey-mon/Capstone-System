<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>User Activity Report</title>
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
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #4CAF50;
            margin: 0;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-section h2 {
            color: #4CAF50;
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
            color: #4CAF50;
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
            background-color: #4CAF50;
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
        <h1>User Activity Report</h1>
        <p>Generated on: {{ $generated_at }}</p>
    </div>

    <div class="info-section">
        <h2>Summary Statistics</h2>
        <div class="stat-box">
            <div class="stat-number">{{ $data['total_users'] ?? 0 }}</div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $data['active_users_30_days'] ?? 0 }}</div>
            <div class="stat-label">Active Users (30 days)</div>
        </div>
    </div>

    @if(isset($data['users_by_role']) && count($data['users_by_role']) > 0)
    <div class="info-section">
        <h2>Users by Role</h2>
        <table>
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Number of Users</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['users_by_role'] as $role => $count)
                <tr>
                    <td>{{ ucfirst($role) }}</td>
                    <td>{{ $count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['recent_assessments']) && count($data['recent_assessments']) > 0)
    <div class="info-section">
        <h2>Recent Assessments</h2>
        <table>
            <thead>
                <tr>
                    <th>Assessment ID</th>
                    <th>Patient</th>
                    <th>BNS</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['recent_assessments'] as $assessment)
                <tr>
                    <td>{{ $assessment['id'] ?? 'N/A' }}</td>
                    <td>
                        @if(isset($assessment['patient']))
                            {{ $assessment['patient']['first_name'] ?? '' }} {{ $assessment['patient']['last_name'] ?? '' }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $assessment['user']['name'] ?? 'N/A' }}</td>
                    <td>{{ isset($assessment['created_at']) ? \Carbon\Carbon::parse($assessment['created_at'])->format('Y-m-d H:i') : 'N/A' }}</td>
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
