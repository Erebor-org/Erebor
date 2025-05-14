import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import PrintMembers from '../views/PrintMembers.vue';
import Blacklist from '../views/CreateBlacklist.vue';
import Register from '../views/RegisterPage.vue';
import ViewWarnings from '../views/ViewWarnings.vue';
import ManageWarnings from '../views/ManageWarnings.vue';
import Statistiques from '../views/Statistiques.vue';
import EventsList from '../views/Events/EventsList.vue';
import EventDetails from '../views/Events/EventDetails.vue';
import EventForm from '../views/Events/EventForm.vue';
import LadderPage from '../views/Events/LadderPage.vue';

const routes = [
  { path: '/', redirect: () => {
      const authStore = useAuthStore();
      return authStore.token ? '/events' : '/inscription'; // ✅ Redirect based on login state
    } 
  },
  { path: '/inscription', name: 'Register', component: Register },
  { path: '/membres', name: 'PrintMembers', component: PrintMembers, meta: { requiresAuth: true } },
  { path: '/blacklist', name: 'Blacklist', component: Blacklist, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/warnings/:id/:pseudo', name: 'ViewWarnings', component: ViewWarnings, meta: { requiresAuth: true } },
  { path: '/warnings-management', name: 'ManageWarnings', component: ManageWarnings, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/statistiques', name: 'Statistiques', component: Statistiques, meta: { requiresAuth: true, requiresAdmin: true } },
  
  // Event system routes
  { path: '/events', name: 'EventsList', component: EventsList, meta: { requiresAuth: true } },
  { path: '/events/create', name: 'EventCreate', component: EventForm, meta: { requiresAuth: true, requiresAdminOrAnim: true } },
  { path: '/events/:id', name: 'EventDetails', component: EventDetails, meta: { requiresAuth: true } },
  { path: '/ladder', name: 'LadderPage', component: LadderPage, meta: { requiresAuth: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Handle authentication and role-based access
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  
  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.token) {
    return next('/inscription');
  }
  
  // Check if route requires admin role
  if (to.meta.requiresAdmin && 
      (!authStore.user?.roles?.includes('ROLE_ADMIN'))) {
    return next('/'); // Redirect to home if not admin
  }
  
  // Check if route requires admin or animator role
  if (to.meta.requiresAdminOrAnim && 
      (!authStore.user?.roles?.includes('ROLE_ADMIN') && 
       !authStore.user?.roles?.includes('ROLE_ANIM'))) {
    return next('/'); // Redirect to home if not admin or animator
  }
  
  next();
});

export default router;
