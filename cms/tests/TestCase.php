<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Only the API owns schema. Every test uses a fresh in-memory connection.
        config(['database.connections.sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        $this->artisan('migrate', ['--path' => realpath(base_path('../api/database/migrations')), '--realpath' => true])->assertExitCode(0);
        require_once base_path('../api/database/seeders/RolesPermissionsSeeder.php');
        $this->seed(\Database\Seeders\RolesPermissionsSeeder::class);
    }

    protected function makeUser(string $role = 'super-admin'): \App\Models\User
    {
        $user = \App\Models\User::create([
            'name' => 'Test user', 'email' => uniqid().'@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('Password123!'), 'must_change_password' => false,
        ]);
        $user->roles()->attach(\App\Models\Role::where('name', $role)->firstOrFail());
        return $user;
    }
}
