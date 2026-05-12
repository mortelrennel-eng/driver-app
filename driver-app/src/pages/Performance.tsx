import { useEffect, useState } from 'react';
import type { FC } from 'react';
import {
  IonContent,
  IonPage,
  IonIcon,
  IonHeader,
  IonToolbar,
  IonButtons,
  IonBackButton,
  IonTitle,
  IonSpinner,
  IonRefresher,
  IonRefresherContent
} from '@ionic/react';
import { 
  trendingUpOutline,
  statsChartOutline,
  calendarOutline,
  starOutline
} from 'ionicons/icons';
import axios from 'axios';
import { endpoints } from '../config/api';
import { useTheme } from '../context/ThemeContext';

interface PerformanceHistory {
  date: string;
  actual_boundary: number;
  target_boundary: number;
  plate_number?: string;
  is_extra?: number;
}

interface PerformanceRating {
  label: string;
  stars: number;
}

const g = {
  bg: '#0a0e1a',
  card: 'linear-gradient(145deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.85))',
  glass: { backdropFilter: 'blur(16px)', WebkitBackdropFilter: 'blur(16px)' } as React.CSSProperties,
  border: '1px solid rgba(255,255,255,0.06)',
  gold: '#eab308',
  blue: '#3b82f6',
  radius: '24px',
};

