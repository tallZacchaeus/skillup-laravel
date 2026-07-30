<?php

namespace App\Notifications;

use App\Models\Content\Lead;
use App\Notifications\Channels\Phase9EmailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewLeadNotification extends Notification
{
    use Queueable;

    public Lead $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    public function via(object $notifiable): array
    {
        return [Phase9EmailChannel::class];
    }

    public function toPhase9Email(object $notifiable): array
    {
        $type = Str::headline($this->lead->type);
        $metadata = $this->metadataSummary();

        return [
            'template_name' => 'New Lead Alert',
            'subject' => 'New Marketing Lead Capture: ' . $type,
            'body_html' => '<p>A new lead has been captured on the SKILLUP platform.</p>'
                . '<p><strong>Name:</strong> ' . e($this->lead->name ?? 'N/A') . '</p>'
                . '<p><strong>Email:</strong> ' . e($this->lead->email) . '</p>'
                . '<p><strong>Phone:</strong> ' . e($this->lead->phone ?? 'N/A') . '</p>'
                . '<p><strong>Source:</strong> ' . e($type) . '</p>'
                . ($metadata ? '<p><strong>Metadata:</strong><br>' . $metadata . '</p>' : ''),
            'variables' => [
                'lead_type' => e($type),
                'name' => e($this->lead->name ?? 'N/A'),
                'email' => e($this->lead->email),
                'phone' => e($this->lead->phone ?? 'N/A'),
                'metadata' => $metadata ?: 'N/A',
            ],
        ];
    }

    private function metadataSummary(): string
    {
        if (empty($this->lead->metadata)) {
            return '';
        }

        return collect($this->lead->metadata)
            ->map(function ($value, string $key): string {
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_SLASHES);
                }

                return '<strong>' . e(Str::headline($key)) . ':</strong> ' . e((string) $value);
            })
            ->implode('<br>');
    }
}
