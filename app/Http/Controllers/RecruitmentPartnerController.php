<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentPartner;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RecruitmentPartnerController extends Controller
{
    public function index(): View
    {
        return view('referrals.partners.index', [
            'partners' => RecruitmentPartner::query()->withCount('studentReferrals')->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
        $partner = RecruitmentPartner::create([
            'name' => trim($data['name']),
            'code' => $this->uniqueCode($data['name']),
            'access_token' => Str::random(48),
            'is_active' => true,
        ]);
        AuditLogger::log('created', $partner, 'Created recruitment partner link', [], ['name' => $partner->name]);

        return back()->with('success', __('Partner link created successfully.'));
    }

    public function toggle(RecruitmentPartner $partner): RedirectResponse
    {
        $partner->update(['is_active' => ! $partner->is_active]);
        AuditLogger::log('status_changed', $partner, 'Changed recruitment partner link status', [], ['is_active' => $partner->is_active]);

        return back()->with('success', __('Partner link status updated.'));
    }

    public function regenerate(RecruitmentPartner $partner): RedirectResponse
    {
        $partner->update(['access_token' => Str::random(48)]);
        AuditLogger::log('updated', $partner, 'Regenerated recruitment partner link');

        return back()->with('success', __('A new partner link was generated. The old link no longer works.'));
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
