/**
 * Vue Router Configuration
 *
 * Defines all application routes with navigation guards.
 * Protected routes redirect to login if no auth token is present.
 * Auth routes (login/register) redirect to tasks if already authenticated.
 */
import { createRouter, createWebHistory } from 'vue-router';

// Lazy-load views for code splitting
const Login = () => import('../views/Login.vue');
const Register = () => import('../views/Register.vue');
const TaskIndex = () => import('../views/TaskIndex.vue');
const TaskShow = () => import('../views/TaskShow.vue');
const TaskCreate = () => import('../views/TaskCreate.vue');
const TaskEdit = () => import('../views/TaskEdit.vue');

const routes = [
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { guest: true },
    },
    {
        path: '/',
        redirect: '/tasks',
    },
    {
        path: '/tasks',
        name: 'tasks.index',
        component: TaskIndex,
        meta: { requiresAuth: true },
    },
    {
        path: '/tasks/create',
        name: 'tasks.create',
        component: TaskCreate,
        meta: { requiresAuth: true },
    },
    {
        path: '/tasks/:id',
        name: 'tasks.show',
        component: TaskShow,
        meta: { requiresAuth: true },
        props: true,
    },
    {
        path: '/tasks/:id/edit',
        name: 'tasks.edit',
        component: TaskEdit,
        meta: { requiresAuth: true },
        props: true,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

/**
 * Global navigation guard.
 *
 * - Routes with meta.requiresAuth redirect to /login if no token exists.
 * - Routes with meta.guest redirect to /tasks if already authenticated.
 */
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token');

    if (to.meta.requiresAuth && !token) {
        next({ name: 'login' });
    } else if (to.meta.guest && token) {
        next({ name: 'tasks.index' });
    } else {
        next();
    }
});

export default router;
