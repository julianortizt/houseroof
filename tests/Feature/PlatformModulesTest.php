<?php

namespace Tests\Feature;

use App\Services\PlatformModules;
use App\Filament\Pages\SuperPanel\PlatformAppearance;
use App\Services\InventoryService;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\InventoryMovement;
use App\Models\Page;
use App\Models\Project;
use App\Models\MapsCalculator\Client as MapClient;
use App\Models\MapsCalculator\Project as MapProject;
use App\Models\MapsCalculator\WorkOrder as MapWorkOrder;
use App\Livewire\MapsCalculator\WorkOrder as WorkOrderComponent;
use App\Models\Quote;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlatformModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_catalog_is_available_by_default_and_can_be_disabled(): void
    {
        $modules = app(PlatformModules::class);

        $this->assertTrue($modules->enabled('products'));
        $this->get('/tienda')->assertOk();
        $this->get('/api/v1/products')->assertOk();
        $this->get('/?lang=es')
            ->assertOk()
            ->assertSee(route('shop.index'))
            ->assertSee('Ver productos');

        $modules->setEnabled('products', false);

        $this->assertFalse($modules->enabled('products'));
        $this->get('/tienda')->assertNotFound();
        $this->get('/api/v1/products')->assertNotFound();

        $modules->setEnabled('products', true);
        $this->assertTrue($modules->enabled('products'));
    }

    public function test_checkout_stays_blocked_until_products_and_payments_are_both_enabled(): void
    {
        app(PlatformModules::class)->setEnabled('products', false);
        Livewire::test(\App\Livewire\Checkout::class)
            ->call('placeOrder')
            ->assertNotFound();

        app(PlatformModules::class)->setEnabled('products', true);
        Livewire::test(\App\Livewire\Checkout::class)
            ->call('placeOrder')
            ->assertNotFound();

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_module_registry_has_safe_defaults_and_enforces_dependencies(): void
    {
        $modules = app(PlatformModules::class);

        $this->assertTrue($modules->enabled('products'));
        $this->assertTrue($modules->enabled('inventory'));
        $this->assertTrue($modules->enabled('invoicing'));
        $this->assertTrue($modules->enabled('accounting'));
        $this->assertFalse($modules->enabled('payments'));
        $this->assertTrue($modules->enabled('quotes'));
        $this->assertTrue($modules->enabled('work_orders'));
        $this->assertTrue($modules->enabled('simulator'));
        $this->assertTrue($modules->enabled('administration'));
        $this->assertTrue($modules->enabled('customer_portal'));

        try {
            $modules->setEnabled('quotes', false);
            $this->fail('Quotes cannot be disabled while work orders depend on them.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('moduleSettings.quotes', $exception->errors());
        }

        $modules->setEnabledModules([
            'quotes' => false,
            'work_orders' => false,
            'invoicing' => false,
            'accounting' => false,
        ]);

        $this->assertFalse($modules->enabled('quotes'));
        $this->assertFalse($modules->enabled('work_orders'));
        $this->assertFalse($modules->enabled('invoicing'));
        $this->assertFalse($modules->enabled('accounting'));
    }

    public function test_admin_roles_receive_the_full_commercial_module_permission_set(): void
    {
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $siteAdmin = Role::firstOrCreate(['name' => 'site_admin', 'guard_name' => 'web']);

        $this->assertTrue($admin->hasPermissionTo('manage invoicing'));
        $this->assertTrue($admin->hasPermissionTo('manage accounting'));
        $this->assertTrue($admin->hasPermissionTo('manage payments'));
        $this->assertTrue($admin->hasPermissionTo('manage work orders'));
        $this->assertTrue($admin->hasPermissionTo('manage simulator'));

        $this->assertTrue($manager->hasPermissionTo('manage invoicing'));
        $this->assertTrue($manager->hasPermissionTo('manage accounting'));
        $this->assertTrue($manager->hasPermissionTo('manage payments'));
        $this->assertTrue($manager->hasPermissionTo('manage customer portal'));

        $this->assertTrue($siteAdmin->hasPermissionTo('manage work orders'));
        $this->assertTrue($siteAdmin->hasPermissionTo('manage quotes'));
    }

    public function test_appearance_preferences_are_reflected_in_the_public_site(): void
    {
        foreach (array_keys(config('platform.styles')) as $style) {
            foreach (array_keys(config('platform.home_templates')) as $template) {
                Setting::set('platform_style', $style);
                Setting::set('homepage_template', $template);

                $this->get('/')
                    ->assertOk()
                    ->assertSee('data-platform-style="' . $style . '"', false)
                    ->assertSee('data-home-template="' . $template . '"', false);
            }
        }
    }

    public function test_homepage_seo_metadata_is_generic_for_every_supported_locale(): void
    {
        Setting::query()->whereIn('key', ['seo_description', 'seo_keywords'])->delete();

        $descriptions = [
            'en' => 'Resin flooring installation, construction work and products for projects in Malaga and Costa Blanca. Tell us what you need.',
            'es' => 'Instalación de pavimentos de resina, trabajos de construcción y venta de productos para proyectos en Málaga y Costa Blanca. Cuéntanos qué necesitas.',
            'de' => 'Verlegung von Kunstharzböden, Bauarbeiten und Produkte für Projekte in Málaga und an der Costa Blanca. Teilen Sie uns mit, was Sie benötigen.',
            'fr' => 'Pose de sols en résine, travaux de construction et vente de produits pour vos projets à Málaga et sur la Costa Blanca.',
            'it' => 'Installazione di pavimenti in resina, lavori di costruzione e vendita di prodotti per progetti a Málaga e sulla Costa Blanca.',
            'nl' => 'Installatie van kunstharsvloeren, bouwwerkzaamheden en productverkoop voor projecten in Málaga en aan de Costa Blanca.',
            'pt' => 'Instalação de pavimentos de resina, trabalhos de construção e venda de produtos para projetos em Málaga e na Costa Blanca.',
        ];

        foreach ($descriptions as $locale => $description) {
            $response = $this->get('/?lang=' . $locale)->assertOk();
            $content = $response->getContent();
            preg_match('/<meta name="description" content="([^"]*)"/', $content, $matches);

            $this->assertSame($description, html_entity_decode($matches[1] ?? '', ENT_QUOTES, 'UTF-8'));
        }
    }

    public function test_developer_can_save_appearance_and_preferences_persist(): void
    {
        config(['platform.developer_email' => 'developer@example.com']);
        $developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'temporary-password',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('super-panel'));

        Livewire::actingAs($developer)
            ->test(PlatformAppearance::class)
            ->set('siteStyle', 'minimal')
            ->set('homeTemplate', 'services')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('minimal', Setting::get('platform_style'));
        $this->assertSame('services', Setting::get('homepage_template'));

        $this->get('/')
            ->assertOk()
            ->assertSee('data-platform-style="minimal"', false)
            ->assertSee('data-home-template="services"', false);
    }

    public function test_appearance_is_rejected_outside_the_developers_super_panel(): void
    {
        config(['platform.developer_email' => 'developer@example.com']);
        $administrator = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => 'temporary-password',
        ]);

        $this->actingAs($administrator)
            ->get('/super-panel/appearance')
            ->assertForbidden();

        config(['platform.developer_email' => $administrator->email]);
        $this->actingAs($administrator)
            ->get('/admin/appearance')
            ->assertForbidden();
    }

    public function test_developer_can_find_homepage_template_controls_in_the_super_panel(): void
    {
        config(['platform.developer_email' => 'developer@example.com']);
        $developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'temporary-password',
        ]);

        $this->actingAs($developer)
            ->get('/super-panel/appearance')
            ->assertOk()
            ->assertSee('Cambiar plantilla de la portada')
            ->assertSee('Portafolio primero')
            ->assertSee('Plataforma corporativa')
            ->assertSee('Guardar plantilla y estilo');

        $this->actingAs($developer)
            ->get('/super-panel/modules')
            ->assertOk()
            ->assertSee('Inventory')
            ->assertSee('Invoicing')
            ->assertSee('Accounting')
            ->assertSee('Save module settings');
    }

    public function test_simulator_page_and_api_are_admin_only_and_respect_module_availability(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('href="' . route('simulator.index') . '"', false);
        $this->get('/simulador')->assertRedirect('/login');
        $this->get('/api/simulador/finishes')->assertRedirect('/login');

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'comercial',
        ]);
        $administrator->assignRole('admin');

        $this->actingAs($administrator)
            ->get('/simulador')
            ->assertOk();

        app(PlatformModules::class)->setEnabled('simulator', false);

        $this->actingAs($administrator)
            ->get('/simulador')
            ->assertNotFound();

        $this->actingAs($administrator)
            ->get('/api/simulador/finishes')
            ->assertNotFound();
    }

    public function test_work_order_routes_are_blocked_when_the_module_is_disabled(): void
    {
        $administrator = User::create([
            'name' => 'Maps Administrator',
            'email' => 'maps-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);

        $this->actingAs($administrator)
            ->get('/mapscalculator/proximas-obras')
            ->assertOk();

        app(PlatformModules::class)->setEnabled('work_orders', false);

        $this->actingAs($administrator)
            ->get('/mapscalculator/proximas-obras')
            ->assertNotFound();
    }

    public function test_upcoming_work_orders_can_be_filtered_by_assignee_period_and_project_search(): void
    {
        $administrator = User::create([
            'name' => 'Maps Administrator',
            'email' => 'maps-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);
        $technician = User::create([
            'name' => 'Technician',
            'email' => 'technician@example.com',
            'password' => 'temporary-password',
            'mapscalculator_role' => 'tecnico',
        ]);
        $client = MapClient::create(['name' => 'Filterable client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'Filterable project',
            'type' => 'interior',
            'status' => 'agendada',
        ]);
        MapWorkOrder::create([
            'project_id' => $project->id,
            'status' => 'agendada',
            'assigned_to' => $technician->id,
            'scheduled_start' => now()->addDays(2)->toDateString(),
        ]);

        $otherClient = MapClient::create(['name' => 'Other client']);
        $otherProject = MapProject::create([
            'client_id' => $otherClient->id,
            'name' => 'Other project',
            'type' => 'interior',
            'status' => 'agendada',
        ]);
        MapWorkOrder::create([
            'project_id' => $otherProject->id,
            'status' => 'agendada',
            'assigned_to' => $technician->id,
            'scheduled_start' => now()->addDays(10)->toDateString(),
        ]);

        $this->actingAs($administrator)
            ->get('/mapscalculator/proximas-obras?' . http_build_query([
                'assigned_to' => $technician->id,
                'from' => now()->addDay()->toDateString(),
                'to' => now()->addDays(5)->toDateString(),
                'q' => 'Filterable',
            ]))
            ->assertOk()
            ->assertSee('Filterable project')
            ->assertDontSee('Other project');
    }

    public function test_commercial_users_only_see_their_clients_projects_quotes_and_work_orders(): void
    {
        $commercial = User::create([
            'name' => 'Commercial',
            'email' => 'commercial@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'comercial',
        ]);
        $otherCommercial = User::create([
            'name' => 'Other Commercial',
            'email' => 'other-commercial@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'comercial',
        ]);
        $administrator = User::create([
            'name' => 'Maps Administrator',
            'email' => 'maps-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);

        $sharedClient = MapClient::create(['name' => 'Shared client']);
        $ownedProject = MapProject::create([
            'client_id' => $sharedClient->id,
            'name' => 'Commercial project',
            'type' => 'interior',
            'created_by' => $commercial->id,
        ]);
        $foreignProject = MapProject::create([
            'client_id' => $sharedClient->id,
            'name' => 'Private foreign project',
            'type' => 'interior',
            'created_by' => $otherCommercial->id,
        ]);
        $ownedQuote = Quote::create([
            'project_id' => $ownedProject->id,
            'name' => 'Commercial quote',
            'email' => 'commercial@example.com',
            'service_type' => 'resin',
            'total' => 101,
        ]);
        Quote::create([
            'project_id' => $foreignProject->id,
            'name' => 'Foreign quote',
            'email' => 'other-commercial@example.com',
            'service_type' => 'resin',
            'total' => 999,
        ]);
        MapWorkOrder::create([
            'project_id' => $ownedProject->id,
            'status' => 'finalizada',
        ]);
        MapWorkOrder::create([
            'project_id' => $foreignProject->id,
            'status' => 'finalizada',
        ]);

        $foreignClient = MapClient::create(['name' => 'Private foreign client']);
        MapProject::create([
            'client_id' => $foreignClient->id,
            'name' => 'Another private project',
            'type' => 'interior',
            'created_by' => $otherCommercial->id,
        ]);

        $this->actingAs($commercial)
            ->get('/mapscalculator/clientes')
            ->assertOk()
            ->assertSee('Shared client')
            ->assertDontSee('Private foreign client');

        $this->get(route('mapscalculator.client.show', $foreignClient))
            ->assertForbidden();

        $this->get(route('mapscalculator.client.show', $sharedClient))
            ->assertOk()
            ->assertSee('Commercial project')
            ->assertDontSee('Private foreign project')
            ->assertSee($ownedQuote->formatted_total)
            ->assertDontSee('999,00 €')
            ->assertSee('1/1')
            ->assertDontSee('2/2');

        $this->actingAs($administrator)
            ->get('/mapscalculator/clientes')
            ->assertOk()
            ->assertSee('Private foreign client');

        $this->get(route('mapscalculator.client.show', $sharedClient))
            ->assertOk()
            ->assertSee('Private foreign project')
            ->assertSee('999,00 €')
            ->assertSee('2/2');
    }

    public function test_upcoming_work_orders_are_limited_to_technician_assignments(): void
    {
        $technician = User::create([
            'name' => 'Assigned Technician',
            'email' => 'assigned-technician@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'tecnico',
        ]);
        $otherTechnician = User::create([
            'name' => 'Other Technician',
            'email' => 'other-technician@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'tecnico',
        ]);
        $administrator = User::create([
            'name' => 'Maps Administrator',
            'email' => 'maps-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);

        $workOrders = [];
        foreach ([
            'Assigned project' => $technician->id,
            'Other technician project' => $otherTechnician->id,
            'Unassigned project' => null,
        ] as $projectName => $assigneeId) {
            $client = MapClient::create(['name' => $projectName . ' client']);
            $project = MapProject::create([
                'client_id' => $client->id,
                'name' => $projectName,
                'type' => 'interior',
                'assigned_to' => $technician->id,
            ]);
            $workOrders[$projectName] = MapWorkOrder::create([
                'project_id' => $project->id,
                'status' => 'agendada',
                'assigned_to' => $assigneeId,
            ]);
        }

        $this->actingAs($technician)
            ->get('/mapscalculator/proximas-obras')
            ->assertOk()
            ->assertSee('Assigned project')
            ->assertDontSee('Other technician project')
            ->assertDontSee('Unassigned project');

        $this->get('/mapscalculator/proximas-obras?assigned_to=' . $otherTechnician->id)
            ->assertOk()
            ->assertDontSee('Other technician project')
            ->assertDontSee('Unassigned project');

        $this->get(route('mapscalculator.work-order.show', $workOrders['Assigned project']))
            ->assertOk();
        $this->get(route('mapscalculator.work-order.show', $workOrders['Unassigned project']))
            ->assertForbidden();

        $this->actingAs($administrator)
            ->get('/mapscalculator/proximas-obras')
            ->assertOk()
            ->assertSee('Assigned project')
            ->assertSee('Other technician project')
            ->assertSee('Unassigned project');
    }

    public function test_approved_quote_can_create_only_one_work_order(): void
    {
        $administrator = User::create([
            'name' => 'Maps Administrator',
            'email' => 'maps-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);
        $client = MapClient::create(['name' => 'Test client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'Approved project',
            'type' => 'interior',
            'status' => 'cotizado',
            'created_by' => $administrator->id,
        ]);
        $quote = Quote::create([
            'project_id' => $project->id,
            'name' => $client->name,
            'email' => null,
            'service_type' => 'resin',
            'status' => 'enviado',
            'is_admin_created' => true,
            'subtotal' => 100,
            'tax_percent' => 21,
            'tax_amount' => 21,
            'total' => 121,
        ]);

        Livewire::actingAs($administrator)
            ->test(\App\Livewire\MapsCalculator\MapulatorProject::class, ['project' => $project])
            ->call('createWorkOrder')
            ->assertHasErrors('quote');

        $quote->update(['status' => 'aprobado']);

        Livewire::actingAs($administrator)
            ->test(\App\Livewire\MapsCalculator\MapulatorProject::class, ['project' => $project])
            ->call('createWorkOrder')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mapscalculator_work_orders', [
            'project_id' => $project->id,
            'quote_id' => $quote->id,
            'status' => 'agendada',
        ]);
        $this->assertSame('agendada', $project->fresh()->status);

        Livewire::actingAs($administrator)
            ->test(\App\Livewire\MapsCalculator\MapulatorProject::class, ['project' => $project->fresh()])
            ->call('createWorkOrder')
            ->assertHasErrors('work_order');

        $this->assertSame(1, MapWorkOrder::where('project_id', $project->id)->count());
    }

    public function test_http_work_order_creation_requires_its_own_approved_quote_and_prevents_duplicates(): void
    {
        $administrator = User::create([
            'name' => 'Maps Administrator',
            'email' => 'maps-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);
        $client = MapClient::create(['name' => 'Test client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'HTTP create project',
            'type' => 'interior',
            'status' => 'cotizado',
            'created_by' => $administrator->id,
        ]);
        $quote = Quote::create([
            'project_id' => $project->id,
            'name' => $client->name,
            'email' => null,
            'service_type' => 'resin',
            'status' => 'enviado',
        ]);

        $this->actingAs($administrator)
            ->from('/mapscalculator')
            ->post(route('mapscalculator.work-order.store'), [
                'project_id' => $project->id,
                'quote_id' => $quote->id,
            ])
            ->assertSessionHasErrors('quote_id');

        $quote->update(['status' => 'aprobado']);
        $this->actingAs($administrator)
            ->post(route('mapscalculator.work-order.store'), [
                'project_id' => $project->id,
                'quote_id' => $quote->id,
                'assigned_to' => $administrator->id,
            ])
            ->assertRedirect(route('mapscalculator.work-order.show', MapWorkOrder::first()));

        $this->assertSame(1, MapWorkOrder::where('project_id', $project->id)->count());

        $this->actingAs($administrator)
            ->from('/mapscalculator')
            ->post(route('mapscalculator.work-order.store'), [
                'project_id' => $project->id,
                'quote_id' => $quote->id,
            ])
            ->assertSessionHasErrors('project_id');

        $this->assertSame(1, MapWorkOrder::where('project_id', $project->id)->count());
    }

    public function test_quote_creation_still_saves_decimal_line_quantities(): void
    {
        $administrator = User::create([
            'name' => 'Maps Administrator',
            'email' => 'maps-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);
        $client = MapClient::create(['name' => 'Test client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'Measured project',
            'type' => 'interior',
            'status' => 'medido',
            'created_by' => $administrator->id,
        ]);

        Livewire::actingAs($administrator)
            ->test(\App\Livewire\MapsCalculator\MapulatorProject::class, ['project' => $project])
            ->set('quoteItems', [[
                'name' => 'Floor coating',
                'quantity' => 12.5,
                'unit_price' => 4,
                'discount_percent' => 0,
                'total_price' => 50,
            ]])
            ->call('calculateQuoteTotals')
            ->call('createQuote')
            ->assertHasNoErrors();

        $quote = Quote::where('project_id', $project->id)->firstOrFail();
        $this->assertSame(12.5, (float) $quote->items()->firstOrFail()->quantity);
        $this->assertSame(50.0, (float) $quote->subtotal);
        $this->assertSame(60.5, (float) $quote->total);
    }

    public function test_livewire_cannot_create_a_work_order_after_its_module_is_disabled(): void
    {
        $administrator = User::create([
            'name' => 'Maps Administrator',
            'email' => 'maps-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);
        $client = MapClient::create(['name' => 'Test client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'Approved project',
            'type' => 'interior',
            'status' => 'cotizado',
            'created_by' => $administrator->id,
        ]);
        Quote::create([
            'project_id' => $project->id,
            'name' => $client->name,
            'email' => null,
            'service_type' => 'resin',
            'status' => 'aprobado',
        ]);

        $component = Livewire::actingAs($administrator)
            ->test(\App\Livewire\MapsCalculator\MapulatorProject::class, ['project' => $project]);
        app(PlatformModules::class)->setEnabled('work_orders', false);

        $component->call('createWorkOrder')->assertStatus(404);
        $this->assertSame(0, MapWorkOrder::where('project_id', $project->id)->count());
    }

    public function test_mapscalculator_project_page_opens_when_client_relation_is_missing(): void
    {
        $administrator = User::create([
            'name' => 'Maps Administrator',
            'email' => 'maps-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);

        $client = MapClient::create(['name' => 'Existing client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'Project without client relation',
            'type' => 'interior',
            'status' => 'nuevo',
            'created_by' => $administrator->id,
        ]);
        $project->setRelation('client', null);

        $this->actingAs($administrator)
            ->get(route('mapscalculator.project.show', $project))
            ->assertOk()
            ->assertSee('Project without client relation');
    }

    public function test_work_order_status_transitions_are_enforced(): void
    {
        $administrator = User::create([
            'name' => 'Maps Administrator',
            'email' => 'maps-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);
        $client = MapClient::create(['name' => 'Test client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'Scheduled project',
            'type' => 'interior',
            'status' => 'agendada',
        ]);
        $workOrder = MapWorkOrder::create([
            'project_id' => $project->id,
            'status' => 'agendada',
        ]);

        Livewire::actingAs($administrator)
            ->test(WorkOrderComponent::class, ['workOrder' => $workOrder, 'project' => $project])
            ->call('updateStatus', 'entregada')
            ->assertHasErrors('status');

        $this->assertSame('agendada', $workOrder->fresh()->status);

        Livewire::actingAs($administrator)
            ->test(WorkOrderComponent::class, ['workOrder' => $workOrder->fresh(), 'project' => $project])
            ->call('updateStatus', 'en_preparacion')
            ->assertHasNoErrors();

        $this->assertSame('en_preparacion', $workOrder->fresh()->status);
        $this->assertSame('en_preparacion', $project->fresh()->status);
    }

    public function test_http_work_order_updates_enforce_transitions_and_preserve_assignment(): void
    {
        $administrator = User::create([
            'name' => 'Maps Administrator',
            'email' => 'maps-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);
        $technician = User::create([
            'name' => 'Technician',
            'email' => 'technician@example.com',
            'password' => 'temporary-password',
            'mapscalculator_role' => 'tecnico',
        ]);
        $client = MapClient::create(['name' => 'Test client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'HTTP project',
            'type' => 'interior',
            'status' => 'agendada',
            'assigned_to' => $technician->id,
        ]);
        $workOrder = MapWorkOrder::create([
            'project_id' => $project->id,
            'status' => 'agendada',
            'assigned_to' => $technician->id,
        ]);

        $this->actingAs($administrator)
            ->from('/mapscalculator/proximas-obras')
            ->put(route('mapscalculator.work-order.update', $workOrder), [
                'status' => 'entregada',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame('agendada', $workOrder->fresh()->status);

        $this->actingAs($administrator)
            ->put(route('mapscalculator.work-order.update', $workOrder), [
                'status' => 'en_preparacion',
            ])
            ->assertRedirect(route('mapscalculator.work-order.show', $workOrder));

        $this->assertSame('en_preparacion', $workOrder->fresh()->status);
        $this->assertSame($technician->id, $workOrder->fresh()->assigned_to);
        $this->assertSame($technician->id, $project->fresh()->assigned_to);
    }

    public function test_work_order_assignment_is_limited_to_manager_roles_and_eligible_users(): void
    {
        $administrator = User::create([
            'name' => 'Maps Administrator',
            'email' => 'maps-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);
        $technician = User::create([
            'name' => 'Technician',
            'email' => 'technician@example.com',
            'password' => 'temporary-password',
            'mapscalculator_role' => 'tecnico',
        ]);
        $client = MapClient::create(['name' => 'Test client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'Assigned project',
            'type' => 'interior',
            'status' => 'agendada',
        ]);
        $workOrder = MapWorkOrder::create([
            'project_id' => $project->id,
            'status' => 'agendada',
        ]);

        Livewire::actingAs($administrator)
            ->test(WorkOrderComponent::class, ['workOrder' => $workOrder, 'project' => $project])
            ->set('assignedTo', (string) $technician->id)
            ->call('updateAssignment')
            ->assertHasNoErrors();

        $this->assertSame($technician->id, $workOrder->fresh()->assigned_to);
        $this->assertSame($technician->id, $project->fresh()->assigned_to);

        Livewire::actingAs($administrator)
            ->test(WorkOrderComponent::class, ['workOrder' => $workOrder->fresh(), 'project' => $project->fresh()])
            ->set('assignedTo', '999999')
            ->call('updateAssignment')
            ->assertHasErrors('assignedTo');

        Livewire::actingAs($technician)
            ->test(WorkOrderComponent::class, ['workOrder' => $workOrder->fresh(), 'project' => $project->fresh()])
            ->set('assignedTo', null)
            ->call('updateAssignment')
            ->assertForbidden();
    }

    public function test_assigned_technician_can_consume_inventory_against_an_active_work_order(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $inventoryAdmin = User::create([
            'name' => 'Inventory Administrator',
            'email' => 'inventory-admin@example.com',
            'password' => 'temporary-password',
            'mapscalculator_role' => 'admin',
        ]);
        $inventoryAdmin->assignRole('admin');
        app(PlatformModules::class)->setEnabled('inventory', true);
        $this->assertTrue(app(PlatformModules::class)->enabled('inventory'));
        $this->assertTrue(app(PlatformModules::class)->enabled('work_orders'));

        $item = InventoryItem::create([
            'sku' => 'MAT-WO-001',
            'name' => 'Work material',
            'unit' => 'kg',
            'reorder_level' => 1,
            'is_active' => true,
        ]);
        $location = InventoryLocation::create([
            'code' => 'WO-MAIN',
            'name' => 'Work order warehouse',
            'is_active' => true,
        ]);
        $inventory = app(InventoryService::class);
        $this->actingAs($inventoryAdmin);
        $inventory->recordMovement([
            'inventory_item_id' => $item->id,
            'inventory_location_id' => $location->id,
            'movement_type' => 'receipt',
            'quantity' => '10',
            'reference' => 'OPENING-WO',
        ]);

        $technician = User::create([
            'name' => 'Assigned technician',
            'email' => 'assigned-technician@example.com',
            'password' => 'temporary-password',
            'mapscalculator_role' => 'tecnico',
        ]);
        $client = MapClient::create(['name' => 'Material client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'Material project',
            'type' => 'interior',
            'status' => 'en_ejecucion',
            'assigned_to' => $technician->id,
        ]);
        $workOrder = MapWorkOrder::create([
            'project_id' => $project->id,
            'status' => 'en_ejecucion',
            'assigned_to' => $technician->id,
        ]);

        $this->assertSame((int) $project->id, (int) $workOrder->project_id);
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($technician)->allows('view', $workOrder));
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($technician)->allows('update', $workOrder));

        $component = Livewire::actingAs($technician)
            ->test(WorkOrderComponent::class, ['workOrder' => $workOrder, 'project' => $project]);
        $this->assertTrue($component->instance()->inventoryEnabled());
        $component->set('materialItemId', (string) $item->id)
            ->set('materialLocationId', (string) $location->id)
            ->set('materialQuantity', '2.375')
            ->set('materialNotes', 'Applied to first room')
            ->call('consumeMaterial')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $item->id,
            'inventory_location_id' => $location->id,
            'work_order_id' => $workOrder->id,
            'movement_type' => 'issue',
            'quantity_change' => '-2.375',
            'balance_after' => '7.625',
            'created_by' => $technician->id,
        ]);
        $this->assertSame(2, InventoryMovement::count());
    }

    public function test_inventory_reservations_protect_stock_and_are_consumed_with_work_order_usage(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Inventory Administrator',
            'email' => 'reservation-admin@example.com',
            'password' => 'temporary-password',
            'mapscalculator_role' => 'admin',
        ]);
        $administrator->assignRole('admin');
        app(PlatformModules::class)->setEnabled('inventory', true);

        $item = InventoryItem::create([
            'sku' => 'MAT-RES-001',
            'name' => 'Reserved material',
            'unit' => 'kg',
            'is_active' => true,
        ]);
        $location = InventoryLocation::create([
            'code' => 'RES-MAIN',
            'name' => 'Reservation warehouse',
            'is_active' => true,
        ]);
        $client = MapClient::create(['name' => 'Reservation client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'Reservation project',
            'type' => 'interior',
            'status' => 'en_preparacion',
        ]);
        $workOrder = MapWorkOrder::create([
            'project_id' => $project->id,
            'status' => 'en_preparacion',
        ]);

        $this->actingAs($administrator);
        $service = app(InventoryService::class);
        $service->recordMovement([
            'inventory_item_id' => $item->id,
            'inventory_location_id' => $location->id,
            'movement_type' => 'receipt',
            'quantity' => '10',
        ]);
        $reservation = $service->reserveForWorkOrder($workOrder, [
            'inventory_item_id' => $item->id,
            'inventory_location_id' => $location->id,
            'quantity' => '6',
        ]);

        try {
            $service->recordMovement([
                'inventory_item_id' => $item->id,
                'inventory_location_id' => $location->id,
                'movement_type' => 'issue',
                'quantity' => '5',
            ]);
            $this->fail('An issue cannot consume quantities reserved for a work order.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quantity', $exception->errors());
        }

        $service->consumeForWorkOrder($workOrder, [
            'inventory_item_id' => $item->id,
            'inventory_location_id' => $location->id,
            'quantity' => '2.5',
        ]);

        $this->assertSame('2.500', $reservation->fresh()->consumed_quantity);
        $this->assertSame('3.500', $reservation->fresh()->remaining_quantity);
        $this->assertSame('7.500', InventoryMovement::latest('id')->value('balance_after'));

        $service->releaseReservation($reservation->fresh());
        $this->assertSame('released', $reservation->fresh()->status);
    }

    public function test_work_order_consumption_rejects_negative_stock_and_inactive_work(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $inventoryAdmin = User::create([
            'name' => 'Inventory Administrator',
            'email' => 'inventory-admin@example.com',
            'password' => 'temporary-password',
            'mapscalculator_role' => 'admin',
        ]);
        $inventoryAdmin->assignRole('admin');
        app(PlatformModules::class)->setEnabled('inventory', true);

        $item = InventoryItem::create([
            'sku' => 'MAT-WO-002',
            'name' => 'Limited material',
            'unit' => 'unit',
            'reorder_level' => 0,
            'is_active' => true,
        ]);
        $location = InventoryLocation::create([
            'code' => 'WO-EMPTY',
            'name' => 'Empty work warehouse',
            'is_active' => true,
        ]);
        $technician = User::create([
            'name' => 'Assigned technician',
            'email' => 'assigned-technician@example.com',
            'password' => 'temporary-password',
            'mapscalculator_role' => 'tecnico',
        ]);
        $client = MapClient::create(['name' => 'Material client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'Material project',
            'type' => 'interior',
            'status' => 'en_ejecucion',
            'assigned_to' => $technician->id,
        ]);
        $workOrder = MapWorkOrder::create([
            'project_id' => $project->id,
            'status' => 'en_ejecucion',
            'assigned_to' => $technician->id,
        ]);

        Livewire::actingAs($technician)
            ->test(WorkOrderComponent::class, ['workOrder' => $workOrder, 'project' => $project])
            ->set('materialItemId', (string) $item->id)
            ->set('materialLocationId', (string) $location->id)
            ->set('materialQuantity', '1')
            ->call('consumeMaterial')
            ->assertHasErrors('quantity');

        $workOrder->update(['status' => 'finalizada']);
        $project->update(['status' => 'finalizada']);

        Livewire::actingAs($technician)
            ->test(WorkOrderComponent::class, ['workOrder' => $workOrder->fresh(), 'project' => $project->fresh()])
            ->set('materialItemId', (string) $item->id)
            ->set('materialLocationId', (string) $location->id)
            ->set('materialQuantity', '1')
            ->call('consumeMaterial')
            ->assertHasErrors('work_order');

        $this->assertSame(0, InventoryMovement::count());
    }

    public function test_material_consumption_is_blocked_if_either_module_is_disabled(): void
    {
        $administrator = User::create([
            'name' => 'Maps Administrator',
            'email' => 'maps-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);
        $client = MapClient::create(['name' => 'Module client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'Module project',
            'type' => 'interior',
            'status' => 'en_ejecucion',
        ]);
        $workOrder = MapWorkOrder::create([
            'project_id' => $project->id,
            'status' => 'en_ejecucion',
        ]);

        app(PlatformModules::class)->setEnabled('inventory', true);
        app(PlatformModules::class)->setEnabled('work_orders', false);

        $this->actingAs($administrator);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(InventoryService::class)->consumeForWorkOrder($workOrder, [
            'inventory_item_id' => 1,
            'inventory_location_id' => 1,
            'quantity' => 1,
        ]);
    }

    public function test_approving_a_quote_does_not_create_a_work_order_when_that_module_is_disabled(): void
    {
        app(PlatformModules::class)->setEnabled('work_orders', false);

        $client = MapClient::create(['name' => 'Test client']);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'Quote-only project',
            'type' => 'interior',
            'status' => 'cotizado',
        ]);
        $quote = Quote::create([
            'project_id' => $project->id,
            'name' => $client->name,
            'email' => null,
            'service_type' => 'resin',
            'status' => 'enviado',
        ]);

        $quote->markAsApproved();

        $this->assertSame('aprobado', $quote->fresh()->status);
        $this->assertSame('aprobado', $project->fresh()->status);
        $this->assertSame(0, MapWorkOrder::where('project_id', $project->id)->count());
    }

    public function test_client_portal_hides_quote_and_work_order_data_when_modules_are_disabled(): void
    {
        app(PlatformModules::class)->setEnabledModules([
            'quotes' => false,
            'work_orders' => false,
            'invoicing' => false,
            'accounting' => false,
        ]);

        $client = MapClient::create([
            'name' => 'Portal client',
            'portal_token' => 'private-portal-token',
        ]);
        $project = MapProject::create([
            'client_id' => $client->id,
            'name' => 'Portal project',
            'type' => 'interior',
            'status' => 'agendada',
            'share_token' => 'private-project-token',
        ]);
        $quote = Quote::create([
            'project_id' => $project->id,
            'name' => $client->name,
            'email' => null,
            'service_type' => 'resin',
            'status' => 'aprobado',
            'quote_number' => 'PRIVATE-QUOTE-93841',
            'total' => 9876.54,
        ]);
        MapWorkOrder::create([
            'project_id' => $project->id,
            'quote_id' => $quote->id,
            'status' => 'en_ejecucion',
        ]);

        $this->get('/mapscalculator/portal/private-portal-token')
            ->assertOk()
            ->assertSee('Portal project')
            ->assertDontSee('PRIVATE-QUOTE-93841')
            ->assertDontSee('9.876,54 €')
            ->assertDontSee(__('mapscalculator.work_in_progress'));
    }

    public function test_inventory_movements_are_audited_and_cannot_create_negative_stock(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Inventory Administrator',
            'email' => 'inventory-admin@example.com',
            'password' => 'temporary-password',
        ]);
        $administrator->assignRole('admin');
        $this->actingAs($administrator);
        app(PlatformModules::class)->setEnabled('inventory', true);

        $item = InventoryItem::create([
            'sku' => 'MAT-001',
            'name' => 'Base material',
            'unit' => 'kg',
            'reorder_level' => 2,
            'is_active' => true,
        ]);
        $location = InventoryLocation::create([
            'code' => 'MAIN',
            'name' => 'Main warehouse',
            'is_active' => true,
        ]);
        $inventory = app(InventoryService::class);
        $movementData = [
            'inventory_item_id' => $item->id,
            'inventory_location_id' => $location->id,
            'movement_type' => 'receipt',
            'quantity' => '12.500',
            'reference' => 'OPENING-001',
            'notes' => 'Opening stock',
        ];

        $receipt = $inventory->recordMovement($movementData);
        $issue = $inventory->recordMovement(array_replace($movementData, [
            'movement_type' => 'issue',
            'quantity' => '4.250',
            'reference' => 'JOB-001',
        ]));
        $adjustment = $inventory->recordMovement(array_replace($movementData, [
            'movement_type' => 'adjustment',
            'quantity' => '-3.000',
            'reference' => 'COUNT-001',
            'notes' => 'Cycle count variance',
        ]));

        $this->assertSame('12.500', $receipt->balance_after);
        $this->assertSame('-4.250', $issue->quantity_change);
        $this->assertSame('5.250', $adjustment->balance_after);
        $this->assertEquals(5.25, (float) $item->movements()->sum('quantity_change'));

        try {
            $inventory->recordMovement(array_replace($movementData, [
                'movement_type' => 'issue',
                'quantity' => '5.251',
            ]));
            $this->fail('A stock issue must not make a location balance negative.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quantity', $exception->errors());
        }

        $this->actingAs($administrator)
            ->get('/admin/inventory-items')
            ->assertOk()
            ->assertSee('MAT-001');

        app(PlatformModules::class)->setEnabled('inventory', false);

        $this->assertSame(3, InventoryMovement::count());

        $this->actingAs($administrator)
            ->get('/admin/inventory-items')
            ->assertForbidden();
    }

    public function test_corporate_template_describes_module_availability_accurately(): void
    {
        Setting::set('homepage_template', 'corporate');

        $this->get('/')
            ->assertOk()
            ->assertSee('data-home-template="corporate"', false)
            ->assertSee('MapsCalculator')
            ->assertSee('Quotes')
            ->assertSee('Work orders')
            ->assertSee('Inventory')
            ->assertSee('Invoicing')
            ->assertSee('Accounting')
            ->assertSee('Only internal non-fiscal drafts')
            ->assertSee('Payments are disabled by default')
            ->assertSee('verified account')
            ->assertDontSee('Client web access pending');
    }

    public function test_project_gallery_can_filter_by_category_and_search_title_or_location(): void
    {
        Project::create([
            'slug' => 'maps-measurement',
            'title' => ['en' => 'Maps measurement', 'es' => 'Medición de mapas'],
            'location' => ['en' => 'Madrid', 'es' => 'Madrid'],
            'category' => 'planning',
        ]);
        Project::create([
            'slug' => 'work-order',
            'title' => ['en' => 'Work order', 'es' => 'Orden de trabajo'],
            'location' => ['en' => 'Valencia', 'es' => 'Valencia'],
            'category' => 'delivery',
        ]);

        $this->get('/proyectos?category=planning&q=Maps')
            ->assertOk()
            ->assertSee('Maps measurement')
            ->assertSee('Planning & measurement')
            ->assertDontSee('Work order');
    }

    public function test_quote_journey_explains_manual_review_and_does_not_claim_automated_billing(): void
    {
        $this->get('/cotizacion')
            ->assertOk()
            ->assertSee('Describe your project')
            ->assertSee('Team review')
            ->assertSee('does not issue an invoice or process a payment');
    }

    public function test_footer_only_links_to_legal_pages_that_exist(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('/legal/privacy-policy')
            ->assertDontSee('/legal/cookie-policy')
            ->assertDontSee('&copy; ©', false);

        Page::create([
            'slug' => 'privacy-policy',
            'title' => ['en' => 'Privacy Policy', 'es' => 'Política de privacidad'],
            'content' => ['en' => '<p>Privacy content.</p>', 'es' => '<p>Contenido de privacidad.</p>'],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('/legal/privacy-policy')
            ->assertDontSee('/legal/cookie-policy');
    }

    public function test_services_page_uses_platform_illustrations_and_connects_to_request_steps(): void
    {
        $this->get('/servicios')
            ->assertOk()
            ->assertSee('A clear process, from start to finish')
            ->assertSee('Start a request')
            ->assertSee('Project workspace')
            ->assertDontSee('images/service-resin.jpg')
            ->assertDontSee('images/service-granite.jpg');
    }
}
