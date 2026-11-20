<?php

namespace Tests\Feature;

use App\Livewire\MapsCalculator\WorkOrder as WorkOrderComponent;
use App\Models\MapsCalculator\Client;
use App\Models\MapsCalculator\Project;
use App\Models\MapsCalculator\WorkLog;
use App\Models\MapsCalculator\WorkOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class WorkOrderCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_offline_logs_sync_idempotently_to_the_authorized_work_order(): void
    {
        [$user, $project, $order] = $this->createWorkOrder();
        $payload = [
            'logs' => [[
                'client_uuid' => (string) Str::uuid(),
                'date' => now()->toDateString(),
                'type' => 'durante',
                'description' => 'Trabajo realizado sin conexión',
            ]],
        ];

        $this->actingAs($user)
            ->postJson(route('mapscalculator.work-order.sync-logs', $order), $payload)
            ->assertOk()
            ->assertJson(['synced' => [$payload['logs'][0]['client_uuid']]]);

        $this->postJson(route('mapscalculator.work-order.sync-logs', $order), $payload)
            ->assertOk()
            ->assertJson(['synced' => [$payload['logs'][0]['client_uuid']]]);

        $this->assertSame(1, WorkLog::query()->where('client_uuid', $payload['logs'][0]['client_uuid'])->count());
        $this->assertSame($order->id, WorkLog::query()->first()->work_order_id);
    }

    public function test_offline_log_cannot_be_replayed_for_another_order(): void
    {
        [$user, , $firstOrder] = $this->createWorkOrder();
        [, , $secondOrder] = $this->createWorkOrder($user);
        $payload = [
            'logs' => [[
                'client_uuid' => (string) Str::uuid(),
                'date' => now()->toDateString(),
                'type' => 'general',
                'description' => 'Apunte local',
            ]],
        ];

        $this->actingAs($user)
            ->postJson(route('mapscalculator.work-order.sync-logs', $firstOrder), $payload)
            ->assertOk();

        $this->postJson(route('mapscalculator.work-order.sync-logs', $secondOrder), $payload)
            ->assertConflict();

        $this->assertSame(1, WorkLog::query()->count());
    }

    public function test_status_transitions_record_actual_start_and_completion_dates(): void
    {
        [$user, $project, $order] = $this->createWorkOrder();

        Livewire::actingAs($user)
            ->test(WorkOrderComponent::class, ['workOrder' => $order, 'project' => $project])
            ->call('updateStatus', 'en_preparacion')
            ->call('updateStatus', 'en_ejecucion')
            ->assertHasNoErrors();

        $this->assertSame(now()->toDateString(), $order->fresh()->actual_start->toDateString());
        $this->assertSame('en_ejecucion', $project->fresh()->status);

        Livewire::actingAs($user)
            ->test(WorkOrderComponent::class, ['workOrder' => $order->fresh(), 'project' => $project->fresh()])
            ->call('updateStatus', 'finalizada')
            ->assertHasNoErrors();

        $this->assertSame(now()->toDateString(), $order->fresh()->actual_end->toDateString());
        $this->assertSame('finalizada', $project->fresh()->status);
    }

    public function test_administrator_can_record_nonnegative_work_order_costs(): void
    {
        [$user, $project, $order] = $this->createWorkOrder();

        Livewire::actingAs($user)
            ->test(WorkOrderComponent::class, ['workOrder' => $order, 'project' => $project])
            ->set('laborCost', '125.50')
            ->set('otherCost', '20')
            ->set('costNotes', 'Desplazamiento')
            ->call('updateCosts')
            ->assertHasNoErrors();

        $this->assertSame(125.5, (float) $order->fresh()->labor_cost);
        $this->assertSame(20.0, (float) $order->fresh()->other_cost);
        $this->assertSame('Desplazamiento', $order->fresh()->cost_notes);
    }

    private function createWorkOrder(?User $user = null): array
    {
        $user ??= User::create([
            'name' => 'Maps Admin',
            'email' => 'maps-admin-' . Str::uuid() . '@example.test',
            'password' => 'password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);
        $client = Client::create(['name' => 'Example Client']);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Example Project',
            'type' => 'exterior',
            'created_by' => $user->id,
        ]);
        $order = WorkOrder::create([
            'project_id' => $project->id,
            'status' => 'agendada',
        ]);

        return [$user, $project, $order];
    }
}
