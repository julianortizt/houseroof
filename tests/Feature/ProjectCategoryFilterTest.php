<?php

namespace Tests\Feature;

use App\Filament\Resources\ProjectResource\Pages\EditProject;
use App\Filament\Resources\ProjectResource\Pages\ListProjects;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::findOrCreate('super_admin', 'web');

        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@costaresin.test',
            'password' => 'password',
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function project(array $attributes = []): Project
    {
        return Project::create(array_merge([
            'slug' => 'proyecto-' . uniqid(),
            'title' => ['en' => 'Maps measurement', 'es' => 'Medición de mapas'],
            'location' => ['en' => 'Madrid', 'es' => 'Madrid'],
            'category' => 'planning',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ], $attributes));
    }

    public function test_super_admin_can_view_projects_index(): void
    {
        $user = $this->adminUser();
        $this->project(['slug' => 'proyecto-admin', 'title' => ['en' => 'Admin visible project', 'es' => 'Proyecto visible admin']]);

        $this->actingAs($user)
            ->withSession(['admin_locale' => 'es'])
            ->get('/admin/projects')
            ->assertOk()
            ->assertSee('Proyecto visible admin');
    }

    public function test_projects_table_can_be_filtered_by_category(): void
    {
        $user = $this->adminUser();
        $planning = $this->project([
            'slug' => 'planning-project',
            'title' => ['en' => 'Planning project', 'es' => 'Proyecto de planificación'],
            'category' => 'planning',
        ]);
        $delivery = $this->project([
            'slug' => 'delivery-project',
            'title' => ['en' => 'Delivery project', 'es' => 'Proyecto de ejecución'],
            'category' => 'delivery',
        ]);

        Livewire::actingAs($user)
            ->test(ListProjects::class)
            ->assertTableFilterExists('category')
            ->filterTable('category', 'planning')
            ->assertCanSeeTableRecords([$planning])
            ->assertCanNotSeeTableRecords([$delivery])
            ->filterTable('category', 'delivery')
            ->assertCanSeeTableRecords([$delivery])
            ->assertCanNotSeeTableRecords([$planning]);
    }

    public function test_projects_table_search_filters_by_title(): void
    {
        $user = $this->adminUser();
        $matching = $this->project([
            'slug' => 'maps-measurement',
            'title' => ['en' => 'Maps measurement', 'es' => 'Medición de mapas'],
            'category' => 'planning',
        ]);
        $other = $this->project([
            'slug' => 'resin-coating',
            'title' => ['en' => 'Resin coating', 'es' => 'Revestimiento de resina'],
            'category' => 'delivery',
        ]);

        Livewire::actingAs($user)
            ->test(ListProjects::class)
            ->searchTable('Maps measurement')
            ->assertCanSeeTableRecords([$matching])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_project_category_can_be_edited_via_edit_page(): void
    {
        $user = $this->adminUser();
        $project = $this->project(['slug' => 'editable-project', 'category' => 'planning']);

        Livewire::actingAs($user)
            ->test(EditProject::class, ['record' => $project->slug])
            ->fillForm([
                'category' => 'delivery',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'category' => 'delivery']);
    }

    public function test_assigned_category_is_shown_in_admin_listing(): void
    {
        $user = $this->adminUser();
        $project = $this->project([
            'slug' => 'listed-project',
            'title' => ['en' => 'Listed project', 'es' => 'Proyecto listado'],
            'category' => 'delivery',
        ]);

        Livewire::actingAs($user)
            ->test(ListProjects::class)
            ->assertTableColumnFormattedStateSet('category', 'Work execution', $project);

        // The listing page also renders the formatted category label.
        $this->actingAs($user)
            ->withSession(['admin_locale' => 'es'])
            ->get('/admin/projects')
            ->assertOk()
            ->assertSee('Ejecución del trabajo');
    }

    public function test_assigned_category_is_shown_on_public_detail_page(): void
    {
        $project = $this->project([
            'slug' => 'public-project',
            'title' => ['en' => 'Public project', 'es' => 'Proyecto público'],
            'category' => 'service',
        ]);

        $this->get('/proyectos/' . $project->slug . '?lang=es')
            ->assertOk()
            ->assertSee('Gestión del servicio');
    }
}
