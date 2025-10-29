import type { Character } from '../stores/memberWheelStore';
import axios from 'axios';

const API_URL = (import.meta.env.VITE_API_URL || '').replace(/\/$/, '');

export async function fetchCharacters(): Promise<Character[]> {
  const res = await axios.get(`${API_URL}/characters`);
  return res.data.map((c: any) => ({
    ...c,
    selected: true,
    eliminated: false,
  }));
}
