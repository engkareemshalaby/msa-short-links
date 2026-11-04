<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\Voting\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View { return view('voting.login'); }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['identity' => ['required', 'string', 'max:255'], 'password' => ['required', 'string']]);
        $identity = trim($data['identity']);
        $email = filter_var($identity, FILTER_VALIDATE_EMAIL)
            ? $identity
            : Member::where('staff_id', $identity)->first()?->user?->email;

        if (! $email || ! Auth::attempt(['email' => $email, 'password' => $data['password']]) || ! $request->user()->hasRole('Voter') || ! Member::where('user_id', $request->user()->id)->exists()) {
            Auth::logout();
            return back()->withErrors(['identity' => 'بيانات الدخول غير صحيحة.'])->onlyInput('identity');
        }

        $request->session()->regenerate();
        return redirect()->route('voting.index');
    }
}
