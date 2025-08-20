import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

const API_URL = (import.meta.env.VITE_API_URL || '').replace(/\/$/, '')

export type GuessRow = {
  id: number, // dofusdb_id !
  name: string,
  img?: string,
  refLevel: number,
  family: string,
  areas: string[],
  subareas: string[],
  rank: 'normal'|'miniboss'|'boss',
  element?: 'water'|'fire'|'earth'|'air'|'neutral',
  ap: number, mp: number, hp: number,
  resistDominant?: 'water'|'fire'|'earth'|'air'|'neutral'|null,
  hint?: {
    family: 'match'|'miss'|'unknown',
    zone: 'exact'|'overlap'|'miss'|'unknown',
    level: 'up'|'down'|'eq'|'unknown',
    rank: 'match'|'miss'|'unknown',
    element: 'match'|'miss'|'unknown',
    ap: 'up'|'down'|'eq'|'unknown',
    mp: 'up'|'down'|'eq'|'unknown',
    hp: 'up'|'down'|'eq'|'unknown',
    resistDominant: 'match'|'miss'|'unknown'
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
        img: m.image,
        refLevel: m.level,
        family: m.family || '',
        areas: Array.isArray(m.areas) ? m.areas.map(String) : (m.areas ? [String(m.areas)] : []),
        subareas: Array.isArray(m.subareas) ? m.subareas.map(String) : (m.subareas ? [String(m.subareas)] : []),
        rank: m.isMiniBoss ? 'miniboss' : (m.isBoss ? 'boss' : 'normal'),
        element: m.element || undefined,
        ap: m.pa,
        mp: m.pm,
        hp: m.pdv,
        resistDominant: undefined, // à compléter si tu as la donnée
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
    loading.value = true
    error.value = null
    try {
      const res = await axios.post(`${API_URL}/erebor/dofusdle/api/classic/guess`, {
        puzzleId: puzzleId.value,
        guessId: monster.id, // id = dofusdb_id
      })
      if (res.data && res.data.hint) {
        addAttempt(monster, res.data.hint)
        if (res.data.correct) correct.value = true
      }
      return res.data
    } catch (e: any) {
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
    let areas = Array.isArray(monster.areas) ? monster.areas.map(String) : (monster.areas ? [String(monster.areas)] : [])
    let subareas = Array.isArray(monster.subareas) ? monster.subareas.map(String) : (monster.subareas ? [String(monster.subareas)] : [])
    // Si guess = daily, copie les zones du daily
    if (dailyMonster.value && monster.id === dailyMonster.value.dofusdb_id) {
      if (Array.isArray(dailyMonster.value.areas)) areas = dailyMonster.value.areas.map(String)
      if (Array.isArray(dailyMonster.value.subareas)) subareas = dailyMonster.value.subareas.map(String)
    }
    if ((!areas || !areas.length) || (!subareas || !subareas.length)) {
      const ref = allMonsters.value.find(m => m.dofusdb_id === monster.id || m.id === monster.id)
      if (ref) {
        if (!areas.length && ref.areas) areas = Array.isArray(ref.areas) ? ref.areas.map(String) : [String(ref.areas)]
        if (!subareas.length && ref.subareas) subareas = Array.isArray(ref.subareas) ? ref.subareas.map(String) : [String(ref.subareas)]
      }
    }
    const idx = attempts.value.findIndex(a => a.id === monster.id)
    const guessRow = {
      ...monster,
      areas,
      subareas,
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
