<?php

namespace App\Services\Discounts;

use App\Models\Catalog\DiscountEligibleEmail;
use App\Models\Catalog\DiscountEligibilityList;
use OpenSpout\Reader\Common\Creator\ReaderFactory;

class DiscountEligibilityImporter
{
    /**
     * @return array{imported: int, duplicates: int, invalid: int, skipped: int}
     */
    public function import(DiscountEligibilityList $list, string $path, ?string $sourceFilename = null): array
    {
        $reader = ReaderFactory::createFromFile($path);
        $reader->open($path);

        $result = [
            'imported' => 0,
            'duplicates' => 0,
            'invalid' => 0,
            'skipped' => 0,
        ];
        $headerMap = null;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $values = array_map(
                    fn ($cell) => trim((string) $cell->getValue()),
                    $row->getCells(),
                );

                if ($values === [] || collect($values)->every(fn (string $value) => $value === '')) {
                    $result['skipped']++;

                    continue;
                }

                if ($headerMap === null && $this->looksLikeHeader($values)) {
                    $headerMap = $this->headerMap($values);

                    continue;
                }

                $email = $this->emailFromRow($values, $headerMap);
                $name = $this->nameFromRow($values, $headerMap);
                $normalizedEmail = DiscountEligibleEmail::normalizeEmail($email);

                if (! filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
                    $result['invalid']++;

                    continue;
                }

                $created = DiscountEligibleEmail::firstOrCreate(
                    [
                        'discount_eligibility_list_id' => $list->id,
                        'normalized_email' => $normalizedEmail,
                    ],
                    [
                        'email' => $normalizedEmail,
                        'name' => $name,
                        'status' => 'active',
                        'metadata' => ['source_row' => $rowIndex],
                    ],
                )->wasRecentlyCreated;

                $created ? $result['imported']++ : $result['duplicates']++;
            }

            break;
        }

        $reader->close();

        $list->forceFill([
            'source_filename' => $sourceFilename ?? basename($path),
            'metadata' => array_merge($list->metadata ?? [], ['last_import' => $result]),
        ])->save();
        $list->refreshEmailCount();

        return $result;
    }

    /**
     * @param  array<int, string>  $values
     */
    private function looksLikeHeader(array $values): bool
    {
        $normalized = array_map(fn (string $value) => strtolower($value), $values);

        return in_array('email', $normalized, true)
            || in_array('email address', $normalized, true)
            || in_array('name', $normalized, true);
    }

    /**
     * @param  array<int, string>  $values
     * @return array<string, int>
     */
    private function headerMap(array $values): array
    {
        $map = [];

        foreach ($values as $index => $value) {
            $normalized = strtolower($value);

            if (in_array($normalized, ['email', 'email address'], true)) {
                $map['email'] = $index;
            }

            if (in_array($normalized, ['name', 'full name'], true)) {
                $map['name'] = $index;
            }
        }

        return $map;
    }

    /**
     * @param  array<int, string>  $values
     * @param  array<string, int>|null  $headerMap
     */
    private function emailFromRow(array $values, ?array $headerMap): string
    {
        if ($headerMap && isset($headerMap['email'])) {
            return $values[$headerMap['email']] ?? '';
        }

        foreach ($values as $value) {
            if (str_contains($value, '@')) {
                return $value;
            }
        }

        return $values[0] ?? '';
    }

    /**
     * @param  array<int, string>  $values
     * @param  array<string, int>|null  $headerMap
     */
    private function nameFromRow(array $values, ?array $headerMap): ?string
    {
        if ($headerMap && isset($headerMap['name'])) {
            return $values[$headerMap['name']] ?? null;
        }

        return $values[1] ?? null;
    }
}
