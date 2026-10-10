<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Post;
use App\Models\Product;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class ReviewRegressionTest extends FeatureTestCase
{
    public function test_admin_cannot_promote_self_or_create_super_admin(): void
    {
        $admin = $this->makeUser('admin');
        $superId = Role::where('name', 'super-admin')->value('id');
        $headers = $this->authHeaders($admin);
        $this->putJson('/api/v1/admin/users/'.$admin->id, ['roles' => [$superId]], $headers)->assertForbidden();
        $this->assertFalse($admin->fresh()->isSuperAdmin());
        $this->postJson('/api/v1/admin/users', [
            'name' => 'Blocked', 'email' => 'blocked@example.com', 'password' => 'Password123!',
            'password_confirmation' => 'Password123!', 'roles' => [$superId],
        ], $headers)->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);
    }

    public function test_admin_cannot_modify_or_delete_a_super_admin(): void
    {
        $admin = $this->makeUser('admin');
        $super = $this->makeUser();
        $headers = $this->authHeaders($admin);
        $this->putJson('/api/v1/admin/users/'.$super->id, ['name' => 'Blocked'], $headers)->assertForbidden();
        $this->deleteJson('/api/v1/admin/users/'.$super->id, [], $headers)->assertForbidden();
        $this->assertSame($super->name, $super->fresh()->name);
    }

    public function test_admin_can_manage_a_lower_role(): void
    {
        $admin = $this->makeUser('admin');
        $editor = $this->makeUser('editor');
        $this->putJson('/api/v1/admin/users/'.$editor->id, ['name' => 'Updated editor'], $this->authHeaders($admin))->assertOk();
        $this->assertSame('Updated editor', $editor->fresh()->name);
    }

    public function test_rejected_role_change_does_not_persist_other_fields(): void
    {
        $user = $this->makeUser();
        $this->putJson('/api/v1/admin/users/'.$user->id, ['name' => 'Must not persist', 'roles' => []], $this->authHeaders($user))->assertUnprocessable();
        $this->assertSame($user->name, $user->fresh()->name);
        $this->assertTrue($user->fresh()->isSuperAdmin());
    }

    public function test_custom_role_cannot_grant_permissions_the_actor_does_not_have(): void
    {
        $user = $this->makeUser('editor');
        $role = Role::create(['name' => 'role-manager', 'display_name' => 'Role manager']);
        $role->permissions()->attach(Permission::where('name', 'roles.create')->value('id'));
        $user->roles()->attach($role);
        $this->postJson('/api/v1/admin/roles', [
            'name' => 'elevated', 'display_name' => 'Elevated',
            'permissions' => [Permission::where('name', 'users.update')->value('id')],
        ], $this->authHeaders($user))->assertForbidden();
        $this->assertDatabaseMissing('roles', ['name' => 'elevated']);
    }

    public function test_forced_password_change_blocks_admin_and_revokes_tokens_after_change(): void
    {
        $user = $this->makeUser();
        $user->update(['password' => Hash::make('OldPassword123!'), 'must_change_password' => true]);
        $headers = $this->authHeaders($user);
        $this->getJson('/api/v1/admin/users', $headers)->assertForbidden()->assertJsonPath('must_change_password', true);
        $this->putJson('/api/v1/auth/password', [
            'current_password' => 'OldPassword123!', 'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ], $headers)->assertOk();
        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/auth/me', $headers)->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/admin/users', $this->authHeaders($user->fresh()))->assertOk();
    }

    public function test_wrong_current_password_does_not_clear_forced_change(): void
    {
        $user = $this->makeUser();
        $user->update(['must_change_password' => true]);
        $this->putJson('/api/v1/auth/password', [
            'current_password' => 'WrongPassword123!', 'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ], $this->authHeaders($user))->assertUnprocessable();
        $this->assertTrue($user->fresh()->must_change_password);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_cms_metadata_is_returned_with_advanced_seo_fields(): void
    {
        $product = Product::factory()->create(['status' => 'published', 'meta_title' => 'CMS title']);
        $product->seoMeta()->create(['meta_title' => 'Stale title', 'og_title' => 'Social title', 'robots' => 'noindex,follow']);
        $this->getJson('/api/v1/products/'.$product->slug)->assertOk()
            ->assertJsonPath('data.product.meta_title', 'CMS title')
            ->assertJsonPath('data.product.seo.meta_title', 'CMS title')
            ->assertJsonPath('data.product.seo.og_title', 'Social title');
    }

    public function test_api_seo_update_and_clear_are_reflected_in_cms_columns(): void
    {
        $product = Product::factory()->create(['meta_title' => 'Old']);
        $headers = $this->authHeaders($this->makeUser());
        $this->putJson('/api/v1/admin/products/'.$product->id, ['seo' => ['meta_title' => 'New', 'og_title' => 'Social']], $headers)->assertOk();
        $this->assertSame('New', $product->fresh()->meta_title);
        $this->putJson('/api/v1/admin/products/'.$product->id, ['seo' => ['meta_title' => null]], $headers)->assertOk();
        $this->assertNull($product->fresh()->meta_title);
        $this->assertNull($product->fresh()->seoMeta->meta_title);
        $this->assertSame('Social', $product->fresh()->seoMeta->og_title);
    }

    public function test_post_without_author_remains_supported(): void
    {
        Post::factory()->create(['status' => 'published', 'user_id' => null]);
        $this->getJson('/api/v1/posts')->assertOk()->assertJsonPath('data.0.author', null);
    }

    public function test_upgrade_migration_backfills_seo_without_overwriting_cms_edits(): void
    {
        $legacy = Product::factory()->create(['meta_title' => null]);
        $legacy->seoMeta()->create(['meta_title' => 'Legacy SEO']);
        $edited = Product::factory()->create(['meta_title' => 'CMS edit']);
        $edited->seoMeta()->create(['meta_title' => 'Old SEO']);
        \App\Models\Setting::create(['key' => 'facebook', 'value' => '#', 'group' => 'social']);
        $migration = require database_path('migrations/2026_10_08_000001_normalize_seo_and_social_settings.php');
        $migration->up();
        $this->assertSame('Legacy SEO', $legacy->fresh()->meta_title);
        $this->assertSame('CMS edit', $edited->fresh()->meta_title);
        $this->assertDatabaseHas('settings', ['key' => 'facebook', 'value' => null]);
    }
}
