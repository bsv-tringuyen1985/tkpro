import type { Config } from "tailwindcss";

const config: Config = {
  content: [
    "./app/**/*.{js,ts,jsx,tsx,mdx}",
    "./components/**/*.{js,ts,jsx,tsx,mdx}",
    "./features/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          DEFAULT: "#087a3b",
          dark: "#065d2d",
          darker: "#063d20",
          light: "#0aa04f",
          mint: "#eaf8ef",
        },
        cta: "#e32121",
        cream: "#f6faf7",
        ink: "#10231a",
        muted: "#66756d",
        line: "#d9e7de",
      },
      fontFamily: {
        serif: ["Georgia", "serif"],
        sans: [
          "Inter",
          "ui-sans-serif",
          "system-ui",
          "-apple-system",
          "BlinkMacSystemFont",
          "Segoe UI",
          "sans-serif",
        ],
      },
      boxShadow: {
        card: "0 16px 34px rgba(8, 122, 59, 0.09)",
        hero: "0 22px 50px rgba(8, 122, 59, 0.13)",
      },
    },
  },
  plugins: [],
};

export default config;
