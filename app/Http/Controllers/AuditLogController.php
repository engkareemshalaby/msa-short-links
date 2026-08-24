<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user')
            ->when($request->action, fn ($query, $action) => $query->where('action', $action))
            ->latest('created_at')->paginate(25)->withQueryString();

        return view('audit.index', compact('logs'));
    }
}
