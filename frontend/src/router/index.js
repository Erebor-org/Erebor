import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import PrintMembers from '../views/PrintMembers.vue';
import Blacklist from '../views/CreateBlacklist.vue';
import Register from '../views/RegisterPage.vue';
import ViewWarnings from '../views/ViewWarnings.vue';
import ManageWarnings from '../views/ManageWarnings.vue';
import Statistiques from '../views/Statistiques.vue';
import ThemeDemo from '../views/ThemeDemo.vue';
import ThemeCustomizer from '../views/ThemeCustomizer.vue';
import ThemeCustomizerDemo from '../views/ThemeCustomizerDemo.vue';
import WheelView from '../views/WheelView.vue';
import WheelClassesView from '../views/WheelClassesView.vue';
import DofusdleClassic from '../views/DofusdleClassic.vue';

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
  { path: '/theme-demo', name: 'ThemeDemo', component: ThemeDemo },
  { path: '/theme-customizer', name: 'ThemeCustomizer', component: ThemeCustomizer },
  { path: '/theme-customizer-demo', name: 'ThemeCustomizerDemo', component: ThemeCustomizerDemo },
  { path: '/wheel', name: 'Wheel', component: WheelView, meta: { requiresAuth: true } },
  { path: '/wheel-classes', name: 'WheelClasses', component: WheelClassesView, meta: { requiresAuth: true } },
  { path: '/dofusdle/classic', name: 'DofusdleClassic', component: DofusdleClassic },
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
