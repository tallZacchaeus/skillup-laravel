<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Catalog\AuditLog;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => $this->record::class,
            'auditable_id' => $this->record->id,
            'action' => 'created',
            'description' => 'Product created from Filament admin.',
            'new_values' => $this->record->fresh()->attributesToArray(),
        ]);
    }
}
