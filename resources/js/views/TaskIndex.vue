<template>
  <div class="px-6 md:px-20 py-8 max-w-[1280px] mx-auto w-full">
    <!-- Filter Bar -->
    <div class="flex flex-col lg:flex-row gap-4 mb-8 items-end lg:items-center bg-white dark:bg-[#2513ec]/5 p-6 rounded-xl border border-slate-200 dark:border-[#2513ec]/10">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1 w-full">
        <!-- Search -->
        <div class="flex flex-col gap-2">
          <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Search</span>
          <div class="relative group">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#2513ec]">search</span>
            <input
              v-model="taskStore.filters.title"
              type="text"
              placeholder="Search task title..."
              class="w-full bg-slate-100 dark:bg-[#121022] border-slate-200 dark:border-[#2513ec]/20 rounded-lg py-2 pl-10 pr-4 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-[#2513ec] focus:border-transparent outline-none transition-all"
              @input="debouncedFetch"
            />
          </div>
        </div>
        <!-- Status Filter -->
        <div class="flex flex-col gap-2">
          <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</span>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">filter_list</span>
            <select
              v-model="taskStore.filters.completed"
              class="w-full appearance-none bg-slate-100 dark:bg-[#121022] border-slate-200 dark:border-[#2513ec]/20 rounded-lg py-2 pl-10 pr-10 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-[#2513ec] focus:border-transparent outline-none transition-all"
              @change="fetchTasks()"
            >
              <option :value="null">All Status</option>
              <option :value="true">Completed</option>
              <option :value="false">Pending</option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
          </div>
        </div>
        <!-- Sort -->
        <div class="flex flex-col gap-2">
          <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sort By</span>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">sort</span>
            <select
              v-model="sortCombo"
              class="w-full appearance-none bg-slate-100 dark:bg-[#121022] border-slate-200 dark:border-[#2513ec]/20 rounded-lg py-2 pl-10 pr-10 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-[#2513ec] focus:border-transparent outline-none transition-all"
              @change="handleSortChange"
            >
              <option value="created_at:desc">Newest First</option>
              <option value="created_at:asc">Oldest First</option>
              <option value="due_date:asc">Due Date</option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
          </div>
        </div>
      </div>
      <button
        @click="openCreateModal"
        class="w-full lg:w-auto flex items-center justify-center gap-2 bg-[#2513ec] hover:bg-[#2513ec]/90 text-white font-bold py-3 px-8 rounded-lg transition-all shadow-lg shadow-[#2513ec]/20 h-[42px] mt-auto lg:mt-6"
      >
        <span class="material-symbols-outlined text-[20px]">add</span>
        <span>New Task</span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-16 text-slate-500 dark:text-slate-400">
      <span class="material-symbols-outlined text-4xl mb-2 animate-spin">progress_activity</span>
      <p>Loading tasks...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="taskStore.tasks.length === 0" class="text-center py-16">
      <span class="material-symbols-outlined text-5xl text-slate-400 dark:text-slate-600 mb-4">inbox</span>
      <p class="text-slate-500 dark:text-slate-400 text-lg mb-4">No tasks found</p>
      <button
        @click="openCreateModal"
        class="text-[#2513ec] font-bold hover:underline"
      >
        Create your first task
      </button>
    </div>

    <!-- Task Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div
        v-for="task in taskStore.tasks"
        :key="task.id"
        class="bg-white dark:bg-[#121022]/40 border border-slate-200 dark:border-[#2513ec]/10 rounded-xl p-6 hover:border-[#2513ec]/40 transition-colors group"
      >
        <div class="flex justify-between items-start mb-4">
          <div class="flex-1 min-w-0 mr-3">
            <router-link
              :to="{ name: 'tasks.show', params: { id: task.id } }"
              :class="[
                'text-lg font-bold transition-colors block',
                task.completed
                  ? 'text-slate-400 dark:text-slate-500 line-through'
                  : 'text-slate-900 dark:text-white group-hover:text-[#2513ec]'
              ]"
            >
              {{ task.title }}
            </router-link>
            <p
              :class="[
                'text-sm mt-1 line-clamp-2',
                task.completed ? 'text-slate-400 dark:text-slate-500' : 'text-slate-500 dark:text-slate-400'
              ]"
            >
              {{ task.description }}
            </p>
          </div>
          <div class="flex gap-1 shrink-0">
            <button
              @click="openEditModal(task)"
              class="p-2 text-slate-400 hover:text-[#2513ec] hover:bg-[#2513ec]/10 rounded-lg transition-all"
            >
              <span class="material-symbols-outlined">edit</span>
            </button>
            <button
              @click="confirmDelete(task)"
              class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-all"
            >
              <span class="material-symbols-outlined">delete</span>
            </button>
          </div>
        </div>
        <div class="flex items-center justify-between mt-6">
          <div class="flex items-center gap-4">
            <span
              v-if="task.due_date"
              :class="[
                'flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full border',
                task.completed
                  ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700'
                  : isOverdue(task.due_date)
                    ? 'bg-red-500/10 text-red-500 border-red-500/20'
                    : 'bg-[#2513ec]/10 text-[#2513ec] border-[#2513ec]/20'
              ]"
            >
              <span class="material-symbols-outlined text-sm">calendar_today</span>
              {{ formatDate(task.due_date) }}
            </span>
          </div>
          <div class="flex items-center gap-3">
            <span :class="['text-xs font-medium', task.completed ? 'text-green-500' : 'text-slate-500 dark:text-slate-400']">
              {{ task.completed ? 'Done' : 'Pending' }}
            </span>
            <button
              @click="toggleComplete(task)"
              :class="[
                'w-12 h-6 rounded-full relative transition-colors cursor-pointer',
                task.completed ? 'bg-green-500' : 'bg-slate-200 dark:bg-slate-700'
              ]"
            >
              <div
                :class="[
                  'absolute top-1 w-4 h-4 bg-white rounded-full transition-transform',
                  task.completed ? 'right-1' : 'left-1'
                ]"
              ></div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="taskStore.pagination.lastPage > 1" class="mt-12 flex items-center justify-center gap-2">
      <button
        :disabled="taskStore.pagination.currentPage <= 1"
        @click="fetchTasks(taskStore.pagination.currentPage - 1)"
        class="flex size-10 items-center justify-center rounded-lg border border-slate-200 dark:border-[#2513ec]/20 text-slate-600 dark:text-slate-400 hover:bg-[#2513ec]/10 hover:text-[#2513ec] transition-all disabled:opacity-30 disabled:cursor-not-allowed"
      >
        <span class="material-symbols-outlined">chevron_left</span>
      </button>
      <button
        v-for="page in paginationPages"
        :key="page"
        @click="fetchTasks(page)"
        :class="[
          'size-10 flex items-center justify-center rounded-lg font-bold transition-all',
          page === taskStore.pagination.currentPage
            ? 'bg-[#2513ec] text-white'
            : 'border border-slate-200 dark:border-[#2513ec]/20 text-slate-600 dark:text-slate-400 hover:bg-[#2513ec]/10 hover:text-[#2513ec]'
        ]"
      >
        {{ page }}
      </button>
      <button
        :disabled="taskStore.pagination.currentPage >= taskStore.pagination.lastPage"
        @click="fetchTasks(taskStore.pagination.currentPage + 1)"
        class="flex size-10 items-center justify-center rounded-lg border border-slate-200 dark:border-[#2513ec]/20 text-slate-600 dark:text-slate-400 hover:bg-[#2513ec]/10 hover:text-[#2513ec] transition-all disabled:opacity-30 disabled:cursor-not-allowed"
      >
        <span class="material-symbols-outlined">chevron_right</span>
      </button>
    </div>

    <!-- Create/Edit Task Modal -->
    <div
      v-if="showTaskModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-[#121022]/60"
      @click.self="showTaskModal = false"
    >
      <div class="w-full max-w-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl overflow-hidden">
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
          <div>
            <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ editingTask ? 'Edit Task' : 'New Task' }}</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ editingTask ? 'Update task details and status' : 'Create a new task to track' }}</p>
          </div>
          <button @click="showTaskModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <!-- Modal Body -->
        <div class="p-6 space-y-6">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
              Title <span class="text-red-500">*</span>
            </label>
            <input
              v-model="taskForm.title"
              type="text"
              required
              maxlength="255"
              class="w-full px-4 py-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 focus:border-[#2513ec] focus:ring-2 focus:ring-[#2513ec]/20 transition-all outline-none text-slate-900 dark:text-slate-100"
              placeholder="e.g. Design system review"
            />
          </div>
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
              Description <span class="text-slate-400 text-xs font-normal ml-1">(Optional)</span>
            </label>
            <textarea
              v-model="taskForm.description"
              rows="4"
              class="w-full px-4 py-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 focus:border-[#2513ec] focus:ring-2 focus:ring-[#2513ec]/20 transition-all outline-none text-slate-900 dark:text-slate-100 resize-none"
              placeholder="Add more details about this task..."
            ></textarea>
          </div>
          <div class="grid grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                Due Date <span class="text-slate-400 text-xs font-normal ml-1">(Optional)</span>
              </label>
              <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl pointer-events-none">calendar_today</span>
                <input
                  v-model="taskForm.due_date"
                  type="date"
                  class="w-full pl-10 pr-4 py-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 focus:border-[#2513ec] focus:ring-2 focus:ring-[#2513ec]/20 transition-all outline-none text-slate-900 dark:text-slate-100"
                />
              </div>
            </div>
            <div v-if="editingTask" class="space-y-2">
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
              <div class="flex items-center h-[50px]">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input v-model="taskForm.completed" type="checkbox" class="sr-only peer" />
                  <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-[#2513ec]"></div>
                  <span class="ms-3 text-sm font-medium text-slate-600 dark:text-slate-400">Completed</span>
                </label>
              </div>
            </div>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="px-6 py-5 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row-reverse gap-3">
          <button
            @click="handleSaveTask"
            :disabled="savingTask || !taskForm.title.trim()"
            class="w-full sm:w-auto px-8 py-2.5 bg-[#2513ec] text-white font-semibold rounded-lg hover:bg-opacity-90 active:scale-95 transition-all shadow-lg shadow-[#2513ec]/20 disabled:opacity-50"
          >
            {{ savingTask ? 'Saving...' : (editingTask ? 'Save Task' : 'Create Task') }}
          </button>
          <button
            @click="showTaskModal = false"
            class="w-full sm:w-auto px-8 py-2.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-700 transition-all"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-[#121022]/80"
      @click.self="showDeleteModal = false"
    >
      <div class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-6 text-center">
          <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
            <span class="material-symbols-outlined text-red-600 dark:text-red-500 text-3xl">delete_forever</span>
          </div>
          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Delete Task?</h3>
          <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed px-4">
            Are you sure you want to delete "<strong>{{ taskToDelete?.title }}</strong>"? This action cannot be undone.
          </p>
        </div>
        <div class="px-6 pb-6 flex flex-col gap-3">
          <button
            @click="handleDelete"
            :disabled="deleting"
            class="flex h-12 w-full items-center justify-center rounded-lg bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-base font-semibold transition-colors duration-200"
          >
            {{ deleting ? 'Deleting...' : 'Delete Task' }}
          </button>
          <button
            @click="showDeleteModal = false"
            class="flex h-12 w-full items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-base font-semibold transition-colors duration-200"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useTaskStore } from '../stores/tasks.js';

const taskStore = useTaskStore();
const loading = ref(false);

// Delete modal state
const showDeleteModal = ref(false);
const taskToDelete = ref(null);
const deleting = ref(false);

// Create/Edit modal state
const showTaskModal = ref(false);
const editingTask = ref(null);
const savingTask = ref(false);
const taskForm = ref({
  title: '',
  description: '',
  due_date: '',
  completed: false,
});

// Sort combo (combines sortBy + sortOrder into one select)
const sortCombo = ref('created_at:desc');

// Debounce timer
let searchTimer = null;

// Pagination pages array
const paginationPages = computed(() => {
  const pages = [];
  for (let i = 1; i <= taskStore.pagination.lastPage; i++) {
    pages.push(i);
  }
  return pages;
});

async function fetchTasks(page = 1) {
  loading.value = true;
  try {
    await taskStore.fetchTasks(page);
  } finally {
    loading.value = false;
  }
}

function debouncedFetch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => fetchTasks(), 300);
}

