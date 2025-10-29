import type { Character } from '../stores/memberWheelStore';
import axios from 'axios';
import { API_URL } from '../config/apiUrl';

export async function fetchCharacters(): Promise<Character[]> {
  const res = await axios.get(`${API_URL}/characters`);
  return res.data.map((c: any) => ({
    ...c,
    selected: true,
    eliminated: false,
  }));
}
