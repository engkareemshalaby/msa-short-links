<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PartnerAuthController extends Controller
{
    public function create(): View
    {
        return view('partner.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember')) || ! $request->user()->hasRole('Partner')) {
            Auth::logout();

            return back()->withErrors(['email' => __('The provided credentials are incorrect.')])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('partner.referrals.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('partner.login');
    }
}
