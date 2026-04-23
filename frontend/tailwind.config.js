/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: "#004824",
        "on-primary": "#ffffff",
        "primary-container": "#006233",
        secondary: "#b71f27",
        "on-secondary": "#ffffff",
        "secondary-container": "#fe5453",
        tertiary: "#735c00",
        surface: "#fff9ea",
        "surface-container": "#f5eed6",
        "on-surface": "#1e1c0d",
        "on-surface-variant": "#3f4940",
        outline: "#6f7a70",
        background: "#fff9ea",
        "error-container": "#ffdad6",
        "on-secondary-fixed-variant": "#930015",
        "surface-dim": "#e1dac3",
        "on-tertiary-container": "#4e3d00",
        "tertiary-container": "#cba72f",
        "on-tertiary-fixed": "#241a00",
        "on-background": "#1e1c0d",
        "surface-bright": "#fff9ea",
        "on-tertiary-fixed-variant": "#574500",
        "on-error": "#ffffff",
        "inverse-surface": "#343121",
        "tertiary-fixed-dim": "#e9c349",
        "on-tertiary": "#ffffff",
        "on-primary-fixed-variant": "#00522a",
        error: "#ba1a1a",
        "primary-fixed-dim": "#86d89d",
        "surface-container-low": "#fbf4db",
        "inverse-on-surface": "#f8f1d8",
        "secondary-fixed-dim": "#ffb3ae",
        "surface-container-lowest": "#ffffff",
        "tertiary-fixed": "#ffe088",
        "on-primary-fixed": "#00210d",
        "on-secondary-container": "#5c0009",
        "inverse-primary": "#86d89d",
        "on-secondary-fixed": "#410004",
        "on-error-container": "#93000a",
        "surface-tint": "#146c3c",
        "primary-fixed": "#a1f5b7",
        "surface-variant": "#e9e2cb",
        "surface-container-high": "#efe8d0",
        "secondary-fixed": "#ffdad7",
        "on-primary-container": "#89dba0",
        "surface-container-highest": "#e9e2cb",
        "outline-variant": "#bfc9be"
      },
      borderRadius: {
        DEFAULT: "1rem",
        lg: "2rem",
        xl: "3rem",
        full: "9999px"
      },
      fontFamily: {
        headline: ["Noto Serif", "serif"],
        serif: ["Noto Serif", "serif"],
        sans: ["Manrope", "sans-serif"],
        body: ["Work Sans", "sans-serif"],
        label: ["Work Sans", "sans-serif"],
        display: ["Noto Serif", "serif"]
      }
    }
  },
  plugins: [],
}

