<template>
  <div>
    <!-- Modal archive character -->
    <div
      v-if="showArchiveModal"
      class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
      @click.self="$emit('close-archive-modal')"
    >
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg p-6 w-1/3 relative">
        <!-- Close Button -->
        <button
          class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-lg"
          @click="$emit('close-archive-modal')"
        >
          &times;
        </button>
        <p class="text-lg text-[#b07d46] mb-6">
          Voulez-vous archiver le joueur <strong>{{ character?.pseudo }}</strong> ?
        </p>
        <div class="flex justify-end space-x-4">
          <button
            @click="$emit('close-archive-modal')"
            class="bg-[#b07d46] text-[#fff5e6] font-bold py-2 px-4 rounded-lg hover:bg-[#9c682e]"
          >
            Annuler
          </button>
          <button
            @click="$emit('confirm-archive', character?.id)"
            class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
            :disabled="!character?.id"
          >
            Archiver
          </button>
        </div>
      </div>
    </div>
    
    <!-- Modal archive mule -->
    <div
      v-if="showArchiveMuleModal"
      class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
      @click.self="$emit('close-archive-mule-modal')"
    >
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg p-6 w-1/3 relative">
        <!-- Close Button -->
        <button
          class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-lg"
          @click="$emit('close-archive-mule-modal')"
        >
          &times;
        </button>
        <p class="text-lg text-[#b07d46] mb-6">
          Voulez-vous archiver le joueur <strong>{{ mule?.pseudo }}</strong> ?
        </p>
        <div class="flex justify-end space-x-4">
          <button
            @click="$emit('close-archive-mule-modal')"
            class="bg-[#b07d46] text-[#fff5e6] font-bold py-2 px-4 rounded-lg hover:bg-[#9c682e]"
          >
            Annuler
          </button>
          <button
            @click="$emit('confirm-archive-mule', mule?.id)"
            class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
            :disabled="!mule?.id"
          >
            Archiver
          </button>
        </div>
      </div>
    </div>
    
    <!-- Modal unarchive character -->
    <div
      v-if="showUnarchiveModal"
      class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
      @click.self="$emit('close-unarchive-modal')"
    >
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg p-6 w-1/3 relative">
        <!-- Close Button -->
        <button
          class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-lg"
          @click="$emit('close-unarchive-modal')"
        >
          &times;
        </button>
        <p class="text-lg text-[#b07d46] mb-6">
          Voulez-vous restaurer le joueur <strong>{{ unarchivedCharacter?.pseudo }}</strong> ?
        </p>
        <div class="flex justify-end space-x-4">
          <button
            @click="$emit('close-unarchive-modal')"
            class="bg-[#b07d46] text-[#fff5e6] font-bold py-2 px-4 rounded-lg hover:bg-[#9c682e]"
          >
            Annuler
          </button>
          <button
            @click="$emit('confirm-unarchive', unarchivedCharacter?.id)"
            class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
            :disabled="!unarchivedCharacter?.id"
          >
            Restaurer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    showArchiveModal: {
      type: Boolean,
      default: false
    },
    showArchiveMuleModal: {
      type: Boolean,
      default: false
    },
    showUnarchiveModal: {
      type: Boolean,
      default: false
    },
    character: {
      type: Object,
      default: () => null
    },
    mule: {
      type: Object,
      default: () => null
    },
    unarchivedCharacter: {
      type: Object,
      default: () => null
    }
  }
};
</script>
