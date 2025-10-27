<script setup>
import { RouterView } from 'vue-router'
import NavigationBar from './components/NavigationBar.vue'
import { useAuthStore } from './stores/authStore'
import { onMounted, onBeforeUnmount } from 'vue'
import { startDisconnectPolling, stopDisconnectPolling } from './config/axios'

const authStore = useAuthStore()

onMounted(async () => {
  // Refresh user profile if user is logged in to ensure roles are up to date
  if (authStore.token) {
    try {
      await authStore.fetchUserProfile()
    } catch (error) {
      console.error('Failed to fetch user profile:', error)
      // If profile fetch fails (e.g., token expired), logout the user
      authStore.logout()
    }
  }
  
  // Start polling for forced disconnects
  startDisconnectPolling()
})

onBeforeUnmount(() => {
  // Stop polling when component is destroyed
  stopDisconnectPolling()
})
</script>

<template>
  <div class="h-screen">
    <header class="w-full bg-theme-card">
        <nav>
        <NavigationBar />
        </nav>
    </header>
    <RouterView class="h-[calc(100vh-128px)] overflow-auto"/>
  </div>
</template>

<style scoped>
/*header {
  line-height: 1.5;
  max-height: 100vh;
}

.logo {
  display: block;
  margin: 0 auto 2rem;
}

nav {
  width: 100%;
  font-size: 12px;
  text-align: center;
  margin-top: 2rem;
}

nav a.router-link-exact-active {
  color: var(--color-text);
}

nav a.router-link-exact-active:hover {
  background-color: transparent;
}

nav a {
  display: inline-block;
  padding: 0 1rem;
  border-left: 1px solid var(--color-border);
}

nav a:first-of-type {
  border: 0;
}

@media (min-width: 1024px) {
  header {
    display: flex;
    place-items: center;
    padding-right: calc(var(--section-gap) / 2);
  }

  .logo {
    margin: 0 2rem 0 0;
  }

  header .wrapper {
    display: flex;
    place-items: flex-start;
    flex-wrap: wrap;
  }

  nav {
    text-align: left;
    margin-left: -1rem;
    font-size: 1rem;

    padding: 1rem 0;
    margin-top: 1rem;
  }
}*/
</style>
