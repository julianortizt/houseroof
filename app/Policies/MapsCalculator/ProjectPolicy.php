<?php

namespace App\Policies\MapsCalculator;

use App\Models\MapsCalculator\Project;
use App\Models\User;

class ProjectPolicy extends MapscalculatorPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->belongsToModule($user);
    }

    public function view(User $user, Project $project): bool
    {
        return $this->canReachProject($user, $project);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Project $project): bool
    {
        // El técnico también puede actualizar el estado de sus obras asignadas.
        return $this->canManage($user) && $this->canReachProject($user, $project)
            || ($user->isTecnico() && $this->canReachProject($user, $project));
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isMapscalculatorAdmin();
    }
}
