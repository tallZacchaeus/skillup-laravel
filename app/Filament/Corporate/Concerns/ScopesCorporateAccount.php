<?php

namespace App\Filament\Corporate\Concerns;

use App\Models\Catalog\CorporateAccount;

trait ScopesCorporateAccount
{
    /**
     * @return array<int, int>
     */
    protected static function corporateAccountIds(): array
    {
        $userId = auth()->id();

        if (! $userId) {
            return [];
        }

        return CorporateAccount::query()
            ->where('primary_contact_user_id', $userId)
            ->orWhereHas('learners', fn ($query) => $query->where('user_id', $userId))
            ->pluck('id')
            ->all();
    }
}
