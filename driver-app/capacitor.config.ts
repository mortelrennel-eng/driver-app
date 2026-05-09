import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.eurotaxi.driver',
  appName: 'EuroTaxi Driver',
  webDir: 'dist',
  server: {
    cleartext: true,
    androidScheme: 'http'
  }
};

export default config;
