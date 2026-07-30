<x-filament-panels::page>
    <div class="max-w-2xl">
        <x-filament::section>
            <x-slot name="heading">Welcome to the SkillUp Community</x-slot>

            <div class="py-2 space-y-4">
                <p class="text-sm text-gray-600">
                    Connect with fellow learners, access cohort-specific channels, participate in discussions, and receive announcements directly from instructors.
                </p>

                <div>
                    <x-filament::button wire:click="launch" size="lg" icon="heroicon-o-chat-bubble-left-right">
                        Launch Discourse Forum
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
