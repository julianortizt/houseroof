<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FiscalProfile
{
    private const FIELDS = [
        'legal_name',
        'tax_id',
        'address',
        'postal_code',
        'city',
        'province',
        'country',
        'contact_email',
        'phone',
        'invoice_serie',
    ];

    public function get(): array
    {
        $settings = Setting::safeGetMany(array_merge(
            array_map(fn (string $field): string => "fiscal_{$field}", self::FIELDS),
            ['fiscal_issuance_enabled'],
        ), '');

        return collect(self::FIELDS)
            ->mapWithKeys(fn (string $field): array => [
                $field => $settings["fiscal_{$field}"] ?: ($field === 'country' ? 'ES' : ''),
            ])
            ->merge([
                'issuance_enabled' => (bool) $settings['fiscal_issuance_enabled'],
            ])
            ->all();
    }

    public function save(array $data): void
    {
        $validated = Validator::make($data, [
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'size:2'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'invoice_serie' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/'],
            'issuance_enabled' => ['nullable', 'boolean'],
        ])->validate();

        $validated['country'] = strtoupper(trim((string) ($validated['country'] ?? 'ES')));
        $validated['invoice_serie'] = strtoupper(trim((string) ($validated['invoice_serie'] ?? '')));
        $issuanceEnabled = (bool) ($validated['issuance_enabled'] ?? false);

        if ($issuanceEnabled) {
            $missing = collect([
                'legal_name' => $validated['legal_name'] ?? null,
                'tax_id' => $validated['tax_id'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'country' => $validated['country'] ?? null,
                'invoice_serie' => $validated['invoice_serie'] ?? null,
            ])->filter(fn ($value) => blank($value))->keys()->all();

            if ($missing !== []) {
                throw ValidationException::withMessages([
                    'issuance_enabled' => ['La emisión fiscal requiere razón social, NIF, domicilio, localidad, país y serie.'],
                ]);
            }
        }

        $oldProfile = $this->get();

        DB::transaction(function () use ($validated, $issuanceEnabled, $oldProfile): void {
            foreach (self::FIELDS as $field) {
                Setting::set(
                    "fiscal_{$field}",
                    trim((string) ($validated[$field] ?? '')),
                    'text',
                    'fiscal',
                );
            }

            Setting::set('fiscal_issuance_enabled', $issuanceEnabled ? '1' : '0', 'boolean', 'fiscal');

            app(AuditLogger::class)->recordFiscalProfileChange($oldProfile, $this->get());
        });
    }

    public function canIssue(): bool
    {
        $profile = $this->get();

        return (bool) $profile['issuance_enabled']
            && filled($profile['legal_name'])
            && filled($profile['tax_id'])
            && filled($profile['address'])
            && filled($profile['city'])
            && filled($profile['country'])
            && filled($profile['invoice_serie']);
    }

    public function operationalStatus(): array
    {
        $profile = $this->get();
        $required = [
            'legal_name',
            'tax_id',
            'address',
            'city',
            'country',
            'invoice_serie',
        ];

        $missing = collect($required)
            ->filter(fn (string $field): bool => blank($profile[$field] ?? ''))
            ->values()
            ->all();

        return [
            'ready' => $this->canIssue(),
            'issuance_enabled' => (bool) $profile['issuance_enabled'],
            'missing_fields' => $missing,
            'profile' => $profile,
        ];
    }

    public function nextInvoiceNumber(): string
    {
        $profile = $this->get();
        $serie = strtoupper(trim((string) ($profile['invoice_serie'] ?? 'F1')));
        $year = now()->format('Y');
        $sequence = max(1, (int) Setting::get('fiscal_last_invoice_number', 0) + 1);
        $number = sprintf('%s-%s-%06d', $serie, $year, $sequence);

        Setting::set('fiscal_last_invoice_number', $sequence, 'integer', 'fiscal');

        return $number;
    }
}
