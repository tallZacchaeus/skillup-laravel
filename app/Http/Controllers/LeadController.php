<?php

namespace App\Http\Controllers;

use App\Models\Content\Lead;
use App\Models\Operations\FormSubmission;
use App\Notifications\NewLeadNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class LeadController extends Controller
{
    public function storeNewsletter(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $lead = Lead::create([
            'email' => $validated['email'],
            'type' => 'newsletter',
        ]);

        $this->recordFormSubmission($request, $lead, 'newsletter', [
            'email' => $validated['email'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for subscribing to our newsletter!',
        ]);
    }

    public function storeContact(Request $request)
    {
        // Honeypot: silently drop bot submissions without storing or signalling.
        if (filled($request->input('website'))) {
            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent successfully. We will get back to you shortly!',
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string|max:2000',
            'enquiry_type' => 'nullable|string|max:100',
            'subject' => 'nullable|string|max:150',
        ]);

        $lead = Lead::create([
            'email' => $validated['email'],
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'type' => 'contact_page',
            'metadata' => [
                'message' => $validated['message'],
                'enquiry_type' => $validated['enquiry_type'] ?? null,
                'subject' => $validated['subject'] ?? null,
            ],
        ]);

        $this->recordFormSubmission($request, $lead, 'contact_page', [
            ...$validated,
            'subject' => ($validated['subject'] ?? null) ?: ($validated['enquiry_type'] ?? null) ?: 'Contact page message',
        ]);

        $this->notifyAdmin($lead);

        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent successfully. We will get back to you shortly!',
        ]);
    }

    public function storeCorporate(Request $request)
    {
        // Honeypot: bots fill hidden fields humans never see. Pretend success so
        // we neither store spam nor signal detection back to the bot.
        if (filled($request->input('website'))) {
            return response()->json([
                'success' => true,
                'message' => 'Your corporate inquiry has been submitted. Our team will review your details and be in touch.',
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company_name' => 'required|string|max:255',
            'employee_count' => 'nullable|string|max:50',
            'message' => 'required|string|max:2000',
            // Curated optional fields — captured in metadata when supplied.
            'job_title' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:50',
            'preferred_track' => 'nullable|string|max:150',
            'start_timeframe' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $lead = Lead::create([
            'email' => $validated['email'],
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'type' => 'corporate_inquiry',
            'metadata' => [
                'company_name' => $validated['company_name'],
                'employee_count' => $validated['employee_count'] ?? null,
                'message' => $validated['message'],
                'job_title' => $validated['job_title'] ?? null,
                'preferred_track' => $validated['preferred_track'] ?? null,
                'start_timeframe' => $validated['start_timeframe'] ?? null,
                'country' => $validated['country'] ?? null,
            ],
        ]);

        $this->recordFormSubmission($request, $lead, 'corporate_inquiry', [
            ...$validated,
            'subject' => 'Corporate training inquiry',
        ]);

        $this->notifyAdmin($lead);

        return response()->json([
            'success' => true,
            'message' => 'Your corporate inquiry has been submitted. Our team will contact you soon!',
        ]);
    }

    protected function notifyAdmin(Lead $lead)
    {
        $adminEmail = config('mail.admin_notification_email') 
            ?? env('ADMIN_NOTIFICATION_EMAIL') 
            ?? config('mail.from.address');

        if ($adminEmail) {
            try {
                Notification::route('mail', $adminEmail)
                    ->notify(new NewLeadNotification($lead));
            } catch (\Exception $e) {
                logger()->error('Failed to send admin lead notification email: ' . $e->getMessage());
            }
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function recordFormSubmission(Request $request, Lead $lead, string $formKey, array $payload): void
    {
        FormSubmission::create([
            'user_id' => $request->user()?->id,
            'lead_id' => $lead->id,
            'form_key' => $formKey,
            'source_url' => $request->headers->get('referer'),
            'name' => $payload['name'] ?? null,
            'email' => $payload['email'] ?? $lead->email,
            'phone' => $payload['phone'] ?? null,
            'subject' => $payload['subject'] ?? null,
            'message' => $payload['message'] ?? null,
            'payload' => $payload,
        ]);
    }
}
