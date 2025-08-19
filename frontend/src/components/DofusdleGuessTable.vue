<template>
  <div class="rounded-2xl shadow-xl border border-theme-border bg-theme-card overflow-hidden animate-fadein">
    <table class="w-full min-w-[900px] divide-y divide-theme-border text-theme-text text-xl">
      <thead class="bg-theme-bg-muted sticky top-0 z-10">
        <tr>
          <th class="px-6 py-4 font-bold text-left text-2xl">Monstre</th>
          <th class="px-4 py-4 font-bold">Niv.</th>
          <th class="px-4 py-4 font-bold">Famille</th>
          <th class="px-4 py-4 font-bold">Zones</th>
          <th class="px-4 py-4 font-bold">Rang</th>
          <th class="px-4 py-4 font-bold">Élément</th>
          <th class="px-4 py-4 font-bold">PA</th>
          <th class="px-4 py-4 font-bold">PM</th>
          <th class="px-4 py-4 font-bold">PV</th>
        </tr>
      </thead>
      <tbody class="bg-white/10">
        <tr v-for="(row, idx) in attempts" :key="row.id"
            :class="[rowClass(idx), isCorrectRow(row) ? 'bg-green-100/20 ring-2 ring-green-400' : '', 'transition hover:scale-[1.01] hover:shadow-lg hover:bg-theme-bg-muted focus-within:bg-theme-bg-muted animate-slidein']">
          <td class="flex items-center gap-5 px-6 py-4">
            <div class="w-16 h-16 rounded-full bg-theme-bg-muted flex items-center justify-center overflow-hidden border-2 border-theme-primary shadow-md">
              <img v-if="row.img" :src="row.img" alt="" class="w-16 h-16 object-cover" />
              <svg v-else class="w-10 h-10 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2" /><path d="M8 14s1.5-2 4-2 4 2 4 2" stroke-width="2" /><circle cx="12" cy="10" r="2" stroke-width="2" /></svg>
            </div>
            <span class="font-bold text-2xl">{{ row.name }}</span>
          </td>
          <CellLevel :value="row.refLevel" :hint="row.hint?.level" :colorize="colorize" fun />
          <CellFamily :value="row.family" :hint="row.hint?.family" :colorize="colorize" fun />
          <CellZone :areas="row.areas" :hint="row.hint?.zone" :colorize="colorize" fun />
          <CellRank :value="row.rank" :hint="row.hint?.rank" :colorize="colorize" fun />
          <CellElement :value="row.element" :hint="row.hint?.element" :colorize="colorize" fun />
          <CellStat :value="row.ap" :hint="row.hint?.ap" :colorize="colorize" label="PA" fun />
          <CellStat :value="row.mp" :hint="row.hint?.mp" :colorize="colorize" label="PM" fun />
          <CellStat :value="row.hp" :hint="row.hint?.hp" :colorize="colorize" label="PV" fun />
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
            <CellLevel :value="row.refLevel" :hint="row.hint?.level" :colorize="colorize" fun />
            <CellFamily :value="row.family" :hint="row.hint?.family" :colorize="colorize" fun />
            <CellZone :areas="row.areas" :hint="row.hint?.zone" :colorize="colorize" fun />
            <CellRank :value="row.rank" :hint="row.hint?.rank" :colorize="colorize" fun />
            <CellElement :value="row.element" :hint="row.hint?.element" :colorize="colorize" fun />
            <CellStat :value="row.ap" :hint="row.hint?.ap" :colorize="colorize" label="PA" fun />
            <CellStat :value="row.mp" :hint="row.hint?.mp" :colorize="colorize" label="PM" fun />
            <CellStat :value="row.hp" :hint="row.hint?.hp" :colorize="colorize" label="PV" fun />
          </div>
        </div>
      </div>
    </div>
    <!-- Légende -->
    <div class="mt-8 text-base text-theme-text-muted flex flex-wrap gap-6 items-center">
      <div><span class="inline-block w-6 h-6 align-middle bg-green-200 rounded mr-1"></span> Vert : ✓, ■, ● = correct</div>
      <div><span class="inline-block w-6 h-6 align-middle bg-yellow-200 rounded mr-1"></span> Jaune : ◐, ▲, ▼ = proche/partiel</div>
      <div><span class="inline-block w-6 h-6 align-middle bg-red-200 rounded mr-1"></span> Rouge : ✗, ○ = faux</div>
      <div><span class="inline-block w-6 h-6 align-middle bg-gray-200 rounded mr-1"></span> Gris : ? = inconnu</div>
      <div class="ml-4">Niv. : ▲ plus haut, ▼ plus bas, ■ exact</div>
      <div class="ml-4">Zones : ● exact, ◐ partiel, ○ faux</div>
    </div>
  </div>
</template>

<script setup>
import CellLevel from './DofusdleGuessTable/CellLevel.vue'
import CellFamily from './DofusdleGuessTable/CellFamily.vue'
import CellZone from './DofusdleGuessTable/CellZone.vue'
import CellRank from './DofusdleGuessTable/CellRank.vue'
import CellElement from './DofusdleGuessTable/CellElement.vue'
import CellStat from './DofusdleGuessTable/CellStat.vue'
// CellResist supprimé

const props = defineProps({
  attempts: { type: Array, required: true },
  colorize: { type: Boolean, default: false },
})

function rowClass(idx) {
  // 1ère ligne neutre, colorisation à partir de la 2e
  return idx === 0 && !props.colorize ? 'bg-theme-bg-muted' : ''
}

function isCorrectRow(row) {
  if (!row.hint) return false
  // On considère la ligne "correcte" si tous les hints sont "match", "eq" ou "exact"
  const ok = ['match', 'eq', 'exact']
  return Object.values(row.hint).every(v => ok.includes(v))
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
