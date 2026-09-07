<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentReferralRequest;
use App\Models\RecruitmentPartner;
use App\Models\StudentReferral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicStudentReferralController extends Controller
{
    public function create(string $token): View
    {
        return view('referrals.public.create', ['partner' => $this->activePartner($token)]);
    }

    public function store(StoreStudentReferralRequest $request, string $token): RedirectResponse
    {
        $partner = $this->activePartner($token);
        $data = $request->validated();
        unset($data['passport'], $data['company_fax']);

        $referral = DB::transaction(function () use ($request, $partner, $data): StudentReferral {
            $passportPath = $request->file('passport')?->store('student-referrals/passports', 'local');

            return $partner->studentReferrals()->create(array_replace($data, [
                'reference_code' => $this->uniqueReferenceCode(),
                'passport_path' => $passportPath,
                'status' => 'new',
                'ip_hash' => $request->ip() ? hash_hmac('sha256', $request->ip(), config('app.key')) : null,
            ]));
        });

        return redirect()->route('student-referrals.thank-you', $partner->access_token)
            ->with('referral_reference', $referral->reference_code);
    }

    public function thankYou(string $token): View|RedirectResponse
    {
        $partner = $this->activePartner($token);
        $reference = session('referral_reference');

        if (! $reference) {
            return redirect()->route('student-referrals.create', $partner->access_token);
        }

        return view('referrals.public.thank-you', compact('partner', 'reference'));
    }

    private function activePartner(string $token): RecruitmentPartner
    {
        return RecruitmentPartner::query()->where('access_token', $token)->where('is_active', true)->firstOrFail();
    }

    private function uniqueReferenceCode(): string
    {
        do {
            $code = 'MSA-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (StudentReferral::query()->where('reference_code', $code)->exists());

        return $code;
    }
}
