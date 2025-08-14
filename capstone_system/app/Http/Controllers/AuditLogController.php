<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');
        
        // Apply filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('log_timestamp', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('log_timestamp', '<=', $request->date_to);
        }
        
        $logs = $query->latest('log_timestamp')->paginate(20);
        
        // Preserve filter parameters in pagination
        $logs->appends($request->query());
        
        return view('admin.audit-logs', compact('logs'));
    }
}
