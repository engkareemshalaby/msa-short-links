<?php

namespace App\Http\Controllers;

use App\Models\CrmSubmission;
use App\Models\RecruitmentPartner;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RecruitmentPartnerController extends Controller
{
    public function index(): View
    {
        return view('referrals.partners.index', [
            'partners' => RecruitmentPartner::query()->with(['user', 'crmSubmission'])->withCount('studentReferrals')->latest()->paginate(20),
            'pendingSubmissions' => CrmSubmission::query()
                ->doesntHave('recruitmentPartner')
                ->latest()
                ->get(),
        ]);
    }

    public function store(CrmSubmission $submission, Request $request): RedirectResponse
    {
        abort_if($submission->recruitmentPartner()->exists(), 422, __('This application already has a partner account.'));
        abort_if(User::query()->where('email', mb_strtolower(trim($submission->email)))->exists(), 422, __('This email address is already used by another account.'));

        $data = $request->validate([
            'password' => [blank($submission->password) ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $password = filled($data['password'] ?? null) ? Hash::make($data['password']) : $submission->password;

        $partner = DB::transaction(function () use ($password, $submission): RecruitmentPartner {
            $user = User::create([
                'name' => trim($submission->contact_name),
                'email' => mb_strtolower(trim($submission->email)),
                'password' => $password,
            ]);
            $user->assignRole(Role::firstOrCreate(['name' => 'Partner', 'guard_name' => 'web']));

            $partner = RecruitmentPartner::create([
                'user_id' => $user->id,
                'crm_submission_id' => $submission->id,
                'name' => trim($submission->agency_name),
                'code' => $this->uniqueCode($submission->agency_name),
                'access_token' => Str::random(48),
                'is_active' => true,
            ]);

            $submission->update(['status' => 'reviewed', 'reviewed_at' => $submission->reviewed_at ?? now()]);

            return $partner;
        });
        AuditLogger::log('created', $partner, 'Created recruitment partner account from application', [], ['name' => $partner->name, 'email' => $submission->email], $request);

        return back()->with('success', __('Partner account created successfully.'));
    }

    public function edit(RecruitmentPartner $partner): View
    {
        $partner->load(['user', 'crmSubmission']);

        return view('referrals.partners.edit', compact('partner'));
    }

    public function update(Request $request, RecruitmentPartner $partner): RedirectResponse
    {
        $partner->load(['user', 'crmSubmission']);
        $countries = ['Egypt', 'Saudi Arabia', 'United Arab Emirates', 'Qatar', 'Kuwait', 'Jordan', 'Oman', 'Bahrain', 'Iraq', 'Palestine', 'Lebanon', 'Syria', 'Libya', 'Sudan', 'Yemen', 'Algeria', 'Morocco', 'Tunisia'];
        $programs = ['Dentistry', 'Pharmacy', 'Biotechnology', 'Engineering', 'Computer Science', 'Arts & Design', 'Management Sciences', 'Languages', 'Other'];

        $data = $request->validate([
            'agency_name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', Rule::in($countries)],
            'city' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'url:http,https', 'max:500'],
            'contact_name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($partner->user_id)],
            'recruitment_countries' => ['nullable', 'array'],
            'recruitment_countries.*' => ['string', Rule::in($countries)],
            'annual_students_range' => ['nullable', 'string', 'max:50'],
            'works_with_egyptian_universities' => ['nullable', 'boolean'],
            'current_universities' => ['nullable', 'string', 'max:3000'],
            'expected_msa_students_range' => ['nullable', 'string', 'max:50'],
            'interested_programs' => ['nullable', 'array'],
            'interested_programs.*' => ['string', Rule::in($programs)],
            'notes' => ['nullable', 'string', 'max:5000'],
            'commission_type' => ['nullable', Rule::in(['percentage'])],
            'commission_value' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'commission_basis' => ['nullable', Rule::in(['installment', 'academic_year'])],
            'exclusive_discount_percent' => ['nullable', 'numeric', 'between:0,30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($data, $partner): void {
            $partner->update(['name' => trim($data['agency_name'])]);

            $userData = [
                'name' => trim($data['contact_name']),
                'email' => mb_strtolower(trim($data['email'])),
            ];

            if (filled($data['password'] ?? null)) {
                $userData['password'] = Hash::make($data['password']);
            }

            if ($partner->user) {
                $partner->user->update($userData);
            } elseif (filled($data['password'] ?? null)) {
                $user = User::create($userData);
                $user->assignRole(Role::firstOrCreate(['name' => 'Partner', 'guard_name' => 'web']));
                $partner->update(['user_id' => $user->id]);
            }

            if ($partner->crmSubmission) {
                $recruitmentCountries = collect($data['recruitment_countries'] ?? [])->unique()->values()->all();

                $partner->crmSubmission->update([
                    'agency_name' => trim($data['agency_name']),
                    'country' => trim((string) ($data['country'] ?? '')),
                    'city' => $data['city'] ?? null,
                    'website' => $data['website'] ?? null,
                    'contact_name' => trim($data['contact_name']),
                    'job_title' => $data['job_title'] ?? null,
                    'mobile' => $data['mobile'] ?? '',
                    'email' => mb_strtolower(trim($data['email'])),
                    'recruitment_countries' => $recruitmentCountries,
                    'annual_students_range' => $data['annual_students_range'] ?? '',
                    'works_with_egyptian_universities' => (bool) ($data['works_with_egyptian_universities'] ?? false),
                    'current_universities' => $data['current_universities'] ?? null,
                    'expected_msa_students_range' => $data['expected_msa_students_range'] ?? '',
                    'interested_programs' => $data['interested_programs'] ?? [],
                    'notes' => $data['notes'] ?? null,
                    'commission_type' => 'percentage',
                    'commission_value' => $data['commission_value'] ?? 0,
                    'commission_basis' => $data['commission_basis'] ?? null,
                    'exclusive_discount_percent' => $data['exclusive_discount_percent'] ?? 0,
                ]);
            }
        });

        AuditLogger::log('updated', $partner, 'Updated recruitment partner account', [], ['name' => $partner->fresh()->name], $request);

        return redirect()->route('crm.partners.index')->with('success', __('Partner account updated successfully.'));
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
