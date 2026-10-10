<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReviewRegressionTest extends TestCase
{
    public function test_admin_cannot_access_role_management_or_promote_self(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin)->get('/admin/vai-tro')->assertForbidden();
        $this->put('/admin/nguoi-dung/'.$admin->id, [
            'name' => $admin->name, 'email' => $admin->email,
            'roles' => [Role::where('name', 'super-admin')->value('id')],
        ])->assertForbidden();
        $this->assertFalse($admin->fresh()->hasRole('super-admin'));
    }

    public function test_core_role_form_can_save_without_name_and_preserves_its_code(): void
    {
        $role = Role::where('name', 'editor')->firstOrFail();
        $this->actingAs($this->makeUser())->get('/admin/vai-tro/'.$role->id.'/edit')->assertOk();
        $this->put('/admin/vai-tro/'.$role->id, [
            'display_name' => 'Content editor', 'permissions' => $role->permissions()->pluck('permissions.id')->all(),
        ])->assertRedirect('/admin/vai-tro')->assertSessionHasNoErrors();
        $this->assertSame('editor', $role->fresh()->name);
        $this->assertSame('Content editor', $role->fresh()->display_name);
    }

    public function test_rejected_user_update_is_atomic(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->put('/admin/nguoi-dung/'.$user->id, [
            'name' => 'Must not persist', 'email' => $user->email, 'roles' => [],
        ])->assertSessionHasErrors('roles');
        $this->assertSame($user->name, $user->fresh()->name);
    }

    public function test_legacy_social_placeholders_do_not_block_settings_save(): void
    {
        Setting::set('facebook', '#', 'social');
        $this->actingAs($this->makeUser())->get('/admin/cai-dat')->assertOk();
        $this->put('/admin/cai-dat', [
            'site_name' => 'Updated TKPRO', 'facebook' => '#', 'youtube' => '#',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Updated TKPRO', Setting::where('key', 'site_name')->value('value'));
        $this->assertNull(Setting::where('key', 'facebook')->value('value'));
    }

    public function test_password_change_revokes_api_tokens(): void
    {
        $user = $this->makeUser();
        $user->update(['must_change_password' => true]);
        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => 'App\\Models\\User', 'tokenable_id' => $user->id,
            'name' => 'test', 'token' => hash('sha256', 'test-token'),
        ]);
        $this->actingAs($user)->get('/admin')->assertRedirect('/admin/doi-mat-khau');
        $this->put('/admin/doi-mat-khau', [
            'current_password' => 'Password123!', 'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertSessionHasNoErrors();
        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_cms_migrate_is_a_no_op_against_api_schema(): void
    {
        $before = DB::table('migrations')->count();
        $this->artisan('migrate')->assertExitCode(0);
        $this->assertSame($before, DB::table('migrations')->count());
        $this->assertGreaterThan(0, Role::count());
    }

    public function test_product_specifications_from_api_can_be_edited_and_saved(): void
    {
        $category = \App\Models\ProductCategory::create(['name' => 'Cameras', 'slug' => 'cameras', 'status' => 'published']);
        $product = \App\Models\Product::create([
            'name' => 'Camera', 'slug' => 'camera', 'category_id' => $category->id, 'status' => 'published',
            'technical_specifications' => [['label' => 'Resolution', 'value' => '2K']],
        ]);
        $this->actingAs($this->makeUser())->get('/admin/san-pham/'.$product->id.'/edit')
            ->assertOk()->assertSee('Resolution: 2K');
        $this->put('/admin/san-pham/'.$product->id, [
            'name' => 'Camera', 'category_id' => $category->id, 'status' => 'published',
            'technical_specifications' => "Resolution: 4K\nConnection: WiFi",
        ])->assertSessionHasNoErrors();
        $this->assertSame([
            ['label' => 'Resolution', 'value' => '4K'], ['label' => 'Connection', 'value' => 'WiFi'],
        ], $product->fresh()->technical_specifications);
    }

    public function test_product_form_rejects_missing_category_and_oversized_description(): void
    {
        $this->actingAs($this->makeUser())->post('/admin/san-pham', [
            'name' => 'Camera', 'status' => 'published', 'short_description' => str_repeat('x', 501),
        ])->assertSessionHasErrors(['category_id', 'short_description']);
        $this->assertDatabaseCount('products', 0);
    }
}
