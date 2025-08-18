const images = import.meta.glob('../assets/icon_classe/*.avif', { eager: true, import: 'default' });

export const classIcons: Record<string, string> = {
  iop: images['../assets/icon_classe/iop.avif'],
  enutrof: images['../assets/icon_classe/enutrof.avif'],
  cra: images['../assets/icon_classe/cra.avif'],
  sram: images['../assets/icon_classe/sram.avif'],
  osamodas: images['../assets/icon_classe/osamodas.avif'],
  sacrieur: images['../assets/icon_classe/sacrieur.avif'],
  pandawa: images['../assets/icon_classe/pandawa.avif'],
  ecaflip: images['../assets/icon_classe/ecaflip.avif'],
  eniripsa: images['../assets/icon_classe/eniripsa.avif'],
  feca: images['../assets/icon_classe/feca.avif'],
  xelor: images['../assets/icon_classe/xelor.avif'],
  sadida: images['../assets/icon_classe/sadida.avif'],
  roublard: images['../assets/icon_classe/roublard.avif'],
  steamer: images['../assets/icon_classe/steamer.avif'],
  ouginak: images['../assets/icon_classe/ouginak.avif'],
  huppermage: images['../assets/icon_classe/huppermage.avif'],
  eliotrope: images['../assets/icon_classe/eliotrope.avif'],
  forgelance: images['../assets/icon_classe/forgelance.avif'],
  zobal: images['../assets/icon_classe/zobal.avif'],
};
export const getClassIcon = (cls: string) => classIcons[cls] || images['../assets/icon_classe/iop.avif'];
