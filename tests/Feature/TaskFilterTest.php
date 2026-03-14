<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for task filtering and sorting functionality.
 *
 * Verifies that the task list endpoint correctly filters by completion status
 * and title, and sorts by due_date or created_at.
 */
class TaskFilterTest extends TestCase
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
     * Test filtering tasks by completed status.
     *
     * Creates a mix of completed and incomplete tasks, then verifies
     * the filter returns only matching tasks.
     */
    public function test_filter_by_completed(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->count(3)->completed()->create();
        Task::factory()->for($user)->count(5)->incomplete()->create();

        // Filter for completed tasks only
        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/tasks?completed=true');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));

        // Filter for incomplete tasks only
        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/tasks?completed=false');

        $response->assertOk();
        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test filtering tasks by title using LIKE search.
     *
     * Creates tasks with known titles and verifies partial matching works.
     */
    public function test_filter_by_title_search(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['title' => 'Deploy to production']);
        Task::factory()->for($user)->create(['title' => 'Write deploy scripts']);
        Task::factory()->for($user)->create(['title' => 'Fix login bug']);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/tasks?title=deploy');

        $response->assertOk();
        // Should match "Deploy to production" and "Write deploy scripts"
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test sorting tasks by due_date in ascending order.
     *
     * Creates tasks with specific due dates and verifies the sort order.
     */
    public function test_sort_by_due_date(): void
    {
        $user = User::factory()->create();
        $taskLate = Task::factory()->for($user)->create(['due_date' => '2026-12-01']);
        $taskEarly = Task::factory()->for($user)->create(['due_date' => '2026-01-01']);
        $taskMid = Task::factory()->for($user)->create(['due_date' => '2026-06-01']);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/tasks?sort_by=due_date&sort_order=asc');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertEquals($taskEarly->id, $data[0]['id']);
        $this->assertEquals($taskMid->id, $data[1]['id']);
        $this->assertEquals($taskLate->id, $data[2]['id']);
    }

    /**
     * Test sorting tasks by created_at in descending order (default).
     *
     * Verifies the default sort behavior when no sort params are provided.
     */
    public function test_sort_by_created_at_desc(): void
    {
        $user = User::factory()->create();

        // Create tasks with slight time gaps to ensure distinct created_at values
        $taskFirst = Task::factory()->for($user)->create(['created_at' => now()->subMinutes(2)]);
        $taskSecond = Task::factory()->for($user)->create(['created_at' => now()->subMinute()]);
        $taskThird = Task::factory()->for($user)->create(['created_at' => now()]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/tasks');

        $response->assertOk();
        $data = $response->json('data');

        // Default sort is created_at desc — newest first
        $this->assertEquals($taskThird->id, $data[0]['id']);
        $this->assertEquals($taskSecond->id, $data[1]['id']);
        $this->assertEquals($taskFirst->id, $data[2]['id']);
    }
}
