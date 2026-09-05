<?php

namespace App\Http\Controllers;

use App\Models\CrmSubmission;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CrmSubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', Rule::in(CrmSubmission::STATUSES)],
        ]);

        $submissions = CrmSubmission::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('agency_name', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('crm.submissions.index', [
            'submissions' => $submissions,
            'counts' => CrmSubmission::query()->selectRaw('status, COUNT(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status'),
        ]);
    }

    public function show(CrmSubmission $submission): View
    {
        if ($submission->status === 'new') {
            $submission->update(['status' => 'reviewed', 'reviewed_at' => now()]);
        }

        return view('crm.submissions.show', compact('submission'));
    }

    public function update(Request $request, CrmSubmission $submission): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(CrmSubmission::STATUSES)]]);
        $oldStatus = $submission->status;
        $submission->update([
            'status' => $data['status'],
            'reviewed_at' => $data['status'] === 'new' ? null : ($submission->reviewed_at ?? now()),
        ]);
        AuditLogger::log('status_changed', $submission, 'Updated partner application status', ['status' => $oldStatus], ['status' => $data['status']], $request);

        return back()->with('success', __('Application status updated.'));
    }

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Submitted at', 'Status', 'Agency', 'Country', 'City', 'Contact', 'Job title', 'Mobile', 'Email', 'Recruitment countries', 'Annual students', 'Works with Egyptian universities', 'Universities', 'Expected MSA students', 'Programs', 'Commission type', 'Commission value', 'Commission basis', 'Exclusive discount %', 'Notes']);

            CrmSubmission::query()->latest()->chunk(500, function ($submissions) use ($output): void {
                foreach ($submissions as $submission) {
                    fputcsv($output, [
                        $submission->created_at->toDateTimeString(), $submission->status, $submission->agency_name,
                        $submission->country, $submission->city, $submission->contact_name, $submission->job_title,
                        $submission->mobile, $submission->email, implode(', ', $submission->recruitment_countries),
                        $submission->annual_students_range, $submission->works_with_egyptian_universities ? 'Yes' : 'No',
                        $submission->current_universities, $submission->expected_msa_students_range,
                        implode(', ', $submission->interested_programs), $submission->commission_type,
                        $submission->commission_value, $submission->commission_basis, $submission->exclusive_discount_percent,
                        $submission->notes,
                    ]);
                }
            });
            fclose($output);
        }, 'partner-applications-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
