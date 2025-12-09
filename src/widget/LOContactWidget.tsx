/**
 * Loan Officer Contact Widget for SureDash
 *
 * A compact widget displaying:
 * - LO avatar/photo
 * - Name and title
 * - Phone button (tel: link)
 * - Email button (mailto: link)
 */

import { Phone, Mail } from 'lucide-react';

interface LOContactWidgetProps {
  name: string;
  title?: string;
  phone?: string;
  email?: string;
  avatar?: string;
  nmls?: string;
  variant?: 'card' | 'inline' | 'minimal';
}

export function LOContactWidget({
  name,
  title = 'Loan Officer',
  phone,
  email,
  avatar,
  nmls,
  variant = 'card',
}: LOContactWidgetProps) {
  // Generate initials for fallback avatar
  const initials = name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);

  const fallbackAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=2563eb&color=fff&size=96&font-size=0.4`;

  if (variant === 'minimal') {
    return (
      <div
        className="frs-lo-contact-widget frs-lo-contact-widget--minimal"
        style={{
          fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
          display: 'flex',
          alignItems: 'center',
          gap: '12px',
          padding: '8px',
        }}
      >
        <img
          src={avatar || fallbackAvatar}
          alt={name}
          style={{
            width: '40px',
            height: '40px',
            borderRadius: '50%',
            objectFit: 'cover',
          }}
        />
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ fontWeight: 600, fontSize: '0.875rem', color: '#1e293b' }}>{name}</div>
          <div style={{ fontSize: '0.75rem', color: '#64748b' }}>{title}</div>
        </div>
        {phone && (
          <a
            href={`tel:${phone.replace(/\D/g, '')}`}
            style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              width: '36px',
              height: '36px',
              borderRadius: '50%',
              background: '#2563eb',
              color: '#ffffff',
              textDecoration: 'none',
            }}
          >
            <Phone style={{ width: '16px', height: '16px' }} />
          </a>
        )}
      </div>
    );
  }

  if (variant === 'inline') {
    return (
      <div
        className="frs-lo-contact-widget frs-lo-contact-widget--inline"
        style={{
          fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
          display: 'flex',
          alignItems: 'center',
          gap: '16px',
          padding: '16px',
          background: '#ffffff',
          borderRadius: '12px',
          boxShadow: '0 2px 8px rgba(0,0,0,0.08)',
        }}
      >
        <img
          src={avatar || fallbackAvatar}
          alt={name}
          style={{
            width: '56px',
            height: '56px',
            borderRadius: '50%',
            objectFit: 'cover',
            border: '3px solid #ffffff',
            boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
          }}
        />
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ fontWeight: 700, fontSize: '1rem', color: '#1e293b' }}>{name}</div>
          <div style={{ fontSize: '0.875rem', color: '#64748b' }}>{title}</div>
          {nmls && (
            <div style={{ fontSize: '0.75rem', color: '#94a3b8', marginTop: '2px' }}>
              NMLS# {nmls}
            </div>
          )}
        </div>
        <div style={{ display: 'flex', gap: '8px' }}>
          {phone && (
            <a
              href={`tel:${phone.replace(/\D/g, '')}`}
              style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: '44px',
                height: '44px',
                borderRadius: '50%',
                background: 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)',
                color: '#ffffff',
                textDecoration: 'none',
                boxShadow: '0 2px 8px rgba(34,197,94,0.3)',
                transition: 'transform 0.2s, box-shadow 0.2s',
              }}
              title={`Call ${phone}`}
            >
              <Phone style={{ width: '20px', height: '20px' }} />
            </a>
          )}
          {email && (
            <a
              href={`mailto:${email}`}
              style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: '44px',
                height: '44px',
                borderRadius: '50%',
                background: 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)',
                color: '#ffffff',
                textDecoration: 'none',
                boxShadow: '0 2px 8px rgba(37,99,235,0.3)',
                transition: 'transform 0.2s, box-shadow 0.2s',
              }}
              title={`Email ${email}`}
            >
              <Mail style={{ width: '20px', height: '20px' }} />
            </a>
          )}
        </div>
      </div>
    );
  }

  // Default: card variant
  return (
    <div
      className="frs-lo-contact-widget frs-lo-contact-widget--card"
      style={{
        fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        background: '#ffffff',
        borderRadius: '16px',
        boxShadow: '0 4px 16px rgba(0,0,0,0.1)',
        overflow: 'hidden',
        maxWidth: '320px',
      }}
    >
      {/* Header with gradient */}
      <div
        style={{
          background: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)',
          padding: '24px 20px 40px',
          textAlign: 'center',
          position: 'relative',
        }}
      >
        <div style={{ position: 'relative', zIndex: 1 }}>
          <div style={{ fontSize: '0.75rem', color: 'rgba(255,255,255,0.8)', marginBottom: '4px' }}>
            Your Loan Officer
          </div>
        </div>
      </div>

      {/* Avatar overlapping header */}
      <div
        style={{
          display: 'flex',
          justifyContent: 'center',
          marginTop: '-40px',
          position: 'relative',
          zIndex: 2,
        }}
      >
        <img
          src={avatar || fallbackAvatar}
          alt={name}
          style={{
            width: '80px',
            height: '80px',
            borderRadius: '50%',
            objectFit: 'cover',
            border: '4px solid #ffffff',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
          }}
        />
      </div>

      {/* Content */}
      <div style={{ padding: '16px 20px 24px', textAlign: 'center' }}>
        <h3
          style={{
            fontSize: '1.125rem',
            fontWeight: 700,
            color: '#1e293b',
            margin: '0 0 4px 0',
          }}
        >
          {name}
        </h3>
        <p style={{ fontSize: '0.875rem', color: '#64748b', margin: '0 0 4px 0' }}>{title}</p>
        {nmls && (
          <p style={{ fontSize: '0.75rem', color: '#94a3b8', margin: '0 0 16px 0' }}>
            NMLS# {nmls}
          </p>
        )}

        {/* Action Buttons */}
        <div style={{ display: 'flex', gap: '12px', justifyContent: 'center' }}>
          {phone && (
            <a
              href={`tel:${phone.replace(/\D/g, '')}`}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
                padding: '12px 20px',
                borderRadius: '50px',
                background: 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)',
                color: '#ffffff',
                textDecoration: 'none',
                fontWeight: 600,
                fontSize: '0.875rem',
                boxShadow: '0 2px 8px rgba(34,197,94,0.3)',
                transition: 'transform 0.2s, box-shadow 0.2s',
              }}
            >
              <Phone style={{ width: '18px', height: '18px' }} />
              Call
            </a>
          )}
          {email && (
            <a
              href={`mailto:${email}`}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
                padding: '12px 20px',
                borderRadius: '50px',
                background: 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)',
                color: '#ffffff',
                textDecoration: 'none',
                fontWeight: 600,
                fontSize: '0.875rem',
                boxShadow: '0 2px 8px rgba(37,99,235,0.3)',
                transition: 'transform 0.2s, box-shadow 0.2s',
              }}
            >
              <Mail style={{ width: '18px', height: '18px' }} />
              Email
            </a>
          )}
        </div>

        {/* Phone number display */}
        {phone && (
          <p
            style={{
              fontSize: '0.875rem',
              color: '#64748b',
              margin: '16px 0 0 0',
            }}
          >
            {phone}
          </p>
        )}
      </div>
    </div>
  );
}
