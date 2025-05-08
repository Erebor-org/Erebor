<template>
  <div>
    <label class="block text-sm md:text-base font-medium text-[#b07d46] mb-2">
      Recruteur
    </label>
    <select
      v-model="recruiterId"
      class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 md:p-3 text-base focus:outline-none focus:ring-2 focus:ring-[#b07d46]"
    >
      <option value="">Sélectionner un recruteur</option>
      <option 
        v-for="character in characters" 
        :key="character.id" 
        :value="character.id"
      >
        {{ character.pseudo }} ({{ character.ankamaPseudo }})
      </option>
    </select>
  </div>
</template>

<script>
export default {
  props: {
    characters: {
      type: Array,
      required: true
    },
    value: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      recruiterId: this.value
    };
  },
  watch: {
    recruiterId(newValue) {
      this.$emit('input', newValue);
    }
  }
};
</script>