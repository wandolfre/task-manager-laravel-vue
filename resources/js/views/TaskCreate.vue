<template>
  <div class="flex-grow flex items-center justify-center py-12 px-6">
    <!-- Modal-style centered form -->
    <div class="w-full max-w-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
        <div>
          <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100">New Task</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Create a new task to track</p>
        </div>
        <router-link :to="{ name: 'tasks.index' }" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
          <span class="material-symbols-outlined">close</span>
        </router-link>
      </div>

      <!-- Error Alert -->
      <div
        v-if="errorMessage"
        class="mx-6 mt-6 bg-red-500/10 border border-red-500/20 text-red-500 px-4 py-3 rounded-lg text-sm flex items-center gap-2"
      >
        <span class="material-symbols-outlined text-lg">error</span>
        {{ errorMessage }}
      </div>

      <!-- Body -->
      <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            Title <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.title"
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
            v-model="form.description"
            rows="4"
            class="w-full px-4 py-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 focus:border-[#2513ec] focus:ring-2 focus:ring-[#2513ec]/20 transition-all outline-none text-slate-900 dark:text-slate-100 resize-none"
            placeholder="Add more details about this task..."
          ></textarea>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            Due Date <span class="text-slate-400 text-xs font-normal ml-1">(Optional)</span>
          </label>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl pointer-events-none">calendar_today</span>
            <input
              v-model="form.due_date"
              type="date"
              class="w-full pl-10 pr-4 py-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 focus:border-[#2513ec] focus:ring-2 focus:ring-[#2513ec]/20 transition-all outline-none text-slate-900 dark:text-slate-100"
            />
          </div>
        </div>
      </form>

      <!-- Footer -->
      <div class="px-6 py-5 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row-reverse gap-3">
        <button
          @click="handleSubmit"
          :disabled="loading || !form.title.trim()"
          class="w-full sm:w-auto px-8 py-2.5 bg-[#2513ec] text-white font-semibold rounded-lg hover:bg-opacity-90 active:scale-95 transition-all shadow-lg shadow-[#2513ec]/20 disabled:opacity-50"
        >
          {{ loading ? 'Creating...' : 'Create Task' }}
        </button>
        <router-link
          :to="{ name: 'tasks.index' }"
          class="w-full sm:w-auto px-8 py-2.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-700 transition-all text-center"
        >
          Cancel
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useTaskStore } from '../stores/tasks.js';

const router = useRouter();
const taskStore = useTaskStore();

const form = reactive({
  title: '',
  description: '',
  due_date: '',
});

const loading = ref(false);
const errorMessage = ref('');

async function handleSubmit() {
  if (!form.title.trim()) return;
  loading.value = true;
  errorMessage.value = '';

  try {
    const payload = { title: form.title };
    if (form.description) payload.description = form.description;
    if (form.due_date) payload.due_date = form.due_date;

    await taskStore.createTask(payload);
    router.push({ name: 'tasks.index' });
  } catch (error) {
    if (error.response?.data?.errors) {
      errorMessage.value = Object.values(error.response.data.errors).flat().join(' ');
    } else {
      errorMessage.value = 'Failed to create task. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
