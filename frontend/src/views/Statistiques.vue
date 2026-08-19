<template>
  <div class="min-h-screen">
    <Notification ref="notificationRef" />

    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Statistiques de la Guilde</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">Analysez les performances et la composition de votre communauté</p>
      </div>

      <!-- Filter Section -->
      <div class="glass-card rounded-2xl p-6 mb-8">
        <div class="flex justify-center mb-6">
          <div class="inline-flex rounded-xl bg-theme-bg-muted p-1 border border-theme-border" role="group">
            <button
              @click="filter = 'global'"
              class="stat-tab"
              :class="{ 'stat-tab--active': filter === 'global' }"
            >
              Global
            </button>
            <button
              @click="filter = 'byRole'"
              class="stat-tab"
              :class="{ 'stat-tab--active': filter === 'byRole' }"
            >
              Par Rôle
            </button>
            <button
              @click="filter = 'byRecruiter'"
              class="stat-tab"
              :class="{ 'stat-tab--active': filter === 'byRecruiter' }"
            >
              Par Recruteur
            </button>
          </div>
        </div>

        <div v-if="filter === 'byRole'" class="mb-4 flex justify-center">
          <div class="w-full md:w-1/3">
            <ThemeSelect
              v-model="selectedRole"
              :options="roleOptions"
              placeholder="Sélectionner un rôle"
              option-value="id"
              option-label="name"
            />
          </div>
        </div>
        <div v-if="filter === 'byRecruiter'" class="mb-4 flex justify-center">
          <div class="w-full md:w-1/3">
            <ThemeSelect
              v-model="selectedRecruiter"
              :options="recruiterOptions"
              placeholder="Sélectionner un recruteur"
              option-value="id"
              option-label="pseudo"
            />
          </div>
        </div>

        <!-- Date Range Selector for Recruitment Stats -->
        <div class="mt-6 pt-6 border-t border-theme-border">
          <div class="flex items-center space-x-3 mb-4">
            <div class="stat-icon-chip">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-lg font-serif font-bold text-theme-primary">Période de recrutement</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-theme-text mb-2">
                Date de début
              </label>
              <ThemedDatePicker
                v-model="startDate"
                model-type="yyyy-MM-dd"
                :format="'dd/MM/yyyy'"
                :max-date="endDate ? new Date(endDate) : new Date()"
                :placeholder="'Sélectionner une date'"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-theme-text mb-2">
                Date de fin
              </label>
              <ThemedDatePicker
                v-model="endDate"
                model-type="yyyy-MM-dd"
                :format="'dd/MM/yyyy'"
                :min-date="startDate ? new Date(startDate) : undefined"
                :max-date="new Date()"
                :placeholder="'Sélectionner une date'"
              />
            </div>
          </div>
          <div class="mt-4 flex justify-center">
            <button
              @click="clearDateRange"
              class="px-4 py-2 text-sm font-medium text-theme-text-muted hover:text-theme-text bg-theme-bg-muted hover:bg-theme-border rounded-lg transition-all duration-200"
            >
              Réinitialiser la période
            </button>
          </div>
        </div>
      </div>

      <!-- Loading Indicator -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="w-14 h-14 border-4 border-theme-border rounded-full animate-spin" style="border-top-color: var(--accent);"></div>
      </div>

      <!-- Statistics Content -->
      <div v-else-if="statistics" class="space-y-6">
        <!-- Character Stats -->
        <div class="glass-card rounded-2xl p-6">
          <div class="flex items-center space-x-3 mb-6">
            <div class="stat-icon-chip">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h2 class="text-xl font-serif font-bold text-theme-primary">Statistiques des Personnages</h2>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="stat-tile">
              <span class="stat-tile-label">Personnages principaux</span>
              <span class="stat-tile-value">{{ statistics.totalCharacters }}</span>
            </div>
            <div class="stat-tile">
              <span class="stat-tile-label">Total personnages</span>
              <span class="stat-tile-value">{{ statistics.totalCharactersIncludingMules }}</span>
            </div>
          </div>
        </div>

        <!-- Booty Distribution -->
        <div class="glass-card rounded-2xl p-6">
          <div class="flex items-center space-x-3 mb-6">
            <div class="stat-icon-chip">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h2 class="text-xl font-serif font-bold text-theme-primary">Butins</h2>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="(count, level) in statistics.bootyCounts" :key="level" class="stat-tile">
              <span class="stat-tile-label">{{ level }}</span>
              <span class="stat-tile-value">{{ count }}</span>
            </div>
          </div>
        </div>

        <!-- Member Roles Distribution -->
        <div class="glass-card rounded-2xl p-6">
          <div class="flex items-center space-x-3 mb-6">
            <div class="stat-icon-chip">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <h2 class="text-xl font-serif font-bold text-theme-primary">Répartition des Rôles</h2>
          </div>

          <div class="flex flex-col md:flex-row items-center">
            <div class="w-full md:w-1/2 h-72">
              <canvas ref="rolesChart"></canvas>
            </div>
            <div class="w-full md:w-1/2 mt-4 md:mt-0 md:pl-6">
              <div class="grid grid-cols-1 gap-2.5">
                <div v-for="(count, role) in statistics.memberRolesDistribution" :key="role" class="flex items-center justify-between gap-3">
                  <span class="text-theme-text text-sm font-medium capitalize truncate">{{ role }}</span>
                  <div class="flex items-center gap-2 flex-shrink-0">
                    <div class="stat-bar">
                      <div
                        class="stat-bar-fill"
                        :style="{ width: `${(count / statistics.totalCharacters) * 100}%` }"
                      ></div>
                    </div>
                    <span class="text-theme-primary font-semibold text-sm w-6 text-right">{{ count }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Class Distribution Chart -->
        <div class="glass-card rounded-2xl p-6">
          <div class="flex items-center space-x-3 mb-6">
            <div class="stat-icon-chip">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h2 class="text-xl font-serif font-bold text-theme-primary">Répartition par Classe</h2>
          </div>

          <div class="flex flex-col md:flex-row items-center">
            <div class="w-full md:w-1/2 h-72">
              <canvas ref="classChart"></canvas>
            </div>
            <div class="w-full md:w-1/2 mt-4 md:mt-0 md:pl-6">
              <div class="grid grid-cols-2 gap-2.5">
                <div v-for="(percentage, className) in statistics.classDistribution" :key="className" class="flex items-center gap-2">
                  <span class="stat-dot" :style="{ backgroundColor: getClassColor(className) }"></span>
                  <span class="text-sm text-theme-text truncate">{{ className }}: {{ percentage }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recruiter Performance -->
        <div class="glass-card rounded-2xl p-6">
          <div class="flex items-center space-x-3 mb-6">
            <div class="stat-icon-chip">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
              </svg>
            </div>
            <h2 class="text-xl font-serif font-bold text-theme-primary">Performance des Recruteurs</h2>
          </div>

          <div class="rounded-xl border border-theme-border overflow-hidden">
            <div v-for="(data, index) in recruiterData" :key="index" class="stat-recruiter-row">
              <span class="stat-avatar">
                <img v-if="data.class" :src="getClassIcon(data.class)" :alt="`Classe ${data.class}`" />
              </span>
              <span class="stat-recruiter-name">{{ data.name }}</span>
              <div class="stat-bar stat-bar--wide">
                <div class="stat-bar-fill" :style="{ width: `${data.percentage}%` }"></div>
              </div>
              <span class="text-theme-primary font-semibold text-sm w-14 text-right">{{ data.percentage }}%</span>
              <span class="stat-recruiter-count">{{ data.count }}</span>
            </div>
            <div v-if="recruiterData.length === 0" class="text-center py-8 text-theme-text-muted text-sm">
              Aucune donnée de recrutement
            </div>
          </div>
        </div>
      </div>

      <!-- No Data State -->
      <div v-else class="text-center py-12 text-theme-text-muted">
        <svg class="w-16 h-16 mx-auto mb-4 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <p class="text-lg font-medium mb-1">Aucune donnée disponible</p>
        <p class="text-sm">Les statistiques apparaîtront ici une fois les données chargées</p>
      </div>
    </div>
    <button
      v-if="showScrollToTop"
      @click="scrollToTop"
      class="fixed bottom-8 right-8 z-[9999] w-14 h-14 glass-modal text-theme-primary rounded-full transition-all duration-300 transform hover:scale-110 focus:outline-none"
      title="Retour en haut de page"
    >
      <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
      </svg>
    </button>
  </div>
</template>

<script>
import axios from 'axios';
import Notification from '@/components/NotificationCenter.vue';
import Chart from 'chart.js/auto';
import ThemeSelect from '@/components/ThemeSelect.vue';
import ThemedDatePicker from '@/components/ThemedDatePicker.vue';
import { getClassIcon } from '@/config/classIcons';
import { useThemeStore } from '@/stores/themeStore';

const API_URL = import.meta.env.VITE_API_URL;

export default {
  name: 'GuildStatistics',
  components: {
    Notification,
    ThemeSelect,
    ThemedDatePicker
  },
  setup() {
    const themeStore = useThemeStore();
    return { themeStore };
  },
  data() {
    return {
      statistics: null,
      filter: 'global',
      selectedRole: '',
      selectedRecruiter: '',
      startDate: null,
      endDate: null,
      loading: true,
      roles: [],
      recruiters: [],
      classChart: null,
      rolesChart: null,
      showScrollToTop: false,
      classColors: {
        sram: '#6b7280',
        forgelance: '#b91c1c',
        cra: '#15803d',
        ecaflip: '#b45309',
        eniripsa: '#db2777',
        enutrof: '#a16207',
        feca: '#0369a1',
        eliotrope: '#4338ca',
        iop: '#b91c1c',
        osamodas: '#15803d',
        pandawa: '#a16207',
        roublard: '#6b7280',
        sacrieur: '#b91c1c',
        sadida: '#15803d',
        steamer: '#0369a1',
        xelor: '#4338ca',
        zobal: '#6b7280',
        huppermage: '#4338ca',
        ouginak: '#b45309',
      },
    };
  },
  computed: {
    recruiterData() {
      if (!this.statistics || !this.statistics.recruiterPerformance) return [];

      const totalRecruits = Object.values(this.statistics.recruiterPerformance).reduce((sum, item) => {
        return sum + (typeof item === 'object' ? item.count : item);
      }, 0);

      return Object.entries(this.statistics.recruiterPerformance).map(([name, item]) => {
        const count = typeof item === 'object' ? item.count : item;
        const recruiterClass = typeof item === 'object' ? item.class : '';

        return {
          name,
          class: recruiterClass || (this.recruiters.find(r => r.pseudo === name)?.class || ''),
          count,
          percentage: totalRecruits > 0 ? Math.round((count / totalRecruits) * 100) : 0
        };
      });
    },
    roleOptions() {
      return this.roles.map(role => ({
        id: role.id,
        name: role.name
      }));
    },
    recruiterOptions() {
      return this.recruiters.map(recruiter => ({
        id: recruiter.id,
        pseudo: recruiter.pseudo
      }));
    }
  },
  watch: {
    filter() {
      this.fetchStatistics();
    },
    selectedRole() {
      if (this.filter === 'byRole') {
        this.fetchStatistics();
      }
    },
    selectedRecruiter() {
      if (this.filter === 'byRecruiter') {
        this.fetchStatistics();
      }
    },
    startDate() {
      this.fetchStatistics();
    },
    endDate() {
      this.fetchStatistics();
    },
    statistics() {
      this.$nextTick(() => {
        this.renderCharts();
      });
    },
    'themeStore.currentTheme'() {
      this.$nextTick(() => {
        this.renderCharts();
      });
    },
  },
  mounted() {
    this.fetchRoles();
    this.fetchRecruiters();
    this.fetchStatistics();
  },
  methods: {
    getClassIcon,
    cssVar(name) {
      return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    },
    hexToRgb(hex) {
      const clean = hex.replace('#', '');
      const bigint = parseInt(clean.length === 3 ? clean.split('').map(c => c + c).join('') : clean, 16);
      return { r: (bigint >> 16) & 255, g: (bigint >> 8) & 255, b: bigint & 255 };
    },
    mixColors(hexA, hexB, t) {
      const a = this.hexToRgb(hexA);
      const b = this.hexToRgb(hexB);
      const r = Math.round(a.r + (b.r - a.r) * t);
      const g = Math.round(a.g + (b.g - a.g) * t);
      const bl = Math.round(a.b + (b.b - a.b) * t);
      return `rgb(${r}, ${g}, ${bl})`;
    },
    // Dégradé rouge/or de la guilde décliné en N teintes, pour un camembert
    // dont les couleurs n'ont pas de signification propre (contrairement aux
    // couleurs de classe, conservées telles quelles).
    getRoleColors(count) {
      const stops = [
        this.cssVar('--primary') || '#9E1B32',
        this.cssVar('--accent') || '#C9A227',
        this.cssVar('--primary-hover') || '#7E1527',
        this.cssVar('--accent-hover') || '#A98620',
      ];
      if (count <= 1) return [stops[0]];
      const colors = [];
      for (let i = 0; i < count; i++) {
        const t = i / (count - 1);
        const scaled = t * (stops.length - 1);
        const segment = Math.min(Math.floor(scaled), stops.length - 2);
        const localT = scaled - segment;
        colors.push(this.mixColors(stops[segment], stops[segment + 1], localT));
      }
      return colors;
    },
    handleScroll() {
      const scrollContainer = document.querySelector('.h-\\[calc\\(100vh-128px\\)\\]');
      if (scrollContainer) {
        const scrollTop = scrollContainer.scrollTop;
        this.showScrollToTop = scrollTop > 300;
      } else {
        const scrollY = window.scrollY || window.pageYOffset || 0;
        this.showScrollToTop = scrollY > 300;
      }
    },
    getClassColor(className) {
      return this.classColors[className.toLowerCase()] || '#6b7280';
    },
    async fetchRoles() {
      try {
        const response = await axios.get(`${API_URL}/ranks`);
        this.roles = response.data;
      } catch (error) {
        console.error('Error fetching roles:', error);
        this.$refs.notificationRef?.showNotification('Erreur lors de la récupération des rôles', 'error');
      }
    },
    async fetchRecruiters() {
      try {
        const response = await axios.get(`${API_URL}/characters/recruiters`);
        this.recruiters = response.data;
      } catch (error) {
        console.error('Error fetching recruiters:', error);
        this.$refs.notificationRef?.showNotification('Erreur lors de la récupération des recruteurs', 'error');
      }
    },
    async fetchStatistics() {
      this.loading = true;
      try {
        let params = { filter: this.filter };

        if (this.filter === 'byRole' && this.selectedRole) {
          params.roleId = this.selectedRole;
        } else if (this.filter === 'byRecruiter' && this.selectedRecruiter) {
          params.recruiterId = this.selectedRecruiter;
        }

        if (this.startDate) {
          params.startDate = this.startDate;
        }
        if (this.endDate) {
          params.endDate = this.endDate;
        }

        const response = await axios.get(`${API_URL}/statistics`, { params });
        this.statistics = response.data;
      } catch (error) {
        console.error('Error fetching statistics:', error);
        this.$refs.notificationRef?.showNotification('Erreur lors de la récupération des statistiques', 'error');
      } finally {
        this.loading = false;
      }
    },
    clearDateRange() {
      this.startDate = null;
      this.endDate = null;
    },
    scrollToTop() {
      try {
        const scrollContainer = document.querySelector('.h-\\[calc\\(100vh-128px\\)\\]');
        if (scrollContainer) {
          scrollContainer.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        setTimeout(() => {
          this.handleScroll();
        }, 100);
      } catch (error) {
        console.error('Error scrolling to top:', error);
      }
    },
    tooltipStyle() {
      return {
        backgroundColor: this.cssVar('--card') || '#1C1512',
        titleColor: this.cssVar('--text') || '#F5EFE6',
        titleFont: { family: 'ui-serif, Georgia, Cambria, "Times New Roman", serif', weight: '700' },
        bodyColor: this.cssVar('--text-muted') || '#B3A79B',
        borderColor: this.cssVar('--border') || '#2E211C',
        borderWidth: 1,
        padding: 10,
        cornerRadius: 8,
        displayColors: true,
        boxPadding: 4,
      };
    },
    renderCharts() {
      if (!this.statistics) return;

      this.renderClassChart();
      this.renderRolesChart();
    },
    renderClassChart() {
      if (this.classChart) {
        this.classChart.destroy();
      }

      const ctx = this.$refs.classChart?.getContext('2d');
      if (!ctx || !this.statistics.classDistribution) return;

      const labels = Object.keys(this.statistics.classDistribution);
      const data = Object.values(this.statistics.classDistribution);
      const backgroundColor = labels.map(className => this.getClassColor(className));
      const borderColor = this.cssVar('--card') || '#1C1512';

      this.classChart = new Chart(ctx, {
        type: 'pie',
        data: {
          labels,
          datasets: [{
            data,
            backgroundColor,
            borderColor,
            borderWidth: 2,
            hoverOffset: 6,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              ...this.tooltipStyle(),
              callbacks: {
                label: (context) => `${context.label}: ${context.raw}%`
              }
            }
          }
        }
      });
    },
    renderRolesChart() {
      if (this.rolesChart) {
        this.rolesChart.destroy();
      }

      const ctx = this.$refs.rolesChart?.getContext('2d');
      if (!ctx || !this.statistics.memberRolesDistribution) return;

      const labels = Object.keys(this.statistics.memberRolesDistribution);
      const data = Object.values(this.statistics.memberRolesDistribution);
      const backgroundColor = this.getRoleColors(labels.length);
      const borderColor = this.cssVar('--card') || '#1C1512';

      this.rolesChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels,
          datasets: [{
            data,
            backgroundColor,
            borderColor,
            borderWidth: 2,
            hoverOffset: 6,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '62%',
          plugins: {
            legend: { display: false },
            tooltip: {
              ...this.tooltipStyle(),
              callbacks: {
                label: (context) => `${context.label}: ${context.raw}`
              }
            }
          }
        }
      });
    }
  },
  beforeUnmount() {
    const scrollContainer = document.querySelector('.h-\\[calc\\(100vh-128px\\)\\]');
    if (scrollContainer) {
      scrollContainer.removeEventListener('scroll', this.handleScroll);
    } else {
      window.removeEventListener('scroll', this.handleScroll);
      document.removeEventListener('scroll', this.handleScroll);
    }
  },
};
</script>

<style scoped>
.stat-icon-chip {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.65rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background-image: linear-gradient(140deg, var(--accent), var(--primary));
}

.stat-tab {
  padding: 0.65rem 1.4rem;
  font-size: 0.85rem;
  font-weight: 600;
  border-radius: 0.6rem;
  color: var(--text-muted);
  transition: background-color 0.2s, color 0.2s;
}
.stat-tab:hover {
  color: var(--text);
}
.stat-tab--active {
  background-image: linear-gradient(140deg, var(--accent), var(--primary));
  color: #fff;
}

.stat-tile {
  background-color: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--border);
  border-radius: 0.85rem;
  padding: 1rem 1.1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  transition: border-color 0.2s, background-color 0.2s;
}
.stat-tile:hover {
  border-color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.05);
}
.stat-tile-label {
  color: var(--text);
  font-weight: 500;
}
.stat-tile-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary);
  flex-shrink: 0;
}

