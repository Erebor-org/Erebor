<template>
  <DashCard :title="title" :loading="loading">
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
    </template>
    <FeedList
      :items="items"
      :has-more="hasMore"
      :loading-more="loadingMore"
      :empty-text="emptyText"
      :max-height="maxHeight"
      @load-more="$emit('load-more')"
    />
  </DashCard>
</template>

<script setup>
import DashCard from './DashCard.vue';
import FeedList from './FeedList.vue';

defineProps({
  title: { type: String, default: 'Activité récente' },
  items: { type: Array, default: () => [] },
  hasMore: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  loadingMore: { type: Boolean, default: false },
  emptyText: { type: String, default: "Rien de neuf pour l'instant." },
  maxHeight: { type: String, default: '24rem' },
});
defineEmits(['load-more']);
</script>
