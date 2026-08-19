// Vérifie un pseudo/Ankama ID contre la blacklist, y compris les personnages
// associés (mules/alts) rattachés à chaque entrée.

export function matchesBlacklistedPseudo(pseudo, blacklist) {
  if (!pseudo) return false;
  const normalized = pseudo.toLowerCase();
  return blacklist.some(entry =>
    entry.pseudo?.toLowerCase() === normalized ||
    entry.associatedCharacters?.some(ac => ac.pseudo?.toLowerCase() === normalized)
  );
}

export function matchesBlacklistedAnkamaPseudo(ankamaPseudo, blacklist) {
  if (!ankamaPseudo) return false;
  const normalized = ankamaPseudo.toLowerCase();
  return blacklist.some(entry =>
    entry.ankamaPseudo?.toLowerCase() === normalized ||
    entry.associatedCharacters?.some(ac => ac.ankamaPseudo?.toLowerCase() === normalized)
  );
}
