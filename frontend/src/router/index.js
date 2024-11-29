import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import Register from '../views/RegisterPage.vue'
import CreateMember from '../views/CreateMember.vue'
import PrintMembers from '../views/PrintMembers.vue'
import CreateBlacklist from '../views/CreateBlacklist.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/inscription',
      name: 'Register',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: Register,
    },
    {
      path: '/import',
      name: 'CreateMember',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: CreateMember,
    },
    {
      path: '/membres',
      name: 'PrintMembers',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: PrintMembers,
    },
    {
      path: '/blacklist',
      name: 'CreateBlacklist',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: CreateBlacklist,
    },
  ],
})

export default router
