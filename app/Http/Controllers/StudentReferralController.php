<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentPartner;
use App\Models\StudentReferral;
use App\Services\AuditLogger;
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

        $referrals = StudentReferral::query()->with('partner')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('student_name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
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

    public function update(Request $request, StudentReferral $referral): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(StudentReferral::STATUSES)]]);
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
