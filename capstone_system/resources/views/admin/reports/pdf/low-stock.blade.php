<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Low Stock Alert Report</title>
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
            border-bottom: 2px solid #f44336;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #f44336;
            margin: 0;
        }
        .alert-badge {
            background: #f44336;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-section h2 {
            color: #f44336;
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
            color: #f44336;
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
            background-color: #f44336;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .critical {
            background-color: #ffebee !important;
            color: #d32f2f;
            font-weight: bold;
        }
        .warning {
            background-color: #fff3e0 !important;
            color: #f57c00;
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
        <h1>Low Stock Alert Report</h1>
        <span class="alert-badge">URGENT ATTENTION REQUIRED</span>
        <p>Generated on: {{ $generated_at }}</p>
    </div>

    <div class="info-section">
        <h2>Alert Summary</h2>
        <div class="stat-box">
            <div class="stat-number">{{ $data['critical_items'] ?? 0 }}</div>
            <div class="stat-label">Critical Items</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $data['warning_items'] ?? 0 }}</div>
            <div class="stat-label">Warning Items</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $data['total_affected_value'] ?? 0 }}</div>
            <div class="stat-label">Total Affected Value</div>
        </div>
    </div>

    @if(isset($data['critical_stock_items']) && count($data['critical_stock_items']) > 0)
    <div class="info-section">
        <h2>Critical Stock Items (Immediate Action Required)</h2>
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Current Stock</th>
                    <th>Minimum Stock</th>
                    <th>Category</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['critical_stock_items'] as $item)
                <tr class="critical">
                    <td>{{ $item['name'] ?? 'N/A' }}</td>
                    <td>{{ $item['current_stock'] ?? 0 }}</td>
                    <td>{{ $item['minimum_stock'] ?? 0 }}</td>
                    <td>{{ $item['category'] ?? 'N/A' }}</td>
                    <td>{{ isset($item['updated_at']) ? \Carbon\Carbon::parse($item['updated_at'])->format('Y-m-d H:i') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['warning_stock_items']) && count($data['warning_stock_items']) > 0)
    <div class="info-section">
        <h2>Warning Stock Items (Reorder Soon)</h2>
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Current Stock</th>
                    <th>Minimum Stock</th>
                    <th>Category</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['warning_stock_items'] as $item)
                <tr class="warning">
                    <td>{{ $item['name'] ?? 'N/A' }}</td>
                    <td>{{ $item['current_stock'] ?? 0 }}</td>
                    <td>{{ $item['minimum_stock'] ?? 0 }}</td>
                    <td>{{ $item['category'] ?? 'N/A' }}</td>
                    <td>{{ isset($item['updated_at']) ? \Carbon\Carbon::parse($item['updated_at'])->format('Y-m-d H:i') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['recommended_actions']) && count($data['recommended_actions']) > 0)
    <div class="info-section">
        <h2>Recommended Actions</h2>
        <ul>
            @foreach($data['recommended_actions'] as $action)
            <li>{{ $action }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Capstone System</p>
        <p><strong>Note:</strong> Please take immediate action on critical items to avoid stockouts</p>
    </div>
</body>
</html>
