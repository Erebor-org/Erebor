<template>
  <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 flex-wrap py-4">
    <button
      @click="$emit('update:page', Math.max(1, page - 1))"
      :disabled="page === 1"
      class="px-3 py-2 rounded-lg text-sm font-medium border border-theme-border bg-theme-card text-theme-text-muted hover:text-theme-primary hover:border-theme-primary transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:text-theme-text-muted disabled:hover:border-theme-border"
    >
      ← Précédent
    </button>

    <button
      v-for="p in visiblePages"
      :key="p === '…' ? `ellipsis-${Math.random()}` : p"
      @click="typeof p === 'number' && $emit('update:page', p)"
      :disabled="p === '…'"
      :class="[
        'min-w-[2.25rem] px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-200 border',
        p === page
          ? 'bg-theme-primary text-white border-theme-primary shadow'
          : p === '…'
            ? 'border-transparent text-theme-text-muted cursor-default'
            : 'border-theme-border bg-theme-card text-theme-text-muted hover:text-theme-primary hover:border-theme-primary'
      ]"
    >
      {{ p }}
    </button>

    <button
      @click="$emit('update:page', Math.min(totalPages, page + 1))"
      :disabled="page === totalPages"
      class="px-3 py-2 rounded-lg text-sm font-medium border border-theme-border bg-theme-card text-theme-text-muted hover:text-theme-primary hover:border-theme-primary transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:text-theme-text-muted disabled:hover:border-theme-border"
    >
      Suivant →
    </button>
  </div>
</template>

<script>
export default {
  name: 'Pagination',
  props: {
    page: {
      type: Number,
      required: true,
    },
    totalPages: {
      type: Number,
      required: true,
    },
  },
  emits: ['update:page'],
  computed: {
    visiblePages() {
      const total = this.totalPages;
      const current = this.page;
      const delta = 1;
      const pages = [];
      for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
          pages.push(i);
        } else if (pages[pages.length - 1] !== '…') {
          pages.push('…');
        }
      }
      return pages;
    },
  },
};
</script>
