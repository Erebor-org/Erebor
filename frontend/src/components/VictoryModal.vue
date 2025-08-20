<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-theme-bg border border-theme-border rounded-xl p-8 max-w-md w-full mx-4 text-center">
      <div class="mb-6">
        <div class="text-6xl mb-4">🎉</div>
        <h2 class="text-2xl font-bold text-theme-success mb-2">Félicitations !</h2>
        <p class="text-theme-text-muted">Vous avez trouvé le monstre du jour !</p>
      </div>
      
      <div class="mb-6">
        <img 
          v-if="monsterImg" 
          :src="monsterImg" 
          :alt="monsterName"
          class="w-24 h-24 mx-auto mb-3 rounded-lg border border-theme-border"
        />
        <div class="text-xl font-semibold text-theme-text">{{ monsterName }}</div>
      </div>
      
      <div class="flex gap-3">
        <button 
          @click="$emit('close')"
          class="flex-1 bg-theme-primary text-white px-6 py-3 rounded-xl hover:bg-theme-primary-dark transition-colors"
        >
          Continuer
        </button>
        <button 
          @click="shareResult"
          class="flex-1 bg-theme-success text-white px-6 py-3 rounded-xl hover:bg-theme-success-dark transition-colors"
        >
          Partager
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  monsterImg: {
    type: String,
    default: null
  },
  monsterName: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close'])

function shareResult() {
  // Ici on pourrait implémenter le partage sur les réseaux sociaux
  // ou la copie dans le presse-papier
  if (navigator.share) {
    navigator.share({
      title: 'Dofusdle Classic',
      text: `J'ai trouvé le monstre du jour : ${props.monsterName} ! 🎯`,
      url: window.location.href
    })
  } else {
    // Fallback pour les navigateurs qui ne supportent pas l'API Share
    navigator.clipboard.writeText(`J'ai trouvé le monstre du jour : ${props.monsterName} ! 🎯`)
    alert('Résultat copié dans le presse-papier !')
  }
}
</script>
