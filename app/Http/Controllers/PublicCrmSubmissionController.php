<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCrmSubmissionRequest;
use App\Models\CrmSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicCrmSubmissionController extends Controller
{
    public function create(): View
    {
        return view('crm.public.create');
    }

    public function store(StoreCrmSubmissionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['company_fax']);
        $data['commission_basis'] = $data['commission_type'] === 'percentage' ? $data['commission_basis'] : null;
        $data['current_universities'] = $data['works_with_egyptian_universities'] ? ($data['current_universities'] ?? null) : null;

        $countries = collect(preg_split('/[,،\n]+/u', $data['recruitment_countries']))
            ->map(fn (string $country) => trim($country))->filter()->unique()->values()->all();

        $existing = CrmSubmission::query()
            ->where('email', $data['email'])
            ->whereRaw('LOWER(agency_name) = ?', [mb_strtolower(trim($data['agency_name']))])
            ->first();

        if ($existing) {
            return redirect()->route('crm.thank-you');
        }

        CrmSubmission::create(array_replace($data, [
            'recruitment_countries' => $countries,
            'ip_hash' => $request->ip() ? hash_hmac('sha256', $request->ip(), config('app.key')) : null,
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]));

        return redirect()->route('crm.thank-you');
    }

    public function thankYou(): View
    {
        return view('crm.public.thank-you');
    }
}
