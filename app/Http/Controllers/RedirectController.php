<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Services\SmartTargetResolver;
use App\Services\VisitTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class RedirectController extends Controller
{
    public function __invoke(Request $request, string $code, VisitTracker $tracker, SmartTargetResolver $targets): Response
    {
        $link = ShortLink::available()->where('code', strtolower($code))->firstOrFail();
        if ($link->access_password && ! $request->session()->get($this->sessionKey($link))) {
            return response()->view('public.password', compact('link'));
        }
        $tracker->record($link, $request);
        $destination = $targets->destination($link, $request);

        if ($link->retargeting_enabled && $link->pixels()->where('is_active', true)->exists()) {
            return response()->view('redirect.pixel', ['destination' => $destination, 'pixels' => $link->pixels()->where('is_active', true)->get()]);
        }

        return redirect()->away($destination, 302);
    }

    public function unlock(Request $request, string $code): RedirectResponse
    {
        $link = ShortLink::available()->where('code', strtolower($code))->firstOrFail();
        $request->validate(['password' => ['required', 'string']]);

        if (! $link->access_password || ! Hash::check((string) $request->input('password'), $link->access_password)) {
            return back()->withErrors(['password' => __('Incorrect password.')]);
        }

        $request->session()->put($this->sessionKey($link), true);

        return redirect()->route('redirect', $link->code);
    }

    private function sessionKey(ShortLink $link): string
    {
        return 'short_link_access_'.$link->id;
    }
}
