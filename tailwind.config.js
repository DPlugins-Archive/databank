const colors = require('tailwindcss/colors')

module.exports = {
  content: [
    './templates/**/*.html.twig',
    './assets/**/*.{js,jsx,ts,tsx,vue}',
  ],
  theme: {
    extend: {
      colors: {
        primary: colors.cyan,
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
