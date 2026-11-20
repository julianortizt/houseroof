<?php

namespace App\Models\MapsCalculator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class Project extends Model
{
    use SoftDeletes;

    protected $table = 'mapscalculator_projects';

    protected $guarded = [];

    protected $casts = [
        'scheduled_date' => 'date',
        'default_map_bounds' => 'array',
        'default_map_center' => 'array',
        'default_measurement_settings' => 'array',
        'is_premium' => 'boolean',
        'is_public' => 'boolean',
        'synced' => 'boolean',
        'last_synced_at' => 'datetime',
        'shared_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isMapscalculatorAdmin()) {
            return $query;
        }

        if ($user->isComercial()) {
            return $query->where('created_by', $user->id);
        }

        if ($user->isTecnico()) {
            return $query->where('assigned_to', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class)->orderBy('sort_order');
    }

    public function exteriorMeasurements(): HasMany
    {
        return $this->measurements()->where('type', 'exterior');
    }

    public function interiorMeasurements(): HasMany
    {
        return $this->measurements()->where('type', 'interior');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(\App\Models\Quote::class)->latest();
    }

    public function latestQuote(): HasOne
    {
        return $this->hasOne(\App\Models\Quote::class)->latestOfMany();
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

    public function syncLogs(): MorphMany
    {
        return $this->morphMany(SyncLog::class, 'syncable');
    }

    public function getTotalAreaAttribute(): float
    {
        return $this->measurements->sum('area_m2');
    }

    public function getTotalPerimeterAttribute(): float
    {
        return $this->measurements->sum('perimeter_m');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'nuevo' => __('mapscalculator.status_nuevo'),
            'medido' => __('mapscalculator.status_medido'),
            'cotizado' => __('mapscalculator.status_cotizado'),
            'aprobado' => __('mapscalculator.status_aprobado'),
            'rechazado' => __('mapscalculator.status_rechazado'),
            'agendada' => __('mapscalculator.status_agendada'),
            'en_preparacion' => __('mapscalculator.status_en_preparacion'),
            'en_ejecucion' => __('mapscalculator.status_en_ejecucion'),
            'finalizada' => __('mapscalculator.status_finalizada'),
            'entregada' => __('mapscalculator.status_entregada'),
            'cancelada' => __('mapscalculator.status_cancelada'),
            default => $this->status,
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'interior' => __('mapscalculator.type_interior'),
            'exterior' => __('mapscalculator.type_exterior'),
            'mixto' => __('mapscalculator.type_mixto'),
            default => $this->type,
        };
    }

    public function getDefaultMeasurementSettingsAttribute(): array
    {
        return $this->default_measurement_settings ?? [
            'default_map_layer' => $this->default_map_layer ?? 'pnoa',
            'default_map_bounds' => $this->default_map_bounds,
            'default_map_zoom' => $this->default_map_zoom,
            'default_map_center' => $this->default_map_center,
        ];
    }

    public function getShareUrlAttribute(): ?string
    {
        return $this->share_token ? url('/mapscalculator/portal/' . $this->share_token) : null;
    }

    public function getClientNameAttribute(): string
    {
        return $this->client?->name ?? __('mapscalculator.client') . ' no asignado';
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $transitions = [
            'nuevo' => ['medido', 'cotizado', 'cancelada'],
            'medido' => ['cotizado', 'nuevo', 'cancelada'],
            'cotizado' => ['aprobado', 'rechazado', 'medido'],
            'aprobado' => ['agendada', 'cotizado'],
            'rechazado' => ['cotizado', 'nuevo'],
            'agendada' => ['en_preparacion', 'cancelada'],
            'en_preparacion' => ['en_ejecucion', 'agendada'],
            'en_ejecucion' => ['finalizada', 'en_preparacion'],
            'finalizada' => ['entregada', 'en_ejecucion'],
            'entregada' => [],
            'cancelada' => ['nuevo'],
        ];

        return in_array($newStatus, $transitions[$this->status] ?? []);
    }
}