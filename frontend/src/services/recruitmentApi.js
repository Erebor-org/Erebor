import axios from '@/config/axios';

const API_URL = import.meta.env.VITE_API_URL;

// Dashboard recrutement : tout en un appel. Le serveur applique le périmètre de l'utilisateur
// (toute la guilde, ou ses seules recrues pour un Enroleur selon le réglage).
export function fetchRecruitmentDashboard({ week, months } = {}) {
  return axios
    .get(`${API_URL}/recruitment/dashboard`, { params: { week: week || undefined, months } })
    .then((res) => res.data);
}

// Réservé aux Meneurs : ce que voient les Enroleurs ('none' | 'own' | 'all').
export function updateAdminScope(scope) {
  return axios
    .put(`${API_URL}/admin/settings`, { 'recruitment.admin_scope': scope })
    .then((res) => res.data);
}
