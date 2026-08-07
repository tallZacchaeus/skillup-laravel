<?php

namespace App\Filament\Pages;

use App\Models\Catalog\AuditLog;
use App\Models\Platform\PageVisit;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Single admin screen for "who is coming to the site, who signed up, and who
 * gets to administer it".
 *
 * Visit figures come from the page_visits table (see RecordPageVisit); they
 * start from the day that middleware was deployed, so early ranges will look
 * short rather than empty-because-nothing-happened.
 */
class VisitsAndUsers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Visits & Users';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Visits & Users';

    protected static string $view = 'filament.pages.visits-and-users';

    /** The admin roles this page can grant, most privileged first. */
    private const ADMIN_ROLES = ['Super Admin', 'Admin'];

    public string $activeTab = 'visits';

    public int $rangeDays = 30;

    /** @var array<string, mixed> */
    public array $visits = [];

    /** @var array<string, mixed> */
    public array $registrations = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(self::ADMIN_ROLES) ?? false;
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->loadStats();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function setRange(int $days): void
    {
        $this->rangeDays = in_array($days, [7, 30, 90], true) ? $days : 30;

        $this->loadStats();
    }

    public function loadStats(): void
    {
        $days = $this->rangeDays;
        $start = now()->subDays($days - 1)->startOfDay();
        $previousStart = now()->subDays(($days * 2) - 1)->startOfDay();

        $this->visits = $this->visitStats($start, $previousStart, $days);
        $this->registrations = $this->registrationStats($start, $previousStart, $days);
    }

    // ---------------------------------------------------------------- Visits

    /**
     * @return array<string, mixed>
     */
    private function visitStats(Carbon $start, Carbon $previousStart, int $days): array
    {
        $inRange = PageVisit::query()->where('visited_at', '>=', $start);

        return [
            'total' => (clone $inRange)->count(),
            'previousTotal' => PageVisit::query()
                ->whereBetween('visited_at', [$previousStart, $start])
                ->count(),
            'unique' => (clone $inRange)->distinct()->count('visitor_id'),
            'today' => PageVisit::query()->where('visited_at', '>=', now()->startOfDay())->count(),
            'signedIn' => (clone $inRange)->whereNotNull('user_id')->count(),
            'allTime' => PageVisit::query()->count(),
            'trackingSince' => PageVisit::query()->min('visited_at'),
            'series' => $this->dailySeries(PageVisit::query(), 'visited_at', $start, $days),
            'topPages' => PageVisit::query()
                ->where('visited_at', '>=', $start)
                ->selectRaw('path, COUNT(*) as views, COUNT(DISTINCT visitor_id) as visitors')
                ->groupBy('path')
                ->orderByDesc('views')
                ->limit(10)
                ->get()
                ->map(fn ($row) => [
                    'path' => $row->path,
                    'views' => (int) $row->views,
                    'visitors' => (int) $row->visitors,
                ])
                ->all(),
            'referrers' => PageVisit::query()
                ->where('visited_at', '>=', $start)
                ->whereNotNull('referrer_host')
                ->selectRaw('referrer_host, COUNT(*) as views')
                ->groupBy('referrer_host')
                ->orderByDesc('views')
                ->limit(8)
                ->get()
                ->map(fn ($row) => [
                    'host' => $row->referrer_host,
                    'views' => (int) $row->views,
                ])
                ->all(),
        ];
    }

    // --------------------------------------------------------- Registrations

    /**
     * @return array<string, mixed>
     */
    private function registrationStats(Carbon $start, Carbon $previousStart, int $days): array
    {
        return [
            'total' => User::query()->count(),
            'inRange' => User::query()->where('created_at', '>=', $start)->count(),
            'previousRange' => User::query()
                ->whereBetween('created_at', [$previousStart, $start])
                ->count(),
            'today' => User::query()->where('created_at', '>=', now()->startOfDay())->count(),
            'verified' => User::query()->whereNotNull('email_verified_at')->count(),
            'series' => $this->dailySeries(User::query(), 'created_at', $start, $days),
            'byRole' => Role::query()
                ->withCount('users')
                ->orderByDesc('users_count')
                ->get()
                ->map(fn (Role $role) => [
                    'name' => $role->name,
                    'count' => (int) $role->users_count,
                ])
                ->all(),
            'withoutRole' => User::query()->doesntHave('roles')->count(),
        ];
    }

    /**
     * Count rows per calendar day, including the days with no rows at all.
     *
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @return array<int, array{label: string, value: int}>
     */
    private function dailySeries(Builder $query, string $column, Carbon $start, int $days): array
    {
        $counts = $query
            ->where($column, '>=', $start)
            ->selectRaw("DATE({$column}) as bucket, COUNT(*) as aggregate")
            ->groupBy('bucket')
            ->pluck('aggregate', 'bucket');

        $series = [];

        for ($offset = 0; $offset < $days; $offset++) {
            $date = $start->copy()->addDays($offset);
            $key = $date->toDateString();

            $series[] = [
                'label' => $date->format('M j'),
                'value' => (int) ($counts[$key] ?? 0),
            ];
        }

        return $series;
    }

    // ------------------------------------------------------ User management

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->with('roles'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Super Admin' => 'danger',
                        'Admin' => 'warning',
                        default => 'gray',
                    })
                    ->placeholder('No role'),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email verified')
                    ->nullable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    $this->grantRoleAction('Admin'),
                    $this->grantRoleAction('Super Admin'),
                    $this->revokeAdminAction(),
                ])->label('Manage access'),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No users yet');
    }

    /**
     * Granting admin rights is itself an admin right — only a Super Admin can
     * do it, otherwise an Admin could quietly promote themselves further.
     */
    private function canManageRoles(): bool
    {
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    private function grantRoleAction(string $role): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('grant'.str($role)->camel()->toString())
            ->label('Upgrade to '.$role)
            ->icon('heroicon-o-shield-check')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Upgrade to '.$role)
            ->modalDescription(fn (User $record): string => sprintf(
                '%s (%s) will get the %s role and full access to the admin panel. This takes effect immediately.',
                $record->name,
                $record->email,
                $role,
            ))
            ->modalSubmitActionLabel('Upgrade')
            ->visible(fn (User $record): bool => $this->canManageRoles() && ! $record->hasRole($role))
            ->action(fn (User $record) => $this->applyRoleChange($record, $role, grant: true));
    }

    private function revokeAdminAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('revokeAdmin')
            ->label('Revoke admin access')
            ->icon('heroicon-o-shield-exclamation')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Revoke admin access')
            ->modalDescription(fn (User $record): string => sprintf(
                '%s will lose the Admin and Super Admin roles and will no longer be able to open the admin panel.',
                $record->name,
            ))
            ->modalSubmitActionLabel('Revoke')
            ->visible(fn (User $record): bool => $this->canManageRoles()
                && $record->hasAnyRole(self::ADMIN_ROLES)
                && $record->isNot(auth()->user()))
            ->action(fn (User $record) => $this->applyRoleChange($record, null, grant: false));
    }

    /**
     * Apply a role change, re-checking authorisation server side — the ->visible()
     * guards above only control what is rendered.
     */
    private function applyRoleChange(User $user, ?string $role, bool $grant): void
    {
        if (! $this->canManageRoles()) {
            $this->blocked('Only a Super Admin can change administrator roles.');

            return;
        }

        if (! $grant && $user->is(auth()->user())) {
            $this->blocked('You cannot revoke your own admin access.');

            return;
        }

        $before = $user->roles->pluck('name')->sort()->values()->all();

        if ($grant) {
            if (! Role::where('name', $role)->exists()) {
                $this->blocked("The {$role} role does not exist. Run the RolesAndPermissionsSeeder first.");

                return;
            }

            $user->assignRole($role);
        } else {
            // Losing the last Super Admin would leave nobody able to grant the
            // role back, so refuse rather than lock the platform out.
            $othersRemaining = User::role('Super Admin')->whereKeyNot($user->getKey())->count();

            if ($user->hasRole('Super Admin') && $othersRemaining === 0) {
                $this->blocked('This is the only Super Admin — promote someone else before revoking this account.');

                return;
            }

            $user->removeRole('Admin');
            $user->removeRole('Super Admin');
        }

        // Spatie caches the role/permission map; without this the change would
        // not be visible until the cache expired.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user->load('roles');
        $after = $user->roles->pluck('name')->sort()->values()->all();

        AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => User::class,
            'auditable_id' => $user->getKey(),
            'action' => $grant ? 'role_granted' : 'role_revoked',
            'description' => $grant
                ? "Granted the {$role} role from the admin panel."
                : 'Revoked admin roles from the admin panel.',
            'old_values' => ['roles' => $before],
            'new_values' => ['roles' => $after],
            'ip_address' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 1000),
        ]);

        Notification::make()
            ->success()
            ->title($grant ? "{$user->name} is now {$role}" : "Admin access revoked for {$user->name}")
            ->body('Roles: '.($after === [] ? 'none' : implode(', ', $after)))
            ->send();

        $this->loadStats();
    }

    private function blocked(string $message): void
    {
        Notification::make()
            ->danger()
            ->title('Role change blocked')
            ->body($message)
            ->send();
    }
}
