<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $days = in_array((int) $request->input('days', 30), [7, 30, 90], true) ? (int) $request->input('days', 30) : 30;
        $query = Visit::where('is_bot', false)->where('visited_at', '>=', now()->subDays($days - 1)->startOfDay());
        if ($request->filled('link_id')) {
            $query->where('short_link_id', $request->integer('link_id'));
        }

        $visits = (clone $query)->count();
        $unique = (clone $query)->distinct('visitor_hash')->count('visitor_hash');
        $dailyRaw = (clone $query)->selectRaw('DATE(visited_at) as day, COUNT(*) as total, COUNT(DISTINCT visitor_hash) as unique_total')
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');
        $start = now()->subDays($days - 1)->startOfDay();
        $daily = collect(range(0, $days - 1))->map(function ($offset) use ($start, $dailyRaw) {
            $date = $start->copy()->addDays($offset);

            return ['day' => $date->format('M d'), 'total' => (int) ($dailyRaw[$date->toDateString()]->total ?? 0), 'unique' => (int) ($dailyRaw[$date->toDateString()]->unique_total ?? 0)];
        });

        $breakdown = fn (string $column, int $limit = 8) => (clone $query)->select($column, DB::raw('COUNT(*) as total'))
            ->groupBy($column)->orderByDesc('total')->limit($limit)->get();

        return view('analytics.index', [
            'days' => $days, 'visits' => $visits, 'unique' => $unique, 'daily' => $daily,
            'devices' => $breakdown('device_type'), 'browsers' => $breakdown('browser'),
            'platforms' => $breakdown('platform'), 'referrers' => $breakdown('referer_host'),
            'links' => ShortLink::orderBy('title')->get(['id', 'title', 'code']),
            'selectedLink' => $request->input('link_id'),
        ]);
    }

    public function link(Request $request, ShortLink $link): View
    {
        $request->merge(['link_id' => $link->id]);

        return $this->index($request);
    }
}
