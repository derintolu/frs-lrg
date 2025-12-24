/**
 * Biolink Frontend JavaScript
 *
 * Handles form slide-ups, thank you overlays, and FluentForms integration
 * for biolink landing pages.
 *
 * @package LendingResourceHub
 * @since 1.0.0
 */

(function() {
    'use strict';

    /**
     * Initialize biolink functionality
     */
    function initBiolink() {
        // Button handlers for form overlays
        document.addEventListener('click', function(e) {
            // Schedule appointment button
            if (e.target.closest('a[href="#schedule-appointment"]')) {
                e.preventDefault();
                const schedulingForm = document.getElementById('scheduling');
                if (schedulingForm) {
                    schedulingForm.classList.add('active');
                }
            }
            // Rate quote button
            else if (e.target.closest('a[href="#rate-quote"]')) {
                e.preventDefault();
                const rateQuoteForm = document.getElementById('rate-quote');
                if (rateQuoteForm) {
                    rateQuoteForm.classList.add('active');
                }
            }
            // Back button in forms
            else if (e.target.classList.contains('frs-form-back')) {
                const parentForm = e.target.closest('[id]');
                if (parentForm) {
                    parentForm.classList.remove('active');
                }
            }
        });

        /**
         * Show thank you overlay
         * Globally accessible for FluentForms integration
         */
        window.showThankYou = function showThankYou() {
            // Hide form overlays
            const schedulingForm = document.getElementById('scheduling');
            const rateQuoteForm = document.getElementById('rate-quote');

            if (schedulingForm) {
                schedulingForm.classList.remove('active');
            }
            if (rateQuoteForm) {
                rateQuoteForm.classList.remove('active');
            }

            // Create thank you overlay if it doesn't exist
            let thankYouOverlay = document.getElementById('thank-you');
            if (!thankYouOverlay) {
                // Get loan officer's first name from page heading
                const headingElement = document.querySelector('h1');
                const fullName = headingElement ? headingElement.textContent : 'Your Loan Officer';
                const firstName = fullName.split(' ')[0];

                // Get video URL from lrhData (set by wp_localize_script)
                const videoUrl = window.lrhData && window.lrhData.pluginUrl
                    ? window.lrhData.pluginUrl + 'assets/images/Blue-Dark-Blue-Gradient-Color-and-Style-Video-Background-1.mp4'
                    : '';

                // Create thank you overlay HTML
                thankYouOverlay = document.createElement('div');
                thankYouOverlay.id = 'thank-you';
                thankYouOverlay.innerHTML = `
                    <div style="position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column; text-align: center; padding: 40px 20px; overflow: hidden;">
                        ${videoUrl ? `
                        <video autoplay muted loop playsinline style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;">
                            <source src="${videoUrl}" type="video/mp4">
                        </video>
                        ` : ''}
                        <div style="position: relative; z-index: 1;">
                            <h2 style="font-size: 3rem; margin-bottom: 30px; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.8); font-weight: 700;">Thank You!</h2>
                            <p style="font-size: 1.4rem; margin-bottom: 40px; max-width: 600px; color: white; text-shadow: 1px 1px 3px rgba(0,0,0,0.8); line-height: 1.6;">
                                Thanks for reaching out! ${firstName} will personally review your information and get back to you soon.
                            </p>
                            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; max-width: 570px; margin: 0 auto;">
                                <button
                                    onclick="document.getElementById('thank-you').classList.remove('active')"
                                    style="display: flex; align-items: center; justify-content: center; background: linear-gradient(145deg, #f8f9fa, #e9ecef); color: #212529; border: 1px solid rgba(255,255,255,0.8); padding: 18px 32px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04); font-size: 17.6px; font-weight: 500; cursor: pointer; transition: all 0.3s ease; flex: 1; min-width: 160px;"
                                    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06)'"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04)'">
                                    Return to Profile
                                </button>
                                <button
                                    onclick="window.close()"
                                    style="display: flex; align-items: center; justify-content: center; background: linear-gradient(145deg, #f8f9fa, #e9ecef); color: #212529; border: 1px solid rgba(255,255,255,0.8); padding: 18px 32px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04); font-size: 17.6px; font-weight: 500; cursor: pointer; transition: all 0.3s ease; flex: 1; min-width: 160px;"
                                    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06)'"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04)'">
                                    Close Tab
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(thankYouOverlay);
            }

            // Show thank you overlay
            thankYouOverlay.classList.add('active');
            console.log('Thank you overlay displayed');
        };

        /**
         * FluentForms integration
         * Listen for successful form submissions
         */
        if (typeof jQuery !== 'undefined') {
            jQuery(document.body).on('fluentform_submission_success', function(e, data) {
                console.log('FluentForms submission success detected:', data);

                // Check if this is a biolink form (Form ID 6 or 7)
                const formId = data && data.form_id ? String(data.form_id) : '';
                if (formId === '6' || formId === '7') {
                    console.log('FluentForms biolink form submitted successfully (Form ID: ' + formId + '), showing thank you');
                    window.showThankYou();
                } else {
                    // Fallback: check if any scheduling or rate quote forms are visible
                    const schedulingVisible = jQuery('#scheduling').hasClass('active');
                    const rateQuoteVisible = jQuery('#rate-quote').hasClass('active');

                    if (schedulingVisible || rateQuoteVisible) {
                        console.log('FluentForms biolink form submitted successfully (fallback detection), showing thank you');
                        window.showThankYou();
                    }
                }
            });
        }

        /**
         * Fallback: Listen for custom lead captured event
         * For other integrations beyond FluentForms
         */
        document.addEventListener('lrh_lead_captured', function(e) {
            console.log('LRH lead captured event detected');
            window.showThankYou();
        });

        /**
         * Legacy fallback: Form submissions via regular submit
         * For non-FluentForms forms (if any)
         */
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const isSchedulingForm = form.closest('#scheduling') ||
                                    (document.getElementById('scheduling') && document.getElementById('scheduling').contains(form));
            const isRateQuoteForm = form.closest('#rate-quote') ||
                                   (document.getElementById('rate-quote') && document.getElementById('rate-quote').contains(form));

            // Only handle non-FluentForms forms (FluentForms forms have ff-form class)
            if ((isSchedulingForm || isRateQuoteForm) && !form.classList.contains('ff-form')) {
                console.log('Non-FluentForms biolink form submitted, showing thank you in 2 seconds');
                setTimeout(function() {
                    window.showThankYou();
                }, 2000);
            }
        });

        /**
         * Test function for debugging
         * Usage: Call window.testThankYou() in browser console
         */
        window.testThankYou = function() {
            console.log('Test thank you triggered');
            window.showThankYou();
        };
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBiolink);
    } else {
        // DOM is already ready
        initBiolink();
    }
})();
