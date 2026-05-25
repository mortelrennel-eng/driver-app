import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.eurotaxisystem.driver',
  appName: 'EuroTaxi Driver',
  webDir: 'dist',
  server: {
    androidScheme: 'https'
  }
};

export default config;
