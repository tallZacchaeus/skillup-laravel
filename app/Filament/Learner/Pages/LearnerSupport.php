<?php

namespace App\Filament\Learner\Pages;

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
use Illuminate\Support\Facades\Auth;

class LearnerSupport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Account';

    protected static ?string $navigationIcon = 'heroicon-o-lifebuoy';

    protected static ?string $navigationLabel = 'Support';

    protected static ?int $navigationSort = 50;

    protected static string $view = 'filament.learner.pages.support';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'priority' => 'normal',
            'category' => 'general',
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
                        'general' => 'General',
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

        $ticket = SupportTicket::create([
            'user_id' => $user?->id,
            'requester_name' => $user?->name,
            'requester_email' => $user?->email,
            'subject' => $data['subject'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => 'open',
            'source' => 'learner',
        ]);

        $ticket->messages()->create([
            'user_id' => $user?->id,
            'author_name' => $user?->name,
            'author_email' => $user?->email,
            'body' => $data['message'],
        ]);

        $this->form->fill([
            'priority' => 'normal',
            'category' => 'general',
        ]);

        Notification::make()
            ->title('Support ticket created')
            ->success()
            ->send();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SupportTicket::query()->where('user_id', Auth::id()))
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
}
