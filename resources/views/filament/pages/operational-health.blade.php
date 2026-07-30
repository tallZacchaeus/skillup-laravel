<x-filament-panels::page>
    <div class="space-y-6">
        <div>
            <x-filament::button wire:click="refreshHealth" icon="heroicon-o-arrow-path">
                Refresh health
            </x-filament::button>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            @foreach ($checks as $name => $check)
                <x-filament::section>
                    <x-slot name="heading">{{ str($name)->replace('_', ' ')->headline() }}</x-slot>

                    <div class="space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-gray-500">Status</span>
                            <span @class([
                                'rounded-md px-2 py-1 text-xs font-semibold',
                                'bg-green-50 text-green-700' => ($check['status'] ?? null) === 'healthy',
                                'bg-yellow-50 text-yellow-700' => ($check['status'] ?? null) === 'attention',
                                'bg-red-50 text-red-700' => ($check['status'] ?? null) === 'critical',
                                'bg-gray-50 text-gray-700' => ! in_array(($check['status'] ?? null), ['healthy', 'attention', 'critical'], true),
                            ])>
                                {{ str($check['status'] ?? 'unknown')->headline() }}
                            </span>
                        </div>

                        <p class="text-gray-600">{{ $check['summary'] ?? 'No summary available.' }}</p>

                        <dl class="space-y-2">
                            @foreach (($check['metrics'] ?? []) as $metric => $value)
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-gray-500">{{ str($metric)->replace('_', ' ')->headline() }}</dt>
                                    <dd class="font-semibold text-gray-950">{{ number_format((float) $value) }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
