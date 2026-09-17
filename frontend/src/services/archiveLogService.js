import axios from '@/config/axios';

const API_URL = import.meta.env.VITE_API_URL;

// Read-only: parses the pasted guild log and matches it against characters/mules, without
// writing anything. `excludeKeys`/`additionalKeys` ("character:<id>" / "mule:<id>") let the
// admin manually drop a detected/cascaded row or bring in one the log didn't mention.
export function analyzeGuildLog(log, excludeKeys = [], additionalKeys = []) {
  return axios
    .post(`${API_URL}/archive-log/analyze`, { log, excludeKeys, additionalKeys })
    .then((res) => res.data);
}

// Re-runs the same classification server-side (log + manual adjustments) and archives every
// matched, not-yet-archived entry.
export function executeGuildLogArchive(log, excludeKeys = [], additionalKeys = []) {
  return axios
    .post(`${API_URL}/archive-log/execute`, { log, excludeKeys, additionalKeys })
    .then((res) => res.data);
}

// Full roster (characters + nested mules) used to power the "add a member manually" search.
export function fetchRosterForManualAdd() {
  return axios.get(`${API_URL}/characters/`).then((res) => res.data);
}
