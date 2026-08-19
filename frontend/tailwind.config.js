/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      animation: {
        shimmer: 'shimmer 3s ease-in-out infinite',
      },
      keyframes: {
        shimmer: {
          '0%': { transform: 'translateX(-100%)' },
          '100%': { transform: 'translateX(100%)' },
        },
      },
      colors: {
        'theme': {
          'bg': 'var(--bg)',
          'bg-muted': 'var(--bg-muted)',
          'card': 'var(--card)',
          'card-hover': 'var(--card-hover)',
          'border': 'var(--border)',
          'border-hover': 'var(--border-hover)',
          'text': 'var(--text)',
          'text-muted': 'var(--text-muted)',
          'primary': 'var(--primary)',
          'primary-hover': 'var(--primary-hover)',
          'accent': 'var(--accent)',
          'accent-hover': 'var(--accent-hover)',
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
          'card-hover': 'var(--card-hover)',
          'primary': 'var(--primary)',
          'primary-hover': 'var(--primary-hover)',
          'accent': 'var(--accent)',
          'accent-hover': 'var(--accent-hover)',
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
          'accent': 'var(--accent)',
          'accent-hover': 'var(--accent-hover)',
          'link': 'var(--link)',
          'success': 'var(--success)',
          'warning': 'var(--warning)',
          'error': 'var(--error)',
        }
      },
      borderColor: {
        'theme': {
          'border': 'var(--border)',
          'border-hover': 'var(--border-hover)',
          'primary': 'var(--primary)',
          'accent': 'var(--accent)',
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

