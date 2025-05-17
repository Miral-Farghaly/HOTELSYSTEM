// vite.config.js
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  server: {
    host: true,
    port: 8888,
  },
  plugins: [
    laravel({
      input: [
        'resources/src/main.jsx',
        'resources/src/styles.css',
        'resources/src/App.jsx',
        'resources/src/index.css',
        'resources/src/App.css',
      ],
      refresh: true,
    }),
    react(),
  ],
  esbuild: {
    loader: {
      '.js': 'jsx', // if you still have .js files using JSX
    },
    include: /resources\/src\/.*\.js$/, // limit JSX loader to your src folder
  },
});
