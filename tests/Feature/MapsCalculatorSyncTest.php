<?php

namespace Tests\Feature;

use App\Livewire\MapsCalculator\SyncModal;
use App\Models\MapsCalculator\Client;
use App\Models\MapsCalculator\Measurement;
use App\Models\MapsCalculator\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MapsCalculatorSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_unconfigured_sync_does_not_mark_project_or_measurements_as_synced(): void
    {
        $user = User::create([
            'name' => 'Maps Admin',
            'email' => 'maps-admin@example.test',
            'password' => 'password',
            'mapscalculator_role' => 'admin',
        ]);
        $client = Client::create(['name' => 'Example Client']);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Example Project',
            'type' => 'exterior',
            'created_by' => $user->id,
        ]);
        $measurement = Measurement::create([
            'project_id' => $project->id,
            'type' => 'exterior',
            'label' => 'Driveway',
        ]);

        Livewire::actingAs($user)
            ->test(SyncModal::class, ['projectId' => $project->id])
            ->call('startSync')
            ->assertSet('showSyncModal', true)
            ->assertDispatched('notify');

        $this->assertFalse((bool) $project->fresh()->synced);
        $this->assertNull($project->fresh()->last_synced_at);
        $this->assertFalse((bool) $measurement->fresh()->synced);
        $this->assertNull($measurement->fresh()->last_synced_at);
    }
}
