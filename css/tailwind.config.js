/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
      "./src/**/*.{html,js,ts,jsx,tsxi,php}",  // İçeriği tarayacağı dizinler
      "./public/index.html"
    ],
    theme: {
      extend: {
        colors: {
          primary: "#1D4ED8",       // Örnek özel mavi tonu
          secondary: "#9333EA",     // Örnek özel mor tonu
          accent: "#F59E0B",        // Örnek özel turuncu tonu
        },
        fontFamily: {
          sans: ['Inter', 'sans-serif'],   // Varsayılan font ailesi
          serif: ['Merriweather', 'serif'],
        },
        spacing: {
          '128': '32rem',          // Ekstra spacing değeri
          '144': '36rem',
        },
        borderRadius: {
          '4xl': '2rem',           // Ekstra radius
        },
        animation: {
          fade: "fadeOut 1s ease-in-out",
        },
        keyframes: {
          fadeOut: {
            "0%": { opacity: 1 },
            "100%": { opacity: 0 },
          },
        },
      },
    },
    plugins: [
      require('@tailwindcss/forms'),       // Form stilleri için eklenti
      require('@tailwindcss/typography'),  // Prose yazılar için
      require('@tailwindcss/aspect-ratio') // Görsel/video oranları için
    ],
  }
  