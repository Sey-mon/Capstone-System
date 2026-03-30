yes<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        /* Make modal wider for better data fit */
        .modal-content {
            width: 800px !important;
            max-width: 95vw;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2196F3;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2196F3;
            margin: 0;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-section h2 {
            color: #2196F3;
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
            color: #2196F3;
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
            background-color: #2196F3;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .low-stock {
            background-color: #ffebee !important;
            color: #d32f2f;
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
        <h1>Inventory Report</h1>
        <p>Generated on: {{ $generated_at }}</p>
    </div>

    <div class="info-section">
        <h2>Summary Statistics</h2>
        <div class="stat-box">
            <div class="stat-number">{{ is_scalar($data['total_items'] ?? null) ? $data['total_items'] : 'N/A' }}</div>
            <div class="stat-label">Total Items</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ is_scalar($data['low_stock_items'] ?? null) ? $data['low_stock_items'] : 'N/A' }}</div>
            <div class="stat-label">Low Stock Items</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">₱{{ is_numeric($data['total_value'] ?? null) ? number_format($data['total_value'], 2) : '0.00' }}</div>
            <div class="stat-label">Total Value</div>
        </div>
    </div>

    @if(isset($data['stock_levels']) && count($data['stock_levels']) > 0)
    <div class="info-section">
        <h2>Stock Levels</h2>
        <table>
            <thead>
                    <td>
                            <td>{{ is_scalar($item['item_name'] ?? null) ? $item['item_name'] : 'N/A' }}</td>
                    </td>
                    <th>Unit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['stock_levels'] as $item)
                <tr>
                    <td>
                        {{ $item['item_name'] ?? 'N/A' }}
                    </td>
                    <td>{{ $item['quantity'] ?? 0 }}</td>
                        <td>{{ is_scalar($item['quantity'] ?? null) ? $item['quantity'] : 'N/A' }}</td>
                    <td>{{ $item['unit'] ?? 'N/A' }}</td>
                        <td>{{ is_scalar($item['unit'] ?? null) ? $item['unit'] : 'N/A' }}</td>
                    <td>{{ $item['status'] ?? 'N/A' }}</td>
                        <td>{{ is_scalar($item['status'] ?? null) ? $item['status'] : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['items_by_category']) && count($data['items_by_category']) > 0)
    <div class="info-section">
        <h2>Items by Category</h2>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Number of Items</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['items_by_category'] as $category => $count)
                <tr>
                        <td>{{ is_scalar($category) ? ucfirst($category) : 'N/A' }}</td>
                        <td>{{ is_scalar($count) ? $count : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['low_stock_items']) && count($data['low_stock_items']) > 0)
    <div class="info-section">
        <h2>Low Stock Items</h2>
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Current Stock</th>
                    <th>Minimum Stock</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['low_stock_items'] as $item)
                <tr class="low-stock">
                    <td>{{ $item['item_name'] ?? 'N/A' }}</td>
                    <td>{{ $item['quantity'] ?? 0 }}</td>
                    <td>{{ $item['minimum_stock'] ?? 0 }}</td>
                    <td>{{ $item['category']['category_name'] ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['recent_transactions']) && count($data['recent_transactions']) > 0)
    <div class="info-section">
        <h2>Recent Transactions</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>User</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['recent_transactions'] as $transaction)
                <tr>
                    <td>{{ isset($transaction['created_at']) ? \Carbon\Carbon::parse($transaction['created_at'])->format('Y-m-d H:i') : 'N/A' }}</td>
                    <td>{{ isset($transaction['item']['item_name']) ? $transaction['item']['item_name'] : ($transaction['item']['name'] ?? 'N/A') }}</td>
                    <td>{{ ucfirst($transaction['type'] ?? 'N/A') }}</td>
                    <td>{{ $transaction['quantity'] ?? 0 }}</td>
                    <td>{{ $transaction['user']['name'] ?? 'N/A' }}</td>
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
