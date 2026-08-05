import axios, { type AxiosRequestConfig } from 'axios';
import { Preferences } from '@capacitor/preferences';

const CACHE_PREFIX = 'ets_cache_v3_';
const TIMEOUT_MS = 6000; // 6 seconds — if no response, use cache

/**
 * Reliable offline-capable GET using Capacitor Preferences (native storage).
 * 
 * Strategy:
 * 1. Show cached data immediately while fetching in background.
 * 2. Race: network vs 6-second timeout.
 * 3. On network fail or timeout → return cached data.
 * 4. On success → update cache and return fresh data.
 */
export async function cachedGet<T = any>(url: string, config?: AxiosRequestConfig): Promise<{ data: T }> {
  const cacheKey = CACHE_PREFIX + url;

  // Helper: read from native storage
  const readCache = async (): Promise<T | null> => {
    try {
      const { value } = await Preferences.get({ key: cacheKey });
      if (value) return JSON.parse(value) as T;
    } catch (e) {
      // fallback to localStorage
      try {
        const v = localStorage.getItem(cacheKey);
        if (v) return JSON.parse(v) as T;
      } catch {}
    }
    return null;
  };

  // Helper: write to native storage (fire and forget)
  const writeCache = (data: T) => {
    const str = JSON.stringify(data);
    Preferences.set({ key: cacheKey, value: str }).catch(() => {
      // fallback to localStorage
      try { localStorage.setItem(cacheKey, str); } catch {}
    });
  };

  // Race network vs timeout
  const networkPromise = axios.get<T>(url, config);
  const timeoutPromise = new Promise<never>((_, reject) =>
    setTimeout(() => reject(new Error('offline_timeout')), TIMEOUT_MS)
  );

  try {
    const response = await Promise.race([networkPromise, timeoutPromise]);
    writeCache(response.data);
    return response;
  } catch (error) {
    // Network failed or timed out → try cache
    const cached = await readCache();
    if (cached !== null) {
      return { data: cached };
    }
    throw error;
  }
}

/**
 * Clear all cached data for a given url (or all if url is omitted)
 */
export async function clearCache(url?: string) {
  if (url) {
    await Preferences.remove({ key: CACHE_PREFIX + url }).catch(() => {
      localStorage.removeItem(CACHE_PREFIX + url);
    });
  } else {
    // Clear all ets cache keys
    try {
      const { keys } = await Preferences.keys();
      for (const key of keys) {
        if (key.startsWith(CACHE_PREFIX)) {
          await Preferences.remove({ key });
        }
      }
    } catch {}
    // Also clear localStorage
    try {
      for (let i = localStorage.length - 1; i >= 0; i--) {
        const k = localStorage.key(i);
        if (k && k.startsWith(CACHE_PREFIX)) localStorage.removeItem(k);
      }
    } catch {}
  }
}

export function clearAllCache() { clearCache(); }
