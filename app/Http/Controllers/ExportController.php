<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Models\Visit;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function links(Request $request): StreamedResponse
    {
        return $this->csv('short-links.csv', ['Code', 'Short URL', 'Title', 'Destination', 'Campaign', 'Tags', 'Status', 'Health', 'Visits', 'Created at', 'Created by'], function ($out) {
            ShortLink::query()->with(['campaign', 'tags', 'creator'])->withCount('visits')->latest()->chunkById(200, function ($links) use ($out) {
                foreach ($links as $link) fputcsv($out, [$link->code, $link->short_url, $link->title, $link->destination_url, $link->campaign?->name, $link->tags->pluck('name')->join(', '), $link->is_active ? 'Active' : 'Inactive', $link->health_status, $link->visits_count, $link->created_at, $link->creator?->name]);
            });
        });
    }

    public function visits(Request $request): StreamedResponse
    {
        return $this->csv('short-link-visits.csv', ['Short code', 'Visited at', 'Country', 'City', 'Device', 'Platform', 'Browser', 'Referrer', 'Bot'], function ($out) {
            Visit::query()->with('shortLink:id,code')->latest()->chunkById(500, function ($visits) use ($out) {
                foreach ($visits as $visit) fputcsv($out, [$visit->shortLink?->code, $visit->visited_at, $visit->country, $visit->city, $visit->device_type, $visit->platform, $visit->browser, $visit->referrer_host, $visit->is_bot ? 'Yes' : 'No']);
            });
        });
    }

    private function csv(string $name, array $headings, callable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows) { $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF"); fputcsv($out, $headings); $rows($out); fclose($out); }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
