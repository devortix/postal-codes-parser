import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite';
import dotenv from 'dotenv';
import path from 'path';

const loadEnvFiles = () => {
  let envVars = {};

  // Завантажуємо файл .env з кореневої папки
  const projectEnv = dotenv.config({ path: path.resolve(__dirname, '.env') });
  if (projectEnv.error) {
    console.warn('Warning: .env file not found in the root directory.');
  } else {
    envVars = { ...envVars, ...projectEnv.parsed };
  }

  // Завантажуємо файл .env з папки /config/
  const configEnvPath = '/config/.env'; // абсолютний шлях до файлу
  const configEnv = dotenv.config({ path: configEnvPath });
  if (configEnv.error) {
    console.warn('Warning: .env file not found in the config directory.');
  } else {
    envVars = { ...envVars, ...configEnv.parsed };
  }

  return envVars;
};

export default defineConfig({
  plugins: [vue()],
  define: {
    'process.env': loadEnvFiles(),
  },
  server: {
    host: true,
    port: 8080
  }
})