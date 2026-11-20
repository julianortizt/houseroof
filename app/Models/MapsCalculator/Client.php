<?php

namespace App\Models\MapsCalculator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Client extends Model
{
    use SoftDeletes;

    protected $table = 'mapscalculator_clients';

    protected $guarded = [];

    protected $casts = [
        'portal_token' => 'string',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isMapscalculatorAdmin()) {
            return $query;
        }

        return $query->whereHas('projects', fn (Builder $projects) => $projects->visibleTo($user));
    }

    /**
     * Presupuestos del cliente (a través de sus proyectos). Se usan en la
     * ficha del CRM para mostrar el historial completo.
     */
    public function quotes(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Quote::class,
            Project::class,
            'client_id',
            'project_id',
            'id',
            'id'
        );
    }

    /**
     * Órdenes de trabajo del cliente (a través de sus proyectos).
     */
    public function workOrders(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            WorkOrder::class,
            Project::class,
            'client_id',
            'project_id',
            'id',
            'id'
        );
    }

    public function getFormattedAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address,
            $this->phone,
            $this->email,
        ]));
    }

    public function generatePortalToken(): string
    {
        $token = \Illuminate\Support\Str::random(40);
        $this->update(['portal_token' => $token]);
        return $token;
    }

    /**
     * Devuelve el token del portal, generándolo la primera vez.
     */
    public function ensurePortalToken(): string
    {
        return $this->portal_token ?: $this->generatePortalToken();
    }

    /**
     * La ruta real es /mapscalculator/portal/{token} (route: mapscalculator.client.portal).
     * Antes apuntaba a /portal/{token}, que no existe: el enlace generado daba 404.
     */
    public function getPortalUrlAttribute(): ?string
    {
        return $this->portal_token
            ? url('/mapscalculator/portal/' . $this->portal_token)
            : null;
    }

    public static function findByPortalToken(string $token): ?self
    {
        return static::where('portal_token', $token)->first();
    }
}