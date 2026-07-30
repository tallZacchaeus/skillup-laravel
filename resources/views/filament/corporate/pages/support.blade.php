<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Create corporate support ticket</x-slot>

            <div class="space-y-4">
                {{ $this->form }}

                <x-filament::button wire:click="createTicket" icon="heroicon-o-paper-airplane">
                    Submit ticket
                </x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Company tickets</x-slot>

            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
