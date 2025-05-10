import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// Event API calls
export const fetchEvents = async () => {
  try {
    const response = await axios.get(`${API_URL}/events`);
    return response.data;
  } catch (error) {
    console.error('Error fetching events:', error);
    throw error;
  }
};

export const fetchUpcomingEvents = async () => {
  try {
    const response = await axios.get(`${API_URL}/events/upcoming`);
    return response.data;
  } catch (error) {
    console.error('Error fetching upcoming events:', error);
    throw error;
  }
};

export const fetchPastEvents = async () => {
  try {
    const response = await axios.get(`${API_URL}/events/past`);
    return response.data;
  } catch (error) {
    console.error('Error fetching past events:', error);
    throw error;
  }
};

export const fetchCompletedEvents = async () => {
  try {
    const response = await axios.get(`${API_URL}/events/completed`);
    return response.data;
  } catch (error) {
    console.error('Error fetching completed events:', error);
    throw error;
  }
};

export const fetchEventById = async (id) => {
  try {
    const response = await axios.get(`${API_URL}/events/${id}`);
    return response.data;
  } catch (error) {
    console.error(`Error fetching event with id ${id}:`, error);
    throw error;
  }
};

export const createEvent = async (eventData) => {
  try {
    const formData = new FormData();
    formData.append('data', JSON.stringify({
      title: eventData.title,
      description: eventData.description,
      eventDate: eventData.eventDate,
      organizerId: eventData.organizerId
    }));
    
    if (eventData.image) {
      formData.append('image', eventData.image);
    }
    
    const response = await axios.post(`${API_URL}/events`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    return response.data;
  } catch (error) {
    console.error('Error creating event:', error);
    throw error;
  }
};

export const updateEvent = async (id, eventData) => {
  try {
    const formData = new FormData();
    formData.append('data', JSON.stringify({
      title: eventData.title,
      description: eventData.description,
      eventDate: eventData.eventDate,
      organizerId: eventData.organizerId
    }));
    
    if (eventData.image) {
      formData.append('image', eventData.image);
    }
    
    const response = await axios.put(`${API_URL}/events/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    return response.data;
  } catch (error) {
    console.error(`Error updating event with id ${id}:`, error);
    throw error;
  }
};

export const deleteEvent = async (id) => {
  try {
    await axios.delete(`${API_URL}/events/${id}`);
    return true;
  } catch (error) {
    console.error(`Error deleting event with id ${id}:`, error);
    throw error;
  }
};

export const completeEvent = async (id) => {
  try {
    const response = await axios.patch(`${API_URL}/events/${id}/complete`);
    return response.data;
  } catch (error) {
    console.error(`Error completing event with id ${id}:`, error);
    throw error;
  }
};

// Event Participation API calls
export const fetchEventParticipations = async (eventId) => {
  try {
    const response = await axios.get(`${API_URL}/event-participations/event/${eventId}`);
    return response.data;
  } catch (error) {
    console.error(`Error fetching participations for event ${eventId}:`, error);
    throw error;
  }
};

export const fetchLadderStandings = async () => {
  try {
    const response = await axios.get(`${API_URL}/event-participations/ladder`);
    return response.data;
  } catch (error) {
    console.error('Error fetching ladder standings:', error);
    throw error;
  }
};

export const addParticipation = async (eventId, characterId, position) => {
  try {
    const response = await axios.post(`${API_URL}/event-participations`, {
      eventId,
      characterId,
      position
    });
    
    return response.data;
  } catch (error) {
    console.error('Error adding participation:', error);
    throw error;
  }
};

export const addBatchParticipations = async (eventId, participations) => {
  try {
    const response = await axios.post(`${API_URL}/event-participations/batch`, {
      eventId,
      participations
    });
    
    return response.data;
  } catch (error) {
    console.error('Error adding batch participations:', error);
    throw error;
  }
};

export const deleteParticipation = async (id) => {
  try {
    await axios.delete(`${API_URL}/event-participations/${id}`);
    return true;
  } catch (error) {
    console.error(`Error deleting participation with id ${id}:`, error);
    throw error;
  }
};

export const fetchCharacterParticipations = async (characterId) => {
  try {
    const response = await axios.get(`${API_URL}/event-participations/character/${characterId}`);
    return response.data;
  } catch (error) {
    console.error(`Error fetching participations for character ${characterId}:`, error);
    throw error;
  }
};
