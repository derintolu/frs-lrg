import { v4wp } from "@kucrut/vite-for-wp";
import react from "@vitejs/plugin-react";
import path from "path"

export default {
  base: './',
  plugins: [
    v4wp({
      input: "src/frontend/main.jsx",
      outDir: "assets/frontend/dist",
    }),
    react(),
  ],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
  server: {
    cors: true,
    origin: 'http://localhost:5173',
    host: 'localhost',
    port: 5173,
  },
};
