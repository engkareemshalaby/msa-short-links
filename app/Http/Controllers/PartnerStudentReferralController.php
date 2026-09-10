<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentReferralRequest;
use App\Models\RecruitmentPartner;
use App\Models\StudentReferral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Services\AuditLogger;

class PartnerStudentReferralController extends Controller
{
    public function index(Request $request): View
    {
        $partner = $this->partner($request);

        return view('partner.referrals.index', [
            'partner' => $partner,
            'referrals' => $partner->studentReferrals()->latest()->paginate(20),
        ]);
    }

    public function create(Request $request): View
    {
        return view('partner.referrals.form', ['partner' => $this->partner($request), 'referral' => new StudentReferral]);
    }

    public function store(StoreStudentReferralRequest $request): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $request->validated();
        unset($data['passport'], $data['company_fax']);

        $referral = DB::transaction(function () use ($request, $partner, $data): StudentReferral {
            return $partner->studentReferrals()->create(array_replace($data, [
                'reference_code' => $this->uniqueReferenceCode(),
                'passport_path' => $request->file('passport')?->store('student-referrals/passports', 'local'),
                'status' => 'new',
                'ip_hash' => $request->ip() ? hash_hmac('sha256', $request->ip(), config('app.key')) : null,
            ]));
        });

        return redirect()->route('partner.referrals.index')->with('success', __('Student registered successfully. Reference: :reference', ['reference' => $referral->reference_code]));
    }

    public function edit(Request $request, StudentReferral $referral): View
    {
        $this->editableReferral($request, $referral);

        return view('partner.referrals.form', ['partner' => $this->partner($request), 'referral' => $referral]);
    }

    public function update(StoreStudentReferralRequest $request, StudentReferral $referral): RedirectResponse
    {
        $this->editableReferral($request, $referral);
        $data = $request->validated();
        unset($data['passport'], $data['company_fax']);

        if ($request->hasFile('passport')) {
            $data['passport_path'] = $request->file('passport')->store('student-referrals/passports', 'local');
        }

        $referral->update($data);

        return redirect()->route('partner.referrals.index')->with('success', __('Student details updated.'));
    }

    public function updateStudyInEgypt(Request $request, StudentReferral $referral): RedirectResponse
    {
        $partner = $this->partner($request);
        abort_unless($referral->recruitment_partner_id === $partner->id, 404);
        $request->validate(['status' => ['required', 'in:applied_on_study_in_egypt']]);
        $old = $referral->status;
        $referral->update([
            'status' => 'applied_on_study_in_egypt',
            'study_in_egypt_applied' => true,
            'study_in_egypt_updated_by' => $request->user()->id,
            'study_in_egypt_updated_at' => now(),
        ]);
        AuditLogger::log('status_changed', $referral, 'Updated student referral status', ['status' => $old], ['status' => 'applied_on_study_in_egypt'], $request);

        return back()->with('success', __('Study in Egypt status updated.'));
    }

    private function partner(Request $request): RecruitmentPartner
    {
        $partner = $request->user()->recruitmentPartner;
        abort_unless($partner?->is_active, 403);

        return $partner;
    }

    private function editableReferral(Request $request, StudentReferral $referral): void
    {
        $partner = $this->partner($request);
        abort_unless($referral->recruitment_partner_id === $partner->id, 404);
        abort_unless(in_array($referral->status, ['new', 'incomplete'], true), 403);
    }

    private function uniqueReferenceCode(): string
    {
        do {
            $code = 'MSA-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (StudentReferral::query()->where('reference_code', $code)->exists());

        return $code;
    }
}
