import React, { useState, useEffect, useRef } from "react";
import { Loader2, ArrowDown } from "lucide-react";

interface PullToRefreshProps {
  onRefresh: () => Promise<void>;
  children: React.ReactNode;
}

export function PullToRefresh({ onRefresh, children }: PullToRefreshProps) {
  const [pullDistance, setPullDistance] = useState(0);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const startY = useRef(0);
  const isDragging = useRef(false);
  const threshold = 80; // Distance in pixels to trigger refresh

  useEffect(() => {
    const handleTouchStart = (e: TouchEvent) => {
      // Only allow pulling down if we are scrolled to the very top of the window/container
      if (window.scrollY === 0) {
        startY.current = e.touches[0].pageY;
        isDragging.current = true;
      }
    };

    const handleTouchMove = (e: TouchEvent) => {
      if (!isDragging.current || isRefreshing) return;

      const currentY = e.touches[0].pageY;
      const distance = currentY - startY.current;

      if (distance > 0) {
        // Prevent default browser refresh gestures
        if (e.cancelable) e.preventDefault();
        
        // Apply resistance curve to dragging
        const resistance = 0.4;
        const dampedDistance = Math.min(distance * resistance, threshold + 20);
        setPullDistance(dampedDistance);
      } else {
        isDragging.current = false;
        setPullDistance(0);
      }
    };

    const handleTouchEnd = async () => {
      isDragging.current = false;
      if (pullDistance >= threshold && !isRefreshing) {
        setIsRefreshing(true);
        setPullDistance(threshold); // Lock at threshold during loading
        try {
          await onRefresh();
        } catch (e) {
          console.error(e);
        } finally {
          setIsRefreshing(false);
          setPullDistance(0);
        }
      } else {
        setPullDistance(0);
      }
    };

    window.addEventListener("touchstart", handleTouchStart, { passive: false });
    window.addEventListener("touchmove", handleTouchMove, { passive: false });
    window.addEventListener("touchend", handleTouchEnd);

    return () => {
      window.removeEventListener("touchstart", handleTouchStart);
      window.removeEventListener("touchmove", handleTouchMove);
      window.removeEventListener("touchend", handleTouchEnd);
    };
  }, [pullDistance, isRefreshing, onRefresh]);

  return (
    <div className="relative w-full">
      {/* Pull Indicator */}
      <div 
        className="absolute left-0 right-0 flex items-center justify-center transition-all duration-150 pointer-events-none z-50"
        style={{ 
          top: `${pullDistance - 45}px`,
          opacity: pullDistance > 10 ? 1 : 0
        }}
      >
        <div className="bg-white text-gray-900 border border-gray-100 rounded-full p-2.5 shadow-lg flex items-center justify-center">
          {isRefreshing ? (
            <Loader2 className="w-5 h-5 text-amber-500 animate-spin" />
          ) : (
            <ArrowDown 
              className="w-5 h-5 text-amber-500 transition-transform duration-150" 
              style={{ transform: `rotate(${Math.min(180, (pullDistance / threshold) * 180)}deg)` }}
            />
          )}
        </div>
      </div>

      {/* Main Content */}
      <div 
        className="transition-transform duration-150 ease-out"
        style={{ transform: `translateY(${pullDistance}px)` }}
      >
        {children}
      </div>
    </div>
  );
}
