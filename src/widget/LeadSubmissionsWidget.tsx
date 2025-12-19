/**
 * Lead Submissions Widget
 *
 * A unified widget that displays lead submissions for both
 * Loan Officers and Realtors based on user role.
 */

import { useState, useEffect } from 'react';
import { Users, Mail, Phone, Calendar, ChevronRight, Loader2, RefreshCw, UserPlus } from 'lucide-react';

interface Lead {
	id: number;
	first_name: string;
	last_name: string;
	email: string;
	phone: string;
	lead_source: string;
	status: string;
	created_date: string;
	property_address?: string;
	loan_amount?: number;
	property_value?: number;
}

interface LeadSubmissionsWidgetProps {
	userId: number;
	userRole: 'loan_officer' | 'realtor';
	limit?: number;
	showHeader?: boolean;
	title?: string;
	restUrl: string;
	nonce: string;
}

const statusColors: Record<string, { bg: string; text: string; label: string }> = {
	new: { bg: '#dbeafe', text: '#1e40af', label: 'New' },
	contacted: { bg: '#fef3c7', text: '#92400e', label: 'Contacted' },
	qualified: { bg: '#d1fae5', text: '#065f46', label: 'Qualified' },
	closed: { bg: '#dcfce7', text: '#166534', label: 'Closed' },
	lost: { bg: '#fee2e2', text: '#991b1b', label: 'Lost' },
};

const sourceLabels: Record<string, string> = {
	mortgage_calculator: 'Calculator',
	mortgage_rate_quote: 'Rate Quote',
	mortgage_application: 'Application',
	manual_entry: 'Manual',
	biolink: 'Biolink',
	open_house: 'Open House',
	partnership: 'Partnership',
	spotlight: 'Spotlight',
	event: 'Event',
	lead_page: 'Lead Page',
};

