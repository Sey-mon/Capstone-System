<div class="dataset-viewer" style="padding: 20px; background: white; border-radius: 8px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #2e7d32;">
        <div>
            <h3 style="margin: 0; color: #1b5e20; font-size: 18px; font-weight: 600;">{{ $title }}</h3>
            <p style="margin: 5px 0 0 0; color: #666; font-size: 13px;">WHO Growth Standards Reference Data</p>
        </div>
    </div>

    @if ($data && count($data) > 0)
        <div style="overflow-x: auto; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #2e7d32, #43a047); color: white;">
                        @if ($type === 'WFA')
                            <th style="padding: 12px 15px; text-align: left; font-weight: 600; border-right: 1px solid rgba(255,255,255,0.2);">Age (months)</th>
                        @else
                            <th style="padding: 12px 15px; text-align: left; font-weight: 600; border-right: 1px solid rgba(255,255,255,0.2);">Height/Length (cm)</th>
                        @endif
                        <th style="padding: 12px 15px; text-align: center; font-weight: 600; border-right: 1px solid rgba(255,255,255,0.2);">-3 SD</th>
                        <th style="padding: 12px 15px; text-align: center; font-weight: 600; border-right: 1px solid rgba(255,255,255,0.2);">-2 SD</th>
                        <th style="padding: 12px 15px; text-align: center; font-weight: 600; border-right: 1px solid rgba(255,255,255,0.2);">-1 SD</th>
                        <th style="padding: 12px 15px; text-align: center; font-weight: 600; border-right: 1px solid rgba(255,255,255,0.2);">Median</th>
                        <th style="padding: 12px 15px; text-align: center; font-weight: 600; border-right: 1px solid rgba(255,255,255,0.2);">+1 SD</th>
                        <th style="padding: 12px 15px; text-align: center; font-weight: 600; border-right: 1px solid rgba(255,255,255,0.2);">+2 SD</th>
                        <th style="padding: 12px 15px; text-align: center; font-weight: 600;">+3 SD</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowCount = 0; @endphp
                    @foreach ($data as $row)
                        <tr style="background: {{ $rowCount % 2 === 0 ? '#f5f5f5' : 'white' }}; border-bottom: 1px solid #e0e0e0; transition: background 0.2s;">
                            <td style="padding: 12px 15px; font-weight: 500; color: #1b5e20;">
                                @if(is_array($row))
                                    {{ $row['age'] ?? $row['height'] ?? ($rowCount + 1) }}
                                @else
                                    {{ $rowCount + 1 }}
                                @endif
                            </td>
                            @if(is_array($row))
                                <td style="padding: 12px 15px; text-align: center; color: #d32f2f;">{{ number_format($row['sd_minus_3'] ?? $row['-3'] ?? 0, 2) }}</td>
                                <td style="padding: 12px 15px; text-align: center; color: #e64a19;">{{ number_format($row['sd_minus_2'] ?? $row['-2'] ?? 0, 2) }}</td>
                                <td style="padding: 12px 15px; text-align: center; color: #f57c00;">{{ number_format($row['sd_minus_1'] ?? $row['-1'] ?? 0, 2) }}</td>
                                <td style="padding: 12px 15px; text-align: center; color: #1b5e20; font-weight: 600;">{{ number_format($row['median'] ?? $row['0'] ?? 0, 2) }}</td>
                                <td style="padding: 12px 15px; text-align: center; color: #f57c00;">{{ number_format($row['sd_plus_1'] ?? $row['+1'] ?? 0, 2) }}</td>
                                <td style="padding: 12px 15px; text-align: center; color: #e64a19;">{{ number_format($row['sd_plus_2'] ?? $row['+2'] ?? 0, 2) }}</td>
                                <td style="padding: 12px 15px; text-align: center; color: #d32f2f;">{{ number_format($row['sd_plus_3'] ?? $row['+3'] ?? 0, 2) }}</td>
                            @else
                                <td colspan="7" style="padding: 12px 15px; text-align: center; color: #999;">Invalid data format</td>
                            @endif
                        </tr>
                        @php $rowCount++; @endphp
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #e8f5e9; border-left: 4px solid #2e7d32; border-radius: 4px; font-size: 12px; color: #1b5e20;">
            <strong>Reference Information:</strong><br>
            <small>SD = Standard Deviation | Data is based on WHO Growth Standards 2006 for {{ $type === 'WFA' ? 'Weight for Age' : 'Length/Height for Age' }}</small>
        </div>
    @else
        <div style="padding: 30px; text-align: center; background: #f5f5f5; border-radius: 6px; color: #999;">
            <p style="margin: 0; font-size: 14px;">No data available for this dataset</p>
        </div>
    @endif
</div>
