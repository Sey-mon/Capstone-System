/**
 * Parent Dashboard Onboarding Tour
 * Provides first-time user guidance and feature introduction
 */

(function() {
    'use strict';

    // Check if user has completed onboarding
    const hasCompletedOnboarding = localStorage.getItem('parentDashboardOnboarding');
    
    // Trigger onboarding if:
    // 1. User hasn't completed it before (checked in localStorage)
    // 2. This is truly their first dashboard visit after account creation
    if (!hasCompletedOnboarding) {
        // Small delay to ensure page is fully loaded
        setTimeout(startOnboarding, 1500);
    }

    function startOnboarding() {
        // Create overlay
        const overlay = document.createElement('div');
        overlay.id = 'onboarding-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9998;
            display: flex;
            align-items: center;
            justify-content: center;
        `;

        // Create modal
        const modal = document.createElement('div');
        modal.id = 'onboarding-modal';
        modal.style.cssText = `
            background: white;
            border-radius: 12px;
            padding: 2.5rem;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 9999;
        `;

        const steps = [
            {
                title: 'Welcome to Your Parent Dashboard! 🎉',
                content: `
                    <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 1.5rem;">
                        We're excited to help you monitor and improve your child's nutrition and health.
                    </p>
                    <p style="margin-bottom: 1rem;">Let's take a quick tour of the key features:</p>
                    <ul style="line-height: 1.8; padding-left: 1.5rem;">
                        <li><strong>Dashboard Overview:</strong> See your child's growth and health status</li>
                        <li><strong>Children Management:</strong> Link and monitor multiple children</li>
                        <li><strong>Health Screenings:</strong> View detailed nutrition screenings</li>
                        <li><strong>Meal Plans:</strong> Access personalized meal recommendations</li>
                    </ul>
                `,
                icon: '<i class="fas fa-hand-sparkles" style="font-size: 3rem; color: #28a745;"></i>'
            },
            {
                title: 'Link Your Child 👶',
                content: `
                    <p style="line-height: 1.6; margin-bottom: 1.5rem;">
                        If you haven't already, the first step is to link your child's patient record.
                    </p>
                    <div style="background: #e7f3ff; padding: 1.25rem; border-radius: 8px; border-left: 4px solid #17a2b8; margin-bottom: 1.5rem;">
                        <h4 style="margin: 0 0 0.75rem 0; color: #17a2b8;"><i class="fas fa-info-circle"></i> How to Link:</h4>
                        <ol style="margin: 0; padding-left: 1.5rem; line-height: 1.8;">
                            <li>Visit the "Children" section from the menu</li>
                            <li>Click "Link Child" or "Add Child"</li>
                            <li>Enter the Patient ID provided by your nutritionist</li>
                        </ol>
                    </div>
                    <p style="font-style: italic; color: #6c757d;">
                        Don't have a Patient ID yet? Contact your nutritionist or visit the health center for your child's first screening.
                    </p>
                `,
                icon: '<i class="fas fa-link" style="font-size: 3rem; color: #17a2b8;"></i>'
            },
            {
                title: 'Monitor Growth & Health 📊',
                content: `
                    <p style="line-height: 1.6; margin-bottom: 1.5rem;">
                        Once your child is linked, you'll see comprehensive health information:
                    </p>
                    <ul style="line-height: 1.8; padding-left: 1.5rem;">
                        <li><strong>Growth Charts:</strong> Track weight and height over time</li>
                        <li><strong>Nutrition Status:</strong> See current malnutrition indicators</li>
                        <li><strong>Screening History:</strong> View past health check-ups</li>
                        <li><strong>Recommendations:</strong> Get personalized nutrition advice</li>
                    </ul>
                    <div style="background: #fff3cd; padding: 1rem; border-radius: 8px; margin-top: 1.5rem; border-left: 4px solid: #ffc107;">
                        <p style="margin: 0;"><i class="fas fa-star" style="color: #ffc107;"></i> <strong>Pro Tip:</strong> Regular check-ups help track your child's progress more effectively!</p>
                    </div>
                `,
                icon: '<i class="fas fa-chart-line" style="font-size: 3rem; color: #28a745;"></i>'
            },
            {
                title: 'Access Meal Plans 🍽️',
                content: `
                    <p style="line-height: 1.6; margin-bottom: 1.5rem;">
                        Get AI-powered personalized meal plans tailored to your child's nutritional needs:
                    </p>
                    <ul style="line-height: 1.8; padding-left: 1.5rem;">
                        <li>Balanced nutrition recommendations</li>
                        <li>Age-appropriate portion sizes</li>
                        <li>Local and affordable ingredients</li>
                        <li>Weekly meal schedules</li>
                    </ul>
                    <p style="margin-top: 1.5rem; padding: 1rem; background: #d4edda; border-radius: 8px; border-left: 4px solid #28a745;">
                        <i class="fas fa-leaf" style="color: #28a745;"></i> <strong>Healthy Eating:</strong> Following the recommended meal plans can significantly improve your child's nutrition status!
                    </p>
                `,
                icon: '<i class="fas fa-utensils" style="font-size: 3rem; color: #fd7e14;"></i>'
            },
            {
                title: 'You\'re All Set! ✅',
                content: `
                    <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 1.5rem;">
                        You now know the basics of using your parent dashboard!
                    </p>
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                        <h4 style="margin: 0 0 1rem 0;"><i class="fas fa-question-circle" style="color: #17a2b8;"></i> Need Help?</h4>
                        <p style="margin: 0; line-height: 1.6;">
                            • Check the Help section for detailed guides<br>
                            • Contact support if you have questions<br>
                            • Your nutritionist is always available for assistance
                        </p>
                    </div>
                    <p style="text-align: center; margin: 0; font-size: 1.1rem; color: #28a745;">
                        <strong>Let's get started on this health journey together!</strong>
                    </p>
                `,
                icon: '<i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745;"></i>'
            }
        ];

        let currentStep = 0;

        function showStep(stepIndex) {
            const step = steps[stepIndex];
            modal.innerHTML = `
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    ${step.icon}
                </div>
                <h2 style="margin: 0 0 1.5rem 0; text-align: center; color: #1f2937; font-size: 1.75rem;">
                    ${step.title}
                </h2>
                <div style="color: #4b5563; margin-bottom: 2rem;">
                    ${step.content}
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                    <div style="font-size: 0.9rem; color: #6c757d;">
                        Step ${stepIndex + 1} of ${steps.length}
                    </div>
                    <div style="display: flex; gap: 0.75rem;">
                        ${stepIndex > 0 ? '<button id="prevBtn" style="padding: 0.75rem 1.5rem; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Previous</button>' : ''}
                        ${stepIndex < steps.length - 1 ? 
                            '<button id="nextBtn" style="padding: 0.75rem 1.5rem; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Next <i class="fas fa-arrow-right"></i></button>' : 
                            '<button id="finishBtn" style="padding: 0.75rem 2rem; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 1rem;">Get Started! <i class="fas fa-rocket"></i></button>'
                        }
                        ${stepIndex === 0 ? '<button id="skipBtn" style="padding: 0.75rem 1.5rem; background: transparent; color: #6c757d; border: 1px solid #dee2e6; border-radius: 6px; cursor: pointer; font-weight: 500;">Skip Tour</button>' : ''}
                    </div>
                </div>
            `;

            // Add event listeners
            const nextBtn = modal.querySelector('#nextBtn');
            const prevBtn = modal.querySelector('#prevBtn');
            const finishBtn = modal.querySelector('#finishBtn');
            const skipBtn = modal.querySelector('#skipBtn');

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    currentStep++;
                    showStep(currentStep);
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    currentStep--;
                    showStep(currentStep);
                });
            }

            if (finishBtn) {
                finishBtn.addEventListener('click', finishOnboarding);
            }

            if (skipBtn) {
                skipBtn.addEventListener('click', finishOnboarding);
            }
        }

        function finishOnboarding() {
            localStorage.setItem('parentDashboardOnboarding', 'completed');
            overlay.style.opacity = '0';
            setTimeout(() => {
                overlay.remove();
            }, 300);
        }

        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        // Fade in
        setTimeout(() => {
            overlay.style.transition = 'opacity 0.3s ease';
            overlay.style.opacity = '1';
        }, 10);

        showStep(0);
    }

    // Add "Replay Tour" button to dashboard (optional)
    window.replayOnboardingTour = function() {
        localStorage.removeItem('parentDashboardOnboarding');
        sessionStorage.removeItem('isFirstDashboardVisit');
        startOnboarding();
    };

})();
