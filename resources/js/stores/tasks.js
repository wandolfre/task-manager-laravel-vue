/**
 * Pinia Task Store
 *
 * Manages task list state including pagination, filters, and sorting.
 * All API calls go through the centralized api.js Axios instance.
 */
import { defineStore } from 'pinia';
import { ref, reactive } from 'vue';
import api from '../api.js';

export const useTaskStore = defineStore('tasks', () => {
    // Task list data
    const tasks = ref([]);
    const currentTask = ref(null);

    // Pagination state
    const pagination = reactive({
        currentPage: 1,
        lastPage: 1,
        perPage: 15,
        total: 0,
    });

    // Filter and sort state
    const filters = reactive({
        completed: null,  // null = all, true = completed, false = incomplete
        title: '',        // LIKE search string
        sortBy: 'created_at',
        sortOrder: 'desc',
    });

    /**
     * Fetch paginated tasks with current filters and sorting applied.
     *
     * @param {number} page - Page number to fetch (default: 1)
     */
    async function fetchTasks(page = 1) {
        const params = {
            page,
            sort_by: filters.sortBy,
            sort_order: filters.sortOrder,
        };

        // Only include filter params when they have values
        if (filters.completed !== null) {
            params.completed = filters.completed;
        }
        if (filters.title) {
            params.title = filters.title;
        }

        const response = await api.get('/tasks', { params });
        tasks.value = response.data.data;
        pagination.currentPage = response.data.current_page;
        pagination.lastPage = response.data.last_page;
        pagination.perPage = response.data.per_page;
        pagination.total = response.data.total;
    }

    /**
     * Fetch a single task by ID.
     *
     * @param {number} id - Task ID
     */
    async function fetchTask(id) {
        const response = await api.get(`/tasks/${id}`);
        currentTask.value = response.data;
        return response.data;
    }

    /**
     * Create a new task.
     *
     * @param {Object} taskData - { title, description?, completed?, due_date? }
     */
    async function createTask(taskData) {
        const response = await api.post('/tasks', taskData);
        return response.data;
    }

    /**
     * Update an existing task.
     *
     * @param {number} id - Task ID
     * @param {Object} taskData - Fields to update
     */
    async function updateTask(id, taskData) {
        const response = await api.put(`/tasks/${id}`, taskData);
        return response.data;
    }

    /**
     * Delete a task.
     *
     * @param {number} id - Task ID
     */
    async function deleteTask(id) {
        await api.delete(`/tasks/${id}`);
    }

    /**
     * Reset all filters to their default values.
     */
    function resetFilters() {
        filters.completed = null;
        filters.title = '';
        filters.sortBy = 'created_at';
        filters.sortOrder = 'desc';
    }

    return {
        tasks,
        currentTask,
        pagination,
        filters,
        fetchTasks,
        fetchTask,
        createTask,
        updateTask,
        deleteTask,
        resetFilters,
    };
});
