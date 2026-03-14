<template>
  <div class="min-h-screen flex items-center justify-center p-4">
    <!-- Background Decoration -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-[#2513ec]/10 rounded-full blur-[120px]"></div>
      <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-[#2513ec]/10 rounded-full blur-[120px]"></div>
    </div>

    <div class="relative z-10 w-full max-w-[480px]">
      <div class="bg-white dark:bg-slate-900/50 backdrop-blur-xl border border-slate-200 dark:border-slate-800 p-8 sm:p-10 rounded-xl shadow-2xl">
        <!-- Logo & Header -->
        <div class="flex flex-col items-center mb-8 text-center">
          <div class="size-12 bg-[#2513ec] flex items-center justify-center rounded-xl mb-6 shadow-lg shadow-[#2513ec]/20">
            <span class="material-symbols-outlined text-white text-3xl">shield_person</span>
          </div>
          <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Create account</h1>
          <p class="mt-2 text-slate-600 dark:text-slate-400">Join our community today</p>
        </div>

        <!-- Error Alert -->
        <div
          v-if="errorMessage"
          class="bg-red-500/10 border border-red-500/20 text-red-500 px-4 py-3 rounded-lg mb-5 text-sm flex items-center gap-2"
        >
          <span class="material-symbols-outlined text-lg">error</span>
          {{ errorMessage }}
        </div>

        <!-- Registration Form -->
        <form @submit.prevent="handleRegister" class="space-y-5">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300 ml-1">First Name</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full h-12 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-[#2513ec] focus:border-transparent transition-all outline-none"
                placeholder="John"
              />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300 ml-1">Last Name</label>
              <input
                v-model="form.last_name"
                type="text"
                required
                class="w-full h-12 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-[#2513ec] focus:border-transparent transition-all outline-none"
                placeholder="Doe"
              />
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700 dark:text-slate-300 ml-1">Email Address</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">mail</span>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full h-12 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg pl-11 pr-4 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-[#2513ec] focus:border-transparent transition-all outline-none"
                placeholder="name@example.com"
              />
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700 dark:text-slate-300 ml-1">Password</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">lock</span>
              <input
                v-model="form.password"
                type="password"
                required
                minlength="8"
                class="w-full h-12 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg pl-11 pr-4 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-[#2513ec] focus:border-transparent transition-all outline-none"
                placeholder="••••••••"
              />
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700 dark:text-slate-300 ml-1">Confirm Password</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">lock_reset</span>
              <input
                v-model="form.password_confirmation"
                type="password"
                required
                class="w-full h-12 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg pl-11 pr-4 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-[#2513ec] focus:border-transparent transition-all outline-none"
                placeholder="••••••••"
              />
            </div>
          </div>

          <div class="pt-4">
            <button
              type="submit"
              :disabled="loading"
              class="w-full h-12 bg-[#2513ec] hover:bg-[#2513ec]/90 disabled:opacity-50 text-white font-semibold rounded-lg shadow-lg shadow-[#2513ec]/30 transition-all flex items-center justify-center gap-2"
            >
              {{ loading ? 'Creating account...' : 'Create Account' }}
              <span v-if="!loading" class="material-symbols-outlined text-lg">arrow_forward</span>
            </button>
          </div>
        </form>

        <!-- Footer Link -->
        <p class="mt-8 text-center text-sm text-slate-600 dark:text-slate-400">
          Already have an account?
          <router-link :to="{ name: 'login' }" class="text-[#2513ec] font-semibold hover:underline decoration-2 underline-offset-4 ml-1">Sign In</router-link>
        </p>
      </div>

      <!-- Privacy Policy Notice -->
      <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-500 px-4">
        By creating an account, you agree to our Terms of Service and Privacy Policy.
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const loading = ref(false);
const errorMessage = ref('');

async function handleRegister() {
  loading.value = true;
  errorMessage.value = '';

  try {
    await authStore.register(form);
    router.push({ name: 'tasks.index' });
  } catch (error) {
    if (error.response?.data?.errors) {
      errorMessage.value = Object.values(error.response.data.errors).flat().join(' ');
    } else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'An unexpected error occurred. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
