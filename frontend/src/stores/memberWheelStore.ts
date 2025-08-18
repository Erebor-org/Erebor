import { defineStore } from 'pinia';

export interface Character {
  id: number;
  pseudo: string;
  class: string;
  selected: boolean;
  eliminated: boolean;
}

interface State {
  characters: Character[];
  eliminated: Character[];
}

export const useMemberWheelStore = defineStore('memberWheel', {
  state: (): State => ({
    characters: [],
    eliminated: [],
  }),
  actions: {
    setCharacters(chars: Character[]) {
      this.characters = chars;
    },
    selectAll() {
      this.characters.forEach(c => { if (!c.eliminated) c.selected = true; });
    },
    deselectAll() {
      this.characters.forEach(c => { if (!c.eliminated) c.selected = false; });
    },
    eliminate(id: number) {
      const char = this.characters.find(c => c.id === id);
      if (char && !char.eliminated) {
        char.eliminated = true;
        this.eliminated.unshift(char);
      }
    },
    undo() {
      const last = this.eliminated.shift();
      if (last) {
        last.eliminated = false;
      }
    },
    reset() {
      this.characters.forEach(c => {
        c.eliminated = false;
        c.selected = false;
      });
      this.eliminated = [];
    },
  },
});
