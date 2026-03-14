<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Task Model
 *
 * Represents a single task in the task management system.
 * Each task is owned by exactly one user.
 *
 * @property int $id Primary key
 * @property int $user_id Foreign key referencing the owning user
 * @property string $title Short summary of the task
 * @property string|null $description Optional detailed description
 * @property bool $completed Whether the task has been completed
 * @property \Illuminate\Support\Carbon|null $due_date Optional deadline
 * @property \Illuminate\Support\Carbon $created_at Record creation timestamp
 * @property \Illuminate\Support\Carbon $updated_at Record last-update timestamp
 */
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'completed',
        'due_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'due_date' => 'date',
        ];
    }

    /**
     * Get the user who owns this task.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
