/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/Views/**/*.php",
    "./public/js/**/*.js",
    "./public/**/*.html"
  ],
  theme: {
    extend: {
      colors: {
        // Colores personalizados para sistema médico
        'medical-blue': '#2563eb',
        'medical-green': '#16a34a',
        'medical-red': '#dc2626',
        'medical-orange': '#ea580c',
        'medical-yellow': '#ca8a04'
      },
      fontFamily: {
        'nunito': ['Nunito', 'Arial', 'sans-serif'],
      }
    },
  },
  plugins: [],
}

