<template>
  <div class="relative w-full max-w-md">
    <label for="monster-input" class="sr-only">Rechercher un monstre</label>
    <input
      id="monster-input"
      v-model="inputValue"
      type="text"
      :placeholder="placeholder"
      class="w-full border-2 border-theme-primary rounded-2xl px-5 py-3 bg-theme-bg-muted text-theme-text focus:outline-none focus:ring-4 focus:ring-theme-ring shadow-xl text-lg font-semibold transition-all duration-200 focus:scale-[1.03]"
      :aria-autocomplete="'list'"
      :aria-controls="dropdownId"
      :aria-activedescendant="activeDescendantId"
      role="combobox"
      :aria-expanded="showDropdown.toString()"
      :aria-disabled="disabled.toString()"
      :disabled="disabled"
      @input="onInput"
      @keydown.down.prevent="onArrowDown"
      @keydown.up.prevent="onArrowUp"
      @keydown.enter.prevent="onEnter"
      @keydown.esc.prevent="onEsc"
      autocomplete="off"
    />
    <ul
      v-if="showDropdown"
      :id="dropdownId"
      class="absolute z-20 mt-2 w-full bg-theme-card border-2 border-theme-primary rounded-2xl shadow-2xl max-h-80 overflow-auto p-2 space-y-2 animate-fadein"
      role="listbox"
    >
      <li
        v-for="(item, idx) in items"
        :key="item.id"
        :id="optionId(idx)"
        class="flex items-center gap-3 px-4 py-3 rounded-xl cursor-pointer transition-all duration-150 hover:bg-theme-bg-muted focus:bg-theme-bg-muted shadow-md text-lg font-medium select-none"
        :class="{ 'ring-4 ring-theme-ring scale-[1.03] bg-theme-bg-muted': idx === highlightedIndex }"
        role="option"
        :aria-selected="(idx === highlightedIndex).toString()"
        @mousedown.prevent="selectItem(item)"
      >
        <div class="w-10 h-10 rounded-full bg-theme-bg-muted flex items-center justify-center overflow-hidden border-2 border-theme-primary shadow">
          <img v-if="item.img" :src="item.img" alt="" class="w-10 h-10 object-cover" />
          <span v-else class="text-2xl">🧩</span>
        </div>
        <div class="flex flex-col">
          <span class="font-bold text-base">{{ item.name }}</span>
          <div class="flex flex-col gap-1">
            <span class="text-xs text-theme-text-muted">
              <span v-if="item.levelMin && item.levelMax">
                🎯 Niv. {{ item.levelMin }}-{{ item.levelMax }}
              </span>
              <span v-else>
                🎯 Niv. ?
              </span>
            </span>
            <span v-if="item.resistancesMax" class="text-xs text-theme-text-muted">
              <DofusdleResistanceIcon 
                :element="getDominantResistance(item.resistancesMax).element"
                v-if="getDominantResistance(item.resistancesMax)"
              />
              +{{ getDominantResistance(item.resistancesMax).value }}%
            </span>
            <span v-if="item.area && item.subarea" class="text-xs text-theme-text-muted">
              🗺️ {{ item.area }} > {{ item.subarea }}
            </span>
          </div>
        </div>
        <span class="ml-auto text-xl">👾</span>
      </li>
      <li v-if="loading" class="px-4 py-3 text-theme-text-muted text-center">Chargement...</li>
      <li v-else-if="!loading && items.length === 0" class="px-4 py-3 text-theme-text-muted text-center">Aucun monstre trouvé <span class="text-xl">😢</span></li>
      <li v-if="error" class="px-4 py-3 text-theme-error text-center">{{ error }}</li>
    </ul>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { debounce } from 'lodash-es'
import DofusdleResistanceIcon from './DofusdleResistanceIcon.vue'

const props = defineProps({
  items: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  placeholder: { type: String, default: 'Nom du monstre...' },
})
const emit = defineEmits(['select', 'input'])

// Expose la fonction de fermeture
defineExpose({
  closeDropdown: () => {
    showDropdown.value = false
    highlightedIndex.value = -1
    inputValue.value = ''
  }
})

// Fonction pour traduire les éléments en français
function translateElement(element) {
  const translations = {
    'water': 'Eau',
    'fire': 'Feu',
    'earth': 'Terre',
    'air': 'Air',
    'neutral': 'Neutre'
  }
  return translations[element] || element
}

// Fonction pour calculer la résistance dominante
function getDominantResistance(resistances) {
  if (!resistances || typeof resistances !== 'object') return null
  
  let maxValue = -999
  let dominantElement = null
  
  for (const [element, value] of Object.entries(resistances)) {
    if (value > maxValue) {
      maxValue = value
      dominantElement = element
    }
  }
  
  if (dominantElement === null) return null
  
  return {
    element: dominantElement,
    value: maxValue
  }
}

const inputValue = ref('')
const showDropdown = ref(false)
const highlightedIndex = ref(-1)
const dropdownId = `monster-dropdown-${Math.random().toString(36).slice(2)}`
const activeDescendantId = computed(() =>
  highlightedIndex.value >= 0 ? optionId(highlightedIndex.value) : undefined
)

function optionId(idx) {
  return `${dropdownId}-option-${idx}`
}

const debouncedInput = debounce((val) => {
  emit('input', val)
}, 250)

function onInput(e) {
  showDropdown.value = true
  highlightedIndex.value = -1
  debouncedInput(e.target.value)
}

function onArrowDown() {
  if (!showDropdown.value) showDropdown.value = true
  if (props.items.length === 0) return
  highlightedIndex.value = (highlightedIndex.value + 1) % props.items.length
}

function onArrowUp() {
  if (!showDropdown.value) showDropdown.value = true
  if (props.items.length === 0) return
  highlightedIndex.value =
    (highlightedIndex.value - 1 + props.items.length) % props.items.length
}

function onEnter() {
  if (highlightedIndex.value >= 0 && highlightedIndex.value < props.items.length) {
    selectItem(props.items[highlightedIndex.value])
  }
}

function onEsc() {
  showDropdown.value = false
}

function selectItem(item) {
  emit('select', item)
  // Reset l'input après sélection
  inputValue.value = ''
  showDropdown.value = false
  highlightedIndex.value = -1
}

watch(inputValue, (val) => {
  if (!val) {
    showDropdown.value = false
    highlightedIndex.value = -1
    emit('input', '')
  }
})
</script>

<style scoped>
@keyframes fadein {
  from { opacity: 0; }
  to { opacity: 1; }
}
.animate-fadein {
  animation: fadein 0.5s;
}
</style>
