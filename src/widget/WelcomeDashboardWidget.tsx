/**
 * Welcome Dashboard Widget for SureDash
 *
 * A standalone bento-style widget with:
 * - Welcome header with user's name
 * - Live clock with AM/PM
 * - Tear-off calendar
 * - Market Matters (mortgage rates)
 */

import { useState, useEffect } from 'react';
import { TrendingUp, TrendingDown } from 'lucide-react';

interface WelcomeDashboardWidgetProps {
  userName?: string;
  showMarketRates?: boolean;
}

interface MortgageRates {
  frm_30: string;
  frm_15: string;
  week: string;
}

export function WelcomeDashboardWidget({
  userName = 'Friend',
  showMarketRates = true,
}: WelcomeDashboardWidgetProps) {
  const [currentTime, setCurrentTime] = useState(new Date());
  const [rates, setRates] = useState<MortgageRates | null>(null);
  const [ratesLoading, setRatesLoading] = useState(true);

  // Update time every second
  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentTime(new Date());
    }, 1000);

    return () => clearInterval(timer);
  }, []);

  // Fetch mortgage rates
  useEffect(() => {
    if (!showMarketRates) {
      setRatesLoading(false);
      return;
    }

    const fetchRates = async () => {
      try {
        const response = await fetch('https://api.api-ninjas.com/v1/mortgagerate', {
          method: 'GET',
          headers: {
            'X-Api-Key': 'TYgp30Q8LTuwp3KTbCku1Q==MFnAgH2amAue4QiZ',
          },
        });

        if (response.ok) {
          const data = await response.json();
          if (data && data.length > 0) {
            setRates(data[0].data);
          }
        }
      } catch (err) {
        console.error('Failed to fetch mortgage rates:', err);
        // Fallback rates
        setRates({
          frm_30: '6.85',
          frm_15: '6.10',
          week: new Date().toISOString().split('T')[0],
        });
      } finally {
        setRatesLoading(false);
      }
    };

    fetchRates();
  }, [showMarketRates]);

  // Extract first name
  const firstName = userName.split(' ')[0];

  // Month names
  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

  return (
    <div className="frs-welcome-dashboard" style={{
      fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
      maxWidth: '100%',
      padding: '12px',
      background: 'linear-gradient(135deg, #f8fafc 0%, #ffffff 50%, #eff6ff 100%)',
      borderRadius: '16px',
    }}>
      {/* Grid Layout */}
      <div style={{
        display: 'grid',
        gridTemplateColumns: showMarketRates ? '1fr 1fr' : '1fr',
        gap: '12px',
      }}>
        {/* Left Column: Welcome + Clock/Calendar */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
          {/* Welcome Header */}
          <div style={{
            background: 'linear-gradient(135deg, #263042 0%, #1a2332 100%)',
            borderRadius: '12px',
            padding: '20px',
            boxShadow: '0 4px 16px rgba(38,48,66,0.4), 0 2px 6px rgba(0,0,0,0.2)',
            position: 'relative',
            overflow: 'hidden',
            flex: 1,
            display: 'flex',
            alignItems: 'center',
          }}>
            <div style={{ position: 'relative', zIndex: 1 }}>
              <h1 style={{
                fontSize: 'clamp(1.25rem, 3vw, 1.75rem)',
                fontWeight: 700,
                color: '#ffffff',
                margin: '0 0 4px 0',
                lineHeight: 1.2,
              }}>
                Welcome,<br />{firstName}
              </h1>
              <p style={{
                fontSize: '0.875rem',
                color: 'rgba(255,255,255,0.9)',
                margin: 0,
              }}>
                Your dashboard is ready
              </p>
            </div>
            {/* Decorative circle */}
            <div style={{
              position: 'absolute',
              right: '-40px',
              bottom: '-40px',
              width: '120px',
              height: '120px',
              background: 'rgba(255,255,255,0.1)',
              borderRadius: '50%',
              filter: 'blur(40px)',
            }} />
          </div>

          {/* Clock + Calendar Row */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' }}>
            {/* Clock */}
            <div style={{
              background: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)',
              borderRadius: '12px',
              padding: '16px 12px',
              boxShadow: '0 2px 8px rgba(37,99,235,0.3), 0 1px 3px rgba(0,0,0,0.1)',
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              justifyContent: 'center',
            }}>
              <div style={{
                color: '#ffffff',
                fontSize: 'clamp(1.5rem, 3vw, 2rem)',
                fontWeight: 700,
                lineHeight: 1,
              }}>
                {currentTime.toLocaleTimeString('en-US', {
                  hour: '2-digit',
                  minute: '2-digit',
                  hour12: true
                }).split(' ')[0]}
              </div>
              <div style={{
                color: '#ffffff',
                fontSize: 'clamp(0.8rem, 1.5vw, 1rem)',
                fontWeight: 500,
                marginTop: '4px',
              }}>
                {currentTime.toLocaleTimeString('en-US', { hour12: true }).split(' ')[1]}
              </div>
            </div>

            {/* Calendar */}
            <div style={{
              background: '#ffffff',
              borderRadius: '12px',
              overflow: 'hidden',
              boxShadow: '0 2px 8px rgba(0,0,0,0.12), 0 1px 3px rgba(0,0,0,0.08)',
            }}>
              {/* Month header */}
              <div style={{
                background: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)',
                color: '#ffffff',
                fontSize: 'clamp(0.7rem, 1.2vw, 0.85rem)',
                fontWeight: 600,
                textAlign: 'center',
                padding: '6px 0',
                textTransform: 'uppercase',
                letterSpacing: '0.05em',
              }}>
                {months[currentTime.getMonth()]}
              </div>
              {/* Date */}
              <div style={{
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                padding: '8px',
                background: '#ffffff',
              }}>
                <div style={{
                  fontSize: 'clamp(2rem, 4vw, 2.5rem)',
                  fontWeight: 700,
                  lineHeight: 1,
                  color: '#1e293b',
                }}>
                  {currentTime.getDate()}
                </div>
                <div style={{
                  fontSize: 'clamp(0.7rem, 1.2vw, 0.85rem)',
                  fontWeight: 500,
                  color: '#64748b',
                  marginTop: '4px',
                }}>
                  {days[currentTime.getDay()]}
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Right Column: Market Matters */}
        {showMarketRates && (
          <div style={{
            background: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)',
            borderRadius: '12px',
            padding: '16px',
            boxShadow: '0 4px 16px rgba(37,99,235,0.3), 0 2px 6px rgba(0,0,0,0.15)',
            display: 'flex',
            flexDirection: 'column',
          }}>
            {/* Header */}
            <div style={{
              display: 'flex',
              alignItems: 'center',
              gap: '8px',
              paddingBottom: '12px',
              borderBottom: '1px solid rgba(255,255,255,0.2)',
              marginBottom: '12px',
            }}>
              <TrendingUp style={{ width: '16px', height: '16px', color: '#ffffff' }} />
              <span style={{
                color: '#ffffff',
                fontSize: '0.875rem',
                fontWeight: 600,
              }}>
                Market Matters
              </span>
            </div>

            {/* Rates */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px', flex: 1 }}>
              {ratesLoading ? (
                <>
                  <div style={{ background: 'rgba(255,255,255,0.1)', borderRadius: '8px', height: '80px' }} />
                  <div style={{ background: 'rgba(255,255,255,0.1)', borderRadius: '8px', height: '80px' }} />
                </>
              ) : (
                <>
                  {/* 30-Year Rate */}
                  <div style={{
                    background: 'rgba(255,255,255,0.1)',
                    border: '1px solid rgba(255,255,255,0.2)',
                    borderRadius: '8px',
                    padding: '12px',
                  }}>
                    <div style={{
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'space-between',
                      marginBottom: '4px',
                    }}>
                      <span style={{ fontSize: '0.875rem', fontWeight: 500, color: '#ffffff' }}>
                        30-Year Fixed
                      </span>
                      <TrendingUp style={{ width: '14px', height: '14px', color: '#ffffff' }} />
                    </div>
                    <div style={{
                      fontSize: 'clamp(1.5rem, 3vw, 2rem)',
                      fontWeight: 700,
                      color: '#ffffff',
                    }}>
                      {rates?.frm_30 ? parseFloat(rates.frm_30).toFixed(2) : '—'}%
                    </div>
                    <div style={{
                      fontSize: '0.75rem',
                      color: 'rgba(255,255,255,0.7)',
                      marginTop: '4px',
                    }}>
                      {rates?.week ? new Date(rates.week).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : 'N/A'}
                    </div>
                  </div>

                  {/* 15-Year Rate */}
                  <div style={{
                    background: 'rgba(255,255,255,0.1)',
                    border: '1px solid rgba(255,255,255,0.2)',
                    borderRadius: '8px',
                    padding: '12px',
                  }}>
                    <div style={{
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'space-between',
                      marginBottom: '4px',
                    }}>
                      <span style={{ fontSize: '0.875rem', fontWeight: 500, color: '#ffffff' }}>
                        15-Year Fixed
                      </span>
                      <TrendingDown style={{ width: '14px', height: '14px', color: '#ffffff' }} />
                    </div>
                    <div style={{
                      fontSize: 'clamp(1.5rem, 3vw, 2rem)',
                      fontWeight: 700,
                      color: '#ffffff',
                    }}>
                      {rates?.frm_15 ? parseFloat(rates.frm_15).toFixed(2) : '—'}%
                    </div>
                    <div style={{
                      fontSize: '0.75rem',
                      color: 'rgba(255,255,255,0.7)',
                      marginTop: '4px',
                    }}>
                      {rates?.week ? new Date(rates.week).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : 'N/A'}
                    </div>
                  </div>
                </>
              )}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
