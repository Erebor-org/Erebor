<template>
  <!-- Wraps an item (the slot content) and shows its in-game style tooltip on hover or focus.
       On touch screens a tap focuses it (opens) and tapping elsewhere blurs it (closes). -->
  <div
    ref="trigger"
    class="gw-tooltip-trigger"
    tabindex="0"
    @mouseenter="show"
    @mouseleave="hide"
    @focus="show"
    @blur="hide"
  >
    <slot />
  </div>

  <!-- Teleported to body: a fixed element inside a backdrop-filter container would be mispositioned -->
  <Teleport to="body">
    <div
      v-if="visible"
      ref="panel"
      class="gw-tooltip glass-modal"
      role="tooltip"
      :style="{ left: `${position.left}px`, top: `${position.top}px`, '--rarity': rarityColor(item.rarity) }"
    >
      <div class="flex items-start gap-3 mb-2">
        <img v-if="item.icon" :src="item.icon" :alt="item.name" class="gw-tooltip-icon" />
        <div class="min-w-0">
          <p class="font-bold leading-snug" :style="{ color: rarityColor(item.rarity) }">{{ item.name || `Objet #${item.id}` }}</p>
          <p class="text-xs text-theme-text-muted">{{ typeLine }}<template v-if="item.count > 1"> · ×{{ item.count }}</template></p>
        </div>
      </div>

      <p v-if="item.power" class="gw-tooltip-line">
        Force de l'arme : <span class="gw-tooltip-value">{{ item.power[0] }} – {{ item.power[1] }}</span>
      </p>
      <p v-if="item.defense" class="gw-tooltip-line">
        Défense : <span class="gw-tooltip-value">{{ item.defense }}</span>
      </p>

      <div v-if="item.attributes?.length" class="gw-tooltip-section">
        <p v-if="item.statsName" class="text-xs uppercase tracking-wide text-theme-text-muted mb-1">Statistiques « {{ capitalize(item.statsName) }} »</p>
        <p v-for="a in item.attributes" :key="a.attribute" class="gw-tooltip-stat">
          +{{ a.modifier }} {{ attributeLabel(a.attribute) }}
        </p>
      </div>

      <div v-for="upgrade in [...(item.upgrades || []), ...(item.infusions || [])]" :key="upgrade.id" class="gw-tooltip-section">
        <div class="flex items-center gap-2 mb-1">
          <img v-if="upgrade.icon" :src="upgrade.icon" :alt="upgrade.name" class="w-6 h-6 rounded border border-theme-border" />
          <span class="text-sm font-semibold text-theme-text">{{ upgrade.name }}</span>
        </div>
        <p v-for="(bonus, i) in upgrade.bonuses" :key="`b${i}`" class="gw-tooltip-bonus">
          <span v-if="upgrade.bonuses.length > 1" class="text-theme-text-muted">({{ i + 1 }})</span> {{ bonus }}
        </p>
        <p v-for="a in upgrade.attributes" :key="a.attribute" class="gw-tooltip-bonus">
          +{{ a.modifier }} {{ attributeLabel(a.attribute) }}
        </p>
      </div>

      <p v-if="item.description" class="gw-tooltip-section text-xs italic text-theme-text-muted whitespace-pre-line">{{ item.description }}</p>

      <div class="gw-tooltip-footer">
        <span :style="{ color: rarityColor(item.rarity) }">{{ RARITY_LABELS[item.rarity] || item.rarity }}</span>
        <span v-if="item.level">Niveau requis : {{ item.level }}</span>
        <span v-if="item.binding">{{ BINDINGS[item.binding] || item.binding }}</span>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, nextTick, onBeforeUnmount } from 'vue';
import {
  SLOTS, WEAPON_TYPES, WEIGHT_CLASSES, RARITY_LABELS, BINDINGS, ITEM_TYPES,
  attributeLabel, rarityColor, capitalize,
} from '@/utils/gw2';

const props = defineProps({
  item: { type: Object, required: true },
});

const trigger = ref(null);
const panel = ref(null);
const visible = ref(false);
const position = ref({ left: 0, top: 0 });

const MARGIN = 8;
const TRINKETS = { Amulet: 'Amulette', Ring: 'Anneau', Accessory: 'Accessoire' };

const typeLine = computed(() => {
  const { itemType, subType, weightClass, slot } = props.item;
  // Worn items have a slot; items in bags or bank only have the type of piece (subType)
  const piece = SLOTS[slot || subType] || slot;
  if (itemType === 'Weapon') return WEAPON_TYPES[subType] || subType;
  if (itemType === 'Armor') return [piece, WEIGHT_CLASSES[weightClass] && `armure ${WEIGHT_CLASSES[weightClass].toLowerCase()}`].filter(Boolean).join(' · ');
  if (itemType === 'Trinket') return TRINKETS[subType] || ITEM_TYPES.Trinket;
  return piece || ITEM_TYPES[itemType] || itemType;
});

// Next to the item, on the side with room, kept inside the viewport
const place = () => {
  if (!trigger.value || !panel.value) return;
  const anchor = trigger.value.getBoundingClientRect();
  const { width, height } = panel.value.getBoundingClientRect();

  let left = anchor.right + MARGIN;
  if (left + width > window.innerWidth - MARGIN) left = anchor.left - width - MARGIN;
  left = Math.max(MARGIN, Math.min(left, window.innerWidth - width - MARGIN));

  const top = Math.max(MARGIN, Math.min(anchor.top, window.innerHeight - height - MARGIN));
  position.value = { left, top };
};

const show = async () => {
  if (visible.value) return;
  visible.value = true;
  await nextTick();
  place();
  window.addEventListener('scroll', hide, true);
  window.addEventListener('resize', hide);
};

const hide = () => {
  visible.value = false;
  window.removeEventListener('scroll', hide, true);
  window.removeEventListener('resize', hide);
};

onBeforeUnmount(hide);
</script>

<style scoped>
.gw-tooltip-trigger {
  border-radius: 0.65rem;
  outline: none;
}
.gw-tooltip-trigger:focus-visible {
  box-shadow: 0 0 0 2px var(--primary);
}

.gw-tooltip {
  position: fixed;
  z-index: 60;
  width: 20rem;
  max-width: calc(100vw - 16px);
  padding: 0.9rem 1rem;
  border-radius: 0.85rem;
  border-top: 3px solid var(--rarity);
  background-color: var(--card); /* opaque: the text under it must not show through */
  pointer-events: none;
  font-size: 0.85rem;
  color: var(--text);
}
.gw-tooltip-icon {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 0.45rem;
  border: 1px solid var(--rarity);
  flex-shrink: 0;
}
.gw-tooltip-line {
  color: var(--text-muted);
}
.gw-tooltip-value {
  color: var(--text);
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}
.gw-tooltip-section {
  margin-top: 0.6rem;
  padding-top: 0.6rem;
  border-top: 1px solid var(--border);
}
.gw-tooltip-stat {
  color: var(--success);
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}
.gw-tooltip-bonus {
  color: var(--link);
  font-size: 0.8rem;
}
.gw-tooltip-footer {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem 0.75rem;
  margin-top: 0.6rem;
  padding-top: 0.6rem;
  border-top: 1px solid var(--border);
  font-size: 0.75rem;
  color: var(--text-muted);
}
</style>
