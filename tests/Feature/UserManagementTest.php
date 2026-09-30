<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function payload(string $role): array
    {
        return [
            'name' => 'professor',
            'email' => 'professor@professor.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => $role,
        ];
    }

    public function test_teacher_gets_403(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->getJson('/users')
            ->assertForbidden();
    }

    public function test_staff_cannot_create_admin(): void
    {
        $this->actingAs(User::factory()->staff()->create())
            ->postJson('/users', $this->payload('admin'))
            ->assertUnprocessable();
    }
    public function test_staff_can_create_teacher(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->postJson('/users', $this->payload('teacher'))
            ->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'professor@professor.com',
            'role'  => 'teacher',
        ]);
    }

    public function test_staff_cannot_update_admin(): void
    {
        $staff = User::factory()->staff()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($staff)
            ->putJson("/users/{$admin->id}", [
                'name'  => 'Novo Nome',
                'email' => $admin->email,
            ])
            ->assertForbidden();
    }

    public function test_user_cannot_deactivate_self(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patchJson("/users/{$admin->id}/toggle-active")
            ->assertUnprocessable();
    }
}
