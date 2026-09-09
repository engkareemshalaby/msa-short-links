<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentPartner;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RecruitmentPartnerController extends Controller
{
    public function index(): View
    {
        return view('referrals.partners.index', [
            'partners' => RecruitmentPartner::query()->with(['user'])->withCount('studentReferrals')->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $partner = DB::transaction(function () use ($data): RecruitmentPartner {
            $user = User::create([
                'name' => trim($data['name']),
                'email' => mb_strtolower(trim($data['email'])),
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole(Role::firstOrCreate(['name' => 'Partner', 'guard_name' => 'web']));

            return RecruitmentPartner::create([
                'user_id' => $user->id,
                'name' => trim($data['name']),
                'code' => $this->uniqueCode($data['name']),
                'access_token' => Str::random(48),
                'is_active' => true,
            ]);
        });
        AuditLogger::log('created', $partner, 'Created recruitment partner account', [], ['name' => $partner->name, 'email' => $data['email']]);

        return back()->with('success', __('Partner account created successfully.'));
    }

    public function toggle(RecruitmentPartner $partner): RedirectResponse
    {
        $partner->update(['is_active' => ! $partner->is_active]);
        AuditLogger::log('status_changed', $partner, 'Changed recruitment partner account status', [], ['is_active' => $partner->is_active]);

        return back()->with('success', __('Partner account status updated.'));
    }

    private function uniqueCode(string $name): string
    {
        $base = Str::slug($name) ?: 'partner';
        $code = $base;
        $suffix = 2;

        while (RecruitmentPartner::query()->where('code', $code)->exists()) {
            $code = $base.'-'.$suffix++;
        }

        return $code;
    }
}
