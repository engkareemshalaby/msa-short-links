<?php

namespace App\Http\Controllers;

use App\Models\StudentReferral;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PartnerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $partner = $request->user()->recruitmentPartner;
        abort_unless($partner?->is_active, 403);
        $partner->load('crmSubmission');
        return view('partner.profile', compact('partner'));
    }

    public function update(Request $request): RedirectResponse
    {
        $partner = $request->user()->recruitmentPartner;
        abort_unless($partner?->is_active, 403);
        $data = $request->validate([
            'agency_name' => ['required', 'string', 'max:255'], 'country' => ['nullable', Rule::in(StudentReferral::COUNTRIES)],
            'city' => ['nullable', 'string', 'max:120'], 'website' => ['nullable', 'url:http,https', 'max:500'],
            'contact_name' => ['required', 'string', 'max:255'], 'job_title' => ['nullable', 'string', 'max:150'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        DB::transaction(function () use ($data, $partner, $request): void {
            $partner->update(['name' => trim($data['agency_name'])]);
            $userData = ['name' => trim($data['contact_name']), 'email' => mb_strtolower(trim($data['email']))];
            if (filled($data['password'] ?? null)) $userData['password'] = Hash::make($data['password']);
            $request->user()->update($userData);
            $partner->crmSubmission?->update([
                'agency_name' => trim($data['agency_name']), 'country' => $data['country'] ?? '', 'city' => $data['city'] ?? null,
                'website' => $data['website'] ?? null, 'contact_name' => trim($data['contact_name']),
                'job_title' => $data['job_title'] ?? null, 'mobile' => $data['mobile'] ?? '',
                'email' => mb_strtolower(trim($data['email'])),
            ]);
        });
        AuditLogger::log('updated', $partner, 'Partner updated own profile', [], ['name' => $partner->fresh()->name], $request);
        return back()->with('success', __('Profile updated successfully.'));
    }
}
