<?php

namespace App\Policies\MapsCalculator;

use App\Models\MapsCalculator\Project;
use App\Models\User;

/**
 * Base común de las policies del módulo MapsCalculator.
 *
 * Roles (según el prompt): admin (todo), comercial (crea clientes, proyectos,
 * mediciones y presupuestos) y técnico (sólo sus obras asignadas).
 */
abstract class MapscalculatorPolicy
{
    /** ¿El usuario pertenece al módulo? */
    protected function belongsToModule(User $user): bool
    {
        return $user->isMapscalculatorAdmin() || $user->isComercial() || $user->isTecnico();
    }

    /** ¿Puede ver/actuar sobre este proyecto? */
    protected function canReachProject(User $user, ?Project $project): bool
    {
        if (! $project) {
            return false;
        }

        if ($user->isMapscalculatorAdmin()) {
            return true;
        }

        if ($user->isComercial()) {
            return (int) $project->created_by === (int) $user->id;
        }

        if ($user->isTecnico()) {
            return (int) $project->assigned_to === (int) $user->id;
        }

        return false;
    }

    /**
     * Comerciales y administradores pueden crear y modificar; los técnicos sólo
     * consultan y actualizan el avance de sus obras.
     */
    protected function canManage(User $user): bool
    {
        return $user->isMapscalculatorAdmin() || $user->isComercial();
    }
}
