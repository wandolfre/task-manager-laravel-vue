<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the Task CRUD API endpoints.
 *
 * Verifies that authenticated users can create, read, update, and delete
 * their own tasks, and that they receive 403 when accessing other users' tasks.
 */
class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to authenticate a user and return the Authorization header.
     *
     * @param User $user The user to authenticate.
     * @return array<string, string> Authorization header array.
     */
    private function authHeaders(User $user): array
    {
        $token = $user->createToken('test-token')->plainTextToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    /**
     * Test listing tasks returns paginated results for the authenticated user only.
     */
    public function test_list_tasks_paginated(): void
    {
        $user = User::factory()->has(Task::factory()->count(20))->create();
        $otherUser = User::factory()->has(Task::factory()->count(5))->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total',
            ]);

        // Should return 15 (page size) out of 20 tasks, none from otherUser
        $this->assertCount(15, $response->json('data'));
        $this->assertEquals(20, $response->json('total'));
    }

    /**
     * Test creating a new task for the authenticated user.
     */
    public function test_create_task(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/tasks', [
                'title' => 'Write unit tests',
                'description' => 'Cover all CRUD operations.',
                'due_date' => '2026-04-01',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'Write unit tests',
                'description' => 'Cover all CRUD operations.',
                'completed' => false,
            ]);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Write unit tests',
        ]);
    }

    /**
     * Test showing a task the authenticated user owns.
     */
    public function test_show_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson("/api/tasks/{$task->id}");

        $response->assertOk()
            ->assertJson([
                'id' => $task->id,
                'title' => $task->title,
            ]);
    }

    /**
     * Test that showing another user's task returns 403.
     */
    public function test_show_other_users_task_returns_403(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($otherUser)->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    /**
     * Test updating a task the authenticated user owns.
     */
    public function test_update_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['completed' => false]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Updated title',
                'completed' => true,
            ]);

        $response->assertOk()
            ->assertJson([
                'title' => 'Updated title',
                'completed' => true,
            ]);
    }

    /**
     * Test that updating another user's task returns 403.
     */
    public function test_update_other_users_task_returns_403(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($otherUser)->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Hacked title',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test deleting a task the authenticated user owns.
     */
    public function test_delete_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * Test that deleting another user's task returns 403.
     */
    public function test_delete_other_users_task_returns_403(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($otherUser)->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
    }
}
