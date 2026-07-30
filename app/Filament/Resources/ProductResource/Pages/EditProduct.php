<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Enums\ProductStatus;
use App\Filament\Resources\ProductResource;
use App\Models\Catalog\AuditLog;
use App\Models\Catalog\Product;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * @var array<string, mixed>
     */
    protected array $oldProductValues = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->icon('heroicon-o-eye')
                ->url(fn (Product $record) => route('courses.products.show', [$record->track->slug, $record->slug]))
                ->openUrlInNewTab(),
            Actions\Action::make('publish')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(function (Product $record) {
                    $missing = $record->missingPublicationFields();

                    if ($missing !== []) {
                        Notification::make()
                            ->title('Product cannot be published')
                            ->body('Missing: '.implode(', ', $missing).'.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $record->publish();
                    $this->auditProductAction($record, 'published', 'Product published from Filament admin.');

                    Notification::make()
                        ->title('Product published')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('hide')
                ->color('warning')
                ->icon('heroicon-o-eye-slash')
                ->requiresConfirmation()
                ->action(function (Product $record) {
                    $record->update(['status' => ProductStatus::Hidden]);
                    $this->auditProductAction($record, 'hidden', 'Product hidden from Filament admin.');
                }),
            Actions\Action::make('markDraft')
                ->label('Mark draft')
                ->icon('heroicon-o-pencil')
                ->requiresConfirmation()
                ->action(function (Product $record) {
                    $record->update(['status' => ProductStatus::Draft, 'published_at' => null]);
                    $this->auditProductAction($record, 'marked_draft', 'Product moved back to draft from Filament admin.');
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldProductValues = $this->record->attributesToArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->auditProductAction($this->record, 'updated', 'Product updated from Filament admin.', $this->oldProductValues);
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    private function auditProductAction(Product $product, string $action, string $description, array $oldValues = []): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => $product::class,
            'auditable_id' => $product->id,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $product->fresh()->attributesToArray(),
        ]);
    }
}
