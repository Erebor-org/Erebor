import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import PrintMembers from '../views/PrintMembers.vue';
import Blacklist from '../views/CreateBlacklist.vue';
import Register from '../views/RegisterPage.vue';
import ViewWarnings from '../views/ViewWarnings.vue';
import ManageWarnings from '../views/ManageWarnings.vue';
import Statistiques from '../views/Statistiques.vue';

const routes = [
  { path: '/', redirect: () => {
      const authStore = useAuthStore();
      return authStore.token ? '/membres' : '/inscription'; // ✅ Redirect based on login state
    } 
  },
  { path: '/inscription', name: 'Register', component: Register },
  { path: '/membres', name: 'PrintMembers', component: PrintMembers, meta: { requiresAuth: true } },
  { path: '/blacklist', name: 'Blacklist', component: Blacklist, meta: { requiresAuth: true } },
  { path: '/warnings/:id/:pseudo', name: 'ViewWarnings', component: ViewWarnings, meta: { requiresAuth: true } },
  { path: '/warnings-management', name: 'ManageWarnings', component: ManageWarnings, meta: { requiresAuth: true } },
  { path: '/statistiques', name: 'Statistiques', component: Statistiques, meta: { requiresAuth: true } },
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
