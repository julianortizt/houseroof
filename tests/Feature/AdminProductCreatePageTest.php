<?php

namespace Tests\Feature;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminProductCreatePageTest extends TestCase
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

    private function category(): Category
    {
        return Category::create([
            'slug' => 'resinas',
            'name' => ['en' => 'Resins', 'es' => 'Resinas'],
            'description' => null,
            'is_active' => true,
        ]);
    }

    public function test_product_create_page_renders(): void
    {
        $user = $this->adminUser();
        $this->category();

        $this->actingAs($user)
            ->withSession(['admin_locale' => 'es'])
            ->get('/admin/products/create')
            ->assertOk()
            ->assertSee('Nombre')
            ->assertSee('Categoría');
    }

    public function test_product_can_be_created_via_form(): void
    {
        $user = $this->adminUser();
        $category = $this->category();

        Livewire::actingAs($user)
            ->test(CreateProduct::class)
            ->fillForm([
                'name' => 'Resina Epoxi',
                'slug' => 'resina-epoxi',
                'category_id' => $category->id,
                'price' => 25.50,
                'stock' => 10,
                'is_active' => true,
                'is_featured' => false,
                'is_coming_soon' => false,
                'has_compare_price' => false,
                'has_dimensions' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', ['slug' => 'resina-epoxi']);
    }
}
