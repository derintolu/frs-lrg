import { v4wp } from "@kucrut/vite-for-wp";
import react from "@vitejs/plugin-react";
import path from "path"

export default {
  plugins: [
    v4wp({
      input: {
        main: "src/frontend/main.jsx",
        "portal/portal-dashboards": "src/frontend/portal/main.tsx",
        "portal/portal-sidebar": "src/frontend/portal/portal-sidebar-main.tsx",
      },
      outDir: "assets/frontend/dist",
    }),
    // wp_scripts(),
    react(),
  ],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
};
