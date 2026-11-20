<?php

namespace App\Models\MapsCalculator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceItem extends Model
{
    protected $table = 'mapscalculator_price_items';

    protected $guarded = [];

    protected $casts = [
        'unit_price_m2' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function quoteItems(): HasMany
    {
        return $this->hasMany(\App\Models\QuoteItem::class, 'price_item_id');
    }

    public function getCategoryLabelAttribute(): string
    {
        return static::categoryLabel($this->category);
    }

    /**
     * Label legible de una categoría. Estático para poder usarse en columnas
     * de Filament, donde sólo se dispone del valor de la columna (no del modelo).
     */
    public static function categoryLabel(?string $category): string
    {
        return match ($category) {
            'resina_autonivelante' => __('mapscalculator.category_resina_autonivelante'),
            'resina_decorativa' => __('mapscalculator.category_resina_decorativa'),
            'resina_industrial' => __('mapscalculator.category_resina_industrial'),
            'preparacion_superficie' => __('mapscalculator.category_preparacion_superficie'),
            'imprimacion' => __('mapscalculator.category_imprimacion'),
            'zocalo_perimetral' => __('mapscalculator.category_zocalo_perimetral'),
            'mano_obra' => __('mapscalculator.category_mano_obra'),
            'traslado' => __('mapscalculator.category_traslado'),
            'otro' => __('mapscalculator.category_otro'),
            default => $category ?? '',
        };
    }

    public function getUnitLabelAttribute(): string
    {
        return match ($this->unit) {
            'm2' => 'm²',
            'ml' => 'ml',
            'unidad' => 'ud',
            'hora' => 'h',
            'bulto' => 'bulto',
            default => $this->unit,
        };
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->unit_price_m2, 2, ',', '.') . ' €/' . $this->unit_label;
    }

    public static function getActiveByCategory(): array
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category')
            ->map(fn ($items) => $items->mapWithKeys(fn ($item) => [$item->id => $item->name . ' (' . $item->formatted_price . ')']))
            ->toArray();
    }
}