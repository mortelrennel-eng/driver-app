import React, { createContext, useCallback, useContext, useState } from 'react';

// ── Types ────────────────────────────────────────────────────────────────────
export interface TutorialStep {
  /** ID of the DOM element to spotlight. If null, shows a centered modal. */
  targetId: string | null;
  title: string;
  description: string;
  /** Where to position the tooltip relative to the target element */
  placement?: 'top' | 'bottom' | 'left' | 'right' | 'center';
  /** Route to navigate to for this step */
  route?: string;
}

export const MASTER_TOUR: TutorialStep[] = [
  // ── Navigation Tabs (1 step — intro) ────────────────────────────────────────
  { route: '/dashboard', targetId: 'nav-tab-home', title: 'Navigation Tabs', description: 'These four tabs let you switch between Home, Live Tracking, Messages, and Settings. Tap any tab to navigate instantly.', placement: 'top' },

  // ── Dashboard (6 steps) ──────────────────────────────────────────────────────
  { route: '/dashboard', targetId: 'dash-greeting', title: 'Welcome to EuroTaxi!', description: 'This is your personal dashboard. Here you can see your daily performance at a glance.', placement: 'bottom' },
  { route: '/dashboard', targetId: 'dash-notif-btn', title: 'Notifications', description: 'Tap this bell icon to view remittance alerts, incident updates, and system messages.', placement: 'bottom' },
  { route: '/dashboard', targetId: 'dash-boundary-card', title: 'Boundary Progress', description: 'This card shows how much you have earned vs your daily boundary target. The progress bar fills as you get closer to your goal.', placement: 'bottom' },
  { route: '/dashboard', targetId: 'dash-coding-status', title: 'Coding & Unit Status', description: 'Shows if your vehicle is under coding today and your current GPS status (Moving, Parked, or Offline).', placement: 'bottom' },
  { route: '/dashboard', targetId: 'dash-toolbox', title: 'Driver Toolbox', description: 'Quick access to Stats, Vehicle, History, Incidents, Debts, and Announcements. Tap any icon to navigate.', placement: 'top' },
  { route: '/dashboard', targetId: 'dash-sos-btn', title: 'Emergency SOS', description: 'Press and hold this button for 3 seconds to trigger an emergency alert. Use this only in real emergencies!', placement: 'top' },

  // ── Tracking (7 steps) ───────────────────────────────────────────────────────
  { route: '/tracking', targetId: 'track-map-area', title: 'Live Map', description: 'This area shows your real-time GPS position, heading, and the exact path you have traveled today.', placement: 'center' },
  { route: '/tracking', targetId: 'track-status-badge', title: 'GPS Status', description: 'This badge shows your current GPS state: MOVING, PARKED, or OFFLINE. The color changes automatically.', placement: 'bottom' },
  { route: '/tracking', targetId: 'track-unit-btn', title: 'Unit Info', description: 'Tap this button to view your assigned vehicle details, plate number, and current GPS coordinates.', placement: 'left' },
  { route: '/tracking', targetId: 'track-nearby-btn', title: 'Nearby Drivers', description: 'Tap this yellow button to find other EuroTaxi drivers near your location and see them on the map.', placement: 'left' },
  { route: '/tracking', targetId: 'track-crosshair', title: 'Auto-follow & Zoom', description: 'Tap this crosshair once to zoom in and auto-follow your position as you move. Tap again to unlock the map.', placement: 'top' },
  { route: '/tracking', targetId: 'track-start-pin', title: 'Trip Start', description: 'This checkered flag marks exactly where you started your driving session today.', placement: 'top' },

  // ── Messages (1 step) ────────────────────────────────────────────────────────
  { route: '/support', targetId: 'nav-tab-messages', title: 'Messages', description: 'Chat directly with EuroTaxi management here. Get support, ask questions, or receive important updates.', placement: 'top' },

  // ── Debts (3 steps) ────────────────────────────────────────────────────────
  { route: '/charges', targetId: 'charges-tabs', title: 'Debts & Incentives', description: 'Toggle between your pending accident debts and your boundary incentives.', placement: 'bottom' },
  { route: '/charges', targetId: 'charges-month', title: 'Filter by Month', description: 'Quickly filter your financial records by month using these tabs or the date picker.', placement: 'bottom' },
  { route: '/charges', targetId: 'charges-list', title: 'Record Details', description: 'View the specifics of each debt (balance, status) or incentive here.', placement: 'top' },

  // ── Performance (2 steps) ────────────────────────────────────────────────────
  { route: '/performance', targetId: 'perf-chart', title: 'Performance Trends', description: 'This chart visually tracks your boundary remittances over the past week compared to your target.', placement: 'bottom' },
  { route: '/performance', targetId: 'perf-list', title: 'Daily Breakdown', description: 'Review your detailed day-by-day remittance records, including any extra driving days.', placement: 'top' },

  // ── History (2 steps) ────────────────────────────────────────────────────────
  { route: '/history', targetId: 'history-month', title: 'Monthly History', description: 'Select a month here to view your boundary payment records for that specific period.', placement: 'bottom' },
  { route: '/history', targetId: 'history-summary', title: 'Payment Summary', description: 'See your total collected vs target. Tap on Records, Paid, or Short to filter the list below.', placement: 'bottom' },

  // ── Settings (2 steps) ───────────────────────────────────────────────────────
  { route: '/settings', targetId: 'settings-profile', title: 'Driver Profile', description: 'Your personal info, license, and assigned vehicle details are stored here.', placement: 'bottom' },
  { route: '/settings', targetId: 'settings-replay', title: 'Replay Tour', description: 'You can tap here to replay this guided tour at any time!', placement: 'bottom' },
];

