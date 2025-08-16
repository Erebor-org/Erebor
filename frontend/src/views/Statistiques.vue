<template>
  <div class="min-h-screen bg-black text-white">
    <!-- Notification -->
    <Notification ref="notificationRef" />

    <!-- Main Container -->
    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-6xl font-bold text-amber-400 mb-6">Statistiques de la Guilde</h1>
        <div class="w-32 h-1 bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-600 mx-auto rounded-full shadow-lg shadow-amber-500/50"></div>
        <p class="text-gray-400 mt-6 text-lg">Analysez les performances et la composition de votre communauté</p>
      </div>

      <!-- Filter Section -->
      <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-2xl p-6 mb-8">
        <div class="flex justify-center mb-6">
          <div class="inline-flex rounded-xl shadow-lg bg-gray-800 p-1" role="group">
            <button 
              @click="filter = 'global'" 
              :class="[
                'px-6 py-3 text-sm font-medium rounded-lg transition-all duration-200',
                filter === 'global' 
                  ? 'bg-gradient-to-r from-amber-600 to-yellow-600 text-black shadow-lg' 
                  : 'text-gray-400 hover:text-gray-300 hover:bg-gray-700'
              ]"
            >
              Global
            </button>
            <button 
              @click="filter = 'byRole'" 
              :class="[
                'px-6 py-3 text-sm font-medium rounded-lg transition-all duration-200',
                filter === 'byRole' 
                  ? 'bg-gradient-to-r from-amber-600 to-yellow-600 text-black shadow-lg' 
                  : 'text-gray-400 hover:text-gray-300 hover:bg-gray-700'
              ]"
            >
              Par Rôle
            </button>
            <button 
              @click="filter = 'byRecruiter'" 
              :class="[
                'px-6 py-3 text-sm font-medium rounded-lg transition-all duration-200',
                filter === 'byRecruiter' 
                  ? 'bg-gradient-to-r from-amber-600 to-yellow-600 text-black shadow-lg' 
                  : 'text-gray-400 hover:text-gray-300 hover:bg-gray-700'
              ]"
            >
              Par Recruteur
            </button>
          </div>
        </div>
        
        <div v-if="filter === 'byRole'" class="mb-4">
          <select 
            v-model="selectedRole" 
            class="w-full md:w-1/3 mx-auto block bg-gray-800 border-2 border-gray-600 text-white rounded-lg p-3 text-base focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200"
          >
            <option value="" class="bg-gray-800 text-white">Sélectionner un rôle</option>
            <option v-for="role in roles" :key="role.id" :value="role.id" class="bg-gray-800 text-white">{{ role.name }}</option>
          </select>
        </div>
        <div v-if="filter === 'byRecruiter'" class="mb-4">
          <select 
            v-model="selectedRecruiter" 
            class="w-full md:w-1/3 mx-auto block bg-gray-800 border-2 border-gray-600 text-white rounded-lg p-3 text-base focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200"
          >
            <option value="" class="bg-gray-800 text-white">Sélectionner un recruteur</option>
            <option v-for="recruiter in recruiters" :key="recruiter.id" :value="recruiter.id" class="bg-gray-800 text-white">{{ recruiter.pseudo }}</option>
          </select>
        </div>
      </div>

      <!-- Loading Indicator -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="w-16 h-16 border-4 border-gray-700 border-t-amber-500 rounded-full animate-spin"></div>
      </div>

      <!-- Statistics Content -->
      <div v-else-if="statistics" class="space-y-8">
        <!-- Character Stats -->
        <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-2xl p-6">
          <div class="flex items-center space-x-3 mb-6">
            <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h2 class="text-xl font-bold text-amber-400">Statistiques des Personnages</h2>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 hover:border-amber-500 transition-colors duration-200">
              <div class="flex items-center justify-between">
                <span class="text-gray-300 font-medium">Personnages principaux</span>
                <span class="text-2xl font-bold text-amber-400">{{ statistics.totalCharacters }}</span>
              </div>
            </div>
            <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 hover:border-amber-500 transition-colors duration-200">
              <div class="flex items-center justify-between">
                <span class="text-gray-300 font-medium">Total personnages</span>
                <span class="text-2xl font-bold text-amber-400">{{ statistics.totalCharactersIncludingMules }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Booty Distribution -->
        <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-2xl p-6">
          <div class="flex items-center space-x-3 mb-6">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h2 class="text-xl font-bold text-amber-400">Butins</h2>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="(count, level) in statistics.bootyCounts" :key="level" class="bg-gray-800 rounded-xl p-4 border border-gray-700 hover:border-amber-500 transition-colors duration-200">
              <div class="flex items-center justify-between">
                <span class="text-gray-300 font-medium">{{ level }}</span>
                <span class="text-2xl font-bold text-amber-400">{{ count }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Member Roles Distribution -->
        <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-2xl p-6">
          <div class="flex items-center space-x-3 mb-6">
            <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <h2 class="text-xl font-bold text-amber-400">Répartition des Rôles</h2>
          </div>
          
          <div class="flex flex-col md:flex-row items-center">
            <div class="w-full md:w-1/2 h-80">
              <canvas ref="rolesChart"></canvas>
            </div>
            <div class="w-full md:w-1/2 mt-4 md:mt-0 md:pl-6">
              <div class="grid grid-cols-1 gap-2">
                <div v-for="(count, role) in statistics.memberRolesDistribution" :key="role" class="flex items-center justify-between">
                  <span class="text-gray-300 font-medium capitalize">{{ role }}</span>
                  <div class="flex items-center">
                    <div class="w-32 bg-gray-700 rounded-full h-2.5 mr-2">
                      <div 
                        class="bg-amber-500 h-2.5 rounded-full transition-all duration-300" 
                        :style="{ width: `${(count / statistics.totalCharacters) * 100}%` }"
                      ></div>
                    </div>
                    <span class="text-amber-400 font-semibold">{{ count }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Class Distribution Chart -->
        <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-2xl p-6">
          <div class="flex items-center space-x-3 mb-6">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h2 class="text-xl font-bold text-amber-400">Répartition par Classe</h2>
          </div>
          
          <div class="flex flex-col md:flex-row items-center">
            <div class="w-full md:w-1/2 h-80">
              <canvas ref="classChart"></canvas>
            </div>
            <div class="w-full md:w-1/2 mt-4 md:mt-0 md:pl-6">
              <div class="grid grid-cols-2 gap-2">
                <div v-for="(percentage, className) in statistics.classDistribution" :key="className" class="flex items-center">
                  <div 
                    class="w-4 h-4 rounded-full mr-2" 
                    :style="{ backgroundColor: getClassColor(className) }"
                  ></div>
                  <span class="text-sm text-gray-300">{{ className }}: {{ percentage }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recruiter Performance -->
        <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-2xl p-6">
          <div class="flex items-center space-x-3 mb-6">
            <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
              </svg>
            </div>
            <h2 class="text-xl font-bold text-amber-400">Performance des Recruteurs</h2>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-gray-800 border-b border-gray-700">
                  <th class="p-3 text-gray-300 font-medium">Recruteur</th>
                  <th class="p-3 text-gray-300 font-medium">Recrues</th>
                  <th class="p-3 text-gray-300 font-medium">Pourcentage</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(data, index) in recruiterData" :key="index" class="hover:bg-gray-800 transition-colors duration-200">
                  <td class="p-3 border-b border-gray-700">
                    <div class="flex items-center">
                      <img v-if="data.class" :src="getClassIcon(data.class)" alt="Class" class="w-6 h-6 mr-2 rounded-lg border border-gray-600" />
                      <span class="text-gray-300">{{ data.name }}</span>
                    </div>
                  </td>
                  <td class="p-3 border-b border-gray-700 text-amber-400 font-semibold">{{ data.count }}</td>
                  <td class="p-3 border-b border-gray-700">
                    <div class="flex items-center">
                      <div class="w-full bg-gray-700 rounded-full h-2.5 mr-2">
                        <div 
                          class="bg-amber-500 h-2.5 rounded-full transition-all duration-300" 
                          :style="{ width: `${data.percentage}%` }"
                        ></div>
                      </div>
                      <span class="text-amber-400 font-semibold">{{ data.percentage }}%</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- No Data State -->
      <div v-else class="text-center py-12">
        <div class="text-gray-400">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <p class="text-lg font-medium text-gray-500">Aucune donnée disponible</p>
          <p class="text-sm text-gray-600">Les statistiques apparaîtront ici une fois les données chargées</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Notification from '@/components/NotificationCenter.vue';
import Chart from 'chart.js/auto';

const API_URL = import.meta.env.VITE_API_URL;

export default {
  name: 'GuildStatistics',
  components: {
    Notification
  },
  data() {
    return {

      statistics: null,
      filter: 'global',
      selectedRole: '',
      selectedRecruiter: '',
      loading: true,
      roles: [],
      recruiters: [],
      classChart: null,
      rolesChart: null,
      classIcons: {
        sram: new URL('@/assets/icon_classe/sram.avif', import.meta.url).href,
        forgelance: new URL('@/assets/icon_classe/forgelance.avif', import.meta.url).href,
        cra: new URL('@/assets/icon_classe/cra.avif', import.meta.url).href,
        ecaflip: new URL('@/assets/icon_classe/ecaflip.avif', import.meta.url).href,
        eniripsa: new URL('@/assets/icon_classe/eniripsa.avif', import.meta.url).href,
        enutrof: new URL('@/assets/icon_classe/enutrof.avif', import.meta.url).href,
        feca: new URL('@/assets/icon_classe/feca.avif', import.meta.url).href,
        eliotrope: new URL('@/assets/icon_classe/eliotrope.avif', import.meta.url).href,
        iop: new URL('@/assets/icon_classe/iop.avif', import.meta.url).href,
        osamodas: new URL('@/assets/icon_classe/osamodas.avif', import.meta.url).href,
        pandawa: new URL('@/assets/icon_classe/pandawa.avif', import.meta.url).href,
        roublard: new URL('@/assets/icon_classe/roublard.avif', import.meta.url).href,
        sacrieur: new URL('@/assets/icon_classe/sacrieur.avif', import.meta.url).href,
        sadida: new URL('@/assets/icon_classe/sadida.avif', import.meta.url).href,
        steamer: new URL('@/assets/icon_classe/steamer.avif', import.meta.url).href,
        xelor: new URL('@/assets/icon_classe/xelor.avif', import.meta.url).href,
        zobal: new URL('@/assets/icon_classe/zobal.avif', import.meta.url).href,
        huppermage: new URL('@/assets/icon_classe/huppermage.avif', import.meta.url).href,
        ouginak: new URL('@/assets/icon_classe/ouginak.avif', import.meta.url).href,
      },
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
      
      const totalRecruits = Object.values(this.statistics.recruiterPerformance).reduce((sum, count) => sum + count, 0);
      
      return Object.entries(this.statistics.recruiterPerformance).map(([name, count]) => {
        const recruiter = this.recruiters.find(r => r.pseudo === name);
        return {
          name,
          class: recruiter ? recruiter.class : '',
          count,
          percentage: totalRecruits > 0 ? Math.round((count / totalRecruits) * 100) : 0
        };
      }).sort((a, b) => b.count - a.count);
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
    statistics() {
      this.$nextTick(() => {
        this.renderCharts();
      });
    }
  },
  mounted() {
    this.fetchRoles();
    this.fetchRecruiters();
    this.fetchStatistics();
  },
  methods: {
    getClassIcon(className) {
      return this.classIcons[className.toLowerCase()] || '';
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
        
        const response = await axios.get(`${API_URL}/statistics`, { params });
        this.statistics = response.data;
      } catch (error) {
        console.error('Error fetching statistics:', error);
        this.$refs.notificationRef?.showNotification('Erreur lors de la récupération des statistiques', 'error');
      } finally {
        this.loading = false;
      }
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
      
      this.classChart = new Chart(ctx, {
        type: 'pie',
        data: {
          labels,
          datasets: [{
            data,
            backgroundColor,
            borderColor: '#ffffff',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return `${context.label}: ${context.raw}%`;
                }
              }
            }
          }
        }
      });
    },
    getRoleColors(count) {
      const colors = [];
      for (let i = 0; i < count; i++) {
        const hue = ((i * 360) / count) % 360;
        const saturation = 65;
        const lightness = 50;
        colors.push(`hsl(${hue}, ${saturation}%, ${lightness}%)`);
      }
      return colors;
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

      this.rolesChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels,
          datasets: [{
            data,
            backgroundColor,
            borderColor: '#ffffff',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return `${context.label}: ${context.raw}`;
                }
              }
            }
          }
        }
      });
    }
  }
};
</script>

<style scoped>
.fas.fa-user::before {
  content: "👤";
}
.fas.fa-users::before {
  content: "👥";
}
.fas.fa-treasure-chest::before {
  content: "🏆";
}
</style>

