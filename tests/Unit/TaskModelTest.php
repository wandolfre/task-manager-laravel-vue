<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the Task Eloquent model.
 *
 * Verifies model relationships, attribute casting, mass assignment,
 * and default values work as expected at the model layer.
 */
class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a Task belongs to a User (belongsTo relationship).
     *
     * Ensures the task->user relationship returns the correct User instance
     * and that the foreign key (user_id) is properly linked.
     */
    public function test_task_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        // The relationship should return the owning user
        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
    }

    /**
     * Test that a User has many Tasks (hasMany relationship).
     *
     * Ensures the user->tasks relationship returns a collection
     * of Task models belonging to that user.
     */
    public function test_user_has_many_tasks(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->count(3)->create();

        // The user should have exactly 3 tasks
        $this->assertCount(3, $user->tasks);
        $this->assertInstanceOf(Task::class, $user->tasks->first());
    }

    /**
     * Test that the 'completed' attribute is cast to a boolean.
     *
     * The database stores completed as an integer (0/1), but the model
     * should always return a native PHP boolean.
     */
    public function test_completed_is_cast_to_boolean(): void
    {
        $task = Task::factory()->create(['completed' => true]);

        $this->assertIsBool($task->completed);
        $this->assertTrue($task->completed);

        $task->update(['completed' => false]);
        $task->refresh();

        $this->assertIsBool($task->completed);
        $this->assertFalse($task->completed);
    }

    /**
     * Test that the 'due_date' attribute is cast to a Carbon date instance.
     *
     * When a due_date is set, it should be returned as a Carbon instance.
     * When null, it should remain null.
     */
    public function test_due_date_is_cast_to_date(): void
    {
        $task = Task::factory()->create(['due_date' => '2026-06-15']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $task->due_date);
        $this->assertEquals('2026-06-15', $task->due_date->toDateString());
    }

    /**
     * Test that due_date can be null (nullable column).
     *
     * Tasks are not required to have a deadline.
     */
    public function test_due_date_can_be_null(): void
    {
        $task = Task::factory()->create(['due_date' => null]);

        $this->assertNull($task->due_date);
    }

    /**
     * Test that the 'completed' field defaults to false.
     *
     * When creating a task without specifying completed, it should
     * default to false (incomplete) as defined in the migration.
     */
    public function test_completed_defaults_to_false(): void
    {
        $user = User::factory()->create();
        $task = $user->tasks()->create([
            'title' => 'Test default completed value',
        ]);

        $this->assertFalse($task->fresh()->completed);
    }

    /**
     * Test that only fillable attributes can be mass-assigned.
     *
     * The user_id should NOT be in $fillable to prevent user spoofing.
     * Tasks should be created via the relationship instead.
     */
    public function test_fillable_attributes(): void
    {
        $task = new Task();
        $fillable = $task->getFillable();

        $this->assertContains('title', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('completed', $fillable);
        $this->assertContains('due_date', $fillable);

        // user_id should NOT be mass-assignable to prevent spoofing
        $this->assertNotContains('user_id', $fillable);
    }

    /**
     * Test that deleting a user cascades to delete their tasks.
     *
     * The tasks table FK has cascadeOnDelete(), so when a user is
     * removed, all their tasks should be automatically deleted.
     */
    public function test_deleting_user_cascades_to_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $taskId = $task->id;
        $user->delete();

        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }
}
