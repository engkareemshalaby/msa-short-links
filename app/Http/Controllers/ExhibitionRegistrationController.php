<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExhibitionRegistrationRequest;
use App\Models\ExhibitionRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
            $reference = 'JOR-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
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
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', Rule::in(ExhibitionRegistration::STATUSES)],
            'registrant_role' => ['nullable', Rule::in(['student', 'parent'])],
            'certificate_type' => ['nullable', Rule::in(ExhibitionRegistration::CERTIFICATES)],
            'faculty' => ['nullable', Rule::in(ExhibitionRegistration::FACULTIES)],
            'preferred_contact_method' => ['nullable', Rule::in(['email', 'whatsapp'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
        $registrations = ExhibitionRegistration::query()
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
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()->paginate(20)->withQueryString();

        return view('crm.exhibition.index', compact('registrations'));
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
}
