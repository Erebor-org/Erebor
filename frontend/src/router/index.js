import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import HomeView from '../views/HomeView.vue'
import PrintMembers from '../views/PrintMembers.vue'
import CreateBlacklist from '../views/CreateBlacklist.vue'
import Register from '../views/RegisterPage.vue'

const routes = [
  { path: '/', name: 'Home', component: HomeView },
  { path: '/inscription', name: 'Register', component: Register },
  { path: '/membres', name: 'PrintMembers', component: PrintMembers, meta: { requiresAuth: true } },
  { path: '/blacklist', name: 'Blacklist', component: CreateBlacklist, meta: { requiresAuth: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  if (to.meta.requiresAuth && !authStore.token) {
    next('/');
  } else if (to.meta.requiresAdmin && (!authStore.user || !authStore.user.roles.includes('ROLE_ADMIN'))) {
    next('/');
  } else {
    next();
  }
});

export default router;
