<x-filament-panels::page>
    <div class="space-y-6">
        <div>
            <x-filament::button wire:click="refreshReport" icon="heroicon-o-arrow-path">
                Refresh reports
            </x-filament::button>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach (['revenue', 'enrollments', 'payments', 'discounts', 'cohorts', 'support', 'email', 'whatsapp', 'community', 'failed_operations'] as $section)
                <x-filament::section>
                    <x-slot name="heading">{{ str($section)->replace('_', ' ')->headline() }}</x-slot>

                    <dl class="space-y-2 text-sm">
                        @foreach (($report[$section] ?? []) as $label => $value)
                            @continue(is_array($value))
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-gray-500">{{ str($label)->replace('_', ' ')->headline() }}</dt>
                                <dd class="font-semibold text-gray-950">{{ is_numeric($value) ? number_format((float) $value, 2) : $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-filament::section>
            @endforeach
        </div>

        <x-filament::section>
            <x-slot name="heading">Product Demand</x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b text-xs uppercase text-gray-500">
                            <th class="py-2 pr-4">Product</th>
                            <th class="py-2 pr-4">Quantity</th>
                            <th class="py-2 pr-4">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (($report['product_demand'] ?? []) as $item)
                            <tr class="border-b last:border-b-0">
                                <td class="py-3 pr-4 font-medium text-gray-950">{{ $item['product_title'] ?? 'Unknown product' }}</td>
                                <td class="py-3 pr-4">{{ number_format((float) ($item['quantity'] ?? 0)) }}</td>
                                <td class="py-3 pr-4">{{ number_format((float) ($item['revenue'] ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-gray-500">No product demand data yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
