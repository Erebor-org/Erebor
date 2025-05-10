<template>
  <div
    class="w-full flex flex-col items-center justify-start bg-cover bg-center py-8"
    :style="{ backgroundImage: `url(${backgroundImage})` }"
  >
    <!-- Notification -->
    <Notification ref="notificationRef" />

    <!-- Header Section -->
    <div
      class="flex flex-col w-11/12 max-w-7xl bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-4 mb-6"
    >
      <h1 class="text-3xl font-bold text-[#b02e2e] mb-4 text-center">Statistiques de la guilde</h1>
    </div>

    <!-- Filter Section -->
    <div class="w-11/12 max-w-7xl bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-4 mb-6">
      <div class="flex justify-center mb-4">
        <div class="inline-flex rounded-md shadow-sm" role="group">
          <button 
            @click="filter = 'global'" 
            :class="[
              'px-4 py-2 text-sm font-medium border border-[#b07d46]',
              filter === 'global' 
                ? 'bg-[#b07d46] text-white' 
                : 'bg-white text-[#b07d46] hover:bg-[#f3d9b1]'
            ]"
            class="rounded-l-lg"
          >
            Global
          </button>
          <button 
            @click="filter = 'byRole'" 
            :class="[
              'px-4 py-2 text-sm font-medium border-t border-b border-[#b07d46]',
              filter === 'byRole' 
                ? 'bg-[#b07d46] text-white' 
                : 'bg-white text-[#b07d46] hover:bg-[#f3d9b1]'
            ]"
          >
            Par Rôle
          </button>
          <button 
            @click="filter = 'byRecruiter'" 
            :class="[
              'px-4 py-2 text-sm font-medium border border-[#b07d46]',
              filter === 'byRecruiter' 
                ? 'bg-[#b07d46] text-white' 
                : 'bg-white text-[#b07d46] hover:bg-[#f3d9b1]'
            ]"
            class="rounded-r-lg"
          >
            Par Recruteur
          </button>
        </div>
        
      </div>

      <div v-if="filter === 'byRole'" class="mb-4">
        <select 
          v-model="selectedRole" 
          class="w-full md:w-1/3 mx-auto block border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 text-base focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
        >
          <option value="">Sélectionner un rôle</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
        </select>
      </div>
      <div v-if="filter === 'byRecruiter'" class="mb-4">
        <select 
          v-model="selectedRecruiter" 
          class="w-full md:w-1/3 mx-auto block border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 text-base focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
        >
          <option value="">Sélectionner un recruteur</option>
          <option v-for="recruiter in recruiters" :key="recruiter.id" :value="recruiter.id">{{ recruiter.pseudo }}</option>
        </select>
      </div>
    </div>

    <!-- Loading Indicator -->
    <div v-if="loading" class="w-11/12 max-w-7xl flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#b07d46]"></div>
    </div>

    <!-- Statistics Content -->
    <div v-else-if="statistics" class="w-11/12 max-w-7xl">
      <!-- Character Stats -->
      <div class="bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-[#b02e2e] mb-4">Statistiques des personnages</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="flex items-center justify-between bg-gray-100 p-2 rounded">
            <span class="text-[#b07d46] font-semibold">Personnages principaux</span>
            <span class="text-[#b07d46]">{{ statistics.totalCharacters }}</span>
          </div>
          <div class="flex items-center justify-between bg-gray-100 p-2 rounded">
            <span class="text-[#b07d46] font-semibold">Total personnages</span>
            <span class="text-[#b07d46]">{{ statistics.totalCharactersIncludingMules }}</span>
          </div>
        </div>
      </div>

      <!-- Booty Distribution -->
      <div class="bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-[#b02e2e] mb-4">Butins</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="(count, level) in statistics.bootyCounts" :key="level" class="flex items-center justify-between bg-gray-100 p-2 rounded">
            <span class="text-[#b07d46] font-semibold">{{ level }}</span>
            <span class="text-[#b07d46]">{{ count }} </span>
          </div>
        </div>
      </div>
      <!-- Member Roles Distribution -->
      <div class="bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-[#b02e2e] mb-4">Répartition des rôles</h2>
        <div class="flex flex-col md:flex-row items-center">
          <div class="w-full md:w-1/2 h-80">
            <canvas ref="rolesChart"></canvas>
          </div>
          <div class="w-full md:w-1/2 mt-4 md:mt-0 md:pl-6">
            <div class="grid grid-cols-1 gap-2">
              <div v-for="(count, role) in statistics.memberRolesDistribution" :key="role" class="flex items-center justify-between">
                <span class="text-[#b07d46]">{{ role }}</span>
                <div class="flex items-center">
                  <div class="w-32 bg-gray-200 rounded-full h-2.5 mr-2">
                    <div 
                      class="bg-[#b07d46] h-2.5 rounded-full" 
                      :style="{ width: `${(count / statistics.totalCharacters) * 100}%` }"
                    ></div>
                  </div>
                  <span class="text-[#b07d46]">{{ count }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Class Distribution Chart -->
      <div class="bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-[#b02e2e] mb-4">Répartition des classes</h2>
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
                <span class="text-sm text-[#b07d46]">{{ className }}: {{ percentage }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      

      <!-- Recruiter Performance -->
      <div class="bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-[#b02e2e] mb-4">Performance des recruteurs</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#f3d9b1]">
                <th class="p-3 border-b-2 border-[#b07d46]">Recruteur</th>
                <th class="p-3 border-b-2 border-[#b07d46]">Recrues</th>
                <th class="p-3 border-b-2 border-[#b07d46]">Pourcentage</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(data, index) in recruiterData" :key="index" class="hover:bg-[#f3d9b1]/30">
                <td class="p-3 border-b border-[#b07d46]/20">
                  <div class="flex items-center">
                    <img v-if="data.class" :src="getClassIcon(data.class)" alt="Class" class="w-6 h-6 mr-2" />
                    <span>{{ data.name }}</span>
                  </div>
                </td>
                <td class="p-3 border-b border-[#b07d46]/20">{{ data.count }}</td>
                <td class="p-3 border-b border-[#b07d46]/20">
                  <div class="flex items-center">
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                      <div 
                        class="bg-[#b07d46] h-2.5 rounded-full" 
                        :style="{ width: `${data.percentage}%` }"
                      ></div>
                    </div>
                    <span>{{ data.percentage }}%</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- No Data Message -->
    <div v-else class="w-11/12 max-w-7xl bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-8 text-center">
      <p class="text-xl text-[#b07d46]">Aucune donnée statistique disponible.</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import members_bg from '@/assets/members_bg.webp';
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
      backgroundImage: members_bg,
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
