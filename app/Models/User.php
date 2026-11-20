<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\MapsCalculator\Project;
use App\Models\MapsCalculator\WorkOrder;
use App\Models\MapsCalculator\Subscription;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'mapscalculator_role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mapscalculator_role' => 'string',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'super-panel') {
            $developerEmail = config('platform.developer_email');

            return filled($developerEmail) && $this->email === $developerEmail;
        }

        return $this->hasRole(['super_admin', 'admin', 'editor', 'manager', 'site_admin']);
    }

    public function isMapscalculatorAdmin(): bool
    {
        return $this->mapscalculator_role === 'admin' || $this->hasRole('super_admin');
    }

    public function isComercial(): bool
    {
        return $this->mapscalculator_role === 'comercial';
    }

    public function isTecnico(): bool
    {
        return $this->mapscalculator_role === 'tecnico';
    }

    public function getMapscalculatorRoleLabelAttribute(): string
    {
        return match ($this->mapscalculator_role) {
            'admin' => 'Administrador',
            'comercial' => 'Comercial',
            'tecnico' => 'Técnico',
            default => 'Comercial',
        };
    }

    public function assignedProjects()
    {
        return Project::where('assigned_to', $this->id);
    }

    public function assignedWorkOrders()
    {
        return WorkOrder::where('assigned_to', $this->id);
    }

    public function createdProjects()
    {
        return Project::where('created_by', $this->id);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }

    public function isPremium(): bool
    {
        return $this->hasActiveSubscription() && $this->subscription->plan === 'premium';
    }
}