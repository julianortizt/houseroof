<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Quote extends Model
{
    use SoftDeletes;

    public $guarded = [];

    protected $casts = [
        'area_m2' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'deposit_percent' => 'decimal:2',
        'valid_until' => 'date',
        'is_admin_created' => 'boolean',
        'sent_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'version' => 'integer',
    ];

    /**
     * The Mapulator's quote lines.
     *
     * Beware: quotes still carries a legacy nullable "items" TEXT column that
     * the public API writes JSON into. A real column always wins over a
     * relation in Eloquent, so `$quote->items` returns that column (usually
     * null) — call sites must use `$quote->items()` to get these rows.
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MapsCalculator\Project::class);
    }

    public function parentQuote(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_quote_id');
    }

    public function childQuotes(): HasMany
    {
        return $this->hasMany(self::class, 'parent_quote_id')->orderBy('version');
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(\App\Models\MapsCalculator\WorkOrder::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(QuotePayment::class);
    }

    public function getStatusLabelAttribute(): string
    {
        // MapsCalculator statuses
        $mapulatorStatuses = [
            'borrador' => __('mapscalculator.qstatus_draft'),
            'enviado' => __('mapscalculator.qstatus_sent'),
            'aprobado' => __('mapscalculator.qstatus_approved'),
            'rechazado' => __('mapscalculator.qstatus_rejected'),
            'expirado' => __('mapscalculator.qstatus_expired'),
        ];

        // Legacy statuses
        $legacyStatuses = [
            'new' => __('mapscalculator.qstatus_new'),
            'contacted' => __('mapscalculator.qstatus_contacted'),
            'quoted' => __('mapscalculator.qstatus_quoted'),
            'closed' => __('mapscalculator.qstatus_closed'),
        ];

        if (isset($mapulatorStatuses[$this->status])) {
            return $mapulatorStatuses[$this->status];
        }

        return $legacyStatuses[$this->status] ?? $this->status;
    }

    public function getMapulatorStatusColorAttribute(): string
    {
        return match ($this->status) {
            'borrador' => 'gray',
            'enviado' => 'info',
            'aprobado' => 'success',
            'rechazado' => 'danger',
            'expirado' => 'warning',
            'new' => 'info',
            'contacted' => 'warning',
            'quoted' => 'success',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public function getServiceTypeLabelAttribute(): string
    {
        return match ($this->service_type) {
            'resin' => __('mapscalculator.servicetype_resin'),
            'granite' => __('mapscalculator.servicetype_granite'),
            'other' => __('mapscalculator.servicetype_other'),
            default => $this->service_type,
        };
    }

    public function getFormattedNumberAttribute(): string
    {
        return $this->quote_number ?? 'Q-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2, ',', '.') . ' €';
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 2, ',', '.') . ' €';
    }

    public function getFormattedTaxAmountAttribute(): string
    {
        return number_format($this->tax_amount, 2, ',', '.') . ' €';
    }

    public function getDepositTargetCentsAttribute(): int
    {
        $totalCents = max(0, (int) round((float) $this->total * 100));
        if ($totalCents < 100) {
            return 0;
        }

        $percentage = (float) ($this->deposit_percent ?: 30);

        return min(
            $totalCents - 50,
            max(50, (int) round($totalCents * $percentage / 100)),
        );
    }

    public function getWhatsAppUrlAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', Setting::get('site_whatsapp', '+34654195493'));
        $message = $this->buildWhatsAppMessage();
        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }

    public function buildWhatsAppMessage(): string
    {
        $lines = [];
        $lines[] = "📋 *" . __('mapscalculator.whatsapp_quote_title', ['number' => $this->formatted_number]) . "*";
        $lines[] = "👤 " . __('mapscalculator.client') . ": {$this->name}";
        if ($this->company) $lines[] = "🏢 " . __('mapscalculator.company') . ": {$this->company}";
        if ($this->email) $lines[] = "📧 " . __('mapscalculator.email') . ": {$this->email}";
        if ($this->phone) $lines[] = "📱 " . __('mapscalculator.phone_short') . ": {$this->phone}";
        if ($this->address) $lines[] = "📍 {$this->address}" . ($this->city ? ", {$this->city}" : "") . ($this->postal_code ? " {$this->postal_code}" : "");
        $lines[] = "";
        $lines[] = "🛠️ *" . __('mapscalculator.whatsapp_service', ['label' => $this->service_type_label]) . "*";
        if ($this->area_m2) $lines[] = "📐 " . __('mapscalculator.whatsapp_area', ['area' => number_format($this->area_m2, 2, ',', '.')]);
        $lines[] = "";
        $lines[] = "📦 *" . __('mapscalculator.whatsapp_details') . "*";

        foreach ($this->items()->get() as $index => $item) {
            $num = $index + 1;
            $line = "  {$num}. {$item->name}";
            if ($item->quantity > 1) $line .= " × {$item->quantity}";
            $line .= " - " . number_format($item->unit_price, 2, ',', '.') . " €/ud";
            if ($item->discount_percent > 0) $line .= " (-{$item->discount_percent}%)";
            $line .= " = " . number_format($item->total_price, 2, ',', '.') . " €";
            $lines[] = $line;
        }

        $lines[] = "";
        $lines[] = "💰 " . __('mapscalculator.whatsapp_subtotal', ['amount' => $this->formatted_subtotal]);
        if ($this->tax_percent > 0) {
            $lines[] = "📊 " . __('mapscalculator.whatsapp_vat', ['percent' => number_format($this->tax_percent, 2, ',', '.'), 'amount' => $this->formatted_tax_amount]);
        }
        $lines[] = "💵 *" . __('mapscalculator.whatsapp_total', ['amount' => $this->formatted_total]) . "*";
        $lines[] = "";
        $lines[] = "📅 " . __('mapscalculator.whatsapp_valid_until', ['date' => ($this->valid_until?->format('d/m/Y') ?? '—')]);
        $lines[] = "";
        $lines[] = "🔗 " . __('mapscalculator.whatsapp_view_online', ['url' => url("/cotizacion/{$this->id}")]);
        $lines[] = "";
        $lines[] = "—";
        $lines[] = config('platform.public_name', 'Costa Resin & Construction');

        return implode("\n", $lines);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => in_array($this->status, ['borrador', 'enviado']) ? 'enviado' : 'quoted',
            'sent_at' => now(),
        ]);
    }

    public function markAsApproved(): void
    {
        $this->update([
            'status' => 'aprobado',
            'approved_at' => now(),
        ]);

        if ($this->project) {
            $this->project->update(['status' => 'aprobado']);
        }
    }

    public function markAsRejected(): void
    {
        $this->update([
            'status' => 'rechazado',
            'rejected_at' => now(),
        ]);

        if ($this->project) {
            $this->project->update(['status' => 'rechazado']);
        }
    }

    public function createNewVersion(): self
    {
        $newQuote = $this->replicate([
            'quote_number',
            'sent_at',
            'approved_at',
            'rejected_at',
            'pdf_path',
        ]);

        $newQuote->version = $this->version + 1;
        $newQuote->parent_quote_id = $this->id;
        $newQuote->status = 'borrador';
        $newQuote->save();

        foreach ($this->items()->get() as $item) {
            $newItem = $item->replicate();
            $newItem->quote_id = $newQuote->id;
            $newItem->save();
        }

        $newQuote->calculateTotals();

        return $newQuote;
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum('total_price');
        $taxAmount = round($subtotal * ($this->tax_percent / 100), 2);
        $total = round($subtotal + $taxAmount, 2);

        // Must stay quiet: the saved() hook below calls this method, so using
        // update() here fires saved() again and recurses until memory runs out.
        $this->updateQuietly([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

    public static function generateQuoteNumber(): string
    {
        $year = now()->year;
        $lastQuote = static::whereYear('created_at', $year)
            ->whereNotNull('quote_number')
            ->latest('quote_number')
            ->first();

        if ($lastQuote && preg_match('/^Q-' . $year . '-(\d+)$/', $lastQuote->quote_number, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return 'Q-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function booted(): void
    {
        static::creating(function (self $quote) {
            if ($quote->is_admin_created && !$quote->quote_number) {
                $quote->quote_number = static::generateQuoteNumber();
            }
            if (!$quote->tax_percent) {
                $quote->tax_percent = 21;
            }

            // `service_type` es NOT NULL en la tabla heredada: sin valor por
            // defecto, cualquier vía de creación que no lo rellene (como el
            // presupuesto del módulo) fallaba al insertar.
            if (! $quote->service_type) {
                $quote->service_type = 'resin';
            }

            if ($quote->name === null) {
                $quote->name = '';
            }
            if (!$quote->valid_until) {
                $quote->valid_until = now()->addDays(30);
            }
            if ($quote->deposit_percent === null) {
                $quote->deposit_percent = 30;
            }
            if ((float) $quote->deposit_percent < 1 || (float) $quote->deposit_percent > 99) {
                throw ValidationException::withMessages([
                    'deposit_percent' => 'El anticipo debe estar entre 1% y 99%.',
                ]);
            }
        });

        static::updating(function (self $quote): void {
            if ($quote->isDirty('deposit_percent')
                && ((float) $quote->deposit_percent < 1 || (float) $quote->deposit_percent > 99)) {
                throw ValidationException::withMessages([
                    'deposit_percent' => 'El anticipo debe estar entre 1% y 99%.',
                ]);
            }

            if ($quote->isDirty('deposit_percent')
                && ($quote->getOriginal('status') !== 'borrador' || $quote->payments()->exists())) {
                throw ValidationException::withMessages([
                    'deposit_percent' => 'El anticipo no se puede modificar después de enviar la cotización.',
                ]);
            }

            if (($quote->isDirty('total') || $quote->isDirty('tax_percent'))
                && $quote->payments()->whereIn('status', ['pending', 'paid'])->exists()) {
                throw ValidationException::withMessages([
                    'total' => 'No se puede cambiar el importe de una cotización con pagos iniciados o confirmados.',
                ]);
            }
        });

        static::saved(function (self $quote) {
            if ($quote->is_admin_created) {
                $quote->calculateTotals();
            }
        });
    }

    public function scopeAdminCreated($query)
    {
        return $query->where('is_admin_created', true);
    }

    public function scopeClientRequested($query)
    {
        return $query->where('is_admin_created', false);
    }
}