import axios from '@/config/axios';

const API_URL = import.meta.env.VITE_API_URL;

export default {
  async getAllEvents() {
    try {
      const response = await axios.get(`${API_URL}/events`);
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Failed to fetch events' };
    }
  },

  async getUpcomingEvents() {
    try {
      const response = await axios.get(`${API_URL}/events/upcoming`);
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Failed to fetch upcoming events' };
    }
  },

  async getPastEvents() {
    try {
      const response = await axios.get(`${API_URL}/events/past`);
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Failed to fetch past events' };
    }
  },

  async getEvent(id) {
    try {
      const response = await axios.get(`${API_URL}/events/${id}`);
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Failed to fetch event' };
    }
  },

  async createEvent(eventData) {
    try {
      const response = await axios.post(`${API_URL}/events`, eventData);
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Failed to create event' };
    }
  },

  async uploadImage(file) {
    try {
      const formData = new FormData();
      formData.append('image', file);
      const response = await axios.post(`${API_URL}/api/upload/event-image`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Failed to upload image' };
    }
  },

  async updateEvent(id, eventData) {
    try {
      const response = await axios.put(`${API_URL}/events/${id}`, eventData);
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Failed to update event' };
    }
  },

  async deleteEvent(id) {
    try {
      const response = await axios.delete(`${API_URL}/events/${id}`);
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Failed to delete event' };
    }
  },

  async subscribeToEvent(id) {
    try {
      const response = await axios.post(`${API_URL}/events/${id}/subscribe`);
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Failed to subscribe to event' };
    }
  },

  async unsubscribeFromEvent(id) {
    try {
      const response = await axios.post(`${API_URL}/events/${id}/unsubscribe`);
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Failed to unsubscribe from event' };
    }
  },

  async finalizeEvent(id, finalizeData) {
    try {
      const response = await axios.post(`${API_URL}/events/${id}/finalize`, finalizeData);
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Failed to finalize event' };
    }
  },

  async getLeaderboard() {
    try {
      const response = await axios.get(`${API_URL}/events/leaderboard`);
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Failed to fetch leaderboard' };
    }
  },
};

