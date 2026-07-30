<?php

namespace App\Services\Operations;

use App\Models\Operations\ExportRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExportService
{
    /**
     * @return array<string, string>
     */
    public static function exportTypeOptions(): array
    {
        return [
            'revenue' => 'Revenue',
            'payments' => 'Payments',
            'enrollments' => 'Enrollments',
            'discounts' => 'Discounts',
            'leads' => 'Leads',
            'form_submissions' => 'Form Submissions',
            'support_tickets' => 'Support Tickets',
            'email_messages' => 'Email Messages',
            'whatsapp_messages' => 'WhatsApp Messages',
        ];
    }

    public function generate(ExportRequest $request): ExportRequest
    {
        $request->forceFill([
            'status' => 'processing',
            'error_message' => null,
        ])->save();

        try {
            $rows = $this->rowsFor($request->export_type);
            $path = 'exports/'.$request->uuid.'-'.$request->export_type.'.csv';

            Storage::disk('local')->put($path, $this->toCsv($rows));

            $request->forceFill([
                'status' => 'completed',
                'file_path' => $path,
                'row_count' => $rows->count(),
                'completed_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            $request->forceFill([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ])->save();
        }

        return $request->refresh();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function rowsFor(string $exportType): Collection
    {
        return match ($exportType) {
            'revenue' => DB::table('orders')
                ->select('order_number', 'status', 'payment_status', 'currency', 'subtotal', 'discount_total', 'total', 'amount_paid', 'balance_due', 'paid_at', 'created_at')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($row) => (array) $row),
            'payments' => DB::table('payments')
                ->select('reference', 'provider', 'status', 'currency', 'amount', 'channel', 'gateway_response', 'paid_at', 'failed_at', 'created_at')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($row) => (array) $row),
            'enrollments' => DB::table('enrollments')
                ->leftJoin('users', 'users.id', '=', 'enrollments.user_id')
                ->leftJoin('products', 'products.id', '=', 'enrollments.product_id')
                ->select('enrollments.uuid', 'users.name as learner', 'users.email', 'products.title as product', 'enrollments.status', 'enrollments.provisioned_at', 'enrollments.created_at')
                ->orderByDesc('enrollments.created_at')
                ->get()
                ->map(fn ($row) => (array) $row),
            'discounts' => DB::table('discount_redemptions')
                ->select('uuid', 'email', 'status', 'discount_type', 'discount_value', 'currency', 'subtotal', 'discount_amount', 'total_after_discount', 'code', 'redeemed_at', 'created_at')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($row) => (array) $row),
            'leads' => DB::table('leads')
                ->select('email', 'name', 'phone', 'type', 'metadata', 'created_at')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($row) => (array) $row),
            'form_submissions' => DB::table('form_submissions')
                ->select('form_key', 'status', 'name', 'email', 'phone', 'subject', 'message', 'source_url', 'created_at', 'reviewed_at')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($row) => (array) $row),
            'support_tickets' => DB::table('support_tickets')
                ->select('uuid', 'requester_name', 'requester_email', 'subject', 'category', 'priority', 'status', 'source', 'last_activity_at', 'resolved_at', 'created_at')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($row) => (array) $row),
            'email_messages' => DB::table('email_messages')
                ->select('recipient_email', 'subject', 'status', 'created_at', 'updated_at')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($row) => (array) $row),
            'whatsapp_messages' => DB::table('whatsapp_messages')
                ->select('recipient_phone', 'template_name', 'status', 'created_at', 'updated_at')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($row) => (array) $row),
            default => throw new \InvalidArgumentException("Unsupported export type [{$exportType}]."),
        };
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     */
    private function toCsv(Collection $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        $headers = $rows->first() ? array_keys($rows->first()) : ['empty'];

        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, array_map(function ($value): string {
                if (is_array($value) || is_object($value)) {
                    return json_encode($value, JSON_UNESCAPED_SLASHES) ?: '';
                }

                return (string) ($value ?? '');
            }, $row));
        }

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }
}
