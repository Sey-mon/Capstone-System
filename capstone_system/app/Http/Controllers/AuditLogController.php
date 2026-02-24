<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');
        
        // Apply filters
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('action')) {
            $action = $request->action;
            // Handle generic login/logout to match all role-specific variants
            if (in_array(strtolower($action), ['login', 'logout'])) {
                $query->where('action', 'like', '%' . strtoupper($action) . '%');
            } else {
                $query->where('action', $action);
            }
        }

        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
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

        $users = User::orderBy('first_name')->get();
        
        return view('admin.audit-logs', compact('logs', 'users'));
    }
}
