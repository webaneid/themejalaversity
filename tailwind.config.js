/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './**/*.php',
    './js/**/*.js',
    '!./node_modules/**',
    '!./docs/**',
  ],
  theme: {
    extend: {
      colors: {
        // Brand — primary green (overridable via CSS vars from Settings Page)
        primary:          'var(--color-primary)',
        'primary-dark':   'var(--color-primary-dark)',
        'primary-med':    'var(--color-primary-medium)',
        'primary-light':  'var(--color-primary-light)',
        'primary-surface':'var(--color-primary-surface)',

        // Brand — accent gold (overridable via CSS vars from Settings Page)
        accent:           'var(--color-accent)',
        'accent-dark':    'var(--color-accent-dark)',
        'accent-light':   'var(--color-accent-light)',
        'accent-surface': 'var(--color-accent-surface)',

        // Neutral
        bg:               'var(--color-bg)',
        surface:          'var(--color-surface)',
        border:           'var(--color-border)',
        divider:          'var(--color-divider)',

        // Text on light backgrounds
        'text-primary':   'var(--color-text-primary)',
        'text-secondary': 'var(--color-text-secondary)',
        'text-muted':     'var(--color-text-muted)',
        'text-caption':   'var(--color-text-caption)',
        'text-nav':       'var(--color-text-nav)',

        // Semantic
        success:          'var(--color-success)',
        warning:          'var(--color-warning)',
        error:            'var(--color-error)',
        info:             'var(--color-info)',
      },
      fontFamily: {
        heading: ['Gontor', 'Georgia', 'serif'],
        body:    ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
      },
      transitionDuration: {
        fast:   '180ms',
        normal: '220ms',
        slow:   '300ms',
      },
      maxWidth: {
        container: '1200px',
        'container-sm': '1120px',
      },
    },
  },
  plugins: [],
};
