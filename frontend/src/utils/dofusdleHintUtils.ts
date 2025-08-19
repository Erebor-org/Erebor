export function classFromHint(hint: string, type: string = ''): string {
  if (!hint) return 'bg-gray-100 text-gray-800'
  switch (type) {
    case 'level':
    case 'ap':
    case 'mp':
    case 'hp':
      if (hint === 'up') return 'bg-red-100 text-red-800'
      if (hint === 'down') return 'bg-blue-100 text-blue-800'
      if (hint === 'eq') return 'bg-green-100 text-green-800'
      break
    case 'zone':
      if (hint === 'exact') return 'bg-green-100 text-green-800'
      if (hint === 'overlap') return 'bg-yellow-100 text-yellow-800'
      if (hint === 'miss') return 'bg-red-100 text-red-800'
      break
    case 'family':
    case 'rank':
      if (hint === 'match') return 'bg-green-100 text-green-800'
      if (hint === 'miss') return 'bg-red-100 text-red-800'
      break
    case 'element':
    case 'resistDominant':
      if (hint === 'match') return 'bg-green-100 text-green-800'
      if (hint === 'miss') return 'bg-red-100 text-red-800'
      if (hint === 'unknown') return 'bg-gray-200 text-gray-500'
      break
    default:
      return 'bg-gray-100 text-gray-800'
  }
  return 'bg-gray-100 text-gray-800'
}

export function symbolFromHint(hint: string, type: string = ''): string {
  if (!hint) return ''
  switch (type) {
    case 'level':
    case 'ap':
    case 'mp':
    case 'hp':
      if (hint === 'up') return '▲'
      if (hint === 'down') return '▼'
      if (hint === 'eq') return '■'
      break
    case 'zone':
      if (hint === 'exact') return '●'
      if (hint === 'overlap') return '◐'
      if (hint === 'miss') return '○'
      break
    case 'family':
    case 'rank':
      if (hint === 'match') return '✓'
      if (hint === 'miss') return '✗'
      break
    case 'element':
    case 'resistDominant':
      if (hint === 'match') return '✓'
      if (hint === 'miss') return '✗'
      if (hint === 'unknown') return '?'
      break
    default:
      return ''
  }
  return ''
}
