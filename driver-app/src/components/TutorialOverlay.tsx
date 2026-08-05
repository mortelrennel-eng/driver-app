import React, { useEffect, useRef, useState } from 'react';
import { useHistory, useLocation } from 'react-router-dom';
import { useTutorial } from '../context/TutorialContext';

interface SpotlightRect {
  top: number;
  left: number;
  width: number;
  height: number;
}

const PADDING = 12; // extra space around spotlight

const TutorialOverlay: React.FC = () => {
  const { isActive, currentStep, steps, nextStep, prevStep, skipTutorial } = useTutorial();
  const [spotlightRect, setSpotlightRect] = useState<SpotlightRect | null>(null);
  const [tooltipStyle, setTooltipStyle] = useState<React.CSSProperties>({});
  const [visible, setVisible] = useState(false);
  const tooltipRef = useRef<HTMLDivElement>(null);

  // Animate in on step change
  useEffect(() => {
    if (!isActive) { setVisible(false); return; }
    setVisible(false);
    const t = setTimeout(() => setVisible(true), 150);
    return () => clearTimeout(t);
  }, [isActive, currentStep]);

  // Auto-navigate to step route
  const history = useHistory();
  const location = useLocation();

  useEffect(() => {
    if (isActive && steps[currentStep]?.route) {
      if (location.pathname !== steps[currentStep].route) {
        history.replace(steps[currentStep].route as string);
      }
    }
  }, [isActive, currentStep, steps, history, location.pathname]);

  // Calculate spotlight & tooltip position continuously to handle scrolling
  useEffect(() => {
    if (!isActive) { setSpotlightRect(null); return; }

    const step = steps[currentStep];
    if (!step) return;

    if (!step.targetId || step.placement === 'center') {
      setSpotlightRect(null);
      setTooltipStyle({
        position: 'fixed',
        top: '50%',
        left: '50%',
        transform: 'translate(-50%, -50%)',
        width: 'min(90vw, 360px)',
        zIndex: 99999,
      });
      return;
    }

    let animationFrameId: number;
    let lastTop = -9999;
    let lastLeft = -9999;
    let lastEstH = -1;
    let attempts = 0;
    let scrolled = false;

    const trackPosition = () => {
      const el = document.getElementById(step.targetId!);
      if (!el) {
        attempts++;
        if (attempts > 120) { // ~2 seconds at 60fps
          setSpotlightRect(null);
          setTooltipStyle({
            position: 'fixed',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
            width: 'min(90vw, 360px)',
            zIndex: 99999,
          });
          return;
        }
        animationFrameId = requestAnimationFrame(trackPosition);
        return;
      }

      const rect = el.getBoundingClientRect();
      const vw = window.innerWidth;
      const vh = window.innerHeight;

      // Scroll into view once if severely off-screen
      if (!scrolled && (rect.top < 0 || rect.bottom > vh)) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        scrolled = true;
      }

      const currentEstH = tooltipRef.current ? tooltipRef.current.offsetHeight : 200;

      // Only update state if position changed significantly (>1px) or tooltip height changed
      if (Math.abs(rect.top - lastTop) > 1 || Math.abs(rect.left - lastLeft) > 1 || lastEstH !== currentEstH) {
        lastTop = rect.top;
        lastLeft = rect.left;
        lastEstH = currentEstH;

        const sr: SpotlightRect = {
          top: rect.top - PADDING,
          left: rect.left - PADDING,
          width: rect.width + PADDING * 2,
          height: rect.height + PADDING * 2,
        };
        setSpotlightRect(sr);

        const placement = step.placement || 'bottom';
        const tooltipW = Math.min(vw * 0.9, 320);

        let style: React.CSSProperties = { position: 'fixed', width: tooltipW, zIndex: 99999 };
        const clamp = (val: number, min: number, max: number) => Math.max(min, Math.min(val, max));
        const estH = currentEstH;
        const safeLeft = clamp(rect.left + rect.width / 2 - tooltipW / 2, 12, vw - tooltipW - 12);

        if (placement === 'bottom') {
          let idealTop = sr.top + sr.height + 16;
          if (idealTop + estH > vh) idealTop = Math.max(12, sr.top - estH - 16);
          style.top = clamp(idealTop, 12, vh - estH - 12);
          style.left = safeLeft;
        } else if (placement === 'top') {
          let idealTop = sr.top - estH - 16;
          if (idealTop < 12) idealTop = Math.min(vh - estH - 12, sr.top + sr.height + 16);
          style.top = clamp(idealTop, 12, vh - estH - 12);
          style.left = safeLeft;
        } else if (placement === 'left') {
          let idealLeft = sr.left - tooltipW - 16;
          if (idealLeft < 12) idealLeft = Math.min(vw - tooltipW - 12, sr.left + sr.width + 16);
          style.left = clamp(idealLeft, 12, vw - tooltipW - 12);
          
          let safeTop = rect.top + rect.height / 2 - estH / 2;
          if (style.left + tooltipW > sr.left && style.left < sr.left + sr.width) {
              if (sr.top - estH - 16 >= 12) safeTop = sr.top - estH - 16;
              else safeTop = sr.top + sr.height + 16;
          }
          style.top = clamp(safeTop, 12, vh - estH - 12);
        } else if (placement === 'right') {
          let idealLeft = sr.left + sr.width + 16;
          if (idealLeft + tooltipW > vw) idealLeft = Math.max(12, sr.left - tooltipW - 16);
          style.left = clamp(idealLeft, 12, vw - tooltipW - 12);
          
          let safeTop = rect.top + rect.height / 2 - estH / 2;
          if (style.left < sr.left + sr.width && style.left + tooltipW > sr.left) {
              if (sr.top - estH - 16 >= 12) safeTop = sr.top - estH - 16;
              else safeTop = sr.top + sr.height + 16;
          }
          style.top = clamp(safeTop, 12, vh - estH - 12);
        }
        setTooltipStyle(style);
      }

      animationFrameId = requestAnimationFrame(trackPosition);
    };

    trackPosition();

    return () => {
      if (animationFrameId) cancelAnimationFrame(animationFrameId);
    };
  }, [isActive, currentStep, steps, location.pathname]);

  if (!isActive) return null;

  const step = steps[currentStep];
  const isFirst = currentStep === 0;
  const isLast = currentStep === steps.length - 1;
  const progressPct = ((currentStep + 1) / steps.length) * 100;

  return (
    <>
      <style>{`
        @keyframes tutorialFadeIn {
          from { opacity: 0; }
          to   { opacity: 1; }
        }
        @keyframes tutorialPulse {
          0%, 100% { box-shadow: 0 0 0 0 rgba(234,179,8,0.5); }
          50%       { box-shadow: 0 0 0 8px rgba(234,179,8,0);  }
        }
        .tutorial-tooltip {
          animation: tutorialFadeIn 0.25s cubic-bezier(0.34,1.36,0.64,1) forwards;
        }
        .tutorial-next-btn {
          transition: all 0.2s ease;
        }
        .tutorial-next-btn:active {
          transform: scale(0.95);
        }
      `}</style>

      {/* Dark overlay with spotlight cutout */}
      <div
        style={{
          position: 'fixed',
          inset: 0,
          zIndex: 99990,
          pointerEvents: 'all',
        }}
        onClick={(e) => e.stopPropagation()}
      >
        {spotlightRect ? (
          /* SVG-based spotlight with smooth rounded cutout */
          <svg
            style={{ position: 'absolute', inset: 0, width: '100%', height: '100%' }}
            xmlns="http://www.w3.org/2000/svg"
          >
            <defs>
              <mask id="tutorial-mask">
                <rect width="100%" height="100%" fill="white" />
                <rect
                  x={spotlightRect.left}
                  y={spotlightRect.top}
                  width={spotlightRect.width}
                  height={spotlightRect.height}
                  rx="14"
                  fill="black"
                />
              </mask>
            </defs>
            <rect
              width="100%"
              height="100%"
              fill="rgba(0,0,0,0.72)"
              mask="url(#tutorial-mask)"
            />
            {/* Spotlight border pulse */}
            <rect
              x={spotlightRect.left}
              y={spotlightRect.top}
              width={spotlightRect.width}
              height={spotlightRect.height}
              rx="14"
              fill="none"
              stroke="#eab308"
              strokeWidth="2.5"
              opacity="0.9"
              style={{ animation: 'tutorialPulse 2s ease-in-out infinite' }}
            />
          </svg>
        ) : (
          /* Full dark overlay when no spotlight */
          <div style={{ position: 'absolute', inset: 0, background: 'rgba(0,0,0,0.72)' }} />
        )}
      </div>

      {/* Tooltip Card */}
      <div
        ref={tooltipRef}
        className="tutorial-tooltip"
        style={{
          ...tooltipStyle,
          background: 'linear-gradient(145deg, #1e293b, #0f172a)',
          borderRadius: '20px',
          border: '1px solid rgba(234,179,8,0.35)',
          boxShadow: '0 24px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.05)',
          padding: '20px',
          color: '#fff',
          fontFamily: 'Inter, system-ui, sans-serif',
          opacity: visible ? 1 : 0,
          transition: 'opacity 0.2s ease',
        }}
      >
        {/* Header row */}
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '14px' }}>
          {/* Step badge */}
          <div style={{
            background: 'rgba(234,179,8,0.18)',
            border: '1px solid rgba(234,179,8,0.4)',
            borderRadius: '8px',
            padding: '3px 10px',
            fontSize: '11px',
            fontWeight: '800',
            color: '#fbbf24',
            letterSpacing: '0.5px',
          }}>
            {currentStep + 1} / {steps.length}
          </div>
          {/* Skip button */}
          <button
            onClick={skipTutorial}
            style={{
              background: 'rgba(255,255,255,0.08)',
              border: '1px solid rgba(255,255,255,0.12)',
              borderRadius: '8px',
              color: '#94a3b8',
              fontSize: '11px',
              fontWeight: '700',
              padding: '3px 10px',
              cursor: 'pointer',
              letterSpacing: '0.5px',
            }}
          >
            SKIP
          </button>
        </div>

        {/* Progress bar */}
        <div style={{
          height: '3px',
          background: 'rgba(255,255,255,0.08)',
          borderRadius: '99px',
          marginBottom: '16px',
          overflow: 'hidden',
        }}>
          <div style={{
            height: '100%',
            width: `${progressPct}%`,
            background: 'linear-gradient(90deg, #eab308, #f59e0b)',
            borderRadius: '99px',
            transition: 'width 0.4s ease',
          }} />
        </div>

        {/* Icon + Title */}
        <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '10px' }}>
          <div style={{
            width: '36px', height: '36px', borderRadius: '10px', flexShrink: 0,
            background: 'linear-gradient(135deg, #eab308, #f59e0b)',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            fontSize: '18px',
          }}>
            🎓
          </div>
          <h3 style={{ margin: 0, fontSize: '15px', fontWeight: '900', color: '#f1f5f9', lineHeight: '1.3' }}>
            {step?.title}
          </h3>
        </div>

        {/* Description */}
        <p style={{
          margin: '0 0 18px',
          fontSize: '13px',
          color: '#94a3b8',
          lineHeight: '1.6',
          fontWeight: '500',
        }}>
          {step?.description}
        </p>

        {/* Navigation buttons */}
        <div style={{ display: 'flex', gap: '10px' }}>
          {!isFirst && (
            <button
              onClick={prevStep}
              className="tutorial-next-btn"
              style={{
                flex: '0 0 auto',
                padding: '10px 18px',
                borderRadius: '12px',
                border: '1px solid rgba(255,255,255,0.15)',
                background: 'rgba(255,255,255,0.07)',
                color: '#94a3b8',
                fontSize: '13px',
                fontWeight: '700',
                cursor: 'pointer',
              }}
            >
              ← Back
            </button>
          )}
          <button
            onClick={nextStep}
            className="tutorial-next-btn"
            style={{
              flex: 1,
              padding: '12px 18px',
              borderRadius: '12px',
              border: 'none',
              background: isLast
                ? 'linear-gradient(135deg, #22c55e, #16a34a)'
                : 'linear-gradient(135deg, #eab308, #f59e0b)',
              color: '#000',
              fontSize: '14px',
              fontWeight: '900',
              cursor: 'pointer',
              boxShadow: isLast
                ? '0 6px 20px rgba(34,197,94,0.4)'
                : '0 6px 20px rgba(234,179,8,0.4)',
            }}
          >
            {isLast ? '✓ Got it!' : 'Next →'}
          </button>
        </div>
      </div>
    </>
  );
};

export default TutorialOverlay;
