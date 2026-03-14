<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * RESTful controller for Task CRUD operations.
 *
 * All endpoints are protected by auth:sanctum middleware.
 * Users can only access their own tasks — ownership is enforced
 * via abort(403) on every show/update/delete action.
 */
class TaskController extends Controller
{
    /**
     * List the authenticated user's tasks with filtering, sorting, and pagination.
     *
     * Supports the following query parameters:
     * - completed: Filter by completion status (true/false/1/0)
     * - title: Filter by title using a LIKE search (partial match)
     * - sort_by: Column to sort by (due_date or created_at, default: created_at)
     * - sort_order: Sort direction (asc or desc, default: desc)
     *
     * @param Request $request The incoming HTTP request with optional query filters.
     * @return JsonResponse Paginated list of the user's tasks (15 per page).
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->tasks();

        // Filter by completion status when the parameter is present
        if ($request->has('completed')) {
            $query->where('completed', filter_var($request->completed, FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by title using LIKE for partial/fuzzy matching
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Determine sort column — whitelist to prevent SQL injection via column name
        $sortBy = in_array($request->sort_by, ['due_date', 'created_at'])
            ? $request->sort_by
            : 'created_at';

        // Determine sort direction — whitelist to asc/desc only
        $sortOrder = in_array($request->sort_order, ['asc', 'desc'])
            ? $request->sort_order
            : 'desc';

        $tasks = $query->orderBy($sortBy, $sortOrder)->paginate(15);

        return response()->json($tasks);
    }

    /**
     * Display a single task.
     *
     * Returns 403 if the authenticated user does not own the task.
     *
     * @param Task $task The task resolved via route model binding.
     * @return JsonResponse The task resource.
     */
    public function show(Request $request, Task $task): JsonResponse
    {
        // Ownership check — users must only access their own tasks
        if ($task->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        return response()->json($task);
    }

    /**
     * Create a new task for the authenticated user.
     *
     * The task is automatically associated with the authenticated user
     * via the relationship, preventing user_id spoofing.
     *
     * @param StoreTaskRequest $request The validated request with task data.
     * @return JsonResponse The newly created task (HTTP 201).
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $request->user()->tasks()->create($request->validated());

        // Refresh to include default values (e.g. completed=false) in the response
        return response()->json($task->fresh(), 201);
    }

    /**
     * Update an existing task.
     *
     * Returns 403 if the authenticated user does not own the task.
     * Supports partial updates — only provided fields are changed.
     *
     * @param UpdateTaskRequest $request The validated request with updated task data.
     * @param Task $task The task resolved via route model binding.
     * @return JsonResponse The updated task resource.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        // Ownership check — users must only modify their own tasks
        if ($task->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $task->update($request->validated());

        return response()->json($task);
    }

    /**
     * Delete a task.
     *
     * Returns 403 if the authenticated user does not own the task.
     * Uses soft-less permanent deletion (hard delete).
     *
     * @param Request $request The incoming authenticated HTTP request.
     * @param Task $task The task resolved via route model binding.
     * @return JsonResponse Confirmation message (HTTP 200).
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        // Ownership check — users must only delete their own tasks
        if ($task->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.']);
    }
}
