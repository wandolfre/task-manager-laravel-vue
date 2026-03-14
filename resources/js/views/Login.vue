<template>
  <div class="min-h-screen flex items-center justify-center p-4">
    <!-- Background Decoration -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-[#2513ec]/10 rounded-full blur-[120px]"></div>
      <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-[#2513ec]/10 rounded-full blur-[120px]"></div>
    </div>

    <main class="relative z-10 w-full max-w-[440px]">
      <!-- Main Card -->
      <div class="bg-white dark:bg-slate-900/50 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl p-8 md:p-10">
        <!-- Logo/Icon Section -->
        <div class="flex flex-col items-center mb-8">
          <div class="w-16 h-16 bg-[#2513ec]/10 rounded-xl flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-[#2513ec] text-4xl">task_alt</span>
          </div>
          <h1 class="text-slate-900 dark:text-slate-100 text-3xl font-black leading-tight tracking-tight">Task Manager</h1>
          <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm">Welcome back! Please enter your details.</p>
        </div>

        <!-- Error Alert -->
        <div
          v-if="errorMessage"
          class="bg-red-500/10 border border-red-500/20 text-red-500 px-4 py-3 rounded-lg mb-5 text-sm flex items-center gap-2"
        >
          <span class="material-symbols-outlined text-lg">error</span>
          {{ errorMessage }}
        </div>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <!-- Email Input -->
          <div class="flex flex-col gap-2">
            <label for="email" class="text-slate-700 dark:text-slate-300 text-sm font-semibold flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">mail</span>
              Email Address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              class="form-input w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 px-4 py-3 focus:ring-2 focus:ring-[#2513ec]/20 focus:border-[#2513ec] outline-none transition-all duration-200 placeholder:text-slate-400 dark:placeholder:text-slate-600"
              placeholder="name@company.com"
            />
          </div>

          <!-- Password Input -->
          <div class="flex flex-col gap-2">
            <label for="password" class="text-slate-700 dark:text-slate-300 text-sm font-semibold flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">lock</span>
              Password
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              autocomplete="current-password"
              class="form-input w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 px-4 py-3 focus:ring-2 focus:ring-[#2513ec]/20 focus:border-[#2513ec] outline-none transition-all duration-200 placeholder:text-slate-400 dark:placeholder:text-slate-600"
              placeholder="••••••••"
            />
          </div>

          <!-- Sign In Button -->
          <div class="pt-2">
            <button
              type="submit"
              :disabled="loading"
              class="w-full bg-[#2513ec] hover:bg-[#2513ec]/90 disabled:opacity-50 text-white font-bold py-3.5 px-6 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 group"
            >
              <span>{{ loading ? 'Signing in...' : 'Sign In' }}</span>
              <span v-if="!loading" class="material-symbols-outlined text-lg transition-transform group-hover:translate-x-1">login</span>
            </button>
          </div>
        </form>

        <!-- Register Link -->
        <div class="mt-10 text-center">
          <p class="text-slate-600 dark:text-slate-400 text-sm">
            Don't have an account?
            <router-link :to="{ name: 'register' }" class="text-[#2513ec] font-bold hover:underline ml-1">Register for free</router-link>
          </p>
        </div>
      </div>

      <!-- Footer Info -->
      <footer class="mt-8 text-center">
        <p class="text-slate-500 dark:text-slate-600 text-xs">&copy; 2026 Task Manager. Secure cloud infrastructure.</p>
      </footer>
    </main>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: '',
});

const loading = ref(false);
const errorMessage = ref('');

async function handleLogin() {
  loading.value = true;
  errorMessage.value = '';

  try {
    await authStore.login(form);
    router.push({ name: 'tasks.index' });
  } catch (error) {
    if (error.response?.status === 401) {
      errorMessage.value = 'Invalid email or password.';
    } else if (error.response?.data?.errors) {
      errorMessage.value = Object.values(error.response.data.errors).flat().join(' ');
    } else {
      errorMessage.value = 'An unexpected error occurred. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
