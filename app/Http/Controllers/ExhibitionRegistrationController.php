<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExhibitionRegistrationRequest;
use App\Mail\IraqExhibitionParentWelcome;
use App\Mail\IraqExhibitionWelcome;
use App\Models\ExhibitionRegistration;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExhibitionRegistrationController extends Controller
{
    public function create(): View
    {
        return view('crm.exhibition.create');
    }

    public function createGeneral(): View
    {
        return view('crm.exhibition.create', ['general' => true]);
    }

    public function storeGeneral(StoreExhibitionRegistrationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if ($data['registration_country'] === 'Other') {
            $data['registration_country'] = $data['registration_country_other'];
        }
        unset($data['consent'], $data['company_fax'], $data['certificate_category'], $data['registration_country_other']);

        do {
            $reference = 'GEN-'.random_int(100000, 999999);
        } while (ExhibitionRegistration::where('reference_code', $reference)->exists());

        ExhibitionRegistration::create(array_replace($data, [
            'reference_code' => $reference,
            'exhibition_location' => 'General',
            'preferred_contact_method' => 'email',
            'status' => 'new',
            'ip_hash' => $request->ip() ? hash('sha256', $request->ip().config('app.key')) : null,
        ]));

        return redirect()->route('crm.general.thank-you')->with('registration_reference', $reference);
    }

    public function thankYouGeneral(): View
    {
        return view('crm.exhibition.thank-you', ['general' => true]);
    }

    public function createIraq(): View
    {
        if (! session()->has('iraq_locale_initialized')) {
            session(['iraq_locale_initialized' => true, 'locale' => 'en']);
            app()->setLocale('en');
        }

        return view('crm.exhibition.iraq-create');
    }

    public function storeIraq(StoreExhibitionRegistrationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['consent'], $data['company_fax']);
        do {
            $reference = 'IRQ-'.random_int(100000, 999999);
        } while (ExhibitionRegistration::where('reference_code', $reference)->exists());
        $emailField = 'student_email';
        try {
            Mail::to($data['student_email'])->send(new IraqExhibitionWelcome(
                $data['student_name'], $reference, app()->getLocale()
            ));
            if (! empty($data['parent_email'])) {
                $emailField = 'parent_email';
                Mail::to($data['parent_email'])->send(new IraqExhibitionParentWelcome(
                    $data['student_name'], $reference, app()->getLocale()
                ));
            }
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                $emailField => __('Email sending failed:').' '.$exception->getMessage(),
            ])->withInput();
        }

        ExhibitionRegistration::create(array_replace($data, [
            'reference_code' => $reference,
            'exhibition_location' => 'Iraq (Baghdad)',
            'preferred_contact_method' => 'email',
            'status' => 'new',
            'ip_hash' => $request->ip() ? hash('sha256', $request->ip().config('app.key')) : null,
        ]));

        return redirect()->route('crm.iraq.thank-you')->with('registration_reference', $reference);
    }

    public function thankYouIraq(): View
    {
        if (! session()->has('locale')) {
            app()->setLocale('en');
        }

        return view('crm.exhibition.iraq-thank-you');
    }

    public function store(StoreExhibitionRegistrationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['consent'], $data['company_fax']);

        do {
            $reference = 'JOR-'.random_int(100000, 999999);
        } while (ExhibitionRegistration::where('reference_code', $reference)->exists());

        ExhibitionRegistration::create(array_replace($data, [
            'reference_code' => $reference,
            'exhibition_location' => 'Jordan',
            'status' => 'new',
            'ip_hash' => $request->ip() ? hash('sha256', $request->ip().config('app.key')) : null,
        ]));

        return redirect()->route('crm.jordan.thank-you')->with('registration_reference', $reference);
    }

    public function thankYou(): View
    {
        return view('crm.exhibition.thank-you');
    }

    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $exhibitionLocations = ExhibitionRegistration::query()->distinct()->orderBy('exhibition_location')->pluck('exhibition_location');
        $registrationCountries = ExhibitionRegistration::query()->whereNotNull('registration_country')->distinct()->orderBy('registration_country')->pluck('registration_country');
        $registrations = $this->filteredQuery($filters)
            ->latest()->paginate(20)->withQueryString();

        return view('crm.exhibition.index', compact('registrations', 'exhibitionLocations', 'registrationCountries'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $filename = 'exhibition-registrations-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Unable to open the export stream.');
            }

            // Excel recognizes the UTF-8 BOM, including Arabic names and locations.
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Reference', 'Submitted at', 'Status', 'Registrant', 'Student name', 'Student email',
                'Student mobile', 'Parent email', 'Parent mobile', 'Certificate type', 'Current result',
                'Interested faculties', 'Preferred contact', 'Relatives in Egypt', 'Accommodation in Egypt',
                'Exhibition location', 'Registration country', 'Full tuition scholarship eligibility',
            ], escape: '');

            $this->filteredQuery($filters)->chunkById(500, function ($registrations) use ($output): void {
                foreach ($registrations as $registration) {
                    $cells = [
                        $registration->reference_code,
                        $registration->created_at?->format('Y-m-d H:i:s'),
                        ucfirst($registration->status),
                        ucfirst($registration->registrant_role),
                        $registration->student_name,
                        $registration->student_email,
                        $registration->student_mobile,
                        $registration->parent_email,
                        $registration->parent_mobile,
                        $registration->certificate_type === 'Other' ? $registration->certificate_type_other : $registration->certificate_type,
                        $registration->current_result,
                        implode(', ', $registration->interested_faculties ?? []),
                        ucfirst($registration->preferred_contact_method),
                        is_null($registration->has_relatives_or_acquaintances_in_egypt) ? 'Not specified' : ($registration->has_relatives_or_acquaintances_in_egypt ? 'Yes' : 'No'),
                        is_null($registration->has_accommodation_in_egypt) ? 'Not specified' : ($registration->has_accommodation_in_egypt ? 'Yes' : 'No'),
                        $registration->exhibition_location,
                        $registration->registration_country,
                        ucfirst($registration->full_scholarship_eligibility ?? ''),
                    ];

                    fputcsv($output, array_map(self::excelSafeCell(...), $cells), escape: '');
                }
                fflush($output);
            });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private static function excelSafeCell(mixed $value): string
    {
        $text = (string) ($value ?? '');

        // Spreadsheet apps can execute CSV values starting with these characters as formulas.
        return preg_match('/^[\s]*[=+\-@]/u', $text) ? "'".$text : $text;
    }

    public function analytics(Request $request): View
    {
        $days = in_array($request->integer('days', 30), [7, 30, 90, 365], true) ? $request->integer('days', 30) : 30;
        $start = now()->subDays($days - 1)->startOfDay();
        $query = ExhibitionRegistration::where('created_at', '>=', $start);

        $dailyRaw = (clone $query)->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');
        $daily = collect(range(0, $days - 1))->map(function ($offset) use ($start, $dailyRaw) {
            $date = $start->copy()->addDays($offset);

            return ['day' => $date->format('M d'), 'total' => (int) ($dailyRaw[$date->toDateString()]->total ?? 0)];
        });
        $breakdown = fn (string $column) => (clone $query)->select($column, DB::raw('COUNT(*) as total'))
            ->groupBy($column)->orderByDesc('total')->get();
        $allFaculties = (clone $query)->pluck('interested_faculties')->flatten()->countBy()->sortDesc();

        return view('crm.exhibition.analytics', [
            'days' => $days,
            'total' => (clone $query)->count(),
            'newCount' => (clone $query)->where('status', 'new')->count(),
            'contactedCount' => (clone $query)->where('status', 'contacted')->count(),
            'appliedCount' => (clone $query)->where('status', 'applied')->count(),
            'daily' => $daily,
            'statuses' => $breakdown('status'),
            'certificates' => $breakdown('certificate_type'),
            'contacts' => $breakdown('preferred_contact_method'),
            'roles' => $breakdown('registrant_role'),
            'faculties' => $allFaculties,
        ]);
    }

    public function update(Request $request, ExhibitionRegistration $registration): RedirectResponse
    {
        $registration->update($request->validate(['status' => ['required', Rule::in(ExhibitionRegistration::STATUSES)]]));

        return back()->with('success', __('Registration status updated.'));
    }

    public function destroy(Request $request, ExhibitionRegistration $registration): RedirectResponse
    {
        AuditLogger::log('deleted', $registration, 'Deleted exhibition registration', [
            'reference_code' => $registration->reference_code,
            'student_name' => $registration->student_name,
            'student_email' => $registration->student_email,
        ], [], $request);
        $registration->delete();

        return back()->with('success', __('Registration deleted successfully.'));
    }

    public function show(ExhibitionRegistration $registration): View
    {
        return view('crm.exhibition.show', compact('registration'));
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', Rule::in(ExhibitionRegistration::STATUSES)],
            'registrant_role' => ['nullable', Rule::in(['student', 'parent'])],
            'certificate_type' => ['nullable', Rule::in(array_unique(array_merge(ExhibitionRegistration::CERTIFICATES, ExhibitionRegistration::IRAQ_CERTIFICATES)))],
            'faculty' => ['nullable', Rule::in(ExhibitionRegistration::FACULTIES)],
            'preferred_contact_method' => ['nullable', Rule::in(['email', 'whatsapp'])],
            'exhibition_location' => ['nullable', 'string', 'max:255'],
            'registration_country' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
    }

    private function filteredQuery(array $filters): Builder
    {
        return ExhibitionRegistration::query()
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('student_name', 'like', "%{$search}%")
                ->orWhere('student_email', 'like', "%{$search}%")
                ->orWhere('student_mobile', 'like', "%{$search}%")
                ->orWhere('reference_code', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['registrant_role'] ?? null, fn ($query, $role) => $query->where('registrant_role', $role))
            ->when($filters['certificate_type'] ?? null, fn ($query, $certificate) => $query->where('certificate_type', $certificate))
            ->when($filters['faculty'] ?? null, fn ($query, $faculty) => $query->whereJsonContains('interested_faculties', $faculty))
            ->when($filters['preferred_contact_method'] ?? null, fn ($query, $method) => $query->where('preferred_contact_method', $method))
            ->when($filters['exhibition_location'] ?? null, fn ($query, $location) => $query->where('exhibition_location', $location))
            ->when($filters['registration_country'] ?? null, fn ($query, $country) => $query->where('registration_country', $country))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date));
    }
}
