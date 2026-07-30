<?php

namespace Database\Seeders;

use App\Models\Notifications\NotificationEvent;
use App\Models\Notifications\EmailTemplate;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'name' => 'otp_auth',
                'description' => 'MFA/OTP Authentication alerts and codes',
                'templates' => [
                    [
                        'name' => 'OTP Code Verification',
                        'subject' => 'Your Verification Code: {{code}}',
                        'body_html' => '<p>Your SkillUp authentication code is: <strong>{{code}}</strong>. This code is valid for 10 minutes.</p>',
                        'body_text' => 'Your SkillUp authentication code is: {{code}}. This code is valid for 10 minutes.',
                        'variables' => ['code' => 'Verification code'],
                    ]
                ]
            ],
            [
                'name' => 'security_alert',
                'description' => 'Password changes and security alerts',
                'templates' => [
                    [
                        'name' => 'Password Changed Alert',
                        'subject' => 'SkillUp Security Alert: Password Changed',
                        'body_html' => '<p>Hello {{name}},</p><p>Your password was recently changed. If this wasn\'t you, contact support immediately.</p>',
                        'body_text' => "Hello {{name}},\n\nYour password was recently changed. If this wasn't you, contact support immediately.",
                        'variables' => ['name' => 'User name'],
                    ]
                ]
            ],
            [
                'name' => 'payment_success',
                'description' => 'Dispatched upon successful order payment',
                'templates' => [
                    [
                        'name' => 'Payment Success Receipt',
                        'subject' => 'Payment Success: Order {{order_number}}',
                        'body_html' => '<p>Hello {{name}},</p><p>Thank you for your payment of {{amount}} for order {{order_number}}.</p>',
                        'body_text' => "Hello {{name}},\n\nThank you for your payment of {{amount}} for order {{order_number}}.",
                        'variables' => ['name' => 'User name', 'amount' => 'Paid amount', 'order_number' => 'Order number'],
                    ]
                ]
            ],
            [
                'name' => 'payment_failed',
                'description' => 'Dispatched upon failed payments',
                'templates' => [
                    [
                        'name' => 'Payment Failed Alert',
                        'subject' => 'Failed Payment Attempt: Order {{order_number}}',
                        'body_html' => '<p>Hello {{name}},</p><p>Your payment attempt of {{amount}} for order {{order_number}} failed.</p>',
                        'body_text' => "Hello {{name}},\n\nYour payment attempt of {{amount}} for order {{order_number}} failed.",
                        'variables' => ['name' => 'User name', 'amount' => 'Payment amount', 'order_number' => 'Order number'],
                    ]
                ]
            ],
            [
                'name' => 'installment_reminder',
                'description' => 'Installment due reminder',
                'templates' => [
                    [
                        'name' => 'Installment Due Reminder',
                        'subject' => 'Installment Due: Order {{order_number}}',
                        'body_html' => '<p>Hello,</p><p>Your installment of {{amount}} is due by {{due_date}} for order {{order_number}}.</p>',
                        'body_text' => "Hello,\n\nYour installment of {{amount}} is due by {{due_date}} for order {{order_number}}.",
                        'variables' => ['amount' => 'Due amount', 'due_date' => 'Due date', 'order_number' => 'Order number'],
                    ]
                ]
            ],
            [
                'name' => 'moodle_enrollment_success',
                'description' => 'LMS course access provisioning success',
                'templates' => [
                    [
                        'name' => 'Moodle Access Active',
                        'subject' => 'Your Course Access is Ready: {{course_name}}',
                        'body_html' => '<p>Hello {{name}},</p><p>You have been enrolled in {{course_name}}. You can access it on the LMS now.</p>',
                        'body_text' => "Hello {{name}},\n\nYou have been enrolled in {{course_name}}. You can access it on the LMS now.",
                        'variables' => ['name' => 'User name', 'course_name' => 'Course Name'],
                    ]
                ]
            ],
            [
                'name' => 'moodle_enrollment_failed',
                'description' => 'LMS course access provisioning failure notification',
                'templates' => [
                    [
                        'name' => 'Moodle Access Failure Alert',
                        'subject' => 'ALERT: LMS Access Sync Failed for {{name}}',
                        'body_html' => '<p>LMS provisioning failed for user {{name}} on course {{course_name}}. Error: {{error_message}}</p>',
                        'body_text' => "LMS provisioning failed for user {{name}} on course {{course_name}}. Error: {{error_message}}",
                        'variables' => ['name' => 'User name', 'course_name' => 'Course Name', 'error_message' => 'Error message'],
                    ]
                ]
            ],
            [
                'name' => 'lead_captured',
                'description' => 'Admin alert for marketing, contact, resource, and corporate lead capture',
                'templates' => [
                    [
                        'name' => 'New Lead Alert',
                        'subject' => 'New Lead Captured: {{lead_type}}',
                        'body_html' => '<p>A new lead has been captured on the SKILLUP platform.</p><p><strong>Name:</strong> {{name}}</p><p><strong>Email:</strong> {{email}}</p><p><strong>Phone:</strong> {{phone}}</p><p><strong>Source:</strong> {{lead_type}}</p><p><strong>Metadata:</strong><br>{{metadata}}</p>',
                        'body_text' => "A new lead has been captured on the SKILLUP platform.\n\nName: {{name}}\nEmail: {{email}}\nPhone: {{phone}}\nSource: {{lead_type}}\nMetadata: {{metadata}}",
                        'variables' => ['lead_type' => 'Lead source', 'name' => 'Lead name', 'email' => 'Lead email', 'phone' => 'Lead phone', 'metadata' => 'Lead metadata'],
                    ]
                ]
            ],
            [
                'name' => 'event_registration',
                'description' => 'Confirmation email after a learner registers for an event or webinar',
                'templates' => [
                    [
                        'name' => 'Event Registration Confirmation',
                        'subject' => 'Registration Confirmed: {{event_title}}',
                        'body_html' => '<p>Hello {{registrant_name}},</p><p>Your registration for <strong>{{event_title}}</strong> has been confirmed.</p><p><strong>Type:</strong> {{event_type}}</p><p><strong>Date and time:</strong> {{starts_at}} - {{ends_at}}</p><p>We look forward to seeing you there.</p>',
                        'body_text' => "Hello {{registrant_name}},\n\nYour registration for {{event_title}} has been confirmed.\nType: {{event_type}}\nDate and time: {{starts_at}} - {{ends_at}}\n\nWe look forward to seeing you there.",
                        'variables' => ['registrant_name' => 'Registrant name', 'event_title' => 'Event title', 'event_type' => 'Event type', 'starts_at' => 'Start time', 'ends_at' => 'End time'],
                    ]
                ]
            ]
        ];

        foreach ($events as $eventData) {
            $event = NotificationEvent::updateOrCreate(
                ['name' => $eventData['name']],
                ['description' => $eventData['description'], 'is_active' => true]
            );

            foreach ($eventData['templates'] as $tmpl) {
                EmailTemplate::updateOrCreate(
                    ['notification_event_id' => $event->id, 'name' => $tmpl['name']],
                    [
                        'subject' => $tmpl['subject'],
                        'body_html' => $tmpl['body_html'],
                        'body_text' => $tmpl['body_text'],
                        'variables' => $tmpl['variables'],
                    ]
                );
            }
        }
    }
}
