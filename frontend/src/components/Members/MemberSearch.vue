<template>
  <div class="flex flex-col md:flex-row md:items-center justify-between">
    <!-- Search Bar -->
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      class="w-full md:w-1/2 border-2 border-[#b07d46] bg-[#fffaf0] rounded-full py-2 px-6 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1] mb-4 md:mb-0"
    />

    <!-- Add Character Button -->
    <button
      @click="$emit('add-character')"
      class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-6 rounded-full hover:bg-[#942828] transition-all duration-300"
    >
      Ajouter un personnage
    </button>
  </div>
</template>

<script>
export default {
  props: {
    modelValue: {
      type: String,
      required: true
    },
    placeholder: {
      type: String,
      default: 'Rechercher...'
    }
  }
};
</script>
