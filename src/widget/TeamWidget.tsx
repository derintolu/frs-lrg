/**
 * Team Widget for SureDash
 *
 * Displays multiple team members (loan officers, etc.) with a header.
 * Perfect for showing "Your 21st Century Lending Team!"
 */

import { Phone, Mail } from 'lucide-react';

interface TeamMember {
  id: string;
  name: string;
  title?: string;
  phone?: string;
  email?: string;
  avatar?: string;
  nmls?: string;
  profileUrl?: string;
}

interface TeamWidgetProps {
  title?: string;
  showTitle?: boolean;
  members: TeamMember[];
  layout?: 'row' | 'column' | 'grid';
  size?: 'default' | 'large';
}

export function TeamWidget({
  title = 'Your 21st Century Lending Team!',
  showTitle = true,
  members,
  layout = 'row',
  size = 'default',
}: TeamWidgetProps) {
  const isLarge = size === 'large';
  // Generate fallback avatar
  const getFallbackAvatar = (name: string) =>
    `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=2563eb&color=fff&size=96&font-size=0.4`;

  // Layout styles
  const getLayoutStyle = () => {
    switch (layout) {
      case 'column':
        return { display: 'flex', flexDirection: 'column' as const, gap: '12px' };
      case 'grid':
        return { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '12px' };
      case 'row':
      default:
        return { display: 'flex', flexWrap: 'wrap' as const, gap: '12px', justifyContent: 'center' };
    }
  };

  return (
    <div
      className="frs-team-widget"
      style={{
        fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        background: 'transparent',
        overflow: 'hidden',
      }}
    >
      {/* Header */}
      {showTitle && (
        <div
          style={{
            padding: '12px 20px',
            textAlign: 'center',
          }}
        >
          <h3
            style={{
              margin: 0,
              color: '#1e293b',
              fontSize: isLarge ? '1.25rem' : '1.125rem',
              fontWeight: 700,
            }}
          >
            {title}
          </h3>
        </div>
      )}

      {/* Team Members */}
      <div style={{ padding: isLarge ? '12px' : '16px', ...getLayoutStyle() }}>
        {members.map((member, index) => (
          <div
            key={member.id || index}
            style={{
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              gap: isLarge ? '12px' : '8px',
              padding: isLarge ? '24px 32px' : '16px 20px',
              background: '#ffffff',
              borderRadius: isLarge ? '16px' : '12px',
              boxShadow: '0 2px 8px rgba(0,0,0,0.12), 0 1px 3px rgba(0,0,0,0.08)',
              minWidth: isLarge ? '200px' : '160px',
            }}
          >
            {/* Avatar - Linked to profile */}
            {member.profileUrl ? (
              <a
                href={member.profileUrl}
                style={{ textDecoration: 'none', flexShrink: 0 }}
                title={`View ${member.name}'s profile`}
              >
                <img
                  src={member.avatar || getFallbackAvatar(member.name)}
                  alt={member.name}
                  style={{
                    width: isLarge ? '80px' : '56px',
                    height: isLarge ? '80px' : '56px',
                    borderRadius: '50%',
                    objectFit: 'cover',
                    border: isLarge ? '4px solid #ffffff' : '3px solid #ffffff',
                    boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
                    transition: 'transform 0.2s, box-shadow 0.2s',
                  }}
                  onMouseOver={(e) => {
                    e.currentTarget.style.transform = 'scale(1.05)';
                    e.currentTarget.style.boxShadow = '0 4px 12px rgba(37,99,235,0.3)';
                  }}
                  onMouseOut={(e) => {
                    e.currentTarget.style.transform = 'scale(1)';
                    e.currentTarget.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                  }}
                />
              </a>
            ) : (
              <img
                src={member.avatar || getFallbackAvatar(member.name)}
                alt={member.name}
                style={{
                  width: isLarge ? '80px' : '56px',
                  height: isLarge ? '80px' : '56px',
                  borderRadius: '50%',
                  objectFit: 'cover',
                  border: isLarge ? '4px solid #ffffff' : '3px solid #ffffff',
                  boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
                  flexShrink: 0,
                }}
              />
            )}

            {/* Info - Name linked to profile */}
            <div style={{ textAlign: 'center' }}>
              {member.profileUrl ? (
                <a
                  href={member.profileUrl}
                  style={{
                    fontWeight: 700,
                    fontSize: isLarge ? '1.1rem' : '0.9rem',
                    color: '#1e293b',
                    textDecoration: 'none',
                    display: 'block',
                    transition: 'color 0.2s',
                  }}
                  onMouseOver={(e) => (e.currentTarget.style.color = '#2563eb')}
                  onMouseOut={(e) => (e.currentTarget.style.color = '#1e293b')}
                  title={`View ${member.name}'s profile`}
                >
                  {member.name}
                </a>
              ) : (
                <div
                  style={{
                    fontWeight: 700,
                    fontSize: isLarge ? '1.1rem' : '0.9rem',
                    color: '#1e293b',
                  }}
                >
                  {member.name}
                </div>
              )}
              <div
                style={{
                  fontSize: isLarge ? '0.9rem' : '0.75rem',
                  color: '#64748b',
                }}
              >
                {member.title || 'Loan Officer'}
              </div>
              {member.nmls && (
                <div
                  style={{
                    fontSize: isLarge ? '0.8rem' : '0.7rem',
                    color: '#94a3b8',
                  }}
                >
                  NMLS# {member.nmls}
                </div>
              )}
            </div>

            {/* Action Buttons */}
            <div style={{ display: 'flex', gap: isLarge ? '8px' : '6px', justifyContent: 'flex-end', width: '100%', marginTop: 'auto' }}>
              {member.phone && (
                <a
                  href={`tel:${member.phone.replace(/\D/g, '')}`}
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    width: isLarge ? '36px' : '30px',
                    height: isLarge ? '36px' : '30px',
                    borderRadius: '50%',
                    background: 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)',
                    color: '#16a34a',
                    textDecoration: 'none',
                    border: '1px solid #bbf7d0',
                    transition: 'all 0.2s',
                  }}
                  title={`Call ${member.phone}`}
                  onMouseOver={(e) => {
                    e.currentTarget.style.background = 'linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%)';
                  }}
                  onMouseOut={(e) => {
                    e.currentTarget.style.background = 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)';
                  }}
                >
                  <Phone style={{ width: isLarge ? '18px' : '14px', height: isLarge ? '18px' : '14px' }} />
                </a>
              )}
              {member.email && (
                <a
                  href={`mailto:${member.email}`}
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    width: isLarge ? '36px' : '30px',
                    height: isLarge ? '36px' : '30px',
                    borderRadius: '50%',
                    background: 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)',
                    color: '#2563eb',
                    textDecoration: 'none',
                    border: '1px solid #bfdbfe',
                    transition: 'all 0.2s',
                  }}
                  title={`Email ${member.email}`}
                  onMouseOver={(e) => {
                    e.currentTarget.style.background = 'linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%)';
                  }}
                  onMouseOut={(e) => {
                    e.currentTarget.style.background = 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)';
                  }}
                >
                  <Mail style={{ width: isLarge ? '18px' : '14px', height: isLarge ? '18px' : '14px' }} />
                </a>
              )}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
