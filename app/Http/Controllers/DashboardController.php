<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $start = now()->subDays(13)->startOfDay();
        $dailyRaw = Visit::where('visited_at', '>=', $start)
            ->where('is_bot', false)
            ->selectRaw('DATE(visited_at) as day, COUNT(*) as total, COUNT(DISTINCT visitor_hash) as unique_total')
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');

        $daily = collect(range(0, 13))->map(function ($offset) use ($start, $dailyRaw) {
            $day = $start->copy()->addDays($offset)->toDateString();

            return [
                'day' => $start->copy()->addDays($offset)->format('M d'),
                'total' => (int) ($dailyRaw[$day]->total ?? 0),
                'unique' => (int) ($dailyRaw[$day]->unique_total ?? 0),
            ];
        });

        $topLinks = ShortLink::with('creator')
            ->withCount(['visits' => fn ($query) => $query->where('is_bot', false)])
            ->orderByDesc('visits_count')->limit(6)->get();

        $devices = Visit::where('is_bot', false)->select('device_type', DB::raw('COUNT(*) as total'))
            ->groupBy('device_type')->orderByDesc('total')->get();

        $referrers = Visit::where('is_bot', false)->whereNotNull('referer_host')
            ->select('referer_host', DB::raw('COUNT(*) as total'))
            ->groupBy('referer_host')->orderByDesc('total')->limit(5)->get();

        return view('dashboard', [
            'stats' => [
                'links' => ShortLink::count(),
                'active' => ShortLink::available()->count(),
                'visits' => Visit::where('is_bot', false)->count(),
                'unique' => Visit::where('is_bot', false)->distinct('visitor_hash')->count('visitor_hash'),
                'today' => Visit::where('is_bot', false)->whereDate('visited_at', today())->count(),
            ],
            'daily' => $daily,
            'topLinks' => $topLinks,
            'devices' => $devices,
            'referrers' => $referrers,
        ]);
    }
}
