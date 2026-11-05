<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'staff_id' => ['required', 'string', 'max:255'],
        ]);
        $email = trim($data['email']);
        $staffId = trim($data['staff_id']);
        $user = User::where('email', $email)->first();

        if (! $user || ! $user->hasRole('Voter')
            || ! Member::where('user_id', $user->id)->where('staff_id', $staffId)->exists()
            || ! Auth::attempt(['email' => $email, 'password' => $staffId])) {
            Auth::logout();

            return back()->withErrors(['email' => 'The email address or Staff ID is incorrect.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('voting.index');
    }
}
