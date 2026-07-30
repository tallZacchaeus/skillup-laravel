<?php

namespace App\Filament\Corporate\Pages;

use App\Models\Catalog\CorporateAccount;
use App\Models\Catalog\CorporateLearner;
use App\Models\Support\SupportTicket;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CorporateSupport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Corporate';

    protected static ?string $navigationIcon = 'heroicon-o-lifebuoy';

    protected static ?string $navigationLabel = 'Support';

    protected static ?int $navigationSort = 50;

    protected static string $view = 'filament.corporate.pages.support';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'priority' => 'normal',
            'category' => 'corporate',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('subject')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('category')
                    ->options([
                        'corporate' => 'Corporate',
                        'billing' => 'Billing',
                        'lms' => 'LMS Access',
                        'technical' => 'Technical',
                    ])
                    ->required(),
                Forms\Components\Select::make('priority')
                    ->options([
                        'low' => 'Low',
                        'normal' => 'Normal',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function createTicket(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();
        $account = $this->corporateAccount();

        $ticket = SupportTicket::create([
            'user_id' => $user?->id,
            'corporate_account_id' => $account?->id,
            'requester_name' => $user?->name,
            'requester_email' => $user?->email,
            'subject' => $data['subject'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => 'open',
            'source' => 'corporate',
        ]);

        $ticket->messages()->create([
            'user_id' => $user?->id,
            'author_name' => $user?->name,
            'author_email' => $user?->email,
            'body' => $data['message'],
        ]);

        $this->form->fill([
            'priority' => 'normal',
            'category' => 'corporate',
        ]);

        Notification::make()
            ->title('Corporate support ticket created')
            ->success()
            ->send();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->ticketQuery())
            ->defaultSort('last_activity_at', 'desc')
            ->columns([
                TextColumn::make('subject')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('category')
                    ->badge(),
                TextColumn::make('priority')
                    ->badge(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('last_activity_at')
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    private function ticketQuery(): Builder
    {
        $account = $this->corporateAccount();

        return SupportTicket::query()
            ->when(
                $account,
                fn (Builder $query) => $query->where('corporate_account_id', $account->id),
                fn (Builder $query) => $query->where('user_id', Auth::id()),
            );
    }

    private function corporateAccount(): ?CorporateAccount
    {
        return CorporateAccount::where('primary_contact_user_id', Auth::id())->first()
            ?? CorporateLearner::with('corporateAccount')
                ->where('user_id', Auth::id())
                ->first()
                ?->corporateAccount;
    }
}
