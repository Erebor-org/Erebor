import axios from '@/config/axios';

const API_URL = import.meta.env.VITE_API_URL;

// Page d'accueil : tous les blocs en un appel. Les blocs de gestion (clé `admin`)
// ne sont renvoyés par le serveur qu'aux ROLE_ADMIN et plus.
export function fetchHomeDashboard() {
  return axios.get(`${API_URL}/dashboard/home`).then((res) => res.data);
}

// Pages suivantes d'un fil : kind = 'movements' (entrées / sorties) ou 'promotions'.
export function fetchActivity(kind, offset, limit = 20) {
  return axios
    .get(`${API_URL}/dashboard/activity`, { params: { kind, offset, limit } })
    .then((res) => res.data);
}
