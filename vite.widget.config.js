import { v4wp } from "@kucrut/vite-for-wp";
import react from "@vitejs/plugin-react";
import path from "path";

export default {
  plugins: [
    v4wp({
      input: "src/widget/widget.tsx",
      outDir: "assets/widget/dist",
    }),
    react(),
  ],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
};
