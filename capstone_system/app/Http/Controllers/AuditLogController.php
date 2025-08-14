<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('user')->latest('log_timestamp')->paginate(20);
        return view('admin.audit-logs', compact('logs'));
    }
}
