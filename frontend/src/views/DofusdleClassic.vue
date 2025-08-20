<template>
  <div class="w-full min-h-screen bg-theme-bg p-8 space-y-8">
    <button @click="regenDaily" class="bg-theme-primary text-white px-6 py-2 rounded-xl text-lg mb-4">Nouveau daily</button>
    <div class="mb-2 text-theme-text-muted text-lg">Daily dofusdb_id : <span class="font-bold">{{ dailyMonster?.dofusdb_id }}</span></div>
    <div class="mb-2 flex items-center gap-2">
      <input v-model="selectedGuessId" type="number" placeholder="guess dofusdb_id" class="border px-2 py-1 rounded" style="width: 120px;" />
      <button @click="debugCompare" class="bg-theme-primary text-white px-3 py-1 rounded">Debug compare</button>
    </div>
    <div v-if="debugResult" class="bg-theme-bg-muted p-4 rounded-xl my-2">
      <div class="font-bold mb-2">Comparaison champ par champ :</div>
      <table class="w-full text-sm border-collapse">
        <thead>
          <tr class="border-b border-theme-border">
            <th class="text-left py-1 pr-4">Champ</th>
            <th class="text-left py-1 pr-4">Daily</th>
            <th class="text-left py-1 pr-4">Guess</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(diff, champ) in debugResult.diffs" :key="champ" :class="diff.daily !== diff.guess ? 'bg-red-50' : 'bg-green-50'">
            <td class="font-mono pr-4">{{ champ }}</td>
            <td class="pr-4">{{ formatVal(diff.daily) }}</td>
            <td class="pr-4">{{ formatVal(diff.guess) }}</td>
          </tr>
        </tbody>
      </table>
      <div class="mt-4 font-bold">Hints :</div>
      <div class="flex flex-wrap gap-2 mt-2">
        <span v-for="(v, k) in debugResult.hint" :key="k" :class="chipClass(v, k)">
          {{ k }}: {{ v }}
        </span>
      </div>
      <details class="mt-4">
        <summary class="cursor-pointer">Voir tout le daily</summary>
        <pre class="bg-gray-100 p-2 rounded text-xs">{{ debugResult.daily }}</pre>
      </details>
      <details class="mt-2">
        <summary class="cursor-pointer">Voir tout le guess</summary>
        <pre class="bg-gray-100 p-2 rounded text-xs">{{ debugResult.guess }}</pre>
      </details>
    </div>
    <VictoryModal :show="showVictory" :monster-img="dailyMonster?.image" :monster-name="dailyMonster?.name" @close="showVictory = false" />
    <div v-if="loading" class="text-center text-theme-text-muted text-2xl">Chargement…</div>
    <div v-else>
      <div class="mb-6">
        <div v-if="correct && !showVictory" class="bg-theme-success/20 border border-theme-success rounded-xl p-6 flex flex-col items-center">
          <div class="text-2xl font-bold mb-2 text-theme-success">Bravo !</div>
          <button class="bg-theme-success text-white px-6 py-3 rounded-xl text-lg" @click="shareResult">Partager le résultat</button>
        </div>
        <div v-else class="bg-theme-bg-muted border border-theme-border rounded-xl p-4 text-center text-theme-primary text-xl">
          Entrez un nom de monstre pour tenter votre chance !
        </div>
      </div>
      <DofusdleMonsterPicker
        :items="suggestions"
        :loading="searching"
        :error="searchError"
        :disabled="correct"
        @input="onSearch"
        @select="onSelectMonster"
      />
      <div class="mt-8 overflow-x-auto" v-if="!showVictory">
        <DofusdleGuessTable :attempts="store.attempts" :colorize="true" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useDofusdleStore } from '../stores/dofusdleStore'
import DofusdleMonsterPicker from '../components/DofusdleMonsterPicker.vue'
import DofusdleGuessTable from '../components/DofusdleGuessTable.vue'
import VictoryModal from '../components/VictoryModal.vue'
import axios from 'axios'
import { classFromHint } from '../utils/dofusdleHintUtils'

const store = useDofusdleStore()
const { fetchDaily, fetchAllMonsters, searchMonsters, guessMonster } = store
const colorize = store.colorize
const correct = store.correct
const loading = store.loading

const suggestions = ref([])
const searching = ref(false)
const searchError = ref('')
const dailyMonster = ref(null)
const selectedGuessId = ref('')
const debugResult = ref(null)
const showVictory = ref(false)

async function loadDaily() {
  const res = await axios.get('/erebor/dofusdle/api/classic/daily')
  dailyMonster.value = res.data.monster
  await fetchDaily()
}

onMounted(async () => {
  await loadDaily()
  fetchAllMonsters()
})

watch(() => correct.value, (val) => {
  if (val) {
    showVictory.value = true
  }
})

let lastSearch = ''
let searchAbort = null

function onSearch(query) {
  if (!query) {
    suggestions.value = []
    return
  }
  searching.value = true
  searchError.value = ''
  lastSearch = query
  try {
    const items = searchMonsters(query)
    if (lastSearch === query) {
      suggestions.value = items.slice(0, 20)
    }
  } catch (e) {
    searchError.value = 'Erreur de recherche'
  } finally {
    searching.value = false
  }
}

async function onSelectMonster(monster) {
  if (!monster || correct.value) return
  await guessMonster(monster)
}

async function regenDaily() {
  await axios.post('/erebor/dofusdle/api/classic/regen-daily')
  await loadDaily()
  store.attempts.length = 0
}

async function debugCompare() {
  if (!dailyMonster.value?.dofusdb_id || !selectedGuessId.value) return
  const res = await axios.post('/erebor/dofusdle/api/classic/compare-debug', {
    dailyId: dailyMonster.value.dofusdb_id,
    guessId: Number(selectedGuessId.value)
  })
  debugResult.value = res.data
}

function formatVal(val) {
  if (Array.isArray(val)) return val.join(', ')
  if (val === null || val === undefined) return '—'
  return val
}

function chipClass(v, k) {
  return `inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-800 text-xs font-mono ${classFromHint(v, k)}`
}

function shareResult() {
  alert('Résultat copié !')
}
</script>
