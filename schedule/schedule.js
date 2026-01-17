// Schedule Direct Functionality
document.addEventListener('DOMContentLoaded', function() {
    const scheduleForm = document.getElementById('schedule-request-form');
    const successMessage = document.getElementById('schedule-success-message');
    
    if (scheduleForm) {
        scheduleForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('schedule-submit-btn');
            const submitText = document.getElementById('schedule-submit-text');
            const loadingIcon = document.getElementById('schedule-loading-icon');
            
            // Show loading state
            submitBtn.disabled = true;
            submitText.textContent = 'Sending Request...';
            loadingIcon.classList.remove('hidden');
            
            try {
                // Collect form data
                const formData = new FormData(scheduleForm);
                const data = {};
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }
                
                // Add scheduling-specific data
                data.type = 'schedule_request';
                data.message = `Schedule Request Details:
                
Preferred Date: ${data.preferred_date}
Preferred Time: ${data.preferred_time}
Project Description: ${data.project_description || 'Not provided'}

Please contact me to confirm the consultation time.`;
                
                console.log('Submitting schedule request:', data);
                
                // Send to contact API
                const response = await fetch('../api/contact.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                console.log('Response status:', response.status);
                
                const text = await response.text();
                console.log('Response text:', text);
                
                let result;
                try {
                    result = JSON.parse(text);
                } catch (err) {
                    console.error('Server returned non-JSON:', text);
                    throw new Error('Server returned an invalid response format.');
                }
                
                console.log('Parsed result:', result);
                
                if (result.success) {
                    // Track conversion with Google Analytics
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'conversion', {
                            'send_to': 'AW-17861189621/T_F2CMGFjOAbEPW_8MRC',
                            'value': 1.0,
                            'currency': 'USD'
                        });
                        
                        // Track as a custom event
                        gtag('event', 'schedule_consultation', {
                            'event_category': 'engagement',
                            'event_label': 'consultation_request',
                            'value': 1
                        });
                    }
                    
                    // Hide form and show success message
                    scheduleForm.style.display = 'none';
                    successMessage.classList.remove('hidden');
                    successMessage.innerHTML = `
                        <div class="flex items-center justify-center flex-col text-center p-6">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Consultation Request Sent!</h3>
                            <p class="text-gray-600 mb-2">Thank you for your interest! We'll contact you within 24 hours to confirm your consultation time.</p>
                            <p class="text-sm text-gray-500 mb-4">We'll send you a calendar invite once the time is confirmed.</p>
                            <p class="text-xs text-gray-400 mb-4">Request ID: ${result.message_id || 'N/A'}</p>
                            <div class="flex gap-4">
                                <button onclick="location.reload()" class="bg-electric-blue text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300 font-medium">Schedule Another</button>
                                <a href="/contact" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition duration-300 font-medium">Send Message</a>
                            </div>
                        </div>
                    `;
                } else {
                    throw new Error(result.message || 'Failed to send schedule request');
                }
                
            } catch (error) {
                console.error('Schedule Request Error:', error);
                
                // Show error message
                let errorDiv = document.getElementById('schedule-error-message');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.id = 'schedule-error-message';
                    errorDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg';
                    scheduleForm.parentNode.insertBefore(errorDiv, scheduleForm.nextSibling);
                }
                
                errorDiv.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span><strong>Error:</strong> ${error.message}</span>
                    </div>
                    <button onclick="this.parentElement.style.display='none'" class="mt-2 text-sm text-red-600 hover:text-red-800 underline">Dismiss</button>
                `;
                errorDiv.style.display = 'block';
                
                // Reset button state
                submitBtn.disabled = false;
                submitText.textContent = 'Request Consultation';
                loadingIcon.classList.add('hidden');
            }
        });
    }
});

// Calendar Setup Modal
function showCalendarSetup() {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-navy">Calendar Integration Setup</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                </div>
                
                <div class="space-y-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-navy mb-3">📅 Recommended: Calendly Integration</h4>
                        <p class="text-gray-600 mb-4">Calendly provides the best booking experience with automatic confirmations and reminders.</p>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-blue-600 mr-2">1.</span>
                                <span>Create a free Calendly account at <a href="https://calendly.com" target="_blank" class="text-blue-600 underline">calendly.com</a></span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-blue-600 mr-2">2.</span>
                                <span>Set up a "30-minute consultation" event type</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-blue-600 mr-2">3.</span>
                                <span>Copy your Calendly embed code</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-blue-600 mr-2">4.</span>
                                <span>Contact us to integrate it into this page</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-navy mb-3">🗓️ Alternative: Google Calendar</h4>
                        <p class="text-gray-600 mb-4">Use Google Calendar's appointment scheduling feature.</p>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-blue-600 mr-2">1.</span>
                                <span>Enable appointment scheduling in Google Calendar</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-blue-600 mr-2">2.</span>
                                <span>Create a booking page for consultations</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-blue-600 mr-2">3.</span>
                                <span>Share the booking link with us for integration</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-navy mb-3">🛠️ Need Help Setting Up?</h4>
                        <p class="text-gray-600 mb-4">We can help you set up calendar integration for free as part of our service.</p>
                        <div class="flex gap-4">
                            <a href="mailto:hello@appcraftservices.com?subject=Calendar Integration Setup" class="bg-electric-blue text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                                Email for Setup Help
                            </a>
                            <a href="/contact" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition duration-300">
                                Contact Form
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Set minimum date to today
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.querySelector('input[name="preferred_date"]');
    if (dateInput) {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        dateInput.min = tomorrow.toISOString().split('T')[0];
    }
});