export function LeadSubmissionsWidget({
	userId,
	userRole,
	limit = 5,
	showHeader = true,
	title = 'Recent Leads',
	restUrl,
	nonce,
}: LeadSubmissionsWidgetProps) {
	const [leads, setLeads] = useState<Lead[]>([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState<string | null>(null);

	const fetchLeads = async () => {
		try {
			setLoading(true);
			setError(null);

			// Use different endpoint based on role
			const endpoint = userRole === 'realtor'
				? `${restUrl}/leads/realtor/${userId}`
				: `${restUrl}/leads/lo/${userId}`;

			const response = await fetch(endpoint, {
				headers: {
					'X-WP-Nonce': nonce,
				},
			});

			if (!response.ok) {
				throw new Error('Failed to fetch leads');
			}

			const data = await response.json();

			if (data.success) {
				// Limit the results
				setLeads(data.data.slice(0, limit));
			} else {
				throw new Error('Failed to load leads');
			}
		} catch (err) {
			setError(err instanceof Error ? err.message : 'An error occurred');
		} finally {
			setLoading(false);
		}
	};

	useEffect(() => {
		fetchLeads();
	}, [userId, userRole, limit]);

	const formatDate = (dateString: string) => {
		const date = new Date(dateString);
		const now = new Date();
		const diffMs = now.getTime() - date.getTime();
		const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

		if (diffDays === 0) {
			return 'Today';
		} else if (diffDays === 1) {
			return 'Yesterday';
		} else if (diffDays < 7) {
			return `${diffDays} days ago`;
		} else {
			return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
		}
	};

	// Loading state
	if (loading) {
		return (
			<div style={{
				fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				background: '#ffffff',
				borderRadius: '16px',
				padding: '20px',
				boxShadow: '0 2px 8px rgba(0,0,0,0.08)',
			}}>
				{showHeader && (
					<div style={{ marginBottom: '16px' }}>
						<h3 style={{ fontSize: '1rem', fontWeight: 600, color: '#1e293b', margin: 0 }}>{title}</h3>
					</div>
				)}
				<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '40px 0' }}>
					<Loader2 style={{ width: '24px', height: '24px', color: '#2563eb', animation: 'spin 1s linear infinite' }} />
				</div>
				<style>{`@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }`}</style>
			</div>
		);
	}

	// Error state
	if (error) {
		return (
			<div style={{
				fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				background: '#ffffff',
				borderRadius: '16px',
				padding: '20px',
				boxShadow: '0 2px 8px rgba(0,0,0,0.08)',
			}}>
				{showHeader && (
					<div style={{ marginBottom: '16px' }}>
						<h3 style={{ fontSize: '1rem', fontWeight: 600, color: '#1e293b', margin: 0 }}>{title}</h3>
					</div>
				)}
				<div style={{
					background: '#fef2f2',
					border: '1px solid #fecaca',
					borderRadius: '8px',
					padding: '16px',
					textAlign: 'center',
				}}>
					<p style={{ color: '#dc2626', margin: '0 0 12px 0', fontSize: '14px' }}>{error}</p>
					<button
						onClick={fetchLeads}
						style={{
							display: 'inline-flex',
							alignItems: 'center',
							gap: '6px',
							padding: '8px 16px',
							background: '#fee2e2',
							color: '#dc2626',
							border: 'none',
							borderRadius: '6px',
							cursor: 'pointer',
							fontSize: '13px',
							fontWeight: 500,
						}}
					>
						<RefreshCw style={{ width: '14px', height: '14px' }} />
						Try Again
					</button>
				</div>
			</div>
		);
	}

	// Empty state
	if (leads.length === 0) {
		return (
			<div style={{
				fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				background: '#ffffff',
				borderRadius: '16px',
				padding: '20px',
				boxShadow: '0 2px 8px rgba(0,0,0,0.08)',
			}}>
				{showHeader && (
					<div style={{ marginBottom: '16px' }}>
						<h3 style={{ fontSize: '1rem', fontWeight: 600, color: '#1e293b', margin: 0 }}>{title}</h3>
					</div>
				)}
				<div style={{
					background: '#f8fafc',
					border: '1px solid #e2e8f0',
					borderRadius: '12px',
					padding: '32px 20px',
					textAlign: 'center',
				}}>
					<UserPlus style={{ width: '40px', height: '40px', color: '#94a3b8', margin: '0 auto 12px' }} />
					<p style={{ color: '#64748b', margin: 0, fontSize: '14px' }}>No leads yet</p>
					<p style={{ color: '#94a3b8', margin: '4px 0 0', fontSize: '13px' }}>
						{userRole === 'realtor'
							? 'Leads from your partnerships will appear here'
							: 'New leads will appear here as they come in'}
					</p>
				</div>
			</div>
		);
	}

	return (
		<div style={{
			fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
			background: '#ffffff',
			borderRadius: '16px',
			padding: '20px',
			boxShadow: '0 2px 8px rgba(0,0,0,0.08)',
		}}>
			{/* Header */}
			{showHeader && (
				<div style={{
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'space-between',
					marginBottom: '16px',
				}}>
					<div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
						<Users style={{ width: '18px', height: '18px', color: '#2563eb' }} />
						<h3 style={{ fontSize: '1rem', fontWeight: 600, color: '#1e293b', margin: 0 }}>{title}</h3>
					</div>
					<button
						onClick={fetchLeads}
						disabled={loading}
						style={{
							display: 'flex',
							alignItems: 'center',
							justifyContent: 'center',
							width: '32px',
							height: '32px',
							background: 'transparent',
							border: 'none',
							borderRadius: '8px',
							cursor: 'pointer',
							color: '#64748b',
							transition: 'all 0.2s',
						}}
						title="Refresh"
					>
						<RefreshCw style={{ width: '16px', height: '16px' }} />
					</button>
				</div>
			)}

			{/* Leads List */}
			<div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
				{leads.map((lead) => {
					const status = statusColors[lead.status] || statusColors.new;
					const source = sourceLabels[lead.lead_source] || lead.lead_source;

					return (
						<div
							key={lead.id}
							style={{
								display: 'flex',
								alignItems: 'center',
								gap: '12px',
								padding: '12px',
								background: '#f8fafc',
								borderRadius: '10px',
								transition: 'all 0.2s',
								cursor: 'pointer',
							}}
						>
							{/* Avatar */}
							<div style={{
								width: '40px',
								height: '40px',
								borderRadius: '50%',
								background: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)',
								display: 'flex',
								alignItems: 'center',
								justifyContent: 'center',
								flexShrink: 0,
							}}>
								<span style={{ color: '#ffffff', fontWeight: 600, fontSize: '14px' }}>
									{lead.first_name?.charAt(0) || ''}{lead.last_name?.charAt(0) || ''}
								</span>
							</div>

							{/* Info */}
							<div style={{ flex: 1, minWidth: 0 }}>
								<div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '4px' }}>
									<span style={{
										fontSize: '14px',
										fontWeight: 600,
										color: '#1e293b',
										whiteSpace: 'nowrap',
										overflow: 'hidden',
										textOverflow: 'ellipsis',
									}}>
										{lead.first_name} {lead.last_name}
									</span>
									<span style={{
										fontSize: '11px',
										fontWeight: 500,
										padding: '2px 6px',
										borderRadius: '4px',
										background: status.bg,
										color: status.text,
									}}>
										{status.label}
									</span>
								</div>
								<div style={{
									display: 'flex',
									alignItems: 'center',
									gap: '12px',
									fontSize: '12px',
									color: '#64748b',
								}}>
									<span style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
										<Calendar style={{ width: '12px', height: '12px' }} />
										{formatDate(lead.created_date)}
									</span>
									<span style={{
										padding: '1px 6px',
										background: '#e2e8f0',
										borderRadius: '4px',
										fontSize: '11px',
									}}>
										{source}
									</span>
								</div>
							</div>

							{/* Actions */}
							<div style={{ display: 'flex', alignItems: 'center', gap: '4px', flexShrink: 0 }}>
								{lead.email && (
									<a
										href={`mailto:${lead.email}`}
										onClick={(e) => e.stopPropagation()}
										style={{
											display: 'flex',
											alignItems: 'center',
											justifyContent: 'center',
											width: '32px',
											height: '32px',
											background: '#dbeafe',
											borderRadius: '8px',
											color: '#2563eb',
											textDecoration: 'none',
										}}
										title={`Email ${lead.email}`}
									>
										<Mail style={{ width: '14px', height: '14px' }} />
									</a>
								)}
								{lead.phone && (
									<a
										href={`tel:${lead.phone}`}
										onClick={(e) => e.stopPropagation()}
										style={{
											display: 'flex',
											alignItems: 'center',
											justifyContent: 'center',
											width: '32px',
											height: '32px',
											background: '#d1fae5',
											borderRadius: '8px',
											color: '#059669',
											textDecoration: 'none',
										}}
										title={`Call ${lead.phone}`}
									>
										<Phone style={{ width: '14px', height: '14px' }} />
									</a>
								)}
								<ChevronRight style={{ width: '16px', height: '16px', color: '#94a3b8' }} />
							</div>
						</div>
					);
				})}
			</div>

			{/* View All Link */}
			{leads.length >= limit && (
				<div style={{ marginTop: '12px', textAlign: 'center' }}>
					<a
						href="/dashboard"
						style={{
							fontSize: '13px',
							fontWeight: 500,
							color: '#2563eb',
							textDecoration: 'none',
						}}
					>
						View all leads →
					</a>
				</div>
			)}
		</div>
	);
}
