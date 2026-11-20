<?php

namespace App\Policies\MapsCalculator;

use App\Models\MapsCalculator\WorkLog;
use App\Models\MapsCalculator\WorkOrder;
use App\Models\User;

class WorkOrderPolicy extends MapscalculatorPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->belongsToModule($user);
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        if ($user->isMapscalculatorAdmin()) {
            return true;
        }

        if ($user->isTecnico()) {
            return (int) $workOrder->assigned_to === (int) $user->id;
        }

        // El comercial ve las obras de sus proyectos.
        return $user->isComercial() && $this->canReachProject($user, $workOrder->project);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    /** El técnico asignado actualiza el estado y la bitácora de su obra. */
    public function update(User $user, WorkOrder $workOrder): bool
    {
        return $this->view($user, $workOrder);
    }

    public function viewReport(User $user, WorkOrder $workOrder): bool
    {
        return $this->view($user, $workOrder);
    }

    public function delete(User $user, WorkOrder $workOrder): bool
    {
        return $user->isMapscalculatorAdmin();
    }

    public function deleteLog(User $user, WorkLog $workLog): bool
    {
        return $this->view($user, $workLog->workOrder);
    }
}