const Performance: FC = () => {
  const { t } = useTheme();
  const [performanceHistory, setPerformanceHistory] = useState<PerformanceHistory[]>([]);
  const [performanceRating, setPerformanceRating] = useState<PerformanceRating | null>(null);
  const [loading, setLoading] = useState(true);

  const fetchHistory = async () => {
    try {
      const response = await axios.get(endpoints.performanceHistory);
      if (response.data.success) {
        setPerformanceHistory(response.data.history);
        localStorage.setItem('cached_performance_history', JSON.stringify(response.data.history));
        
        if (response.data.rating) {
            setPerformanceRating(response.data.rating);
            localStorage.setItem('cached_perf_rating', JSON.stringify(response.data.rating));
        }
      }
    } catch (e) {
      console.error('Failed to fetch performance data', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const cachedHist = localStorage.getItem('cached_performance_history');
    if (cachedHist) {
      try { setPerformanceHistory(JSON.parse(cachedHist)); } catch (e) {}
    }
    const cachedRating = localStorage.getItem('cached_perf_rating');
    if (cachedRating) {
      try { setPerformanceRating(JSON.parse(cachedRating)); } catch (e) {}
    }
    
    if (cachedHist) setLoading(false);
    fetchHistory();
  }, []);

  const doRefresh = (event: CustomEvent) => {
    fetchHistory().then(() => event.detail.complete());
  };

  // Simple SVG Chart Helper
  const renderChart = () => {
    if (performanceHistory.length < 1) {
      return (
        <div style={{ height: '220px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#475569', fontSize: '14px', border: '1px dashed rgba(255,255,255,0.1)', borderRadius: '16px' }}>
          Not enough data for chart yet.
        </div>
      );
    }

    const width = 340;
    const height = 200;
    const padding = 30;
    
    const maxVal = Math.max(...performanceHistory.map(h => Math.max(Number(h.actual_boundary || 0), Number(h.target_boundary || 0))), 1500);
    const minVal = 0;

    const getX = (index: number) => {
      if (performanceHistory.length === 1) return width / 2;
      return (index * (width - padding * 2)) / (performanceHistory.length - 1) + padding;
    };
    const getY = (val: number) => height - ((val - minVal) * (height - padding * 2)) / (maxVal - minVal) - padding;

    const actualPoints = performanceHistory.map((h, i) => `${getX(i)},${getY(Number(h.actual_boundary || 0))}`).join(' ');
    const targetPoints = performanceHistory.map((h, i) => `${getX(i)},${getY(Number(h.target_boundary || 0))}`).join(' ');

    return (
      <div style={{ background: t.subtleBg, borderRadius: '20px', padding: '20px 5px', border: `1px solid ${t.border.split(' ')[2]}` }}>
        <svg viewBox={`0 0 ${width} ${height}`} style={{ width: '100%', height: 'auto', overflow: 'visible' }}>
          {/* Grid Lines */}
          {[0, 0.5, 1].map((p, i) => (
            <line 
              key={i} 
              x1={padding} 
              y1={getY(maxVal * p)} 
              x2={width - padding} 
              y2={getY(maxVal * p)} 
              stroke={t.border.split(' ')[2]} 
              strokeWidth="1" 
              strokeDasharray="4"
            />
          ))}

          {/* Target Line/Dot */}
          {performanceHistory.length > 1 ? (
            <polyline
              fill="none"
              stroke={t.gold}
              strokeWidth="2"
              strokeDasharray="4"
              points={targetPoints}
              style={{ opacity: 0.6 }}
            />
          ) : (
            <circle cx={getX(0)} cy={getY(Number(performanceHistory[0].target_boundary || 0))} r="4" fill={t.gold} style={{ opacity: 0.6 }} />
          )}

          {/* Actual Line/Dot */}
          {performanceHistory.length > 1 ? (
            <polyline
              fill="none"
              stroke={g.blue}
              strokeWidth="4"
              strokeLinecap="round"
              strokeLinejoin="round"
              points={actualPoints}
            />
          ) : (
             <rect 
               x={getX(0) - 20} 
               y={getY(Number(performanceHistory[0].actual_boundary || 0))} 
               width="40" 
               height={height - getY(Number(performanceHistory[0].actual_boundary || 0)) - padding} 
               fill={g.blue} 
               rx="6"
             />
          )}

          {/* Data Points & Labels */}
          {performanceHistory.map((h, i) => (
            <g key={i}>
              {performanceHistory.length > 1 && <circle cx={getX(i)} cy={getY(Number(h.actual_boundary || 0))} r="5" fill={g.blue} stroke={t.bg} strokeWidth="2" />}
              <text 
                x={getX(i)} 
                y={height - 5} 
                textAnchor="middle" 
                fill={t.textSecondary} 
                fontSize="10" 
                fontWeight="700"
              >
                {new Date(h.date).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })}
              </text>
              <text 
                x={getX(i)} 
                y={getY(Number(h.actual_boundary || 0)) - 10} 
                textAnchor="middle" 
                fill={t.textPrimary} 
                fontSize="10" 
                fontWeight="900"
              >
                ₱{Number(h.actual_boundary || 0).toLocaleString()}
              </text>
            </g>
          ))}
        </svg>
        <div style={{ display: 'flex', justifyContent: 'center', gap: '24px', padding: '15px 10px 0', borderTop: `1px solid ${t.border.split(' ')[2]}`, marginTop: '15px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <div style={{ width: '10px', height: '10px', borderRadius: '50%', background: g.blue }}></div>
            <span style={{ fontSize: '11px', color: t.textSecondary, fontWeight: '800' }}>REMITTANCE</span>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <div style={{ width: '12px', height: '2px', background: t.gold, borderRadius: '2px', opacity: 0.6 }}></div>
            <span style={{ fontSize: '11px', color: t.textSecondary, fontWeight: '800' }}>TARGET</span>
          </div>
        </div>
      </div>
    );
  };

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--background': t.headerBg, '--color': t.headerText }}>
          <IonButtons slot="start">
            <IonBackButton defaultHref="/dashboard" />
          </IonButtons>
          <IonTitle style={{ fontWeight: '800', fontSize: '18px' }}>Performance Insights</IonTitle>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen style={{ '--background': t.bg }}>
        <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
          <IonRefresherContent></IonRefresherContent>
        </IonRefresher>

        <div style={{ background: t.bg, minHeight: '100%', padding: '20px' }}>
          
          <div style={{ marginBottom: '24px' }}>
            <h1 style={{ color: t.textPrimary, fontSize: '28px', fontWeight: '900', margin: '0 0 8px' }}>Your Trends</h1>
            <p style={{ color: t.textSecondary, fontSize: '14px', margin: 0 }}>Analyze your consistency over the last 7 days.</p>
          </div>

          {loading ? (
            <div style={{ display: 'flex', justifyContent: 'center', padding: '40px' }}>
              <IonSpinner color="warning" />
            </div>
          ) : (
            <>
              {/* Chart Section */}
              <div style={{ padding: '20px', background: t.card, ...t.glass, border: t.border, borderRadius: g.radius, marginBottom: '20px' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px' }}>
                  <IonIcon icon={statsChartOutline} style={{ fontSize: '18px', color: t.gold }} />
                  <span style={{ fontSize: '12px', fontWeight: '800', color: t.gold, textTransform: 'uppercase', letterSpacing: '1.5px' }}>Boundary Trend</span>
                </div>
                {renderChart()}
              </div>

              {/* Weekly Summary Cards - Made smaller as requested */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', marginBottom: '20px' }}>
                <div style={{ padding: '12px 16px', background: t.card, ...t.glass, border: t.border, borderRadius: '16px' }}>
                  <IonIcon icon={trendingUpOutline} style={{ fontSize: '18px', color: '#22c55e', marginBottom: '8px' }} />
                  <div style={{ fontSize: '9px', color: t.textSecondary, fontWeight: '700', textTransform: 'uppercase' }}>Avg. Remittance</div>
                  <div style={{ fontSize: '18px', fontWeight: '900', color: t.textPrimary }}>
                    ₱{performanceHistory.length ? (performanceHistory.reduce((a, b) => a + Number(b.actual_boundary || 0), 0) / performanceHistory.length).toLocaleString(undefined, { maximumFractionDigits: 0 }) : 0}
                  </div>
                </div>
                <div style={{ padding: '12px 16px', background: t.card, ...t.glass, border: t.border, borderRadius: '16px' }}>
                  <IonIcon icon={starOutline} style={{ fontSize: '18px', color: t.gold, marginBottom: '8px' }} />
                  <div style={{ fontSize: '9px', color: t.textSecondary, fontWeight: '700', textTransform: 'uppercase' }}>Performance Rating</div>
                  <div style={{ fontSize: '16px', fontWeight: '900', color: t.textPrimary, marginBottom: '2px' }}>
                    {performanceRating?.label || (loading ? 'Wait...' : 'No Rating')}
                  </div>
                  <div style={{ fontSize: '12px', color: t.gold }}>
                    {performanceRating ? (
                      <>
                        {'★'.repeat(performanceRating.stars)}{'☆'.repeat(5 - performanceRating.stars)}
                      </>
                    ) : (
                      '☆☆☆☆☆'
                    )}
                  </div>
                </div>
              </div>

              {/* Daily List */}
              <div style={{ padding: '20px', background: t.card, ...t.glass, border: t.border, borderRadius: g.radius }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px' }}>
                  <IonIcon icon={calendarOutline} style={{ fontSize: '18px', color: t.gold }} />
                  <span style={{ fontSize: '12px', fontWeight: '800', color: t.gold, textTransform: 'uppercase', letterSpacing: '1.5px' }}>Daily Breakdown</span>
                </div>
                
                <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                  {performanceHistory.slice().reverse().map((day, i) => (
                    <div key={i} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '12px', background: t.subtleBg, borderRadius: '14px' }}>
                      <div>
                        <div style={{ fontSize: '13px', fontWeight: '700', color: t.textPrimary }}>{new Date(day.date).toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' })}</div>
                        <div style={{ fontSize: '11px', color: t.textMuted, fontWeight: '600' }}>
                          Target: ₱{day.target_boundary.toLocaleString()} 
                          {day.plate_number && <span style={{ color: t.textPrimary }}> • {day.plate_number}</span>}
                          {day.is_extra == 1 && <span style={{ color: t.gold, fontWeight: '800', marginLeft: '4px' }}> (EXTRA)</span>}
                        </div>
                      </div>
                      <div style={{ textAlign: 'right' }}>
                        <div style={{ fontSize: '14px', fontWeight: '900', color: day.actual_boundary >= day.target_boundary ? '#22c55e' : '#ef4444' }}>
                          ₱{day.actual_boundary.toLocaleString()}
                        </div>
                        <div style={{ fontSize: '10px', fontWeight: '700', color: day.actual_boundary >= day.target_boundary ? '#22c55e' : '#ef4444' }}>
                          {day.actual_boundary >= day.target_boundary ? 'TARGET MET' : `SHORT ₱${(day.target_boundary - day.actual_boundary).toLocaleString()}`}
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>


            </>
          )}
        </div>

      </IonContent>
    </IonPage>
  );
};

export default Performance;
