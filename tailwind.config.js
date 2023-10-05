/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./themes/sekawan/**/*.htm",
    "./node_modules/flowbite/**/*.js"
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('flowbite/plugin')
  ],
}

