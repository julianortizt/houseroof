<?php

namespace App\Livewire\MapsCalculator;

use App\Models\MapsCalculator\Project;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class SyncModal extends Component
{
    public bool $showSyncModal = false;
    public bool $syncing = false;
    public ?int $projectId = null;

    /** Mediciones del proyecto, ya formateadas para el listado del modal. */
    public array $measurements = [];

    protected $listeners = [
        'syncStarted' => 'startSync',
    ];

    public function mount(?int $projectId = null): void
    {
        $this->projectId = $projectId;
        $this->loadSyncStatus();
    }

    public function startSync(array $data = []): void
    {
        $this->projectId = $data['projectId'] ?? $this->projectId;

        if ($this->projectId) {
            $project = Project::findOrFail($this->projectId);
            Gate::authorize('view', $project);
        }

        $this->showSyncModal = true;
        $this->syncing = false;
        $this->loadSyncStatus();
        $this->dispatch('notify', message: __('mapscalculator.sync_unavailable'), type: 'warning');
    }

    public function loadSyncStatus(): void
    {
        $this->measurements = [];

        if (! $this->projectId) {
            return;
        }

        $project = Project::findOrFail($this->projectId);
        Gate::authorize('view', $project);

        $this->measurements = $project->measurements()
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'label' => $m->label ?: ('#' . $m->id),
            ])
            ->all();
    }

    public function render()
    {
        return view('livewire.mapscalculator.sync-modal');
    }
}