function handleSortChange() {
  const [sortBy, sortOrder] = sortCombo.value.split(':');
  taskStore.filters.sortBy = sortBy;
  taskStore.filters.sortOrder = sortOrder;
  fetchTasks();
}

function openCreateModal() {
  editingTask.value = null;
  taskForm.value = { title: '', description: '', due_date: '', completed: false };
  showTaskModal.value = true;
}

function openEditModal(task) {
  editingTask.value = task;
  taskForm.value = {
    title: task.title,
    description: task.description || '',
    due_date: task.due_date ? task.due_date.split('T')[0] : '',
    completed: task.completed,
  };
  showTaskModal.value = true;
}

async function handleSaveTask() {
  savingTask.value = true;
  try {
    const payload = { title: taskForm.value.title };
    if (taskForm.value.description) payload.description = taskForm.value.description;
    if (taskForm.value.due_date) payload.due_date = taskForm.value.due_date;

    if (editingTask.value) {
      payload.completed = taskForm.value.completed;
      payload.description = taskForm.value.description || null;
      payload.due_date = taskForm.value.due_date || null;
      await taskStore.updateTask(editingTask.value.id, payload);
    } else {
      await taskStore.createTask(payload);
    }
    showTaskModal.value = false;
    await fetchTasks(taskStore.pagination.currentPage);
  } finally {
    savingTask.value = false;
  }
}

async function toggleComplete(task) {
  await taskStore.updateTask(task.id, { completed: !task.completed });
  await fetchTasks(taskStore.pagination.currentPage);
}

function confirmDelete(task) {
  taskToDelete.value = task;
  showDeleteModal.value = true;
}

async function handleDelete() {
  if (!taskToDelete.value) return;
  deleting.value = true;
  try {
    await taskStore.deleteTask(taskToDelete.value.id);
    showDeleteModal.value = false;
    taskToDelete.value = null;
    await fetchTasks(taskStore.pagination.currentPage);
  } finally {
    deleting.value = false;
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

function isOverdue(dateStr) {
  if (!dateStr) return false;
  return new Date(dateStr) < new Date(new Date().toDateString());
}

onMounted(() => fetchTasks());
</script>
