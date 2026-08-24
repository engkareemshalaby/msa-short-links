<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Services\VisitTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function __invoke(Request $request, string $code, VisitTracker $tracker): RedirectResponse
    {
        $link = ShortLink::available()->where('code', strtolower($code))->firstOrFail();
        $tracker->record($link, $request);

        return redirect()->away($link->destination_url, 302);
    }
}
