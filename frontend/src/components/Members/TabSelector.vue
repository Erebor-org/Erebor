<template>
  <div class="flex justify-center mb-4">
    <div class="flex space-x-4">
      <button
        @click="$emit('update:active-tab', 'active')"
        :class="{
          'bg-[#b02e2e] text-[#f3d9b1] shadow-md': activeTab === 'active',
          'bg-[#f3d9b1] text-[#b02e2e]': activeTab !== 'active',
        }"
        class="px-6 py-2 rounded-full font-bold transition-all duration-300"
      >
        Membres actifs
      </button>
      <button
        @click="$emit('update:active-tab', 'archived')"
        :class="{
          'bg-[#b02e2e] text-[#f3d9b1] shadow-md': activeTab === 'archived',
          'bg-[#f3d9b1] text-[#b02e2e]': activeTab !== 'archived',
        }"
        class="px-6 py-2 rounded-full font-bold transition-all duration-300"
      >
        Membres archivés
      </button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    activeTab: {
      type: String,
      required: true
    }
  }
};
</script>
