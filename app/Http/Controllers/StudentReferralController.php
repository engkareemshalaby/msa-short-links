<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentPartner;
use App\Models\StudentReferral;
use App\Services\AuditLogger;
use App\Http\Requests\StoreStudentReferralRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentReferralController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', Rule::in(StudentReferral::STATUSES)],
            'partner' => ['nullable', 'integer', 'exists:recruitment_partners,id'],
        ]);

        $referrals = StudentReferral::query()->with(['partner', 'studyInEgyptUpdatedBy'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('student_name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('reference_code', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['partner'] ?? null, fn ($query, int $partner) => $query->where('recruitment_partner_id', $partner))
            ->latest()->paginate(20)->withQueryString();

        return view('referrals.index', [
            'referrals' => $referrals,
            'partners' => RecruitmentPartner::query()->orderBy('name')->get(),
            'counts' => StudentReferral::query()->selectRaw('status, COUNT(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('referrals.form', [
            'referral' => new StudentReferral,
            'partners' => RecruitmentPartner::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreStudentReferralRequest $request): RedirectResponse
    {
        $extra = $request->validate([
            'recruitment_partner_id' => ['required', 'exists:recruitment_partners,id'],
            'study_in_egypt_applied' => ['nullable', 'boolean'],
        ]);
        $data = $request->validated();
        unset($data['passport'], $data['company_fax']);
        $referral = DB::transaction(function () use ($request, $extra, $data): StudentReferral {
            do {
                $reference = 'MSA-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
            } while (StudentReferral::query()->where('reference_code', $reference)->exists());
            return StudentReferral::create(array_replace($data, [
                'recruitment_partner_id' => $extra['recruitment_partner_id'],
                'reference_code' => $reference,
                'passport_path' => $request->file('passport')?->store('student-referrals/passports', 'local'),
                'status' => 'new',
                'study_in_egypt_applied' => $extra['study_in_egypt_applied'] ?? null,
                'study_in_egypt_updated_by' => isset($extra['study_in_egypt_applied']) ? $request->user()->id : null,
                'study_in_egypt_updated_at' => isset($extra['study_in_egypt_applied']) ? now() : null,
            ]));
        });
        AuditLogger::log('created', $referral, 'Created student referral by admin', [], ['partner_id' => $referral->recruitment_partner_id], $request);
        return redirect()->route('crm.student-referrals.index')->with('success', __('Student registered successfully. Reference: :reference', ['reference' => $referral->reference_code]));
    }

    public function update(Request $request, StudentReferral $referral): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', 'required', Rule::in(StudentReferral::STATUSES)],
            'study_in_egypt_applied' => ['sometimes', 'required', 'boolean'],
        ]);
        if (array_key_exists('study_in_egypt_applied', $data)) {
            $data['study_in_egypt_updated_by'] = $request->user()->id;
            $data['study_in_egypt_updated_at'] = now();
        }
        if (($data['status'] ?? null) === 'applied_on_study_in_egypt') {
            $data['study_in_egypt_applied'] = true;
            $data['study_in_egypt_updated_by'] = $request->user()->id;
            $data['study_in_egypt_updated_at'] = now();
        }
        $oldStatus = $referral->status;
        $referral->update($data);
        AuditLogger::log('status_changed', $referral, 'Updated student referral status', ['status' => $oldStatus], $data, $request);

        return back()->with('success', __('Student status updated.'));
    }

    public function passport(StudentReferral $referral): StreamedResponse
    {
        abort_unless($referral->passport_path && Storage::disk('local')->exists($referral->passport_path), 404);

        return Storage::disk('local')->download($referral->passport_path);
    }
}
