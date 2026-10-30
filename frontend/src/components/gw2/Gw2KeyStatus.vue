<template>
  <!-- Permissions of the server-side GW2 API key, shown at the top of every GW2 page -->
  <div class="glass-card rounded-2xl p-5 mb-6">
    <div v-if="loading" class="text-sm text-theme-text-muted">Vérification de la clé API...</div>
    <div v-else-if="error" class="text-sm text-theme-error font-medium">{{ error }}</div>
    <div v-else class="flex flex-col md:flex-row md:items-center gap-3">
      <div class="flex items-center gap-2 flex-shrink-0">
        <svg class="w-5 h-5" :class="info.missing.length ? 'text-theme-warning' : 'text-theme-success'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
        <span class="font-semibold text-theme-text">Clé « {{ info.name }} »</span>
      </div>
      <div class="flex flex-wrap gap-1.5">
        <span
          v-for="permission in info.expected"
          :key="permission"
          class="gw-chip"
          :class="info.permissions.includes(permission) ? 'gw-chip--ok' : 'gw-chip--missing'"
          :title="info.permissions.includes(permission) ? 'Permission accordée' : 'Permission manquante'"
        >
          {{ permissionLabel(permission) }}
        </span>
        <span v-for="permission in extraPermissions" :key="permission" class="gw-chip" title="Permission accordée (non requise)">
          {{ permissionLabel(permission) }}
        </span>
      </div>
      <span v-if="info.missing.length" class="text-xs text-theme-warning md:ml-auto">
        {{ info.missing.length }} permission{{ info.missing.length > 1 ? 's' : '' }} manquante{{ info.missing.length > 1 ? 's' : '' }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { fetchGw2TokenInfo, gw2ErrorMessage } from '@/services/gw2Api';
import { PERMISSION_LABELS } from '@/utils/gw2';

const info = ref(null);
const loading = ref(true);
const error = ref('');

const permissionLabel = (permission) => PERMISSION_LABELS[permission] || permission;

const extraPermissions = computed(() =>
  (info.value?.permissions || []).filter(p => !info.value.expected.includes(p))
);

onMounted(async () => {
  try {
    info.value = await fetchGw2TokenInfo();
  } catch (e) {
    error.value = gw2ErrorMessage(e, 'Impossible de vérifier la clé API');
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.gw-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.15rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid var(--border);
  color: var(--text-muted);
}
.gw-chip--ok {
  color: var(--success);
  border-color: color-mix(in srgb, var(--success) 40%, transparent);
  background-color: color-mix(in srgb, var(--success) 10%, transparent);
}
.gw-chip--missing {
  color: var(--error);
  border-color: color-mix(in srgb, var(--error) 40%, transparent);
  background-color: color-mix(in srgb, var(--error) 10%, transparent);
  text-decoration: line-through;
}
</style>
