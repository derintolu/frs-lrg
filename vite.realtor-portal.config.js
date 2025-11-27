import { v4wp } from "@kucrut/vite-for-wp";
import react from "@vitejs/plugin-react";
import path from "path"

export default {
  plugins: [
    v4wp({
      input: "src/frontend/realtor-portal-main.tsx",
      outDir: "assets/realtor-portal/dist",
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
    origin: 'http://localhost:5181',
    host: 'localhost',
    port: 5181,
  },
};
