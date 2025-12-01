/**
 * Frontend JavaScript for the Bento Grid block.
 *
 * Vanilla JS version of WelcomeBento component.
 */

// Get WordPress config
const wpData = window.frsPortalConfig || {};
const userId = wpData.userId || '';
const userName = wpData.userName || 'there';
const firstName = userName.split(' ')[0] || 'there';

// Month and day names
const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Update clock function
function updateClock(clockEl, ampmEl, dateEl, monthEl, dayEl) {
	const now = new Date();

	// Update clock
	const timeStr = now.toLocaleTimeString('en-US', {
		hour: '2-digit',
		minute: '2-digit',
		hour12: true
	});
	const parts = timeStr.split(' ');
	if (clockEl) clockEl.textContent = parts[0];
	if (ampmEl) ampmEl.textContent = parts[1];

	// Update calendar
	if (dateEl) dateEl.textContent = now.getDate();
	if (monthEl) monthEl.textContent = monthNames[now.getMonth()].toUpperCase();
	if (dayEl) dayEl.textContent = dayNames[now.getDay()];
}

// Initialize bento grid
const bentoContainers = document.querySelectorAll( '.lrh-bento-grid' );

if ( bentoContainers.length > 0 ) {
	bentoContainers.forEach( ( container ) => {
		// Add CSS for responsive bento grid
		const styleEl = document.createElement('style');
		styleEl.textContent = `
			.bento-grid {
				display: grid;
				grid-template-columns: repeat(2, 1fr);
				gap: 0.75rem;
			}
			@media (min-width: 768px) {
				.bento-grid {
					grid-template-columns: repeat(4, 1fr);
				}
			}
			.bento-welcome { grid-column: span 2; grid-row: span 2; }
			.bento-clock { grid-column: span 1; }
			.bento-calendar { grid-column: span 1; }
			.bento-market { grid-column: span 2; grid-row: span 2; }
			.bento-blog { grid-column: span 2; }
			.bento-app-launcher { grid-column: span 2; }
			@media (min-width: 768px) {
				.bento-blog { grid-column: span 4; }
				.bento-app-launcher { grid-column: span 4; }
			}
		`;
		if (!document.querySelector('#bento-grid-styles')) {
			styleEl.id = 'bento-grid-styles';
			document.head.appendChild(styleEl);
		}

		// Create the full bento grid layout
		container.innerHTML = `
			<div class="max-w-full h-full overflow-visible" style="margin: 1rem 1.5rem; background: linear-gradient(135deg, rgb(248 250 252) 0%, rgb(255 255 255) 50%, rgba(239 246 255 / 0.3) 100%);">
				<div class="bento-grid">
					<!-- Welcome Header - Brand Navy Gradient (2x2 on desktop) -->
					<div class="bento-welcome" style="position: relative; overflow: hidden; border-radius: 0.5rem; padding: 1rem; width: 100%; box-shadow: 0 4px 16px rgba(38,48,66,0.4), 0 2px 6px rgba(0,0,0,0.2); display: flex; align-items: center; background: linear-gradient(45deg, #263042 0%, #2563eb 50%, #263042 100%);">
						<div style="position: relative; z-index: 10; display: flex; flex-direction: column; justify-content: center; width: 100%;">
							<h1 style="font-size: clamp(1.5rem, 4vw, 1.875rem); font-weight: bold; color: white; margin-bottom: 0.25rem;">
								Welcome,<br />
								${firstName}
							</h1>
							<p style="font-size: 0.875rem; color: rgba(255,255,255,0.9);">
								Your dashboard is ready
							</p>
						</div>
						<div style="position: absolute; right: -2.5rem; bottom: -2.5rem; width: 8rem; height: 8rem; background: rgba(255,255,255,0.1); border-radius: 50%; filter: blur(3rem);"></div>
					</div>

					<!-- Clock Widget -->
					<div class="bento-clock clock-widget" style="box-shadow: 0 2px 8px rgba(37,99,235,0.3), 0 1px 3px rgba(0,0,0,0.1); border-radius: 0.5rem; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0.75rem 1rem; background: linear-gradient(135deg, #2563eb 0%, #2dd4da 100%);">
						<div class="clock-time" style="color: #ffffff; font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 700; line-height: 1; font-family: Poppins, -apple-system, sans-serif;">
							--:--
						</div>
						<div class="clock-ampm" style="color: #ffffff; font-size: clamp(0.8rem, 1.5vw, 1rem); font-weight: 500; margin-top: 0.25em; font-family: Poppins, -apple-system, sans-serif;">
							--
						</div>
					</div>

					<!-- Calendar Widget -->
					<div class="bento-calendar" style="box-shadow: 0 2px 8px rgba(0,0,0,0.12), 0 1px 3px rgba(0,0,0,0.08); border-radius: 0.5rem; overflow: hidden; background: white;">
						<!-- Month header (tear-off top) -->
						<div class="calendar-month" style="text-align: center; padding: 0.375rem; background: linear-gradient(135deg, #2563eb 0%, #2dd4da 100%); color: #ffffff; font-size: clamp(0.7rem, 1.2vw, 0.85rem); font-weight: 600; font-family: Poppins, -apple-system, sans-serif; letter-spacing: 0.05em; text-transform: uppercase;">
							MONTH
						</div>
						<!-- Date number -->
						<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0.5rem; background: white;">
							<div class="calendar-date" style="font-size: clamp(2rem, 4vw, 2.5rem); font-weight: 700; line-height: 1; color: #171A1F; font-family: Poppins, -apple-system, sans-serif;">
								--
							</div>
							<div class="calendar-day" style="font-size: clamp(0.7rem, 1.2vw, 0.85rem); font-weight: 500; color: #444B57; margin-top: 0.25em; font-family: Poppins, -apple-system, sans-serif;">
								Day
							</div>
						</div>
					</div>

					<!-- Market Matters Widget (2x2 on desktop) -->
					<div class="bento-market market-widget" style="width: 100%; min-height: 240px; border: 0; overflow: hidden; border-radius: 0.5rem; box-shadow: 0 4px 16px rgba(37,99,235,0.3), 0 2px 6px rgba(0,0,0,0.15); background: linear-gradient(135deg, #2563eb 0%, #2dd4da 100%); padding: 0; color: white;">
						<!-- Header -->
						<div style="padding: 0.5rem 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.2);">
							<div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
								<svg style="width: 1rem; height: 1rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
								</svg>
								<span style="color: white; font-weight: 500;">Market Matters</span>
							</div>
						</div>
						<!-- Content -->
						<div class="market-content" style="padding: 0.5rem;">
							<div style="display: flex; flex-direction: column; gap: 0.75rem;">
								<!-- 30-Year Rate -->
								<div style="position: relative; overflow: hidden; padding: 1rem; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 0.5rem;">
									<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25rem;">
										<span style="font-size: 0.875rem; font-weight: 500; color: white;">30-Year Fixed</span>
										<svg style="width: 1rem; height: 1rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
										</svg>
									</div>
									<div class="rate-30" style="font-size: 1.875rem; font-weight: 700; color: white;">---%</div>
									<div class="rate-30-date" style="font-size: 0.75rem; color: rgba(255,255,255,0.7); margin-top: 0.25rem;">Loading...</div>
								</div>
								<!-- 15-Year Rate -->
								<div style="position: relative; overflow: hidden; padding: 1rem; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 0.5rem;">
									<div style="display: flex; align-items: center; justify-between; margin-bottom: 0.25rem;">
										<span style="font-size: 0.875rem; font-weight: 500; color: white;">15-Year Fixed</span>
										<svg style="width: 1rem; height: 1rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17l-5-5m0 0l5-5m-5 5h12"></path>
										</svg>
									</div>
									<div class="rate-15" style="font-size: 1.875rem; font-weight: 700; color: white;">---%</div>
									<div class="rate-15-date" style="font-size: 0.75rem; color: rgba(255,255,255,0.7); margin-top: 0.25rem;">Loading...</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Blog Posts Section (full width) -->
					<div class="bento-blog blog-posts">
						<div style="position: relative; width: 100%; height: 100%; overflow: hidden; background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); border-radius: 0.5rem; border: 1px solid rgb(241 245 249);">
							<div style="padding: 0.75rem 0.75rem 0.25rem; border-bottom: 1px solid rgb(241 245 249);">
								<div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0;">
									<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 1rem; height: 1rem; color: #171A1F;">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
									</svg>
									<span style="font-size: 0.875rem; font-weight: 600; color: #171A1F;">Latest Updates</span>
								</div>
							</div>
							<div class="blog-content" style="padding: 0.5rem; overflow-y: auto; max-height: 300px;">
								<div style="text-align: center; padding: 3rem;">
									<p style="color: #444B57; font-size: 0.875rem;">Loading updates...</p>
								</div>
							</div>
						</div>
					</div>

					<!-- App Launcher (full width) -->
					<div class="bento-app-launcher" style="grid-column: span 2;">
						<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.75rem;">
							<!-- Mortgage Calculator -->
							<a href="lo/tools" style="display: block; padding: 1rem; background: white; border: 1px solid #E5E7EB; border-radius: 0.5rem; text-align: center; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#2563EB'; this.style.boxShadow='0 4px 12px rgba(37,99,235,0.15)';" onmouseout="this.style.borderColor='#E5E7EB'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
								<div style="width: 3rem; height: 3rem; margin: 0 auto 0.5rem; display: flex; align-items: center; justify-content: center;">
									<svg style="width: 100%; height: 100%; color: #2563EB;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
									</svg>
								</div>
								<span style="font-size: 0.75rem; font-weight: 500; color: #171A1F;">Mortgage Calculator</span>
							</a>
							<!-- Property Valuation -->
							<a href="lo/tools" style="display: block; padding: 1rem; background: white; border: 1px solid #E5E7EB; border-radius: 0.5rem; text-align: center; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#263042'; this.style.boxShadow='0 4px 12px rgba(38,48,66,0.15)';" onmouseout="this.style.borderColor='#E5E7EB'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
								<div style="width: 3rem; height: 3rem; margin: 0 auto 0.5rem; display: flex; align-items: center; justify-content: center;">
									<svg style="width: 100%; height: 100%; color: #263042;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
									</svg>
								</div>
								<span style="font-size: 0.75rem; font-weight: 500; color: #171A1F;">Property Valuation</span>
							</a>
							<!-- Outlook -->
							<a href="https://outlook.office.com/" target="_blank" rel="noopener noreferrer" style="display: block; padding: 1rem; background: white; border: 1px solid #E5E7EB; border-radius: 0.5rem; text-align: center; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#0078D4'; this.style.boxShadow='0 4px 12px rgba(0,120,212,0.15)';" onmouseout="this.style.borderColor='#E5E7EB'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
								<div style="width: 3rem; height: 3rem; margin: 0 auto 0.5rem; display: flex; align-items: center; justify-content: center;">
									<img src="/wp-content/plugins/frs-lrg/icons8-outlook.svg" alt="Outlook" style="width: 100%; height: 100%; object-fit: contain;" />
								</div>
								<span style="font-size: 0.75rem; font-weight: 500; color: #171A1F;">Outlook</span>
							</a>
							<!-- Arive -->
							<a href="https://app.arive.com/login" target="_blank" rel="noopener noreferrer" style="display: block; padding: 1rem; background: white; border: 1px solid #E5E7EB; border-radius: 0.5rem; text-align: center; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#2DD4DA'; this.style.boxShadow='0 4px 12px rgba(45,212,218,0.15)';" onmouseout="this.style.borderColor='#E5E7EB'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
								<div style="width: 3rem; height: 3rem; margin: 0 auto 0.5rem; display: flex; align-items: center; justify-content: center;">
									<img src="/wp-content/plugins/frs-lrg/assets/images/Arive-Highlight-Logo - 01.webp" alt="Arive" style="width: 100%; height: 100%; object-fit: contain;" />
								</div>
								<span style="font-size: 0.75rem; font-weight: 500; color: #171A1F;">Arive</span>
							</a>
							<!-- Follow Up Boss -->
							<a href="https://app.followupboss.com/login" target="_blank" rel="noopener noreferrer" style="display: block; padding: 1rem; background: white; border: 1px solid #E5E7EB; border-radius: 0.5rem; text-align: center; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#2563EB'; this.style.boxShadow='0 4px 12px rgba(37,99,235,0.15)';" onmouseout="this.style.borderColor='#E5E7EB'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
								<div style="width: 3rem; height: 3rem; margin: 0 auto 0.5rem; display: flex; align-items: center; justify-content: center;">
									<img src="/wp-content/plugins/frs-lrg/assets/images/FUB LOG.webp" alt="Follow Up Boss" style="width: 100%; height: 100%; object-fit: contain;" />
								</div>
								<span style="font-size: 0.75rem; font-weight: 500; color: #171A1F;">Follow Up Boss</span>
							</a>
						</div>
					</div>
				</div>
			</div>
		`;

		// Get clock elements
		const clockEl = container.querySelector('.clock-time');
		const ampmEl = container.querySelector('.clock-ampm');
		const dateEl = container.querySelector('.calendar-date');
		const monthEl = container.querySelector('.calendar-month');
		const dayEl = container.querySelector('.calendar-day');

		// Initialize clock immediately
		updateClock(clockEl, ampmEl, dateEl, monthEl, dayEl);

		// Update clock every second
		setInterval(() => {
			updateClock(clockEl, ampmEl, dateEl, monthEl, dayEl);
		}, 1000);

		// Load blog posts
		const blogContent = container.querySelector('.blog-content');
		if (blogContent) {
			const nonce = wpData.restNonce || '';
			const headers = nonce ? { 'X-WP-Nonce': nonce } : {};

			fetch('/wp-json/wp/v2/posts?per_page=2&_embed', { headers })
				.then(response => {
					if (!response.ok) {
						throw new Error('Failed to fetch posts');
					}
					return response.json();
				})
				.then(posts => {
					if (posts.length === 0) {
						blogContent.innerHTML = `
							<div style="text-align: center; padding: 3rem;">
								<svg style="width: 4rem; height: 4rem; margin: 0 auto 1rem; color: #DADEE3;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
								</svg>
								<p style="color: #444B57; font-size: 0.875rem;">No updates available</p>
							</div>
						`;
						return;
					}

					const postsHTML = posts.map(post => {
						// Decode HTML entities
						const titleDiv = document.createElement('div');
						titleDiv.innerHTML = post.title.rendered;
						const title = titleDiv.textContent || titleDiv.innerText || '';

						const excerptDiv = document.createElement('div');
						excerptDiv.innerHTML = post.excerpt.rendered;
						const excerpt = (excerptDiv.textContent || excerptDiv.innerText || '').substring(0, 150) + '...';

						const date = new Date(post.date).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
						const authorName = post._embedded?.author?.[0]?.name || 'Author';
						let authorAvatar = '';

						if (post._embedded?.author?.[0]?.avatar_urls) {
							const avatars = post._embedded.author[0].avatar_urls;
							authorAvatar = avatars['96'] || avatars['48'] || avatars['24'] || Object.values(avatars)[0];
						}

						if (!authorAvatar) {
							authorAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(authorName)}&background=2DD4DA&color=fff&size=96`;
						}

						return `
							<a href="${post.link}" target="_blank" rel="noopener noreferrer" style="display: block; padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s; text-decoration: none;" onmouseover="this.style.background='#F8F7F9'" onmouseout="this.style.background=''">
								<h4 style="font-weight: 600; font-size: 0.75rem; color: #171A1F; margin-bottom: 0.25rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
									${title}
								</h4>
								<p style="color: #444B57; font-size: 0.75rem; margin-bottom: 0.25rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
									${excerpt}
								</p>
								<div style="display: flex; align-items: center; gap: 0.25rem;">
									<img src="${authorAvatar}" alt="${authorName}" style="width: 1rem; height: 1rem; border-radius: 50%; border: 1px solid #DADEE3;" />
									<div style="display: flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; color: #444B57; flex: 1;">
										<span style="font-weight: 500; color: #171A1F;">${authorName}</span>
										<span>•</span>
										<span>${date}</span>
									</div>
									<svg style="width: 0.75rem; height: 0.75rem; color: #444B57;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
									</svg>
								</div>
							</a>
						`;
					}).join('');

					blogContent.innerHTML = `<div style="display: flex; flex-direction: column; gap: 0.5rem;">${postsHTML}</div>`;
				})
				.catch(err => {
					console.error('Failed to load blog posts:', err);
					blogContent.innerHTML = `
						<div style="text-align: center; padding: 3rem;">
							<p style="color: #EF4444; font-size: 0.875rem;">Failed to load updates</p>
						</div>
					`;
				});
		}

		// Load mortgage rates
		const rate30El = container.querySelector('.rate-30');
		const rate30DateEl = container.querySelector('.rate-30-date');
		const rate15El = container.querySelector('.rate-15');
		const rate15DateEl = container.querySelector('.rate-15-date');

		if (rate30El && rate15El) {
			fetch('https://api.api-ninjas.com/v1/mortgagerate', {
				method: 'GET',
				headers: {
					'X-Api-Key': 'TYgp30Q8LTuwp3KTbCku1Q==MFnAgH2amAue4QiZ',
				},
			})
				.then(response => {
					if (!response.ok) {
						throw new Error('Failed to fetch rates');
					}
					return response.json();
				})
				.then(data => {
					if (data && data.length > 0) {
						const rateData = data[0];
						const rate30 = parseFloat(rateData.data.frm_30).toFixed(2);
						const rate15 = parseFloat(rateData.data.frm_15).toFixed(2);
						const weekDate = new Date(rateData.data.week).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

						rate30El.textContent = `${rate30}%`;
						rate30DateEl.textContent = weekDate;
						rate15El.textContent = `${rate15}%`;
						rate15DateEl.textContent = weekDate;
					}
				})
				.catch(err => {
					console.error('Failed to load mortgage rates:', err);
					// Use fallback rates
					rate30El.textContent = '6.85%';
					rate30DateEl.textContent = 'Current';
					rate15El.textContent = '6.10%';
					rate15DateEl.textContent = 'Current';
				});
		}
	} );
}
