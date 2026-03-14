<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for task API validation rules and route protection.
 *
 * Verifies that:
 * - Protected routes return 401 without authentication.
 * - Validation rules reject invalid data with proper 422 responses.
 * - Edge cases (empty strings, wrong types) are handled correctly.
 */
class TaskValidationTest extends TestCase
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

    // ─── Unauthenticated Access Tests ────────────────────────────────────

    /**
     * Test that listing tasks without a token returns 401.
     *
     * All task endpoints are protected by auth:sanctum middleware.
     */
    public function test_unauthenticated_user_cannot_list_tasks(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401);
    }

    /**
     * Test that creating a task without a token returns 401.
     */
    public function test_unauthenticated_user_cannot_create_task(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Should not work',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that viewing a task without a token returns 401.
     */
    public function test_unauthenticated_user_cannot_show_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(401);
    }

    /**
     * Test that updating a task without a token returns 401.
     */
    public function test_unauthenticated_user_cannot_update_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Hacked',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that deleting a task without a token returns 401.
     */
    public function test_unauthenticated_user_cannot_delete_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(401);
    }

    // ─── Task Creation Validation Tests ──────────────────────────────────

    /**
     * Test that creating a task without a title returns 422.
     *
     * The 'title' field is required by StoreTaskRequest validation.
     */
    public function test_create_task_requires_title(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/tasks', [
                'description' => 'Task with no title',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /**
     * Test that creating a task with a title exceeding 255 characters returns 422.
     */
    public function test_create_task_title_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/tasks', [
                'title' => str_repeat('A', 256),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /**
     * Test that creating a task with an invalid due_date format returns 422.
     */
    public function test_create_task_invalid_due_date(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/tasks', [
                'title' => 'Valid title',
                'due_date' => 'not-a-date',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    /**
     * Test that creating a task with a non-boolean 'completed' value returns 422.
     */
    public function test_create_task_invalid_completed_value(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/tasks', [
                'title' => 'Valid title',
                'completed' => 'not-a-boolean',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['completed']);
    }

    // ─── Task Update Validation Tests ────────────────────────────────────

    /**
     * Test that updating a task with an empty title returns 422.
     *
     * When 'title' is provided in the update payload, it must be non-empty.
     */
    public function test_update_task_title_cannot_be_empty(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->putJson("/api/tasks/{$task->id}", [
                'title' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /**
     * Test that updating a task with an invalid due_date returns 422.
     */
    public function test_update_task_invalid_due_date(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->putJson("/api/tasks/{$task->id}", [
                'due_date' => 'invalid-date',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    /**
     * Test that a valid partial update succeeds (only updating one field).
     *
     * The UpdateTaskRequest uses 'sometimes' rules, so partial payloads
     * should be accepted without requiring all fields.
     */
    public function test_partial_update_succeeds(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['completed' => false]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->putJson("/api/tasks/{$task->id}", [
                'completed' => true,
            ]);

        $response->assertOk()
            ->assertJson(['completed' => true]);

        // Original title should be unchanged
        $this->assertEquals($task->title, $response->json('title'));
    }
}
