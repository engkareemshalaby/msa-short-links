<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExhibitionRegistrationRequest;
use App\Models\ExhibitionRegistration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExhibitionRegistrationController extends Controller
{
    public function create(): View
    {
        return view('crm.exhibition.create');
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
        $registrations = $this->filteredQuery($filters)
            ->latest()->paginate(20)->withQueryString();

        return view('crm.exhibition.index', compact('registrations'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $registrations = $this->filteredQuery($filters)->oldest()->get();
        $filename = 'jordan-exhibition-registrations-'.now()->format('Y-m-d-His').'.xlsx';

        return response()->streamDownload(function () use ($registrations): void {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Registrations');
            $headers = [
                'Reference', 'Submitted at', 'Status', 'Registrant', 'Student name', 'Student email',
                'Student mobile', 'Parent email', 'Parent mobile', 'Certificate type', 'Current result',
                'Interested faculties', 'Preferred contact', 'Relatives in Egypt', 'Accommodation in Egypt',
                'Exhibition location',
            ];
            $sheet->fromArray($headers, null, 'A1');

            $row = 2;
            foreach ($registrations as $registration) {
                $values = [
                    $registration->reference_code,
                    Date::dateTimeToExcel($registration->created_at),
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
                ];

                foreach ($values as $column => $value) {
                    $coordinate = Coordinate::stringFromColumnIndex($column + 1).$row;
                    if ($column === 1) {
                        $sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->setCellValueExplicit($coordinate, (string) ($value ?? ''), DataType::TYPE_STRING);
                    }
                }
                $row++;
            }

            $lastRow = max(1, $row - 1);
            $header = $sheet->getStyle('A1:P1');
            $header->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $header->getFill()->setFillType('solid')->getStartColor()->setARGB('FF072841');
            $header->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $header->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF538F3F');
            $sheet->getRowDimension(1)->setRowHeight(28);
            $sheet->freezePane('A2');
            $sheet->setAutoFilter("A1:P{$lastRow}");
            $sheet->setShowGridlines(false);
            $sheet->getStyle("A2:P{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle("B2:B{$lastRow}")->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm');
            $sheet->getStyle("A2:P{$lastRow}")->getFont()->setName('Arial')->setSize(10);
            $sheet->getStyle('A1:P1')->getFont()->setName('Arial')->setSize(10);

            foreach (range('A', 'P') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            foreach (['E', 'J', 'K', 'L'] as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(false)->setWidth($column === 'L' ? 34 : 24);
                $sheet->getStyle("{$column}2:{$column}{$lastRow}")->getAlignment()->setWrapText(true);
            }

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
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
            'certificate_type' => ['nullable', Rule::in(ExhibitionRegistration::CERTIFICATES)],
            'faculty' => ['nullable', Rule::in(ExhibitionRegistration::FACULTIES)],
            'preferred_contact_method' => ['nullable', Rule::in(['email', 'whatsapp'])],
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
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date));
    }
}
