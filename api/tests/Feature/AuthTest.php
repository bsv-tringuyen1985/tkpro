<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends FeatureTestCase
{
    public function test_dang_nhap_thanh_cong_tra_ve_token(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('Admin123!'),
            'must_change_password' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'Admin123!',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.must_change_password', true)
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'email', 'roles']]]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_dang_nhap_sai_mat_khau_tra_ve_401(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('Admin123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'sai-mat-khau',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_dang_nhap_thieu_du_lieu_tra_ve_422(): void
    {
        $response = $this->postJson('/api/v1/auth/login', ['email' => 'khong-phai-email']);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['email', 'password']]);
    }

    public function test_me_yeu_cau_dang_nhap(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_me_tra_ve_thong_tin_user(): void
    {
        $user = $this->makeUser('admin');

        $this->getJson('/api/v1/auth/me', $this->authHeaders($user))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonStructure(['data' => ['roles']]);
    }

    public function test_dang_xuat_thu_hoi_token(): void
    {
        $user = $this->makeUser();
        $headers = $this->authHeaders($user);

        $this->postJson('/api/v1/auth/logout', [], $headers)
            ->assertOk()
            ->assertJsonPath('success', true);

        // Token cu khong con hieu luc.
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/auth/me', $headers)->assertUnauthorized();
    }
}
