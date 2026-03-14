<template>
  <div class="flex-grow flex flex-col items-center justify-start py-12 px-6">
    <!-- Success Toast -->
    <Transition name="fade">
      <div v-if="successMessage" class="fixed top-4 right-4 z-50 bg-emerald-500/90 backdrop-blur-sm text-white px-5 py-3 rounded-lg shadow-lg flex items-center gap-2 text-sm font-medium">
        <span class="material-symbols-outlined text-lg">check_circle</span>
        {{ successMessage }}
      </div>
    </Transition>

    <!-- Back Button -->
    <div class="w-full max-w-2xl mb-6">
      <router-link
        :to="{ name: 'tasks.index' }"
        class="flex items-center gap-2 text-slate-500 hover:text-[#2513ec] transition-colors text-sm font-medium"
      >
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        Back to Dashboard
      </router-link>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-16 text-slate-500 dark:text-slate-400">
      <span class="material-symbols-outlined text-4xl mb-2 animate-spin">progress_activity</span>
      <p>Loading task...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="!task" class="w-full max-w-2xl text-center py-16">
      <span class="material-symbols-outlined text-6xl text-slate-600 mb-4">error_outline</span>
      <h3 class="text-xl font-semibold text-slate-300 mb-2">Task not found</h3>
      <p class="text-slate-500 mb-6">The task you're looking for doesn't exist or was deleted.</p>
      <router-link :to="{ name: 'tasks.index' }" class="bg-[#2513ec] text-white px-6 py-2.5 rounded-lg font-semibold hover:opacity-90 transition-opacity inline-block">
        Back to Tasks
      </router-link>
    </div>

    <!-- Task Detail Card -->
    <div v-else class="w-full max-w-2xl bg-white dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-[#2513ec]/10 shadow-xl overflow-hidden">
      <div class="p-8">
        <div class="flex justify-between items-start mb-6">
          <div class="flex flex-col gap-3">
            <div class="flex items-center gap-2">
              <span
                :class="[
                  'px-2.5 py-1 rounded-full text-xs font-semibold flex items-center gap-1',
                  task.completed
                    ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                    : 'bg-amber-500/10 text-amber-500 border border-amber-500/20'
                ]"
              >
                <span class="material-symbols-outlined text-[14px]">{{ task.completed ? 'check_circle' : 'pending' }}</span>
                {{ task.completed ? 'Completed' : 'Pending' }}
              </span>
            </div>
            <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-100 leading-tight">
              {{ task.title }}
            </h2>
          </div>
          <div class="flex items-center gap-2">
            <router-link
              :to="{ name: 'tasks.edit', params: { id: task.id } }"
              aria-label="Edit task"
              class="p-2.5 rounded-lg bg-slate-100 dark:bg-[#2513ec]/10 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-[#2513ec]/20 transition-all border border-slate-200 dark:border-[#2513ec]/20"
            >
              <span class="material-symbols-outlined text-[20px]">edit</span>
            </router-link>
            <button
              @click="showDeleteModal = true"
              aria-label="Delete task"
              class="p-2.5 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500/20 transition-all border border-red-500/20"
            >
              <span class="material-symbols-outlined text-[20px]">delete</span>
            </button>
          </div>
        </div>

        <div class="space-y-8">
          <!-- Description -->
          <div class="space-y-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Description</h3>
            <p v-if="task.description" class="text-slate-600 dark:text-slate-300 leading-relaxed text-lg whitespace-pre-wrap">
              {{ task.description }}
            </p>
            <p v-else class="text-slate-400 dark:text-slate-600 italic">No description provided</p>
          </div>

          <!-- Metadata -->
          <div class="grid grid-cols-2 gap-6 pt-6 border-t border-slate-200 dark:border-[#2513ec]/10">
            <div class="space-y-1">
              <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Due Date</h4>
              <div class="flex items-center gap-2 text-slate-700 dark:text-slate-200">
                <span class="material-symbols-outlined text-[#2513ec]">calendar_today</span>
                <span class="font-medium">{{ task.due_date ? formatDate(task.due_date) : 'No due date' }}</span>
              </div>
            </div>
            <div class="space-y-1">
              <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Created</h4>
              <div class="flex items-center gap-2 text-slate-700 dark:text-slate-200">
                <span class="material-symbols-outlined text-[#2513ec]">schedule</span>
                <span class="font-medium">{{ formatDate(task.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="bg-slate-50 dark:bg-[#2513ec]/5 px-8 py-4 flex items-center justify-between border-t border-slate-200 dark:border-[#2513ec]/10">
        <span class="text-sm text-slate-500 italic">Last updated {{ formatDate(task.updated_at) }}</span>
        <button
          @click="toggleComplete"
          class="bg-[#2513ec] text-white px-6 py-2 rounded-lg font-bold text-sm hover:opacity-90 transition-all shadow-lg shadow-[#2513ec]/20"
        >
          {{ task.completed ? 'Mark as Incomplete' : 'Mark as Complete' }}
        </button>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-[#121022]/80"
      @click.self="showDeleteModal = false"
      @keydown.escape="showDeleteModal = false"
      tabindex="-1"
    >
      <div class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-6 text-center">
          <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
            <span class="material-symbols-outlined text-red-600 dark:text-red-500 text-3xl">delete_forever</span>
          </div>
          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Delete Task?</h3>
          <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed px-4">
            Are you sure you want to delete this task? This action cannot be undone.
          </p>
        </div>
        <div class="px-6 pb-6 flex flex-col gap-3">
          <button
            @click="handleDelete"
            class="flex h-12 w-full items-center justify-center rounded-lg bg-red-600 hover:bg-red-700 text-white text-base font-semibold transition-colors duration-200"
          >
            Delete Task
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
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useTaskStore } from '../stores/tasks.js';

const props = defineProps({
  id: { type: [String, Number], required: true },
});

const router = useRouter();
const taskStore = useTaskStore();

const task = ref(null);
const loading = ref(true);
const showDeleteModal = ref(false);
const successMessage = ref('');

function showSuccess(msg) {
  successMessage.value = msg;
  setTimeout(() => { successMessage.value = ''; }, 3000);
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

async function toggleComplete() {
  try {
    const newState = !task.value.completed;
    await taskStore.updateTask(props.id, { completed: newState });
    task.value = await taskStore.fetchTask(props.id);
    showSuccess(newState ? 'Task marked as complete' : 'Task marked as pending');
  } catch (error) {
    console.error('Failed to toggle task:', error);
  }
}

async function handleDelete() {
  try {
    await taskStore.deleteTask(props.id);
    router.push({ name: 'tasks.index' });
  } catch (error) {
    console.error('Failed to delete task:', error);
  }
}

onMounted(async () => {
  try {
    task.value = await taskStore.fetchTask(props.id);
  } finally {
    loading.value = false;
  }
});
</script>
