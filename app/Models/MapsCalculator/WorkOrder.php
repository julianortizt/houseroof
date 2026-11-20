<?php

namespace App\Models\MapsCalculator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class WorkOrder extends Model
{
    protected $table = 'mapscalculator_work_orders';

    protected $guarded = [];

    protected $casts = [
        'scheduled_start' => 'date',
        'scheduled_end' => 'date',
        'actual_start' => 'date',
        'actual_end' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isMapscalculatorAdmin()) {
            return $query;
        }

        if ($user->isComercial()) {
            return $query->whereHas('project', fn (Builder $project) => $project->where('created_by', $user->id));
        }

        if ($user->isTecnico()) {
            return $query->where('assigned_to', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Quote::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WorkLog::class)->orderBy('date');
    }

    public function latestLog(): HasOne
    {
        return $this->hasOne(WorkLog::class)->latestOfMany('date');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(\App\Models\InventoryMovement::class)->orderByDesc('created_at');
    }

    public function inventoryReservations(): HasMany
    {
        return $this->hasMany(\App\Models\InventoryReservation::class)->orderByDesc('created_at');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'agendada' => __('mapscalculator.status_agendada'),
            'en_preparacion' => __('mapscalculator.status_en_preparacion'),
            'en_ejecucion' => __('mapscalculator.status_en_ejecucion'),
            'finalizada' => __('mapscalculator.status_finalizada'),
            'entregada' => __('mapscalculator.status_entregada'),
            'cancelada' => __('mapscalculator.status_cancelada'),
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'agendada' => 'info',
            'en_preparacion' => 'warning',
            'en_ejecucion' => 'primary',
            'finalizada' => 'success',
            'entregada' => 'gray',
            'cancelada' => 'danger',
            default => 'gray',
        };
    }

    public function getProgressAttribute(): int
    {
        return match ($this->status) {
            'agendada' => 10,
            'en_preparacion' => 30,
            'en_ejecucion' => 60,
            'finalizada' => 90,
            'entregada' => 100,
            'cancelada' => 0,
            default => 0,
        };
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $transitions = [
            'agendada' => ['en_preparacion', 'cancelada'],
            'en_preparacion' => ['en_ejecucion', 'agendada', 'cancelada'],
            'en_ejecucion' => ['finalizada', 'en_preparacion', 'cancelada'],
            'finalizada' => ['entregada', 'en_ejecucion'],
            'entregada' => [],
            'cancelada' => ['agendada'],
        ];

        return in_array($newStatus, $transitions[$this->status] ?? [], true);
    }

    public function transitionTo(string $newStatus): bool
    {
        if (! $this->canTransitionTo($newStatus)) {
            return false;
        }

        $this->status = $newStatus;
        if ($newStatus === 'en_ejecucion' && $this->actual_start === null) {
            $this->actual_start = now()->toDateString();
        }
        if ($newStatus === 'finalizada' && $this->actual_end === null) {
            $this->actual_end = now()->toDateString();
        }
        $this->save();

        return true;
    }
}