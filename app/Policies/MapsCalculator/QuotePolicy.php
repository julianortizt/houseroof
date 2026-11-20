<?php

namespace App\Policies\MapsCalculator;

use App\Models\Quote;
use App\Models\User;

class QuotePolicy extends MapscalculatorPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->belongsToModule($user);
    }

    public function view(User $user, Quote $quote): bool
    {
        return $this->canReachProject($user, $quote->project);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Quote $quote): bool
    {
        return $this->canManage($user) && $this->canReachProject($user, $quote->project);
    }

    public function send(User $user, Quote $quote): bool
    {
        return $this->update($user, $quote);
    }

    public function approve(User $user, Quote $quote): bool
    {
        return $this->update($user, $quote);
    }

    public function reject(User $user, Quote $quote): bool
    {
        return $this->update($user, $quote);
    }

    public function delete(User $user, Quote $quote): bool
    {
        return $user->isMapscalculatorAdmin();
    }
}
