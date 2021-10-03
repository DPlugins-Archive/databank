const colors = require('tailwindcss/colors')

module.exports = {
  mode: 'jit',
  purge: [
    './templates/**/*.html.twig',
    './assets/**/*.{js,jsx,ts,tsx,vue}',
  ],
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {
      colors: {
        primary: colors.cyan,
      },
    },
  },
  variants: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
