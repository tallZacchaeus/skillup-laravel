@php
    /**
     * Renders a labelled statistic in Filament's section chrome.
     */
    $trend = function (int $current, int $previous) use ($rangeDays): array {
        if ($previous === 0) {
            return $current > 0
                ? ['up', 'no traffic in the previous period']
                : ['flat', 'no change'];
        }

        $delta = round((($current - $previous) / $previous) * 100);

        return [
            $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat'),
            sprintf('%+d%% vs previous %d days', $delta, $rangeDays),
        ];
    };
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Tabs --------------------------------------------------------- --}}
        <x-filament::tabs>
            <x-filament::tabs.item
                icon="heroicon-m-globe-alt"
                :active="$activeTab === 'visits'"
                wire:click="setTab('visits')"
            >
                Visits
            </x-filament::tabs.item>

            <x-filament::tabs.item
                icon="heroicon-m-user-plus"
                :active="$activeTab === 'registrations'"
                :badge="number_format($registrations['total'] ?? 0)"
                wire:click="setTab('registrations')"
            >
                Registrations
            </x-filament::tabs.item>

            <x-filament::tabs.item
                icon="heroicon-m-shield-check"
                :active="$activeTab === 'access'"
                wire:click="setTab('access')"
            >
                Manage access
            </x-filament::tabs.item>
        </x-filament::tabs>

        {{-- Range selector (shared by the two reporting tabs) ------------- --}}
        @if ($activeTab !== 'access')
            <div class="flex items-center justify-between gap-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing the last {{ $rangeDays }} days.
                </p>

                <x-filament::button.group>
                    @foreach ([7, 30, 90] as $days)
                        <x-filament::button
                            size="sm"
                            :color="$rangeDays === $days ? 'primary' : 'gray'"
                            wire:click="setRange({{ $days }})"
                        >
                            {{ $days }} days
                        </x-filament::button>
                    @endforeach
                </x-filament::button.group>
            </div>
        @endif

        {{-- Tab: Visits --------------------------------------------------- --}}
        @if ($activeTab === 'visits')
            @php
                [$visitDirection, $visitTrendLabel] = $trend($visits['total'] ?? 0, $visits['previousTotal'] ?? 0);
            @endphp

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-filament::section>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Page views ({{ $rangeDays }}d)</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-950 dark:text-white">
                        {{ number_format($visits['total'] ?? 0) }}
                    </div>
                    <div @class([
                        'mt-1 text-xs',
                        'text-success-600 dark:text-success-400' => $visitDirection === 'up',
                        'text-danger-600 dark:text-danger-400' => $visitDirection === 'down',
                        'text-gray-500 dark:text-gray-400' => $visitDirection === 'flat',
                    ])>
                        {{ $visitTrendLabel }}
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Unique visitors ({{ $rangeDays }}d)</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-950 dark:text-white">
                        {{ number_format($visits['unique'] ?? 0) }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($visits['signedIn'] ?? 0) }} views while signed in
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Page views today</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-950 dark:text-white">
                        {{ number_format($visits['today'] ?? 0) }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">since midnight</div>
                </x-filament::section>

                <x-filament::section>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Recorded all time</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-950 dark:text-white">
                        {{ number_format($visits['allTime'] ?? 0) }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        @if ($visits['trackingSince'] ?? null)
                            since {{ \Illuminate\Support\Carbon::parse($visits['trackingSince'])->format('M j, Y') }}
                        @else
                            tracking not started yet
                        @endif
                    </div>
                </x-filament::section>
            </div>

            <x-filament::section>
                <x-slot name="heading">Page views per day</x-slot>

                @if (($visits['allTime'] ?? 0) === 0)
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No visits recorded yet. Traffic is counted from the moment this feature was
                        deployed — public page views will start appearing here within minutes.
                    </p>
                @else
                    @php
                        $visitSeries = collect($visits['series'] ?? []);
                        $maxVisits = max(1, $visitSeries->max('value') ?? 0);
                    @endphp

                    <div class="flex h-48 items-end gap-px">
                        @foreach ($visits['series'] ?? [] as $point)
                            <div
                                class="group flex h-full flex-1 items-end"
                                title="{{ $point['label'] }}: {{ number_format($point['value']) }} views"
                            >
                                <div
                                    class="w-full rounded-t bg-primary-500 group-hover:bg-primary-400 dark:bg-primary-600 dark:group-hover:bg-primary-500"
                                    style="height: {{ $point['value'] > 0 ? max(2, round($point['value'] / $maxVisits * 100)) : 1 }}%"
                                ></div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-2 flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ data_get($visitSeries->first(), 'label') }}</span>
                        <span>peak {{ number_format($maxVisits) }}/day</span>
                        <span>{{ data_get($visitSeries->last(), 'label') }}</span>
                    </div>
                @endif
            </x-filament::section>

            <div class="grid gap-4 lg:grid-cols-2">
                <x-filament::section>
                    <x-slot name="heading">Most visited pages</x-slot>

                    @forelse ($visits['topPages'] ?? [] as $page)
                        <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2 text-sm last:border-0 dark:border-white/10">
                            <span class="truncate font-medium text-gray-950 dark:text-white">{{ $page['path'] }}</span>
                            <span class="shrink-0 text-gray-500 dark:text-gray-400">
                                {{ number_format($page['views']) }} views · {{ number_format($page['visitors']) }} visitors
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nothing recorded in this period.</p>
                    @endforelse
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">Traffic sources</x-slot>

                    @forelse ($visits['referrers'] ?? [] as $referrer)
                        <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2 text-sm last:border-0 dark:border-white/10">
                            <span class="truncate font-medium text-gray-950 dark:text-white">{{ $referrer['host'] }}</span>
                            <span class="shrink-0 text-gray-500 dark:text-gray-400">{{ number_format($referrer['views']) }} views</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            No external referrers yet — visitors are arriving directly.
                        </p>
                    @endforelse
                </x-filament::section>
            </div>
        @endif

        {{-- Tab: Registrations -------------------------------------------- --}}
        @if ($activeTab === 'registrations')
            @php
                [$signupDirection, $signupTrendLabel] = $trend(
                    $registrations['inRange'] ?? 0,
                    $registrations['previousRange'] ?? 0,
                );
                $total = $registrations['total'] ?? 0;
                $verified = $registrations['verified'] ?? 0;
            @endphp

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-filament::section>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total accounts</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-950 dark:text-white">
                        {{ number_format($total) }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">all time</div>
                </x-filament::section>

                <x-filament::section>
                    <div class="text-sm text-gray-500 dark:text-gray-400">New sign-ups ({{ $rangeDays }}d)</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-950 dark:text-white">
                        {{ number_format($registrations['inRange'] ?? 0) }}
                    </div>
                    <div @class([
                        'mt-1 text-xs',
                        'text-success-600 dark:text-success-400' => $signupDirection === 'up',
                        'text-danger-600 dark:text-danger-400' => $signupDirection === 'down',
                        'text-gray-500 dark:text-gray-400' => $signupDirection === 'flat',
                    ])>
                        {{ $signupTrendLabel }}
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Registered today</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-950 dark:text-white">
                        {{ number_format($registrations['today'] ?? 0) }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">since midnight</div>
                </x-filament::section>

                <x-filament::section>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Verified emails</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-950 dark:text-white">
                        {{ number_format($verified) }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $total > 0 ? round($verified / $total * 100) : 0 }}% of all accounts
                    </div>
                </x-filament::section>
            </div>

            <x-filament::section>
                <x-slot name="heading">New accounts per day</x-slot>

                @php
                    $signupSeries = collect($registrations['series'] ?? []);
                    $maxSignups = max(1, $signupSeries->max('value') ?? 0);
                @endphp

                <div class="flex h-48 items-end gap-px">
                    @foreach ($registrations['series'] ?? [] as $point)
                        <div
                            class="group flex h-full flex-1 items-end"
                            title="{{ $point['label'] }}: {{ number_format($point['value']) }} sign-ups"
                        >
                            <div
                                class="w-full rounded-t bg-success-500 group-hover:bg-success-400 dark:bg-success-600 dark:group-hover:bg-success-500"
                                style="height: {{ $point['value'] > 0 ? max(2, round($point['value'] / $maxSignups * 100)) : 1 }}%"
                            ></div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-2 flex justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ data_get($signupSeries->first(), 'label') }}</span>
                    <span>peak {{ number_format($maxSignups) }}/day</span>
                    <span>{{ data_get($signupSeries->last(), 'label') }}</span>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Accounts by role</x-slot>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($registrations['byRole'] ?? [] as $role)
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-gray-50 px-3 py-2 text-sm dark:bg-white/5">
                            <span class="font-medium text-gray-950 dark:text-white">{{ $role['name'] }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ number_format($role['count']) }}</span>
                        </div>
                    @endforeach

                    @if (($registrations['withoutRole'] ?? 0) > 0)
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-warning-50 px-3 py-2 text-sm dark:bg-warning-500/10">
                            <span class="font-medium text-warning-700 dark:text-warning-400">No role assigned</span>
                            <span class="text-warning-700 dark:text-warning-400">
                                {{ number_format($registrations['withoutRole']) }}
                            </span>
                        </div>
                    @endif
                </div>
            </x-filament::section>
        @endif

        {{-- Tab: Manage access -------------------------------------------- --}}
        @if ($activeTab === 'access')
            @unless (auth()->user()?->hasRole('Super Admin'))
                <x-filament::section>
                    <div class="flex items-start gap-3">
                        <x-filament::icon
                            icon="heroicon-o-lock-closed"
                            class="mt-0.5 h-5 w-5 shrink-0 text-warning-500"
                        />
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            You can view accounts here, but only a <strong>Super Admin</strong> can grant or
                            revoke administrator roles.
                        </p>
                    </div>
                </x-filament::section>
            @endunless

            {{ $this->table }}
        @endif
    </div>
</x-filament-panels::page>
