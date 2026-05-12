import { createContext, useContext, useState, useEffect } from 'react';
import type { ReactNode } from 'react';

type ThemeMode = 'dark' | 'light';

interface ThemeColors {
  // Core backgrounds
  bg: string;
  bgAlt: string;
  card: string;
  cardSolid: string;
  glass: React.CSSProperties;
  border: string;
  borderSubtle: string;

  // Text
  textPrimary: string;
  textSecondary: string;
  textMuted: string;
  textInverse: string;

  // Brand
  gold: string;
  goldGrad: string;
  goldBg: string;

  // Inputs & Forms
  inputBg: string;
  inputBorder: string;
  inputText: string;
  inputPlaceholder: string;

  // Components
  headerBg: string;
  headerText: string;
  menuBg: string;
  menuBorder: string;
  sectionCardBg: string;
  modalBg: string;
  footerBg: string;
  footerBorder: string;

  // Status badges
  subtleBg: string;
  
  // Scrollbar
  scrollThumb: string;

  // Shadows
  shadow: string;
  cardShadow: string;

  // SVG Chart
  chartLabelColor: string;
  chartValueColor: string;
  chartGridColor: string;

  // Button/back button
  backBtnBg: string;
  backBtnColor: string;

  // Tab switcher
  tabBg: string;
  tabInactiveText: string;

  // Description bg
  descBg: string;
}

interface ThemeContextType {
  mode: ThemeMode;
  toggleTheme: () => void;
  setTheme: (mode: ThemeMode) => void;
  t: ThemeColors;
  isDark: boolean;
}

const darkTheme: ThemeColors = {
  bg: '#0a0e1a',
  bgAlt: '#0f172a',
  card: 'linear-gradient(145deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.85))',
  cardSolid: 'rgba(30, 41, 59, 0.7)',
  glass: { backdropFilter: 'blur(16px)', WebkitBackdropFilter: 'blur(16px)' },
  border: '1px solid rgba(255,255,255,0.06)',
  borderSubtle: '1px solid rgba(255,255,255,0.05)',
  
  textPrimary: '#f8fafc',
  textSecondary: '#94a3b8',
  textMuted: '#64748b',
  textInverse: '#020617',
  
  gold: '#eab308',
  goldGrad: 'linear-gradient(135deg, #eab308, #f59e0b)',
  goldBg: 'rgba(234,179,8,0.1)',
  
  inputBg: 'rgba(15, 23, 42, 0.6)',
  inputBorder: '1px solid rgba(51, 65, 85, 0.8)',
  inputText: '#f8fafc',
  inputPlaceholder: '#475569',
  
  headerBg: '#0f172a',
  headerText: '#ffffff',
  menuBg: 'rgba(255,255,255,0.02)',
  menuBorder: 'rgba(255,255,255,0.06)',
  sectionCardBg: 'linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9))',
  modalBg: '#0a0e1a',
  footerBg: 'rgba(15, 23, 42, 0.95)',
  footerBorder: '1px solid rgba(255,255,255,0.05)',
  
  subtleBg: 'rgba(255,255,255,0.03)',
  scrollThumb: '#334155',
  
  shadow: '0 8px 32px rgba(0,0,0,0.4)',
  cardShadow: '0 8px 32px rgba(0, 0, 0, 0.3)',
  
  chartLabelColor: '#64748b',
  chartValueColor: '#fff',
  chartGridColor: 'rgba(255,255,255,0.05)',
  
  backBtnBg: 'rgba(255,255,255,0.06)',
  backBtnColor: '#94a3b8',
  
  tabBg: 'rgba(255,255,255,0.04)',
  tabInactiveText: '#64748b',
  
  descBg: 'rgba(0,0,0,0.2)',
};

const lightTheme: ThemeColors = {
  bg: '#f1f5f9',
  bgAlt: '#ffffff',
  card: 'linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(241, 245, 249, 0.9))',
  cardSolid: 'rgba(255, 255, 255, 0.95)',
  glass: { backdropFilter: 'blur(16px)', WebkitBackdropFilter: 'blur(16px)' },
  border: '1px solid rgba(0,0,0,0.08)',
  borderSubtle: '1px solid rgba(0,0,0,0.05)',
  
  textPrimary: '#0f172a',
  textSecondary: '#475569',
  textMuted: '#94a3b8',
  textInverse: '#ffffff',
  
  gold: '#ca8a04',
  goldGrad: 'linear-gradient(135deg, #ca8a04, #eab308)',
  goldBg: 'rgba(202,138,4,0.1)',
  
  inputBg: 'rgba(241, 245, 249, 0.8)',
  inputBorder: '1px solid rgba(0, 0, 0, 0.12)',
  inputText: '#0f172a',
  inputPlaceholder: '#94a3b8',
  
  headerBg: '#ffffff',
  headerText: '#0f172a',
  menuBg: 'rgba(0,0,0,0.02)',
  menuBorder: 'rgba(0,0,0,0.08)',
  sectionCardBg: 'linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 0.9))',
  modalBg: '#f1f5f9',
  footerBg: 'rgba(255, 255, 255, 0.95)',
  footerBorder: '1px solid rgba(0,0,0,0.08)',
  
  subtleBg: 'rgba(0,0,0,0.03)',
  scrollThumb: '#cbd5e1',
  
  shadow: '0 8px 32px rgba(0,0,0,0.08)',
  cardShadow: '0 4px 24px rgba(0, 0, 0, 0.06)',
  
  chartLabelColor: '#64748b',
  chartValueColor: '#0f172a',
  chartGridColor: 'rgba(0,0,0,0.06)',
  
  backBtnBg: 'rgba(0,0,0,0.05)',
  backBtnColor: '#475569',
  
  tabBg: 'rgba(0,0,0,0.04)',
  tabInactiveText: '#94a3b8',
  
  descBg: 'rgba(0,0,0,0.04)',
};

const ThemeContext = createContext<ThemeContextType | undefined>(undefined);

export const ThemeProvider = ({ children }: { children: ReactNode }) => {
  const [mode, setMode] = useState<ThemeMode>(() => {
    const saved = localStorage.getItem('eurotaxi_theme');
    return (saved === 'light' || saved === 'dark') ? saved : 'dark';
  });

  useEffect(() => {
    localStorage.setItem('eurotaxi_theme', mode);
    
    // Update CSS variables for Ionic components
    const root = document.documentElement;
    const colors = mode === 'dark' ? darkTheme : lightTheme;
    root.style.setProperty('--ion-background-color', colors.bg);
    root.style.setProperty('--ion-text-color', colors.textPrimary);
    root.style.setProperty('--ion-toolbar-background', colors.headerBg);
    root.style.setProperty('--ion-toolbar-color', colors.headerText);
    root.style.setProperty('--ion-item-background', colors.cardSolid);
    
    // Update body
    document.body.style.backgroundColor = colors.bg;
    document.body.style.color = colors.textPrimary;
  }, [mode]);

  const toggleTheme = () => setMode(prev => prev === 'dark' ? 'light' : 'dark');
  const setTheme = (m: ThemeMode) => setMode(m);

  const t = mode === 'dark' ? darkTheme : lightTheme;
  const isDark = mode === 'dark';

  return (
    <ThemeContext.Provider value={{ mode, toggleTheme, setTheme, t, isDark }}>
      {children}
    </ThemeContext.Provider>
  );
};

export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (context === undefined) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};
