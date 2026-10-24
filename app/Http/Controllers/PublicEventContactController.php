<?php

namespace App\Http\Controllers;

use App\Mail\IraqEventContactConfirmation;
use App\Models\EventContact;
use App\Models\EventContactStage;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PublicEventContactController extends Controller
{
    private const EVENT_NAME = 'Iraq Exhibition';

    public function create(): View
    {
        return view('crm.event-contacts.public-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^\+?[0-9][0-9\s().-]{5,38}$/'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
            'company_fax' => ['nullable', 'max:0'],
        ]);

        $eventName = self::EVENT_NAME;
        $email = $data['email'];
        if (! EventContact::query()->where('event_name', $eventName)->where('primary_email', $email)->exists()) {
            $phone = filled($data['phone'] ?? null) ? trim($data['phone']) : null;
            $contact = EventContact::create([
                'import_key' => 'public:'.Str::uuid(),
                'name' => trim($data['name']),
                'organization_name' => filled($data['organization_name'] ?? null) ? trim($data['organization_name']) : null,
                'job_title' => filled($data['job_title'] ?? null) ? trim($data['job_title']) : null,
                'notes' => filled($data['notes'] ?? null) ? trim($data['notes']) : null,
                'primary_email' => $email,
                'emails' => [$email],
                'primary_phone' => $phone,
                'phones' => $phone ? [$phone] : [],
                'event_name' => $eventName,
                'source' => 'self_registration',
                'status' => 'new',
                'event_contact_stage_id' => EventContactStage::query()->where('name', 'Awareness')->value('id'),
                'raw_data' => ['source' => 'self_registration', 'consent' => true],
            ]);
            AuditLogger::log('created', $contact, 'Created event contact through public registration', [],
                $contact->only(['name', 'primary_email', 'event_name', 'source']), $request);
            try {
                Mail::to($email)->send(new IraqEventContactConfirmation($contact->name, app()->getLocale()));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->route('crm.event-contacts.register.thank-you');
    }

    public function thankYou(): View
    {
        return view('crm.event-contacts.public-thank-you');
    }
}
