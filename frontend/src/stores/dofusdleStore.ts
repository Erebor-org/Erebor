import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

const API_URL = (import.meta.env.VITE_API_URL || '').replace(/\/$/, '')

export type GuessRow = {
  id: number, // dofusdb_id !
  name: string,
  img?: string,
  levelMin?: number,
  levelMax?: number,
  race?: string,
  area?: string,
  subarea?: string,
  apMin?: number,
  apMax?: number,
  mpMin?: number,
  mpMax?: number,
  hpMin?: number,
  hpMax?: number,
  resistancesMax?: Record<string, number>,
  hint?: {
    race: 'match'|'miss'|'unknown',
    zone: 'exact'|'partial'|'miss'|'unknown',
    level: 'exact'|'partial'|'up'|'down'|'unknown',
    ap: 'exact'|'partial'|'up'|'down'|'unknown',
    mp: 'exact'|'partial'|'up'|'down'|'unknown',
    hp: 'exact'|'partial'|'up'|'down'|'unknown',
    resistDominant: 'exact'|'partial'|'miss'|'unknown'
  }
}

export const useDofusdleStore = defineStore('dofusdle', () => {
  const puzzleId = ref<string>('')
  const date = ref<string>('')
  const attempts = ref<GuessRow[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const colorize = computed(() => attempts.value.length >= 2)
  const correct = ref(false)
  const allMonsters = ref<any[]>([])
  // Ajoute une référence au daily courant (stocké lors du fetchDaily)
  const dailyMonster = ref<any>(null)

  async function fetchDaily() {
    loading.value = true
    error.value = null
    try {
      const res = await axios.get(`${API_URL}/erebor/dofusdle/api/classic/daily`)
      const data = res.data
      puzzleId.value = data.puzzleId
      date.value = data.date
      dailyMonster.value = data.monster
      attempts.value = []
      correct.value = false
    } catch (e: any) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function fetchAllMonsters() {
    try {
      const res = await axios.get(`${API_URL}/erebor/dofusdle/api/monsters`)
      // id = dofusdb_id partout
      allMonsters.value = res.data.map((m: any) => ({
        ...m,
        id: m.dofusdb_id // id = dofusdb_id
      }))
    } catch (e) {
      allMonsters.value = []
    }
  }

  function searchMonsters(query: string) {
    if (!query) return []
    const q = query.toLowerCase()
    return allMonsters.value
      .filter(m => m.name && m.name.toLowerCase().includes(q))
      .map(m => ({
        id: m.dofusdb_id, // id = dofusdb_id
        name: m.name,
        img: m.imageUrl,
        levelMin: m.levelMin,
        levelMax: m.levelMax,
        race: m.race,
        area: m.area,
        subarea: m.subarea,
        apMin: m.apMin,
        apMax: m.apMax,
        mpMin: m.mpMin,
        mpMax: m.mpMax,
        hpMin: m.hpMin,
        hpMax: m.hpMax,
        resistancesMax: m.resistancesMax || {},
      }))
  }

  // Recherche côté backend (optionnel)
  async function searchMonstersApi(query: string) {
    if (!query) return []
    try {
      const res = await axios.get(`${API_URL}/erebor/dofusdle/api/classic/search?q=${encodeURIComponent(query)}`)
      // Adapter le mapping si besoin
      return res.data.map((m: any) => ({
        id: m.id, // ici c'est déjà dofusdb_id côté API
        name: m.name,
        img: m.img,
        refLevel: m.level,
        family: '',
        areas: [],
        rank: 'normal',
        element: undefined,
        ap: undefined,
        mp: undefined,
        hp: undefined,
        resistDominant: undefined,
      }))
    } catch (e) {
      return []
    }
  }

  async function guessMonster(monster: GuessRow) {
    console.log('🚀 Store: Début du guess pour monstre:', monster.id)
    console.log('🔑 Store: PuzzleId:', puzzleId.value)
    console.log('📅 Store: Date:', date.value)
    
    loading.value = true
    error.value = null
    
    try {
      const res = await axios.post(`${API_URL}/erebor/dofusdle/api/classic/guess`, {
        puzzleId: puzzleId.value,
        guessId: monster.id, // id = dofusdb_id
      })
      
      console.log('📡 Store: Réponse API:', res.data)
      
      if (res.data && res.data.hint) {
        console.log('💡 Store: Ajout de l\'attempt avec hint:', res.data.hint)
        addAttempt(monster, res.data.hint)
        if (res.data.correct) {
          console.log('🎉 Store: Guess correct!')
          correct.value = true
        }
      } else {
        console.log('⚠️ Store: Pas de hint dans la réponse')
      }
      
      return res.data
    } catch (e: any) {
      console.error('❌ Store: Erreur lors du guess:', e)
      error.value = e.message
      return null
    } finally {
      loading.value = false
    }
  }

  function addAttempt(monster: GuessRow, hint: any) {
    // Remplace les champs null/undefined par 'unknown'
    const normalizedHint = { ...hint }
    for (const k in normalizedHint) {
      if (normalizedHint[k] === null || normalizedHint[k] === undefined) {
        normalizedHint[k] = 'unknown'
      }
    }
    
    // Récupère les zones depuis allMonsters si absentes
    let area = monster.area
    let subarea = monster.subarea
    
    // Si guess = daily, copie les zones du daily
    if (dailyMonster.value && monster.id === dailyMonster.value.dofusdb_id) {
      area = dailyMonster.value.area || area
      subarea = dailyMonster.value.subarea || subarea
    }
    
    // Si zones manquantes, cherche dans allMonsters
    if (!area || !subarea) {
      const ref = allMonsters.value.find(m => m.dofusdb_id === monster.id || m.id === monster.id)
      if (ref) {
        area = area || ref.area
        subarea = subarea || ref.subarea
      }
    }
    
    const idx = attempts.value.findIndex(a => a.id === monster.id)
    const guessRow = {
      ...monster,
      area,
      subarea,
      hint: normalizedHint
    }
    if (idx >= 0) {
      attempts.value.splice(idx, 1)
      attempts.value.unshift(guessRow)
    } else {
      attempts.value.unshift(guessRow)
    }
  }

  return {
    puzzleId, date, attempts, loading, error, colorize, correct,
    allMonsters, fetchDaily, fetchAllMonsters, searchMonsters, searchMonstersApi, guessMonster, addAttempt
  }
})
