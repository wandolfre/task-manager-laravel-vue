<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeds the database with sample data for development and demo purposes.
 *
 * Creates 5 users, each with 10 tasks of varying completion states and due dates.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Generates 5 users with 10 tasks each, providing a realistic dataset
     * for development, testing, and demo walkthroughs.
     */
    public function run(): void
    {
        User::factory(5)
            ->has(Task::factory()->count(10))
            ->create();
    }
}
