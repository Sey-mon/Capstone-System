<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Weekly Meal Plan - {{ $plan->patient->first_name }} {{ $plan->patient->last_name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 50px 60px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1a1a1a;
            background: white;
            padding: 0 20px;
        }
        
        /* Official Header Section */
        .official-header {
            padding: 15px 0;
            margin-bottom: 20px;
            border-bottom: 3px solid #059669;
        }
        
        .header-container {
            display: table;
            width: 100%;
        }
        
        .header-logo-left,
        .header-logo-right {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
            text-align: center;
        }
        
        .header-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        
        .header-center {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding: 0 20px;
        }
        
        .header-republic {
            font-size: 11px;
            color: #374151;
            margin-bottom: 2px;
        }
        
        .header-province {
            font-size: 11px;
            color: #374151;
            margin-bottom: 4px;
        }
        
        .header-city {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        
        .header-office {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 6px;
        }
        
        .header-contact {
            font-size: 8.5px;
            color: #6b7280;
            line-height: 1.4;
        }
        
        /* Date/Time Header */
        .pdf-header {
            text-align: right;
            padding: 0 0 8px 0;
            font-size: 9px;
            color: #666;
        }
        
        /* Title Section */
        .title-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .main-title {
            font-size: 22px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }
        
        .patient-name {
            font-size: 14px;
            color: #374151;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .generated-date {
            font-size: 11px;
            color: #6b7280;
        }
        
        /* Meal Plan Table */
        .meal-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px auto;
            font-size: 10.5px;
            max-width: 95%;
        }
        
        .meal-table thead {
            background-color: #f3f4f6;
        }
        
        .meal-table th {
            padding: 16px 12px;
            text-align: center;
            font-weight: bold;
            color: #1a1a1a;
            border: 1px solid #d1d5db;
            font-size: 11px;
            letter-spacing: 0.3px;
        }
        
        .meal-table th:first-child {
            text-align: left;
            padding-left: 16px;
        }
        
        .meal-table td {
            padding: 18px 12px;
            border: 1px solid #d1d5db;
            vertical-align: top;
            line-height: 1.7;
        }
        
        .meal-table td:first-child {
            font-weight: 600;
            background-color: #f9fafb;
            color: #059669;
            width: 95px;
            padding-left: 16px;
        }
        
        .meal-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        /* Footer Section */
        .pdf-footer {
            margin-top: 35px;
            padding-top: 18px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .system-name {
            font-size: 11px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        
        .guidance-text {
            font-size: 9px;
            color: #059669;
            margin-bottom: 5px;
        }
        
        .print-date {
            font-size: 9px;
            color: #dc2626;
        }
    </style>
</head>
<body>
    <!-- Official Government Header -->
    <div class="official-header">
        <div class="header-container">
            <div class="header-logo-left">
                @if(file_exists(public_path('img/san-pedro-logo.png')))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/san-pedro-logo.png'))) }}" alt="San Pedro Logo" class="header-logo">
                @endif
            </div>
            <div class="header-center">
                <div class="header-republic">Republic of the Philippines</div>
                <div class="header-province">Province of Laguna</div>
                <div class="header-city">CITY OF SAN PEDRO</div>
                <div class="header-office">CITY HEALTH OFFICE</div>
                <div class="header-contact">3rd Floor New City Hall Building<br>Brgy. Poblacion, City of San Pedro, Laguna<br>(02) 8808 - 2020 Loc 302<br>chonutrition.spl@gmail.com</div>
            </div>
            <div class="header-logo-right">
                @if(file_exists(public_path('img/bagong-pilipinas-logo.png')))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/bagong-pilipinas-logo.png'))) }}" alt="Bagong Pilipinas Logo" class="header-logo">
                @endif
            </div>
        </div>
    </div>
    
    <!-- Header with Date/Time -->
    <div class="pdf-header">
        {{ now()->format('n/j/y, g:i A') }}
    </div>
    
    <!-- Title Section -->
    <div class="title-section">
        <h1 class="main-title">Weekly Meal Plan</h1>
        <div class="patient-name"><strong>Patient{{ $plan->patient->patient_id }}</strong> Testcase</div>
        <div class="generated-date">Generated: {{ $plan->generated_at->format('F d, Y') }}</div>
    </div>
    
    <!-- Meal Plan Table -->
    <table class="meal-table">
        <thead>
            <tr>
                <th>MEAL</th>
                <th>DAY 1</th>
                <th>DAY 2</th>
                <th>DAY 3</th>
                <th>DAY 4</th>
                <th>DAY 5</th>
                <th>DAY 6</th>
                <th>DAY 7</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Breakfast</td>
                @for($day = 0; $day < 7; $day++)
                    <td>{{ $parsedMeals['breakfast'][$day] ?? '-' }}</td>
                @endfor
            </tr>
            <tr>
                <td>Lunch</td>
                @for($day = 0; $day < 7; $day++)
                    <td>{{ $parsedMeals['lunch'][$day] ?? '-' }}</td>
                @endfor
            </tr>
            <tr>
                <td>PM Snack</td>
                @for($day = 0; $day < 7; $day++)
                    <td>{{ $parsedMeals['snack'][$day] ?? '-' }}</td>
                @endfor
            </tr>
            <tr>
                <td>Dinner</td>
                @for($day = 0; $day < 7; $day++)
                    <td>{{ $parsedMeals['dinner'][$day] ?? '-' }}</td>
                @endfor
            </tr>
        </tbody>
    </table>
    
    <!-- Footer -->
    <div class="pdf-footer">
        <div class="system-name">SHARES</div>
        <div class="print-date">Printed on {{ now()->format('n/j/Y') }}</div>
    </div>
</body>
</html>
