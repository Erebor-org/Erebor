import axios from '@/config/axios';

const API_URL = import.meta.env.VITE_API_URL;

// Guild Wars 2 private section (ROLE_DAMS). Everything goes through our backend:
// the GW2 API key never reaches the browser.

export function fetchGw2TokenInfo() {
  return axios.get(`${API_URL}/gw2/tokeninfo`).then((res) => res.data);
}

export function fetchGw2Characters() {
  return axios.get(`${API_URL}/gw2/characters`).then((res) => res.data);
}

// Bags of every character, shared slots and bank, with prices and sell/salvage advice.
// refresh: bypass the 5 minute server cache
export function fetchGw2Inventory({ refresh = false } = {}) {
  return axios.get(`${API_URL}/gw2/inventory`, { params: refresh ? { refresh: 1 } : {} }).then((res) => res.data);
}

export function fetchGw2Character(name) {
  return axios.get(`${API_URL}/gw2/characters/${encodeURIComponent(name)}`).then((res) => res.data);
}

// Legendaries: catalogue, followed ones, detail and combined view. refresh bypasses the server cache
const refreshParams = (refresh) => ({ params: refresh ? { refresh: 1 } : {} });

export function fetchGw2LegendaryCatalog({ refresh = false } = {}) {
  return axios.get(`${API_URL}/gw2/legendaries/catalog`, refreshParams(refresh)).then((res) => res.data);
}

export function fetchGw2TrackedLegendaries({ refresh = false } = {}) {
  return axios.get(`${API_URL}/gw2/legendaries/tracked`, refreshParams(refresh)).then((res) => res.data);
}

export function fetchGw2LegendaryCombined({ refresh = false } = {}) {
  return axios.get(`${API_URL}/gw2/legendaries/combined`, refreshParams(refresh)).then((res) => res.data);
}

export function fetchGw2Legendary(id, { refresh = false } = {}) {
  return axios.get(`${API_URL}/gw2/legendaries/${id}`, refreshParams(refresh)).then((res) => res.data);
}

export function trackGw2Legendary(id) {
  return axios.post(`${API_URL}/gw2/legendaries/tracked`, { itemId: id }).then((res) => res.data);
}

export function untrackGw2Legendary(id) {
  return axios.delete(`${API_URL}/gw2/legendaries/tracked/${id}`).then((res) => res.data);
}

const ERROR_MESSAGES = {
  not_configured: "La clé API Guild Wars 2 n'est pas configurée sur le serveur (GW2_API_KEY).",
  invalid_key: 'La clé API Guild Wars 2 est invalide ou a été révoquée.',
  missing_permission: 'Il manque une permission à la clé API Guild Wars 2.',
  not_found: 'Introuvable côté Guild Wars 2.',
  rate_limited: "Trop de requêtes vers l'API Guild Wars 2, réessaie dans une minute.",
  unavailable: "L'API Guild Wars 2 ne répond pas pour le moment.",
};

export function gw2ErrorMessage(error, fallback) {
  if (error.response?.status === 403) return "Tu n'as pas accès à cette section.";
  return ERROR_MESSAGES[error.response?.data?.code] || fallback;
}
