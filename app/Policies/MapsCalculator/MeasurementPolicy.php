<?php

namespace App\Policies\MapsCalculator;

use App\Models\MapsCalculator\Measurement;
use App\Models\MapsCalculator\Project;
use App\Models\User;

class MeasurementPolicy extends MapscalculatorPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->belongsToModule($user);
    }

    public function view(User $user, Measurement $measurement): bool
    {
        return $this->canReachProject($user, $measurement->project);
    }

    /** El editor se abre con el proyecto: se comprueba el proyecto. */
    public function createForProject(User $user, Project $project): bool
    {
        return $this->canReachProject($user, $project);
    }

    public function update(User $user, Measurement $measurement): bool
    {
        return $this->canReachProject($user, $measurement->project);
    }

    public function delete(User $user, Measurement $measurement): bool
    {
        return $this->canReachProject($user, $measurement->project);
    }
}
