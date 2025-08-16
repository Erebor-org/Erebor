/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        'theme': {
          'bg': 'var(--bg)',
          'bg-muted': 'var(--bg-muted)',
          'card': 'var(--card)',
          'border': 'var(--border)',
          'text': 'var(--text)',
          'text-muted': 'var(--text-muted)',
          'primary': 'var(--primary)',
          'primary-hover': 'var(--primary-hover)',
          'link': 'var(--link)',
          'ring': 'var(--ring)',
          'success': 'var(--success)',
          'warning': 'var(--warning)',
          'error': 'var(--error)',
        }
      },
      backgroundColor: {
        'theme': {
          'bg': 'var(--bg)',
          'bg-muted': 'var(--bg-muted)',
          'card': 'var(--card)',
          'primary': 'var(--primary)',
          'primary-hover': 'var(--primary-hover)',
          'success': 'var(--success)',
          'warning': 'var(--warning)',
          'error': 'var(--error)',
        }
      },
      textColor: {
        'theme': {
          'text': 'var(--text)',
          'text-muted': 'var(--text-muted)',
          'primary': 'var(--primary)',
          'primary-hover': 'var(--primary-hover)',
          'link': 'var(--link)',
          'success': 'var(--success)',
          'warning': 'var(--warning)',
          'error': 'var(--error)',
        }
      },
      borderColor: {
        'theme': {
          'border': 'var(--border)',
          'primary': 'var(--primary)',
          'ring': 'var(--ring)',
        }
      },
      ringColor: {
        'theme': {
          'ring': 'var(--ring)',
        }
      }
    },
  },
  screens: {
    sm: '640px',
    md: '768px',
    lg: '1024px',
    xl: '1280px',
    xxl: '1980px',
  },
  plugins: [],
  purge: false,
  mode: 'jit'
}

