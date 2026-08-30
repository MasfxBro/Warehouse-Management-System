<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Tampilkan halaman Log Activity (Read-Only).
     */
    public function index(Request $request)
    {
        $search    = $request->query('search');
        $dateRange = $request->query('date_range', 'all');

        $query = ActivityLog::with('user')->latest();

        // Filter pencarian — case-insensitive partial match
        if ($search) {
            $searchLower = strtolower($search);
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(operator_name) LIKE ?', ['%' . $searchLower . '%'])
                  ->orWhereRaw('LOWER(action) LIKE ?', ['%' . $searchLower . '%']);
            });
        }

        // Filter rentang tanggal
        match ($dateRange) {
            'today'   => $query->whereDate('created_at', today()),
            'week'    => $query->where('created_at', '>=', now()->subDays(7)),
            'month'   => $query->where('created_at', '>=', now()->subDays(30)),
            'year'    => $query->whereYear('created_at', now()->year),
            default   => null, // 'all' — no filter
        };

        $logs = $query->paginate(20)->withQueryString();

        return view('logs.index', compact('logs', 'search', 'dateRange'));
    }
}
