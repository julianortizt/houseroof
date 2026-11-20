<?php

namespace Tests\Feature;

use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPageEditContentTest extends TestCase
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

    private function legalPage(array $attributes = []): Page
    {
        return Page::create(array_merge([
            'slug' => 'privacy-policy',
            'title' => ['es' => 'Privacidad', 'en' => 'Privacy Policy'],
            'content' => ['es' => '<p>Cuerpo ES</p>', 'en' => '<p>Body EN</p>'],
            'is_active' => true,
        ], $attributes));
    }

    public function test_legal_page_edit_renders_with_page_configuration(): void
    {
        $user = $this->adminUser();
        $this->legalPage();

        $this->actingAs($user)
            ->get('/admin/pages/privacy-policy/edit')
            ->assertOk()
            ->assertSee('Privacy Policy');
    }

    public function test_content_editor_field_shows_after_toggling(): void
    {
        $user = $this->adminUser();
        $page = $this->legalPage();

        Livewire::actingAs($user)
            ->test(EditPage::class, ['record' => $page->slug])
            ->set('showContentEditor', true)
            ->assertFormFieldExists('content')
            ->assertFormSet(['content' => '<p>Body EN</p>']);
    }

    public function test_saving_legal_page_updates_only_active_locale_content(): void
    {
        $user = $this->adminUser();
        $page = $this->legalPage();

        Livewire::actingAs($user)
            ->test(EditPage::class, ['record' => $page->slug])
            ->set('showContentEditor', true)
            ->fillForm([
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'is_active' => true,
                'content' => '<p>Updated EN body</p>',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $page->refresh();

        $decoded = json_decode($page->getRawOriginal('content'), true);

        $this->assertSame('<p>Updated EN body</p>', $decoded['en'] ?? null);
        $this->assertSame('<p>Cuerpo ES</p>', $decoded['es'] ?? null);
    }
}