.stat-bar {
  width: 8rem;
  height: 0.4rem;
  border-radius: 9999px;
  background-color: var(--bg-muted);
  overflow: hidden;
}
.stat-bar--wide {
  width: 100%;
  flex: 1;
}
.stat-bar-fill {
  height: 100%;
  border-radius: 9999px;
  background-image: linear-gradient(90deg, var(--primary), var(--accent));
  transition: width 0.3s ease;
}

.stat-dot {
  width: 0.7rem;
  height: 0.7rem;
  border-radius: 9999px;
  flex-shrink: 0;
}

.stat-recruiter-row {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.7rem 1rem;
  border-bottom: 1px solid var(--border);
  transition: background-color 0.2s;
}
.stat-recruiter-row:last-child {
  border-bottom: none;
}
.stat-recruiter-row:hover {
  background-color: rgba(var(--primary-rgb), 0.04);
}
.stat-avatar {
  display: inline-flex;
  padding: 2px;
  border-radius: 9999px;
  background-image: conic-gradient(from 200deg, var(--accent), transparent 40%, var(--primary), var(--accent));
  flex-shrink: 0;
}
.stat-avatar img {
  width: 1.85rem;
  height: 1.85rem;
  border-radius: 9999px;
  object-fit: cover;
  background-color: var(--card);
  display: block;
}
.stat-recruiter-name {
  flex-shrink: 0;
  width: 140px;
  min-width: 0;
  color: var(--text);
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.stat-recruiter-count {
  flex-shrink: 0;
  width: 2.5rem;
  text-align: right;
  color: var(--text-muted);
  font-size: 0.82rem;
}

@media (max-width: 640px) {
  .stat-recruiter-name {
    width: 90px;
  }
}
</style>