interface TutorialContextValue {
  isActive: boolean;
  currentStep: number;
  steps: TutorialStep[];
  startTutorial: () => void;
  nextStep: () => void;
  prevStep: () => void;
  skipTutorial: () => void;
  hasCompletedBefore: () => boolean;
  resetTutorial: () => void;
}

const STORAGE_KEY = 'eurotaxi_tutorial_done';

// ── Context ──────────────────────────────────────────────────────────────────
const TutorialContext = createContext<TutorialContextValue | null>(null);

export const TutorialProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [isActive, setIsActive] = useState(false);
  const [currentStep, setCurrentStep] = useState(0);

  const hasCompletedBefore = useCallback(() => {
    return localStorage.getItem(STORAGE_KEY) === 'true';
  }, []);

  const startTutorial = useCallback(() => {
    setCurrentStep(0);
    setIsActive(true);
  }, []);

  const nextStep = useCallback(() => {
    setCurrentStep(prev => {
      const next = prev + 1;
      if (next >= MASTER_TOUR.length) {
        setIsActive(false);
        localStorage.setItem(STORAGE_KEY, 'true');
        return 0;
      }
      return next;
    });
  }, []);

  const prevStep = useCallback(() => {
    setCurrentStep(prev => Math.max(0, prev - 1));
  }, []);

  const skipTutorial = useCallback(() => {
    setIsActive(false);
    setCurrentStep(0);
    localStorage.setItem(STORAGE_KEY, 'true');
  }, []);

  const resetTutorial = useCallback(() => {
    localStorage.removeItem(STORAGE_KEY);
  }, []);

  return (
    <TutorialContext.Provider value={{
      isActive, currentStep, steps: MASTER_TOUR,
      startTutorial, nextStep, prevStep,
      skipTutorial, hasCompletedBefore, resetTutorial,
    }}>
      {children}
    </TutorialContext.Provider>
  );
};

// ── Hook ─────────────────────────────────────────────────────────────────────
export const useTutorial = (): TutorialContextValue => {
  const ctx = useContext(TutorialContext);
  if (!ctx) throw new Error('useTutorial must be used inside <TutorialProvider>');
  return ctx;
};
