<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" @click="closeModal"></div>
    
    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div class="relative w-full max-w-4xl bg-gray-900 rounded-2xl border border-gray-800 shadow-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-800">
          <div class="flex items-center space-x-4">
            <img
              :src="classes[member?.member?.class]"
              :alt="`Classe ${member?.member?.class}`"
              class="w-12 h-12 rounded-lg border-2 border-gray-600"
            />
            <div>
              <h2 class="text-2xl font-bold text-amber-400">Mules de {{ member?.member?.pseudo }}</h2>
              <p class="text-gray-400">{{ filteredMulesByCharacter(member?.id)?.length || 0 }} mule(s)</p>
            </div>
          </div>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-white transition-colors duration-200"
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
              class="w-full px-4 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2"
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
              class="bg-gray-800 rounded-lg p-4 border border-gray-700 hover:border-amber-500 transition-colors duration-200"
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
                    class="px-3 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200"
                  >
                    Archiver
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- No Mules State -->
          <div v-else class="text-center py-12">
            <div class="text-gray-400">
              <svg class="w-20 h-20 mx-auto mb-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
              <p class="text-xl font-medium text-gray-500 mb-2">Aucune mule disponible</p>
              <p class="text-gray-600">Cliquez sur "Ajouter une mule" pour en créer une</p>
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

export default {
  name: 'MulesModal',
  components: {
    EditablePseudo,
    ClassDropdown,
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
  },
};
</script>
