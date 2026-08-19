import axios from '@/config/axios';

const API_URL = import.meta.env.VITE_API_URL;

// Single source of truth for the Ghost Members API calls — used by both
// GhostMembers.vue (dedicated page) and PrintMembers.vue (Members page button),
// so voting behaves identically from either place.

export function fetchCurrentGhostRound() {
  return axios.get(`${API_URL}/ghost/current`).then((res) => res.data);
}

export function voteGhost(characterId) {
  return axios.post(`${API_URL}/ghost/characters/${characterId}/vote`).then((res) => res.data);
}

export function unvoteGhost(characterId) {
  return axios.delete(`${API_URL}/ghost/characters/${characterId}/vote`).then((res) => res.data);
}

export function updateGhostThreshold(threshold) {
  return axios.put(`${API_URL}/ghost/threshold`, { threshold }).then((res) => res.data);
}

export function closeGhostRound() {
  return axios.post(`${API_URL}/ghost/close`).then((res) => res.data);
}

export function fetchGhostHistory(characterId) {
  return axios.get(`${API_URL}/ghost/characters/${characterId}/history`).then((res) => res.data);
}
