import axios from 'axios';

const API_URL = 'http://127.0.0.1:8000'; // Change based on your Symfony backend

export default {
  async login(username, password) {
    try {
      const response = await axios.post(
        `${API_URL}/login`,
        { username, password },
        { withCredentials: true }
      );
      return response.data;
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Login failed' };
    }
  },

  async checkSession() {
    try {
      const response = await axios.get(`${API_URL}/session/check`, { withCredentials: true });
      return response.data;
    } catch (error) {
      console.log(error);
      return { isLoggedIn: false };
    }
  },

  async logout() {
    try {
      const response = await axios.post(`${API_URL}/logout`, {}, { withCredentials: true });
      return response.data;
    } catch (error) {
      console.log(error);
      return { success: false };
    }
  },
};
