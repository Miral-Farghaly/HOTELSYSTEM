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
    react(),
  ],
  server: {
    hmr: {
      host: 'localhost',
    },
    host: 'localhost',
    port: 8888,
  },
  esbuild: {
    loader: {
      '.js': 'jsx', // if you still have .js files using JSX
    },
    include: /resources\/src\/.*\.js$/, // limit JSX loader to your src folder
  },
  resolve: {
    alias: {
      '@': '/resources/src',
    },
  },
});
