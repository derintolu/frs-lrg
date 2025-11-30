// vite.widget.config.js
import { defineConfig } from "file:///Users/derintolu/Local%20Sites/hub21/app/public/wp-content/plugins/frs-lrg/node_modules/vite/dist/node/index.js";
import react from "file:///Users/derintolu/Local%20Sites/hub21/app/public/wp-content/plugins/frs-lrg/node_modules/@vitejs/plugin-react/dist/index.js";
import path from "path";
import tailwindcss from "file:///Users/derintolu/Local%20Sites/hub21/app/public/wp-content/plugins/frs-lrg/node_modules/tailwindcss/lib/index.js";
import autoprefixer from "file:///Users/derintolu/Local%20Sites/hub21/app/public/wp-content/plugins/frs-lrg/node_modules/autoprefixer/lib/autoprefixer.js";
var __vite_injected_original_dirname = "/Users/derintolu/Local Sites/hub21/app/public/wp-content/plugins/frs-lrg";
var vite_widget_config_default = defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      "@": path.resolve(__vite_injected_original_dirname, "./src")
    }
  },
  build: {
    outDir: "assets/widget/dist",
    emptyOutDir: true,
    lib: {
      entry: path.resolve(__vite_injected_original_dirname, "src/widget/widget.tsx"),
      name: "FRSMortgageCalculator",
      formats: ["iife"],
      // Immediately Invoked Function Expression for embedding
      fileName: () => "frs-mortgage-calculator.js"
    },
    rollupOptions: {
      output: {
        // Ensure CSS is extracted
        assetFileNames: (assetInfo) => {
          if (assetInfo.name === "style.css") {
            return "frs-mortgage-calculator.css";
          }
          return assetInfo.name;
        },
        // Inline all dependencies for standalone widget
        inlineDynamicImports: true
      }
    },
    // Optimize for production
    minify: "terser",
    terserOptions: {
      compress: {
        drop_console: false
        // Keep console for debugging webhook issues
      }
    },
    // Source maps for debugging
    sourcemap: true
  },
  // CSS configuration
  css: {
    postcss: {
      plugins: [
        tailwindcss,
        autoprefixer
      ]
    }
  }
});
export {
  vite_widget_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS53aWRnZXQuY29uZmlnLmpzIl0sCiAgInNvdXJjZXNDb250ZW50IjogWyJjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfZGlybmFtZSA9IFwiL1VzZXJzL2RlcmludG9sdS9Mb2NhbCBTaXRlcy9odWIyMS9hcHAvcHVibGljL3dwLWNvbnRlbnQvcGx1Z2lucy9mcnMtbHJnXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ZpbGVuYW1lID0gXCIvVXNlcnMvZGVyaW50b2x1L0xvY2FsIFNpdGVzL2h1YjIxL2FwcC9wdWJsaWMvd3AtY29udGVudC9wbHVnaW5zL2Zycy1scmcvdml0ZS53aWRnZXQuY29uZmlnLmpzXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ltcG9ydF9tZXRhX3VybCA9IFwiZmlsZTovLy9Vc2Vycy9kZXJpbnRvbHUvTG9jYWwlMjBTaXRlcy9odWIyMS9hcHAvcHVibGljL3dwLWNvbnRlbnQvcGx1Z2lucy9mcnMtbHJnL3ZpdGUud2lkZ2V0LmNvbmZpZy5qc1wiO2ltcG9ydCB7IGRlZmluZUNvbmZpZyB9IGZyb20gJ3ZpdGUnO1xuaW1wb3J0IHJlYWN0IGZyb20gJ0B2aXRlanMvcGx1Z2luLXJlYWN0JztcbmltcG9ydCBwYXRoIGZyb20gJ3BhdGgnO1xuaW1wb3J0IHRhaWx3aW5kY3NzIGZyb20gJ3RhaWx3aW5kY3NzJztcbmltcG9ydCBhdXRvcHJlZml4ZXIgZnJvbSAnYXV0b3ByZWZpeGVyJztcblxuLy8gV2lkZ2V0IGJ1aWxkIGNvbmZpZ3VyYXRpb24gZm9yIHN0YW5kYWxvbmUgZW1iZWRkYWJsZSB3aWRnZXRcbmV4cG9ydCBkZWZhdWx0IGRlZmluZUNvbmZpZyh7XG4gIHBsdWdpbnM6IFtyZWFjdCgpXSxcblxuICByZXNvbHZlOiB7XG4gICAgYWxpYXM6IHtcbiAgICAgICdAJzogcGF0aC5yZXNvbHZlKF9fZGlybmFtZSwgJy4vc3JjJyksXG4gICAgfSxcbiAgfSxcblxuICBidWlsZDoge1xuICAgIG91dERpcjogJ2Fzc2V0cy93aWRnZXQvZGlzdCcsXG4gICAgZW1wdHlPdXREaXI6IHRydWUsXG5cbiAgICBsaWI6IHtcbiAgICAgIGVudHJ5OiBwYXRoLnJlc29sdmUoX19kaXJuYW1lLCAnc3JjL3dpZGdldC93aWRnZXQudHN4JyksXG4gICAgICBuYW1lOiAnRlJTTW9ydGdhZ2VDYWxjdWxhdG9yJyxcbiAgICAgIGZvcm1hdHM6IFsnaWlmZSddLCAvLyBJbW1lZGlhdGVseSBJbnZva2VkIEZ1bmN0aW9uIEV4cHJlc3Npb24gZm9yIGVtYmVkZGluZ1xuICAgICAgZmlsZU5hbWU6ICgpID0+ICdmcnMtbW9ydGdhZ2UtY2FsY3VsYXRvci5qcydcbiAgICB9LFxuXG4gICAgcm9sbHVwT3B0aW9uczoge1xuICAgICAgb3V0cHV0OiB7XG4gICAgICAgIC8vIEVuc3VyZSBDU1MgaXMgZXh0cmFjdGVkXG4gICAgICAgIGFzc2V0RmlsZU5hbWVzOiAoYXNzZXRJbmZvKSA9PiB7XG4gICAgICAgICAgaWYgKGFzc2V0SW5mby5uYW1lID09PSAnc3R5bGUuY3NzJykge1xuICAgICAgICAgICAgcmV0dXJuICdmcnMtbW9ydGdhZ2UtY2FsY3VsYXRvci5jc3MnO1xuICAgICAgICAgIH1cbiAgICAgICAgICByZXR1cm4gYXNzZXRJbmZvLm5hbWU7XG4gICAgICAgIH0sXG4gICAgICAgIC8vIElubGluZSBhbGwgZGVwZW5kZW5jaWVzIGZvciBzdGFuZGFsb25lIHdpZGdldFxuICAgICAgICBpbmxpbmVEeW5hbWljSW1wb3J0czogdHJ1ZSxcbiAgICAgIH1cbiAgICB9LFxuXG4gICAgLy8gT3B0aW1pemUgZm9yIHByb2R1Y3Rpb25cbiAgICBtaW5pZnk6ICd0ZXJzZXInLFxuICAgIHRlcnNlck9wdGlvbnM6IHtcbiAgICAgIGNvbXByZXNzOiB7XG4gICAgICAgIGRyb3BfY29uc29sZTogZmFsc2UsIC8vIEtlZXAgY29uc29sZSBmb3IgZGVidWdnaW5nIHdlYmhvb2sgaXNzdWVzXG4gICAgICB9XG4gICAgfSxcblxuICAgIC8vIFNvdXJjZSBtYXBzIGZvciBkZWJ1Z2dpbmdcbiAgICBzb3VyY2VtYXA6IHRydWUsXG4gIH0sXG5cbiAgLy8gQ1NTIGNvbmZpZ3VyYXRpb25cbiAgY3NzOiB7XG4gICAgcG9zdGNzczoge1xuICAgICAgcGx1Z2luczogW1xuICAgICAgICB0YWlsd2luZGNzcyxcbiAgICAgICAgYXV0b3ByZWZpeGVyLFxuICAgICAgXSxcbiAgICB9LFxuICB9LFxufSk7XG4iXSwKICAibWFwcGluZ3MiOiAiO0FBQTBaLFNBQVMsb0JBQW9CO0FBQ3ZiLE9BQU8sV0FBVztBQUNsQixPQUFPLFVBQVU7QUFDakIsT0FBTyxpQkFBaUI7QUFDeEIsT0FBTyxrQkFBa0I7QUFKekIsSUFBTSxtQ0FBbUM7QUFPekMsSUFBTyw2QkFBUSxhQUFhO0FBQUEsRUFDMUIsU0FBUyxDQUFDLE1BQU0sQ0FBQztBQUFBLEVBRWpCLFNBQVM7QUFBQSxJQUNQLE9BQU87QUFBQSxNQUNMLEtBQUssS0FBSyxRQUFRLGtDQUFXLE9BQU87QUFBQSxJQUN0QztBQUFBLEVBQ0Y7QUFBQSxFQUVBLE9BQU87QUFBQSxJQUNMLFFBQVE7QUFBQSxJQUNSLGFBQWE7QUFBQSxJQUViLEtBQUs7QUFBQSxNQUNILE9BQU8sS0FBSyxRQUFRLGtDQUFXLHVCQUF1QjtBQUFBLE1BQ3RELE1BQU07QUFBQSxNQUNOLFNBQVMsQ0FBQyxNQUFNO0FBQUE7QUFBQSxNQUNoQixVQUFVLE1BQU07QUFBQSxJQUNsQjtBQUFBLElBRUEsZUFBZTtBQUFBLE1BQ2IsUUFBUTtBQUFBO0FBQUEsUUFFTixnQkFBZ0IsQ0FBQyxjQUFjO0FBQzdCLGNBQUksVUFBVSxTQUFTLGFBQWE7QUFDbEMsbUJBQU87QUFBQSxVQUNUO0FBQ0EsaUJBQU8sVUFBVTtBQUFBLFFBQ25CO0FBQUE7QUFBQSxRQUVBLHNCQUFzQjtBQUFBLE1BQ3hCO0FBQUEsSUFDRjtBQUFBO0FBQUEsSUFHQSxRQUFRO0FBQUEsSUFDUixlQUFlO0FBQUEsTUFDYixVQUFVO0FBQUEsUUFDUixjQUFjO0FBQUE7QUFBQSxNQUNoQjtBQUFBLElBQ0Y7QUFBQTtBQUFBLElBR0EsV0FBVztBQUFBLEVBQ2I7QUFBQTtBQUFBLEVBR0EsS0FBSztBQUFBLElBQ0gsU0FBUztBQUFBLE1BQ1AsU0FBUztBQUFBLFFBQ1A7QUFBQSxRQUNBO0FBQUEsTUFDRjtBQUFBLElBQ0Y7QUFBQSxFQUNGO0FBQ0YsQ0FBQzsiLAogICJuYW1lcyI6IFtdCn0K
