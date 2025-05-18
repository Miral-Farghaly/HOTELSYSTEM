// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/src/main.jsx'],
      refresh: true,
    }),
    react({
      jsxRuntime: 'automatic',
      fastRefresh: true,
    }),
  ],
  server: {
    hmr: {
      host: 'localhost',
    },
    host: 'localhost',
    port: 8888,
  },
  resolve: {
    alias: {
      '@': '/resources/src',
    },
  },
});
