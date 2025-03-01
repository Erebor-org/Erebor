import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import PrintMembers from '../views/PrintMembers.vue';
import CreateBlacklist from '../views/CreateBlacklist.vue';
import Register from '../views/RegisterPage.vue';

const routes = [
  { path: '/inscription', name: 'Register', component: Register },
  { path: '/membres', name: 'PrintMembers', component: PrintMembers, meta: { requiresAuth: true } },
  { path: '/blacklist', name: 'Blacklist', component: CreateBlacklist, meta: { requiresAuth: true } },
  { path: '/', redirect: () => {
      const authStore = useAuthStore();
      return authStore.token ? '/membres' : '/inscription';
    }
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  // Redirect '/' dynamically based on auth state
  if (to.path === '/') {
    next(authStore.token ? '/membres' : '/inscription');
    return;
  }

  // Protect routes that require authentication
  if (to.meta.requiresAuth && !authStore.token) {
    next('/inscription');
    return;
  }

  // Restrict admin-only routes
  if (to.meta.requiresAdmin && (!authStore.user || !authStore.user.roles.includes('ROLE_ADMIN'))) {
    next('/membres');
    return;
  }

  next();
});

export default router;
