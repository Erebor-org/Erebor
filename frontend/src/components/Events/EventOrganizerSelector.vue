<template>
  <div class="md:col-span-2">
    <label class="block text-base font-medium text-[#b07d46] mb-2">
      Organisateur :
    </label>
    <!-- Si non sélectionné -->
    <div v-if="!modelValue">
      <input
        type="text"
        v-model="searchQuery"
        :placeholder="label"
        class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 text-base mb-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
      />
      <ul
        class="max-h-32 overflow-y-auto bg-[#fffaf0] border-2 border-[#b07d46] rounded-md p-2"
      >
        <li
          v-for="character in filteredCharacters"
          :key="character.id"
          @click="selectCharacter(character.id)"
          class="flex items-center gap-3 p-2 cursor-pointer hover:bg-[#f3d9b1] rounded-md"
        >
          <img :src="classes[character.class]" alt="Classe" class="w-7 h-7" />
          <span class="text-base text-[#b07d46]">{{ character.pseudo }}</span>
        </li>
      </ul>
    </div>
    <!-- Si sélectionné -->
    <div v-else class="flex items-center gap-3">
      <img :src="selectedCharacterIcon" alt="Classe" class="w-10 h-10 rounded-full" />
      <span class="text-base font-semibold text-[#b07d46]">{{ selectedCharacterName }}</span>
      <button
        type="button"
        @click="clearSelectedCharacter"
        class="text-red-500 text-lg font-bold focus:outline-none"
      >
        &times;
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';


const props = defineProps({
  modelValue: {
    type: [String, Number],
    required: true
  },
  characters: {
    type: Array,
    required: true
  },
  isLoading: {
    type: Boolean,
    default: false
  },
  label: {
    type: String,
    default: 'Rechercher un organisateur...'
  },
  classes: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update:modelValue']);

const searchQuery = ref('');
const selectedCharacterName = ref('');
const selectedCharacterIcon = ref('');

const filteredCharacters = computed(() => {
  if (!searchQuery.value) return props.characters;
  
  const query = searchQuery.value.toLowerCase();
  return props.characters.filter(character => 
    character.pseudo.toLowerCase().includes(query)
  );
});

const selectCharacter = (characterId) => {
  const character = props.characters.find(c => c.id === characterId);
  if (character) {
    emit('update:modelValue', characterId);
    selectedCharacterName.value = character.pseudo;
    selectedCharacterIcon.value = props.classes[character.class];
    searchQuery.value = '';
  }
};

const clearSelectedCharacter = () => {
  emit('update:modelValue', '');
  selectedCharacterName.value = '';
  selectedCharacterIcon.value = '';
};
</script>
