<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <ConfirmModal
      :show="showConfirmSwitch"
      title="Confirmer le switch"
      :message="confirmSwitchMessage"
      confirmText="Oui, switcher"
      @confirm="doSwitchWithMule"
      @cancel="showConfirmSwitch = false"
    />
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-theme-bg bg-opacity-75 transition-opacity" @click="closeModal"></div>
    
    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div class="relative w-full max-w-4xl bg-theme-card rounded-2xl border border-theme-border shadow-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-theme-border">
          <div class="flex items-center space-x-4">
            <img
              :src="classes[member?.member?.class]"
              :alt="`Classe ${member?.member?.class}`"
              class="w-12 h-12 rounded-lg border-2 border-theme-border"
            />
            <div>
              <h2 class="text-2xl font-bold text-theme-primary">Mules de {{ member?.member?.pseudo }}</h2>
              <p class="text-theme-text-muted">{{ filteredMulesByCharacter(member?.id)?.length || 0 }} mule(s)</p>
            </div>
          </div>
          <button
            @click="closeModal"
            class="text-theme-text-muted hover:text-theme-text transition-colors duration-200"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-6">
          <!-- Add Mule Button -->
          <div class="mb-6">
            <button
              @click="openAddMuleModal"
              class="w-full px-4 py-3 bg-theme-primary hover:bg-theme-primary-hover text-white font-medium rounded-lg transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-primary/30 shadow-sm hover:shadow-md flex items-center justify-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span>Ajouter une mule</span>
            </button>
          </div>

          <!-- Mules List -->
          <div v-if="filteredMulesByCharacter(member?.id) && filteredMulesByCharacter(member?.id).length > 0" class="space-y-4">
            <div
              v-for="mule in filteredMulesByCharacter(member?.id)"
              :key="`mule-${mule.id}`"
              class="bg-theme-bg-muted rounded-lg p-4 border border-theme-bg-muted hover:border-theme-primary transition-colors duration-200"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <div class="relative">
                    <ClassDropdown
                      :class-name="mule.class"
                      :classes="classes"
                      :entity-id="mule.id"
                      :entity-type="'mule'"
                      @update-class="updateMuleClass"
                    />
                  </div>
                  <div class="flex-1 min-w-0">
                    <EditablePseudo
                      :entity="mule"
                      :entity-type="'mule'"
                      :editing-pseudo="editingPseudo"
                      :edit-pseudo="editPseudo"
                      @save-pseudo="savePseudo"
                    />
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <button
                    @click="openMuleModal(mule)"
                    class="px-3 py-2 text-sm bg-theme-error hover:bg-theme-error/80 text-white font-medium rounded-lg transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-error/30 shadow-sm hover:shadow-md"
                  >
                    Archiver
                  </button>
                  <button
                    @click="confirmSwitchWithMule(member.member, mule)"
                    class="px-3 py-2 text-xs bg-theme-primary hover:bg-theme-primary/80 text-white font-semibold rounded-lg border border-theme-primary transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-primary/30 shadow-sm hover:shadow-md"
                  >
                    Switch avec ce main
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- No Mules State -->
          <div v-else class="text-center py-12">
            <div class="text-theme-text-muted">
              <svg class="w-20 h-20 mx-auto mb-6 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
              <p class="text-xl font-medium text-theme-text-muted mb-2">Aucune mule disponible</p>
              <p class="text-theme-text-muted">Cliquez sur "Ajouter une mule" pour en créer une</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import EditablePseudo from './EditablePseudo.vue';
import ClassDropdown from './ClassDropdown.vue';
import ConfirmModal from './ConfirmModal.vue';
import { useAuthStore } from '@/stores/authStore';
const API_URL = import.meta.env.VITE_API_URL;

export default {
  name: 'MulesModal',
  data() {
    return {
      showConfirmSwitch: false,
      switchMain: null,
      switchMule: null,
    };
  },
  components: {
    EditablePseudo,
    ClassDropdown,
    ConfirmModal,
  },
  computed: {
    confirmSwitchMessage() {
      if (!this.switchMain || !this.switchMule) return '';
      return `Êtes-vous sûr de vouloir échanger ${this.switchMain.pseudo} avec la mule ${this.switchMule.pseudo} ?`;
    },
  },
  props: {
    show: {
      type: Boolean,
      default: false,
    },
    member: {
      type: Object,
      default: null,
    },
    classes: {
      type: Object,
      required: true,
    },
    filteredMulesByCharacter: {
      type: Function,
      required: true,
    },
    editingPseudo: {
      type: Object,
      default: () => ({ type: null, id: null }),
    },
    editPseudo: {
      type: String,
      default: '',
    },
  },
  emits: [
    'close',
    'save-pseudo',
    'update-mule-class',
    'open-mule-modal',
    'open-add-mule-modal',
    'refresh-data',
  ],
  methods: {
    closeModal() {
      this.$emit('close');
    },
    savePseudo(entity, type, newPseudo) {
      this.$emit('save-pseudo', entity, type, newPseudo);
    },
    updateMuleClass(muleId, newClass) {
      this.$emit('update-mule-class', muleId, newClass);
    },
    openMuleModal(mule) {
      this.$emit('open-mule-modal', mule);
    },
    openAddMuleModal() {
      this.$emit('open-add-mule-modal', this.member);
    },
    async confirmSwitchWithMule(main, mule) {
      this.switchMain = main;
      this.switchMule = mule;
      this.showConfirmSwitch = true;
    },
    async doSwitchWithMule() {
      this.showConfirmSwitch = false;
      try {
        const authStore = useAuthStore();
        const userPseudo = authStore.user?.username || '';
        const token = authStore.token || localStorage.getItem('token');
        const headers = { 'Content-Type': 'application/json' };
        if (token) {
          headers['Authorization'] = `Bearer ${token}`;
        }
        const response = await fetch(`${API_URL}/characters/${this.switchMain.id}/switch-with-mule/${this.switchMule.id}`, {
          method: 'POST',
          headers,
          body: JSON.stringify({ switchedBy: userPseudo })
        });
        if (!response.ok) throw new Error('Erreur lors du switch');
        this.$emit('refresh-data');
      } catch (e) {
        this.$emit('refresh-data');
      } finally {
        this.switchMain = null;
        this.switchMule = null;
      }
    },
  },
};
</script>
