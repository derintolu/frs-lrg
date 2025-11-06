/**
 * Mortgage Landing Page Interactivity API
 * Handles form steps, calculator, and lead submission
 *
 * @package LendingResourceHub
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

store('lrh-mortgage-landing', {
    state: {
        get isStep1() {
            const context = getContext();
            return context.step === 1;
        },

        get isStep2() {
            const context = getContext();
            return context.step === 2;
        },

        get isStep3() {
            const context = getContext();
            return context.step === 3;
        },

        get progressPercentage() {
            const context = getContext();
            return Math.round((context.step / 3) * 100) + '% Complete';
        },

        get progressWidth() {
            const context = getContext();
            return ((context.step / 3) * 100) + '%';
        },

        get formattedHomePrice() {
            const context = getContext();
            return '$' + context.homePrice.toLocaleString();
        },

        get formattedDownPayment() {
            const context = getContext();
            const percentage = ((context.downPayment / context.homePrice) * 100).toFixed(0);
            return '$' + context.downPayment.toLocaleString() + ' (' + percentage + '%)';
        },

        get formattedInterestRate() {
            const context = getContext();
            return context.interestRate.toFixed(3) + '%';
        },

        get monthlyPayment() {
            const context = getContext();
            const loanAmount = context.homePrice - context.downPayment;
            const monthlyRate = (context.interestRate / 100) / 12;
            const numPayments = 360; // 30 years

            if (loanAmount <= 0) return '$0';

            const payment = (loanAmount * monthlyRate * Math.pow(1 + monthlyRate, numPayments)) /
                           (Math.pow(1 + monthlyRate, numPayments) - 1);

            return '$' + Math.round(payment).toLocaleString();
        }
    },

    actions: {
        nextStep() {
            const context = getContext();
            if (context.step < 3) {
                context.step++;
            }
        },

        prevStep() {
            const context = getContext();
            if (context.step > 1) {
                context.step--;
            }
        },

        scrollToForm() {
            const formElement = document.getElementById('application-form');
            if (formElement) {
                formElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        updateHomePrice() {
            const context = getContext();
            const { ref } = getElement();
            context.homePrice = parseInt(ref.value);
        },

        updateDownPayment() {
            const context = getContext();
            const { ref } = getElement();
            context.downPayment = parseInt(ref.value);
        },

        updateInterestRate() {
            const context = getContext();
            const { ref } = getElement();
            context.interestRate = parseFloat(ref.value);
        },

        selectGoal() {
            const context = getContext();
            const { ref } = getElement();
            // Read the goal value from the button's data attribute
            const goalValue = ref.getAttribute('data-goal-value');
            context.goal = goalValue;
            console.log('Goal selected:', goalValue);
        },

        selectTime() {
            const context = getContext();
            const { ref } = getElement();
            // Read the time value from the button's data attribute
            const timeValue = ref.getAttribute('data-time-value');
            context.bestTime = timeValue;
            console.log('Time selected:', timeValue);
        },

        async handleSubmit(event) {
            event.preventDefault();
            const context = getContext();
            const { ref } = getElement();

            // Collect form data from all inputs
            const formElement = ref;
            const formData = new FormData(formElement);

            // Build lead submission data
            const leadData = {
                pageId: context.pageId,
                loanOfficerId: context.loanOfficerId,
                template: context.template,
                firstName: formData.get('firstName') || '',
                lastName: formData.get('lastName') || '',
                email: formData.get('email') || '',
                phone: formData.get('phone') || '',
                propertyZip: formData.get('propertyZip') || '',
                homePrice: formData.get('homePrice') || '',
                downPayment: formData.get('downPayment') || '',
                propertyType: formData.get('propertyType') || '',
                goal: context.goal || '',
                bestTimeToContact: context.bestTime || '',
            };

            // Get REST nonce from global config
            const restNonce = window.frsPortalConfig?.restNonce || '';

            try {
                const response = await fetch('/wp-json/frs/v1/mortgage-lead', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': restNonce
                    },
                    body: JSON.stringify(leadData)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Success - show confirmation message
                    alert('Thank you! Your application has been submitted. We\'ll contact you within 24 hours.');

                    // Reset form
                    formElement.reset();
                    context.step = 1;
                } else {
                    // Error
                    const errorMessage = result.message || 'There was an error submitting your application. Please try again or call us directly.';
                    alert(errorMessage);
                }
            } catch (error) {
                console.error('Form submission error:', error);
                alert('There was a network error. Please try again or call us directly.');
            }
        }
    }
});
