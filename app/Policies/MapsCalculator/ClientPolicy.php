<?php

namespace App\Policies\MapsCalculator;

use App\Models\MapsCalculator\Client;
use App\Models\MapsCalculator\Project;
use App\Models\User;

class ClientPolicy extends MapscalculatorPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->belongsToModule($user);
    }

    public function view(User $user, Client $client): bool
    {
        if ($user->isMapscalculatorAdmin()) {
            return true;
        }

        return $this->belongsToModule($user)
            && $client->projects()->visibleTo($user)->exists();
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Client $client): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Client $client): bool
    {
        if (! $user->isMapscalculatorAdmin()) {
            return false;
        }

        // Sin cliente no hay proyecto: no se borra si todavía tiene obras vivas.
        return ! $client->projects()->exists();
    }

    public function createProjectFor(User $user, Client $client): bool
    {
        return $this->canManage($user) && $this->view($user, $client);
    }

    public function viewProject(User $user, Client $client, Project $project): bool
    {
        return $this->belongsToModule($user);
    }
}
