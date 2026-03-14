<!--
  Root Application Component

  Provides the app shell with navigation header and router-view outlet.
  Dark theme matching the Stitch design system.
-->
<template>
  <div class="min-h-screen flex flex-col">
    <!-- Navigation Bar — only shown when authenticated -->
    <header
      v-if="isAuthenticated"
      class="flex items-center justify-between whitespace-nowrap border-b border-solid border-slate-200 dark:border-[#2513ec]/20 px-6 md:px-20 py-4 bg-white dark:bg-[#121022]/50 backdrop-blur-md sticky top-0 z-50"
    >
      <div class="flex items-center gap-3">
        <div class="size-8 bg-[#2513ec] rounded-lg flex items-center justify-center text-white">
          <span class="material-symbols-outlined">task_alt</span>
        </div>
        <router-link :to="{ name: 'tasks.index' }" class="text-slate-900 dark:text-white text-xl font-bold leading-tight tracking-tight">
          Task Manager
        </router-link>
      </div>
      <div class="flex items-center gap-4">
        <div class="hidden md:flex flex-col items-end">
          <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ authStore.fullName }}</span>
          <span class="text-xs text-slate-500 dark:text-slate-400">User Account</span>
        </div>
        <div class="h-10 w-10 rounded-full border-2 border-[#2513ec]/30 overflow-hidden bg-[#2513ec]/20 flex items-center justify-center">
          <span class="material-symbols-outlined text-[#2513ec]">person</span>
        </div>
        <button
          @click="handleLogout"
          class="flex min-w-[84px] cursor-pointer items-center justify-center rounded-lg h-10 px-4 bg-[#2513ec] text-white text-sm font-bold transition-opacity hover:opacity-90"
        >
          Logout
        </button>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow">
      <router-view />
    </main>

    <!-- Footer — only on authenticated pages -->
    <footer v-if="isAuthenticated" class="mt-auto py-10 px-6 md:px-20 text-center text-slate-500 dark:text-slate-600 text-sm">
      <p>&copy; 2026 Task Manager. All rights reserved.</p>
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from './stores/auth.js';

const router = useRouter();
const authStore = useAuthStore();

const isAuthenticated = computed(() => authStore.isAuthenticated);

/**
 * Handle logout — clear auth state and redirect to login page.
 */
async function handleLogout() {
  await authStore.logout();
  router.push({ name: 'login' });
}
</script>
