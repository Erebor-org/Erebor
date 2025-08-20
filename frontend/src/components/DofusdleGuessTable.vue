<template>
  <div class="rounded-2xl shadow-2xl border-2 border-slate-700 bg-slate-900/80 overflow-hidden animate-fadein backdrop-blur-sm">
    <table class="w-full min-w-[900px] divide-y divide-slate-700 text-white text-xl">
      <thead class="bg-slate-800/80 sticky top-0 z-10 backdrop-blur-sm">
        <tr>
          <th class="px-6 py-4 font-bold text-left text-2xl">Monstre</th>
          <th class="px-4 py-4 font-bold">Niv.</th>
          <th class="px-4 py-4 font-bold">Race</th>
          <th class="px-4 py-4 font-bold">Area</th>
          <th class="px-4 py-4 font-bold">Subarea</th>
          <th class="px-4 py-4 font-bold">Résistance</th>
          <th class="px-4 py-4 font-bold">PA</th>
          <th class="px-4 py-4 font-bold">PM</th>
          <th class="px-4 py-4 font-bold">PV</th>
        </tr>
      </thead>
      <tbody class="bg-slate-800/20">
        <tr v-for="(row, idx) in attempts" :key="row.id"
            :class="[rowClass(idx), isCorrectRow(row, idx) ? 'bg-emerald-500/20 ring-2 ring-emerald-400' : '', 'transition hover:scale-[1.01] hover:shadow-lg hover:bg-slate-700/50 focus-within:bg-slate-700/50 animate-slidein']">
          <td class="flex items-center gap-5 px-6 py-4">
            <div class="w-16 h-16 rounded-full bg-theme-bg-muted flex items-center justify-center overflow-hidden border-2 border-theme-primary shadow-md">
              <img v-if="row.img" :src="row.img" alt="" class="w-16 h-16 object-cover" />
              <svg v-else class="w-10 h-10 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2" /><path d="M8 14s1.5-2 4-2 4 2 4 2" stroke-width="2" /><circle cx="12" cy="10" r="2" stroke-width="2" /></svg>
            </div>
            <span class="font-bold text-2xl">{{ row.name }}</span>
          </td>
          <CellLevel :value="row.levelMin && row.levelMax ? `${row.levelMin}-${row.levelMax}` : '?'" :hint="row.hint?.level" :colorize="colorize" />
          <CellRace :value="row.race" :hint="row.hint?.race" :colorize="colorize" />
          <CellArea :value="row.area" :hint="row.hint?.zone" :colorize="colorize" />
          <CellSubarea :value="row.subarea" :hint="row.hint?.zone" :colorize="colorize" />
          <CellResistance :resistances="row.resistancesMax" :hint="row.hint?.resistDominant" :colorize="colorize" />
          <CellStat :value="row.apMin && row.apMax ? `${row.apMin}-${row.apMax}` : '?'" :hint="row.hint?.ap" :colorize="colorize" label="PA" :correct="isCorrectRow(row, idx)" />
          <CellStat :value="row.mpMin && row.mpMax ? `${row.mpMin}-${row.mpMax}` : '?'" :hint="row.hint?.mp" :colorize="colorize" label="PM" :correct="isCorrectRow(row, idx)" />
          <CellStat :value="row.hpMin && row.hpMax ? `${row.hpMin}-${row.hpMax}` : '?'" :hint="row.hint?.hp" :colorize="colorize" label="PV" :correct="isCorrectRow(row, idx)" />
        </tr>
      </tbody>
    </table>
    <!-- Responsive : cartes sur mobile -->
    <div class="md:hidden flex flex-col gap-4 p-2">
      <div v-for="(row, idx) in attempts" :key="'card-'+row.id" class="rounded-2xl border-2 border-theme-primary bg-theme-bg-muted p-6 flex gap-4 items-center shadow-lg animate-slidein">
        <div class="w-20 h-20 rounded-full bg-theme-card flex items-center justify-center overflow-hidden border-2 border-theme-primary shadow-md">
          <img v-if="row.img" :src="row.img" alt="" class="w-20 h-20 object-cover" />
          <svg v-else class="w-12 h-12 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2" /><path d="M8 14s1.5-2 4-2 4 2 4 2" stroke-width="2" /><circle cx="12" cy="10" r="2" stroke-width="2" /></svg>
        </div>
        <div class="flex-1">
          <div class="font-extrabold text-2xl mb-2">{{ row.name }}</div>
          <div class="flex flex-wrap gap-3 mt-2 text-lg">
            <CellLevel :value="row.levelMin && row.levelMax ? `${row.levelMin}-${row.levelMax}` : '?'" :hint="row.hint?.level" :colorize="colorize" />
            <CellRace :value="row.race" :hint="row.hint?.race" :colorize="colorize" />
            <CellArea :value="row.area" :hint="row.hint?.zone" :colorize="colorize" />
            <CellSubarea :value="row.subarea" :hint="row.hint?.zone" :colorize="colorize" />
            <CellResistance :resistances="row.resistancesMax" :hint="row.hint?.resistDominant" :colorize="colorize" />
            <CellStat :value="row.apMin && row.apMax ? `${row.apMin}-${row.apMax}` : '?'" :hint="row.hint?.ap" :colorize="colorize" label="PA" :correct="isCorrectRow(row, idx)" />
            <CellStat :value="row.mpMin && row.mpMax ? `${row.mpMin}-${row.mpMax}` : '?'" :hint="row.hint?.mp" :colorize="colorize" label="PM" :correct="isCorrectRow(row, idx)" />
            <CellStat :value="row.hpMin && row.hpMax ? `${row.hpMin}-${row.hpMax}` : '?'" :hint="row.hint?.hp" :colorize="colorize" label="PV" :correct="isCorrectRow(row, idx)" />
          </div>
        </div>
      </div>
    </div>
    <!-- Légende moderne -->
    <div class="mt-8 p-6 bg-slate-800/50 rounded-2xl border border-slate-700">
      <h3 class="text-lg font-bold text-white mb-4 text-center">🎯 Guide des couleurs</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 bg-emerald-500 rounded-lg shadow-lg"></div>
          <span class="text-white">Vert : Exact/Correct</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 bg-amber-500 rounded-lg shadow-lg"></div>
          <span class="text-white">Orange : Partiel/Proche</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 bg-blue-500 rounded-lg shadow-lg"></div>
          <span class="text-white">Bleu : Plus haut</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 bg-purple-500 rounded-lg shadow-lg"></div>
          <span class="text-white">Violet : Plus bas</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 bg-red-500 rounded-lg shadow-lg"></div>
          <span class="text-white">Rouge : Incorrect</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 bg-slate-500 rounded-lg shadow-lg"></div>
          <span class="text-white">Gris : Inconnu</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import CellLevel from './DofusdleGuessTable/CellLevel.vue'
import CellRace from './DofusdleGuessTable/CellRace.vue'
import CellArea from './DofusdleGuessTable/CellArea.vue'
import CellSubarea from './DofusdleGuessTable/CellSubarea.vue'
import CellResistance from './DofusdleGuessTable/CellResistance.vue'
import CellStat from './DofusdleGuessTable/CellStat.vue'
import { useDofusdleStore } from '../stores/dofusdleStore'
const store = useDofusdleStore()
// CellResist supprimé

const props = defineProps({
  attempts: { type: Array, required: true },
  colorize: { type: Boolean, default: false },
})

function rowClass(idx) {
  // 1ère ligne neutre, colorisation à partir de la 2e
  return idx === 0 && !props.colorize ? 'bg-theme-bg-muted' : ''
}

function isCorrectRow(row, idx) {
  // Ligne correcte = première ligne du tableau ET store.correct true
  return idx === 0 && store.correct
}
</script>

<style scoped>
@keyframes fadein {
  from { opacity: 0; }
  to { opacity: 1; }
}
.animate-fadein {
  animation: fadein 0.7s;
}
@keyframes slidein {
  from { transform: translateY(30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.animate-slidein {
  animation: slidein 0.5s cubic-bezier(.4,2,.6,1) both;
}
</style>
