<template>
  <!-- An amount of copper shown like in game: gold / silver / copper, zero units left out -->
  <span v-if="value === null || value === undefined" class="text-theme-text-muted">—</span>
  <span v-else class="gw-coins" :title="title">
    <span v-if="coins.gold" class="gw-coin">{{ coins.gold.toLocaleString('fr-FR') }}<i class="gw-coin-dot gw-coin-dot--gold"></i></span>
    <span v-if="coins.silver" class="gw-coin">{{ coins.silver }}<i class="gw-coin-dot gw-coin-dot--silver"></i></span>
    <span v-if="coins.copper || (!coins.gold && !coins.silver)" class="gw-coin">{{ coins.copper }}<i class="gw-coin-dot gw-coin-dot--copper"></i></span>
  </span>
</template>

<script setup>
import { computed } from 'vue';
import { splitCoins } from '@/utils/gw2';

const props = defineProps({
  value: { type: Number, default: null },
});

const coins = computed(() => splitCoins(props.value));
const title = computed(() => {
  const { gold, silver, copper } = coins.value;
  return [gold && `${gold} po`, silver && `${silver} pa`, (copper || (!gold && !silver)) && `${copper} pc`].filter(Boolean).join(' ');
});
</script>

<style scoped>
.gw-coins {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}
.gw-coin {
  display: inline-flex;
  align-items: center;
  gap: 0.15rem;
}
.gw-coin-dot {
  display: inline-block;
  width: 0.6rem;
  height: 0.6rem;
  border-radius: 9999px;
  box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.25);
}
.gw-coin-dot--gold {
  background: linear-gradient(135deg, #F7D774, #C9971C);
}
.gw-coin-dot--silver {
  background: linear-gradient(135deg, #EDEDED, #9A9A9A);
}
.gw-coin-dot--copper {
  background: linear-gradient(135deg, #E59866, #A0522D);
}
</style>
