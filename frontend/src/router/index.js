import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import PrintMembers from '../views/PrintMembers.vue';
import Register from '../views/RegisterPage.vue';

const routes = [
  { path: '/', redirect: () => {
      const authStore = useAuthStore();
      return authStore.token ? '/membres' : '/inscription'; // ✅ Redirect based on login state
    } 
  },
  { path: '/inscription', name: 'Register', component: Register },
  { path: '/membres', name: 'PrintMembers', component: PrintMembers, meta: { requiresAuth: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// ✅ Redirect users who are not logged in
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  if (to.meta.requiresAuth && !authStore.token) {
    next('/inscription');
  } else {
    next();
  }
});

export default router;
