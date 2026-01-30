import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import path from "path";
import Icons from "unplugin-icons/vite";

export default defineConfig({
  plugins: [
    vue(),
    Icons({
      compiler: "vue3",
      autoInstall: true,
    }),
  ],

  build: {
    outDir: "dist",
    sourcemap: false,
    minify: "esbuild",
    chunkSizeWarningLimit: 1000,
  },

  server: {
    host: "0.0.0.0",
    port: 5173,
    watch: {
      usePolling: true,
    },
    hmr: {
      host: "localhost",
      port: 5173,
    },
  },
  
  preview: {
    host: "0.0.0.0",
    port: 5173,
  },
  
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "src"),
      "@pages": path.resolve(__dirname, "src/pages"),
      "@components": path.resolve(__dirname, "src/components"),
    },
  },
});
