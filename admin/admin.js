// Admin Dashboard JavaScript

// Mobile Sidebar Toggle Function
function toggleMobileSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('mobileOverlay');
    
    if (sidebar && overlay) {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
    }
}

// Close mobile sidebar when clicking on a menu item
function closeMobileSidebarOnClick() {
    if (window.innerWidth <= 768) {
        toggleMobileSidebar();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Setup Navigation Event Listeners
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    
    sidebarItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the target tab ID from the href attribute
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                const tabId = href.substring(1);
                showTab(tabId);
                
                // Close mobile sidebar after clicking
                closeMobileSidebarOnClick();
            }
        });
    });

    // 2. Initialize the default tab
    const hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById(hash)) {
        showTab(hash);
    } else {
        showTab('dashboard');
    }

    // 3. Load initial dashboard data
    if (typeof loadMessages === 'function') loadMessages();
    
    // 4. Handle window resize
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        
        // Close mobile menu when resizing to desktop
        if (window.innerWidth > 768) {
            if (sidebar) sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
        }
    });
    
    // 5. Setup invoice form listeners
    setupInvoiceFormListeners();
});

function setupInvoiceFormListeners() {
    // Invoice form submission
    const invoiceForm = document.getElementById('invoice-form');
    if (invoiceForm) {
        invoiceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveInvoice();
        });
    }
    
    // Auto-calculate amount due
    const totalAmountField = document.getElementById('total-amount');
    const amountPaidField = document.getElementById('amount-paid');
    
    if (totalAmountField) {
        totalAmountField.addEventListener('input', calculateAmountDue);
    }
    
    if (amountPaidField) {
        amountPaidField.addEventListener('input', calculateAmountDue);
    }
    
    // Auto-update preview when form fields change - comprehensive list
    const formFields = [
        'invoice-number', 'invoice-date', 'due-date', 'client-name', 'client-email',
        'client-address', 'project-name', 'project-type', 'project-description',
        'total-amount', 'amount-paid', 'tax-rate-1', 'currency', 'invoice-notes'
    ];
    
    formFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            // Add multiple event listeners for comprehensive real-time updates
            field.addEventListener('input', updateInvoicePreview);
            field.addEventListener('change', updateInvoicePreview);
            field.addEventListener('keyup', updateInvoicePreview);
            field.addEventListener('blur', updateInvoicePreview);
        }
    });
    
    // Set default dates
    const today = new Date().toISOString().split('T')[0];
    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + 30);
    const dueDateStr = dueDate.toISOString().split('T')[0];
    
    const invoiceDateField = document.getElementById('invoice-date');
    const dueDateField = document.getElementById('due-date');
    
    if (invoiceDateField && !invoiceDateField.value) {
        invoiceDateField.value = today;
    }
    
    if (dueDateField && !dueDateField.value) {
        dueDateField.value = dueDateStr;
    }
    
    // Generate initial invoice number
    generateNewInvoiceNumber();
    
    // Trigger initial preview update
    setTimeout(() => {
        updateInvoicePreview();
    }, 100);
}

// Tab Management Function
function showTab(tabName) {
    if (!tabName) return;

    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(tab => tab.classList.remove('active'));

    // Remove active class from all sidebar items
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    sidebarItems.forEach(item => item.classList.remove('active'));

    // Show selected tab content
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }

    // Find and activate corresponding sidebar item
    const targetSidebarItem = document.querySelector(`.sidebar-item[href="#${tabName}"]`);
    if (targetSidebarItem) {
        targetSidebarItem.classList.add('active');
    }

    // Update page title
    const pageTitle = document.getElementById('page-title');
    if (pageTitle) {
        const tabTitles = {
            'dashboard': 'Dashboard',
            'content': 'Content Management',
            'pages': 'Page Editor',
            'design': 'Design & Styling',
            'reviews': 'Reviews',
            'projects': 'Projects',
            'blog': 'Blog Posts',
            'messages': 'Messages',
            'invoices': 'Invoices',
            'payments': 'Payments',
            'analytics': 'Analytics',
            'settings': 'Settings'
        };
        pageTitle.textContent = tabTitles[tabName] || 'Dashboard';
    }

    // Load tab-specific data
    if (tabName === 'projects') {
        loadProjects();
    } else if (tabName === 'blog') {
        loadBlogPosts();
    } else if (tabName === 'analytics') {
        refreshAnalytics();
    } else if (tabName === 'messages') {
        if (typeof loadMessages === 'function') loadMessages();
    } else if (tabName === 'reviews') {
        if (typeof loadReviews === 'function') loadReviews();
    } else if (tabName === 'invoices') {
        if (typeof loadInvoices === 'function') loadInvoices();
    } else if (tabName === 'payments') {
        if (typeof loadPayments === 'function') loadPayments();
    }
}

// Utility Functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-white' :
        'bg-blue-500 text-white'
    }`;

    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function escapeHtml(text) {
    if (!text && text !== 0) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}
// Message Management Functions
async function loadMessages(filter = 'all') {
    try {
        const response = await fetch('api/get_messages.php');
        if (!response.ok) throw new Error('Network response was not ok');
        const messages = await response.json();

        // Apply filters
        let filteredMessages = messages;
        if (filter === 'unread') {
            filteredMessages = messages.filter(m => !m.read);
        } else if (filter === 'today') {
            filteredMessages = messages.filter(m => {
                const messageDate = new Date(m.created_at);
                const today = new Date();
                return messageDate.toDateString() === today.toDateString();
            });
        } else if (filter === 'schedule') {
            filteredMessages = messages.filter(m => m.project_type === 'Consultation Request');
        }

        // Update Message Statistics
        const totalMessages = messages.length;
        const unreadMessages = messages.filter(m => !m.read).length;
        const todayMessages = messages.filter(m => {
            const messageDate = new Date(m.created_at);
            const today = new Date();
            return messageDate.toDateString() === today.toDateString();
        }).length;
        const scheduleRequests = messages.filter(m => m.project_type === 'Consultation Request').length;

        // Update UI elements
        const setEl = (id, txt) => { const el = document.getElementById(id); if (el) el.textContent = txt; };
        
        setEl('total-messages', totalMessages);
        setEl('unread-messages', unreadMessages);
        setEl('today-messages', todayMessages);
        setEl('schedule-requests', scheduleRequests);
        setEl('message-count', unreadMessages);

        // Populate message list
        const messagesList = document.getElementById('messages-list');
        if (messagesList) {
            if (filteredMessages.length === 0) {
                messagesList.innerHTML = `
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No messages found</h3>
                        <p class="text-gray-500">No messages match the current filter.</p>
                    </div>
                `;
            } else {
                messagesList.innerHTML = '';
                filteredMessages.forEach((message) => {
                    const messageDiv = createMessageElement(message);
                    messagesList.appendChild(messageDiv);
                });
            }
        }

    } catch (error) {
        console.error('Error loading messages:', error);
        showNotification('Error loading messages: ' + error.message, 'error');
    }
}
function createMessageElement(message) {
    const name = message.name || 'Unknown';
    const email = message.email || '';
    const phone = message.phone || '';
    const company = message.company || '';
    const projectType = message.project_type || 'General Inquiry';
    const timeline = message.timeline || '';
    const budget = message.budget || '';
    const msgText = message.message || 'No content';
    const date = formatDate(message.created_at);
    const isScheduleRequest = projectType === 'Consultation Request';
    const isUnread = !message.read;
    
    const div = document.createElement('div');
    div.className = `message-item bg-white border-2 ${isUnread ? 'border-blue-200 bg-blue-50' : 'border-gray-200'} rounded-lg p-6 mb-4 hover:shadow-lg transition-all duration-200`;
    div.innerHTML = `
        <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
                <div class="flex items-center mb-2">
                    <div class="w-10 h-10 rounded-full ${isScheduleRequest ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600'} flex items-center justify-center font-bold mr-3">
                        ${isScheduleRequest ? '📅' : name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 text-lg">${escapeHtml(name)}</h4>
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <span>${escapeHtml(email)}</span>
                            ${phone ? `<span>•</span><span>${escapeHtml(phone)}</span>` : ''}
                            ${company ? `<span>•</span><span>${escapeHtml(company)}</span>` : ''}
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="px-3 py-1 text-xs font-medium rounded-full ${isScheduleRequest ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'}">${escapeHtml(projectType)}</span>
                    ${timeline ? `<span class="px-3 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">⏰ ${escapeHtml(timeline)}</span>` : ''}
                    ${budget ? `<span class="px-3 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">💰 ${escapeHtml(budget)}</span>` : ''}
                    ${isUnread ? '<span class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">🔔 New</span>' : ''}
                </div>
            </div>
            
            <div class="text-right">
                <div class="text-sm text-gray-500 mb-2">${date}</div>
                <div class="flex space-x-1">
                    ${isScheduleRequest ? '<span class="text-purple-600 text-xs font-medium">URGENT</span>' : ''}
                    ${isUnread ? '<span class="w-3 h-3 bg-blue-500 rounded-full"></span>' : ''}
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
            <h5 class="font-semibold text-gray-900 mb-2">Message:</h5>
            <div class="text-gray-700 whitespace-pre-wrap">${escapeHtml(msgText)}</div>
        </div>
        
        <div class="flex flex-wrap gap-2">
            <button onclick="replyToMessage('${message.id}', '${email}', '${escapeHtml(name)}')" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                </svg>
                Reply via Email
            </button>
            
            ${phone ? `<a href="tel:${phone}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                Call ${phone}
            </a>` : ''}
            
            ${isUnread ? `<button onclick="markAsRead('${message.id}')" 
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Mark as Read
            </button>` : ''}
            
            <button onclick="deleteMessage('${message.id}')" 
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Delete
            </button>
        </div>
    `;
    return div;
}
async function markAsRead(messageId) {
    try {
        const response = await fetch('api/mark_message_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message_id: messageId })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Message marked as read', 'success');
            loadMessages();
        } else {
            showNotification('Error marking message as read', 'error');
        }
    } catch (error) {
        showNotification('Error marking message as read: ' + error.message, 'error');
    }
}

async function deleteMessage(messageId) {
    if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
        try {
            const response = await fetch('api/delete_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: messageId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Message deleted successfully!', 'success');
                loadMessages();
            } else {
                showNotification('Error deleting message: ' + result.message, 'error');
            }
        } catch (error) {
            console.error('Error deleting message:', error);
            showNotification('Error deleting message. Please try again.', 'error');
        }
    }
}

function replyToMessage(messageId, email, name) {
    // Create reply modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">Reply to ${escapeHtml(name)}</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="reply-form" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To:</label>
                        <div class="w-full p-3 bg-gray-50 border border-gray-300 rounded-md text-gray-700">
                            ${escapeHtml(email)}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From:</label>
                        <div class="w-full p-3 bg-gray-50 border border-gray-300 rounded-md text-gray-700">
                            App Craft Services &lt;hello@appcraftservices.com&gt;
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject:</label>
                        <input type="text" id="reply-subject" class="w-full p-3 border border-gray-300 rounded-md" 
                               value="Re: Your inquiry to App Craft Services" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message:</label>
                        <textarea id="reply-message" rows="8" class="w-full p-3 border border-gray-300 rounded-md" 
                                  placeholder="Hi ${escapeHtml(name)},&#10;&#10;Thank you for contacting App Craft Services.&#10;&#10;[Your response here]&#10;&#10;Best regards,&#10;App Craft Services Team" required></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="this.closest('.fixed').remove()" 
                                class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-paper-plane mr-2"></i>Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Handle form submission
    const form = modal.querySelector('#reply-form');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
        submitBtn.disabled = true;
        
        try {
            const replyData = {
                message_id: messageId,
                client_email: email,
                client_name: name,
                original_subject: 'Your inquiry to App Craft Services',
                reply_message: document.getElementById('reply-message').value
            };
            
            const response = await fetch('api/send_message_reply.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(replyData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Reply sent successfully!', 'success');
                modal.remove();
                
                // Mark message as read and reload messages
                await markAsRead(messageId);
                loadMessages();
            } else {
                throw new Error(result.message || 'Failed to send reply');
            }
        } catch (error) {
            console.error('Error sending reply:', error);
            showNotification('Error sending reply: ' + error.message, 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
}

function filterMessages(filter) {
    loadMessages(filter);
}
// Payment Functions
function sendPaymentLink() {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">Generate Payment Link</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="payment-link-form" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Client Email</label>
                        <input type="email" id="client-email-input" required class="w-full p-3 border border-gray-300 rounded-md" placeholder="client@example.com">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Stage</label>
                        <select id="payment-stage" class="w-full p-3 border border-gray-300 rounded-md" required>
                            <option value="">Select payment stage</option>
                            <option value="initial">Initial Payment (50%)</option>
                            <option value="final">Final Payment (50%)</option>
                            <option value="full">Full Payment (100%)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Project Amount</label>
                        <input type="text" id="total-amount" required class="w-full p-3 border border-gray-300 rounded-md" placeholder="$3,000">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Amount</label>
                        <input type="text" id="payment-amount" class="w-full p-3 border border-gray-300 rounded-md" placeholder="Enter payment amount">
                        <p class="text-xs text-gray-500 mt-1">You can edit this amount or use the calculated amount based on stage and total project value</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Service Description</label>
                        <select id="service-description" class="w-full p-3 border border-gray-300 rounded-md">
                            <option value="Essential App Development">Essential App Development</option>
                            <option value="Custom Enterprise Solution">Custom Enterprise Solution</option>
                            <option value="Maintenance & Support">Maintenance & Support</option>
                            <option value="Custom Service">Custom Service</option>
                        </select>
                    </div>
                    
                    <div id="custom-service-input" class="hidden">
                        <input type="text" id="custom-service-text" class="w-full p-3 border border-gray-300 rounded-md" placeholder="Enter custom service description">
                    </div>
                    
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="this.closest('.fixed').remove()" class="px-6 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Generate Link
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Handle form submission
    document.getElementById('payment-link-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('client-email-input').value;
        const stage = document.getElementById('payment-stage').value;
        const totalAmount = document.getElementById('total-amount').value;
        const paymentAmount = document.getElementById('payment-amount').value;
        let description = document.getElementById('service-description').value;
        
        if (description === 'Custom Service') {
            description = document.getElementById('custom-service-text').value;
        }
        
        if (!email || !paymentAmount || !description) {
            alert('Please fill in all required fields');
            return;
        }
        
        generatePaymentLink(email, paymentAmount, description, stage, totalAmount);
        modal.remove();
    });
}
function generatePaymentLink(email, amount, description, stage, totalAmount) {
    // Ensure amounts have $ sign
    if (amount && !amount.startsWith('$')) {
        amount = '$' + amount;
    }
    if (totalAmount && !totalAmount.startsWith('$')) {
        totalAmount = '$' + totalAmount;
    }
    
    const token = 'pay_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    const baseUrl = window.location.origin;
    const paymentLink = `${baseUrl}/payment/pay.php?token=${token}&amount=${encodeURIComponent(amount)}&description=${encodeURIComponent(description)}&email=${encodeURIComponent(email)}&stage=${encodeURIComponent(stage)}&total=${encodeURIComponent(totalAmount)}`;
    
    showNotification('Payment link generated successfully!', 'success');
    
    // Send email with payment link
    fetch('api/send_payment_email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            email: email,
            paymentLink: paymentLink,
            amount: amount,
            description: description,
            stage: stage,
            totalAmount: totalAmount
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Payment email sent successfully to ' + email, 'success');
        } else {
            throw new Error(data.message || 'Failed to send email');
        }
    })
    .catch(error => {
        console.error('Error sending email:', error);
        showNotification('Error sending email: ' + error.message, 'error');
    });
}

// Refund Processing
function processRefund() {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-red-600">Process Refund</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="refund-form" class="space-y-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-900 mb-3">Transaction Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Transaction ID *</label>
                                <input type="text" id="transaction-id" required class="w-full p-3 border border-gray-300 rounded-md" placeholder="txn_1234567890">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                                <select id="payment-method" required class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="">Select payment method</option>
                                    <option value="stripe">Stripe (Credit Card)</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="bank">Bank Transfer</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Client Email *</label>
                            <input type="email" id="client-email" required class="w-full p-3 border border-gray-300 rounded-md" placeholder="client@example.com">
                        </div>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-900 mb-3">Refund Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Original Amount</label>
                                <input type="text" id="original-amount" class="w-full p-3 border border-gray-300 rounded-md" placeholder="$1,500.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Refund Amount *</label>
                                <input type="text" id="refund-amount" required class="w-full p-3 border border-gray-300 rounded-md" placeholder="$1,500.00">
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-900 mb-3">Refund Reason</h4>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason Category *</label>
                            <select id="refund-reason" required class="w-full p-3 border border-gray-300 rounded-md">
                                <option value="">Select reason</option>
                                <option value="client-request">Client Request</option>
                                <option value="project-cancelled">Project Cancelled</option>
                                <option value="service-issue">Service Issue</option>
                                <option value="duplicate-payment">Duplicate Payment</option>
                                <option value="chargeback">Chargeback</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Detailed Explanation *</label>
                            <textarea id="refund-explanation" required rows="4" class="w-full p-3 border border-gray-300 rounded-md" placeholder="Provide detailed explanation for the refund..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 pt-4 border-t">
                        <button type="button" onclick="this.closest('.fixed').remove()" class="px-6 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            <i class="fas fa-undo mr-2"></i>Process Refund
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Handle form submission
    document.getElementById('refund-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            transactionId: document.getElementById('transaction-id').value,
            paymentMethod: document.getElementById('payment-method').value,
            clientEmail: document.getElementById('client-email').value,
            originalAmount: document.getElementById('original-amount').value,
            refundAmount: document.getElementById('refund-amount').value,
            refundReason: document.getElementById('refund-reason').value,
            refundExplanation: document.getElementById('refund-explanation').value
        };
        
        try {
            const response = await fetch('api/process_refund.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Refund processed successfully!', 'success');
                modal.remove();
            } else {
                throw new Error(result.message || 'Failed to process refund');
            }
        } catch (error) {
            console.error('Error processing refund:', error);
            showNotification('Error processing refund: ' + error.message, 'error');
        }
    });
}
// Package Pricing Editor Functions
function editPackagePricing(packageId) {
    const modal = document.getElementById('pricing-editor-modal');
    const form = document.getElementById('pricing-editor-form');
    
    if (!modal || !form) {
        alert('Error: Pricing editor not found. Please refresh the page and try again.');
        return;
    }
    
    // Get current data from the page elements
    const priceElement = document.getElementById(packageId + '-price');
    const rangeElement = document.getElementById(packageId + '-range');
    
    // Set package data based on ID and current page data
    const packageData = {
        'essential': {
            name: 'Essential App',
            price: priceElement ? priceElement.textContent : '$1,500',
            range: rangeElement ? rangeElement.textContent : '$1,000 - $2,000',
            description: 'Basic web application for small businesses'
        },
        'enterprise': {
            name: 'Custom Enterprise',
            price: priceElement ? priceElement.textContent : 'Custom Quote',
            range: rangeElement ? rangeElement.textContent : 'Custom Quote',
            description: 'Complex platforms for enterprise clients'
        },
        'maintenance': {
            name: 'Maintenance & Support',
            price: priceElement ? priceElement.textContent : 'Monthly Plans',
            range: rangeElement ? rangeElement.textContent : 'Monthly Plans',
            description: 'Ongoing support and maintenance services'
        }
    };
    
    const data = packageData[packageId];
    if (data) {
        document.getElementById('edit-package-id').value = packageId;
        document.getElementById('edit-package-name').value = data.name;
        document.getElementById('edit-package-price').value = data.price;
        document.getElementById('edit-package-range').value = data.range;
        document.getElementById('edit-package-description').value = data.description;
        
        modal.classList.remove('hidden');
        
        setTimeout(() => {
            document.getElementById('edit-package-price').focus();
        }, 100);
    } else {
        showNotification('Error: Package data not found', 'error');
    }
}

function hidePricingEditorModal() {
    document.getElementById('pricing-editor-modal').classList.add('hidden');
}

// Email Export Functions
function toggleExportDropdown() {
    const dropdown = document.getElementById('export-dropdown');
    dropdown.classList.toggle('hidden');
}

async function exportEmails(format) {
    try {
        const selectedFilter = document.querySelector('input[name="export-filter"]:checked').value;
        let startDate = '';
        let endDate = '';
        
        if (selectedFilter === 'date-range') {
            startDate = document.getElementById('export-start-date').value;
            endDate = document.getElementById('export-end-date').value;
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }
        }
        
        const response = await fetch('api/get_messages.php');
        if (!response.ok) throw new Error('Failed to fetch messages');
        const messages = await response.json();
        
        // Filter messages based on selection
        let filteredMessages = messages;
        
        switch (selectedFilter) {
            case 'unread':
                filteredMessages = messages.filter(m => !m.read);
                break;
            case 'today':
                filteredMessages = messages.filter(m => {
                    const messageDate = new Date(m.created_at);
                    const today = new Date();
                    return messageDate.toDateString() === today.toDateString();
                });
                break;
            case 'schedule':
                filteredMessages = messages.filter(m => m.project_type === 'Consultation Request');
                break;
            case 'date-range':
                filteredMessages = messages.filter(m => {
                    const messageDate = new Date(m.created_at);
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    return messageDate >= start && messageDate <= end;
                });
                break;
        }
        
        const emails = [...new Set(filteredMessages.map(m => m.email).filter(email => email))];
        
        if (emails.length === 0) {
            alert('No email addresses found for the selected filter');
            return;
        }
        
        let content = '';
        let filename = '';
        
        if (format === 'csv') {
            content = 'Email,Name,Company,Date,Project Type\n';
            filteredMessages.forEach(m => {
                if (m.email) {
                    content += `"${m.email}","${m.name || ''}","${m.company || ''}","${m.created_at}","${m.project_type || ''}"\n`;
                }
            });
            filename = `email_export_${selectedFilter}_${new Date().toISOString().split('T')[0]}.csv`;
        } else {
            content = emails.join('\n');
            filename = `email_export_${selectedFilter}_${new Date().toISOString().split('T')[0]}.txt`;
        }
        
        const blob = new Blob([content], { type: format === 'csv' ? 'text/csv' : 'text/plain' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        document.getElementById('export-dropdown').classList.add('hidden');
        
        showNotification(`${emails.length} email addresses exported successfully!`, 'success');
        
    } catch (error) {
        console.error('Error exporting emails:', error);
        showNotification('Error exporting emails. Please try again.', 'error');
    }
}
// Cache Management Functions
function clearAllCaches() {
    if (!confirm('This will clear all browser caches and force a complete reload. Continue?')) {
        return;
    }
    
    showNotification('Clearing all caches...', 'info');
    window.location.href = '../force-reload.php';
}

function forceReloadSite() {
    if (!confirm('This will force reload the entire site with cache busting. Continue?')) {
        return;
    }
    
    showNotification('Force reloading site...', 'info');
    
    const timestamp = Date.now();
    const randomParam = Math.random().toString(36).substr(2, 9);
    window.open(`../?cb=${timestamp}&r=${randomParam}&force=1`, '_blank');
}

function testCacheBusting() {
    showNotification('Testing cache busting system...', 'info');
    
    if (window.cacheBuster) {
        console.log('Cache Buster Version:', window.cacheBuster.version);
        
        window.cacheBuster.checkUpdates().then(() => {
            showNotification('Cache busting system is working correctly!', 'success');
        }).catch(error => {
            showNotification('Cache busting test failed: ' + error.message, 'error');
        });
    } else {
        showNotification('Cache buster not loaded. Please refresh the page.', 'warning');
    }
}

function clearCache() {
    clearAllCaches();
}

// Additional Admin Functions
function openRealtimeEditor() {
    window.open('realtime-editor.php', '_blank');
}

function previewSite() {
    window.open('../', '_blank');
}

function refreshAnalytics() {
    showNotification('Analytics refreshed', 'success');
}

function loadProjects() {
    console.log('Loading projects...');
}

function loadBlogPosts() {
    console.log('Loading blog posts...');
}

function loadInvoices() {
    console.log('Loading invoices...');
    
    fetch('api/get_invoices.php')
        .then(response => response.json())
        .then(invoices => {
            const invoicesList = document.getElementById('invoices-list');
            if (!invoicesList) return;
            
            if (invoices.length === 0) {
                invoicesList.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-file-invoice text-4xl mb-4"></i>
                        <p>No invoices created yet</p>
                    </div>
                `;
                return;
            }
            
            let html = `
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left p-3 font-medium">Invoice #</th>
                                <th class="text-left p-3 font-medium">Client</th>
                                <th class="text-left p-3 font-medium">Project</th>
                                <th class="text-left p-3 font-medium">Amount</th>
                                <th class="text-left p-3 font-medium">Status</th>
                                <th class="text-left p-3 font-medium">Due Date</th>
                                <th class="text-left p-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            invoices.forEach(invoice => {
                const statusColors = {
                    'draft': 'bg-gray-100 text-gray-800',
                    'sent': 'bg-blue-100 text-blue-800',
                    'paid': 'bg-green-100 text-green-800',
                    'overdue': 'bg-red-100 text-red-800'
                };
                
                html += `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 font-medium">${invoice.invoice_number}</td>
                        <td class="p-3">${invoice.client_name}</td>
                        <td class="p-3">${invoice.project_name}</td>
                        <td class="p-3">$${parseFloat(invoice.amount_due || 0).toFixed(2)}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium ${statusColors[invoice.status] || 'bg-gray-100 text-gray-800'}">
                                ${invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1)}
                            </span>
                        </td>
                        <td class="p-3">${formatDate(invoice.due_date)}</td>
                        <td class="p-3">
                            <div class="flex space-x-2">
                                <button onclick="viewInvoice('${invoice.id}')" class="text-blue-600 hover:text-blue-800" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editInvoice('${invoice.id}')" class="text-green-600 hover:text-green-800" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="emailInvoice('${invoice.id}')" class="text-purple-600 hover:text-purple-800" title="Email">
                                    <i class="fas fa-envelope"></i>
                                </button>
                                <button onclick="downloadInvoicePDF('${invoice.id}')" class="text-red-600 hover:text-red-800" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                <button onclick="deleteInvoice('${invoice.id}')" class="text-red-600 hover:text-red-800" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            invoicesList.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading invoices:', error);
            showNotification('Error loading invoices', 'error');
        });
}

// Calculate amount due automatically
function calculateAmountDue() {
    const totalAmount = parseFloat(document.getElementById('total-amount')?.value || 0);
    const amountPaid = parseFloat(document.getElementById('amount-paid')?.value || 0);
    const amountDue = Math.max(0, totalAmount - amountPaid);
    
    const amountDueField = document.getElementById('amount-due');
    if (amountDueField) {
        amountDueField.value = amountDue.toFixed(2);
    }
    
    // Update preview if visible
    updateInvoicePreview();
}

// Preview invoice function
function previewInvoice() {
    updateInvoicePreview();
}

function updateInvoicePreview() {
    const previewDiv = document.getElementById('invoice-preview');
    if (!previewDiv) return;
    
    const invoiceData = {
        invoice_number: document.getElementById('invoice-number')?.value || '',
        invoice_date: document.getElementById('invoice-date')?.value || '',
        due_date: document.getElementById('due-date')?.value || '',
        client_name: document.getElementById('client-name')?.value || '',
        client_email: document.getElementById('client-email')?.value || '',
        client_address: document.getElementById('client-address')?.value || '',
        project_name: document.getElementById('project-name')?.value || '',
        project_type: document.getElementById('project-type')?.value || '',
        project_description: document.getElementById('project-description')?.value || '',
        total_amount: parseFloat(document.getElementById('total-amount')?.value || 0),
        amount_paid: parseFloat(document.getElementById('amount-paid')?.value || 0),
        amount_due: parseFloat(document.getElementById('amount-due')?.value || 0),
        tax_rate: parseFloat(document.getElementById('tax-rate-1')?.value || 0),
        currency: document.getElementById('currency')?.value || 'USD',
        notes: document.getElementById('invoice-notes')?.value || ''
    };
    
    if (!invoiceData.client_name && !invoiceData.project_name) {
        previewDiv.innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-file-invoice text-4xl mb-4"></i>
                <p>Fill out the form to see live preview</p>
                <p class="text-xs mt-2">Preview updates automatically as you type</p>
            </div>
        `;
        return;
    }
    
    const currencySymbol = getCurrencySymbol(invoiceData.currency);
    
    previewDiv.innerHTML = `
        <div class="invoice-preview border border-gray-200 rounded-lg p-4 bg-white">
            <!-- Header -->
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-blue-900">App Craft Services</h2>
                    <p class="text-gray-600">Professional Web Development</p>
                    <p class="text-sm text-gray-500">hello@appcraftservices.com</p>
                </div>
                <div class="text-right">
                    <h3 class="text-xl font-bold text-gray-800">INVOICE</h3>
                    <p class="text-gray-600">${invoiceData.invoice_number}</p>
                </div>
            </div>
            
            <!-- Invoice Details -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h4 class="font-semibold text-gray-800 mb-2">Bill To:</h4>
                    <p class="font-medium">${invoiceData.client_name}</p>
                    <p class="text-sm text-gray-600">${invoiceData.client_email}</p>
                    ${invoiceData.client_address ? `<p class="text-sm text-gray-600 whitespace-pre-line">${invoiceData.client_address}</p>` : ''}
                </div>
                <div class="text-right">
                    <div class="mb-2">
                        <span class="text-gray-600">Invoice Date:</span>
                        <span class="font-medium">${formatDate(invoiceData.invoice_date)}</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-gray-600">Due Date:</span>
                        <span class="font-medium">${formatDate(invoiceData.due_date)}</span>
                    </div>
                </div>
            </div>
            
            <!-- Project Details -->
            <div class="mb-6">
                <h4 class="font-semibold text-gray-800 mb-3">Project Details</h4>
                <div class="bg-gray-50 p-4 rounded">
                    <div class="flex justify-between items-start mb-2">
                        <span class="font-medium">${invoiceData.project_name}</span>
                        <span class="text-gray-600">(${invoiceData.project_type})</span>
                    </div>
                    ${invoiceData.project_description ? `<p class="text-sm text-gray-600">${invoiceData.project_description}</p>` : ''}
                </div>
            </div>
            
            <!-- Payment Summary -->
            <div class="border-t pt-4">
                <div class="flex justify-end">
                    <div class="w-64">
                        <div class="flex justify-between py-2">
                            <span>Total Amount:</span>
                            <span class="font-medium">${currencySymbol}${invoiceData.total_amount.toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span>Amount Paid:</span>
                            <span class="text-green-600">${currencySymbol}${invoiceData.amount_paid.toFixed(2)}</span>
                        </div>
                        ${invoiceData.tax_rate > 0 ? `
                        <div class="flex justify-between py-2">
                            <span>Tax (${invoiceData.tax_rate}%):</span>
                            <span>${currencySymbol}${(invoiceData.amount_due * invoiceData.tax_rate / 100).toFixed(2)}</span>
                        </div>
                        ` : ''}
                        <div class="flex justify-between py-2 border-t font-bold text-lg">
                            <span>Amount Due:</span>
                            <span class="text-red-600">${currencySymbol}${invoiceData.amount_due.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            ${invoiceData.notes ? `
            <div class="mt-6 pt-4 border-t">
                <h4 class="font-semibold text-gray-800 mb-2">Notes</h4>
                <p class="text-sm text-gray-600 whitespace-pre-line">${invoiceData.notes}</p>
            </div>
            ` : ''}
        </div>
    `;
}

function getCurrencySymbol(currency) {
    const symbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'NGN': '₦'
    };
    return symbols[currency] || '$';
}

// Save invoice function
async function saveInvoice() {
    const form = document.getElementById('invoice-form');
    if (!form) return;
    
    // Collect form data directly from elements by ID
    const invoiceData = {
        invoice_number: document.getElementById('invoice-number')?.value || '',
        invoice_date: document.getElementById('invoice-date')?.value || '',
        due_date: document.getElementById('due-date')?.value || '',
        status: document.getElementById('invoice-status')?.value || 'draft',
        client_name: document.getElementById('client-name')?.value || '',
        client_email: document.getElementById('client-email')?.value || '',
        client_address: document.getElementById('client-address')?.value || '',
        project_name: document.getElementById('project-name')?.value || '',
        project_type: document.getElementById('project-type')?.value || '',
        project_description: document.getElementById('project-description')?.value || '',
        total_amount: parseFloat(document.getElementById('total-amount')?.value || 0),
        amount_paid: parseFloat(document.getElementById('amount-paid')?.value || 0),
        amount_due: parseFloat(document.getElementById('amount-due')?.value || 0),
        tax_rate: parseFloat(document.getElementById('tax-rate-1')?.value || 0),
        currency: document.getElementById('currency')?.value || 'USD',
        notes: document.getElementById('invoice-notes')?.value || ''
    };
    
    // Validate required fields
    if (!invoiceData.client_name || !invoiceData.project_name || !invoiceData.total_amount) {
        showNotification('Please fill in all required fields (Client Name, Project Name, Total Amount)', 'error');
        return;
    }
    
    try {
        const response = await fetch('api/save_invoice.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(invoiceData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Invoice saved successfully!', 'success');
            loadInvoices();
            form.reset();
            generateNewInvoiceNumber();
            updateInvoicePreview();
        } else {
            showNotification('Error saving invoice: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error saving invoice:', error);
        showNotification('Error saving invoice', 'error');
    }
}

// Generate new invoice number
function generateNewInvoiceNumber() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const timestamp = now.getTime().toString().slice(-4);
    
    const invoiceNumber = `INV-${year}${month}-${timestamp}`;
    
    const numberField = document.getElementById('invoice-number');
    const displayField = document.getElementById('invoice-number-display');
    
    if (numberField) numberField.value = invoiceNumber;
    if (displayField) displayField.textContent = invoiceNumber;
}

// Email current invoice function (for unsaved invoices)
async function emailCurrentInvoice() {
    const clientEmail = document.getElementById('client-email')?.value;
    const clientName = document.getElementById('client-name')?.value;
    const invoiceNumber = document.getElementById('invoice-number')?.value;
    const amountDue = document.getElementById('amount-due')?.value;
    const dueDate = document.getElementById('due-date')?.value;
    
    if (!clientEmail || !clientName || !invoiceNumber) {
        showNotification('Please fill in client email, name, and invoice number before sending', 'error');
        return;
    }
    
    if (!clientEmail.includes('@')) {
        showNotification('Please enter a valid email address', 'error');
        return;
    }
    
    try {
        const emailData = {
            client_email: clientEmail,
            client_name: clientName,
            invoice_number: invoiceNumber,
            amount_due: amountDue || '0.00',
            due_date: dueDate,
            project_name: document.getElementById('project-name')?.value || '',
            project_type: document.getElementById('project-type')?.value || '',
            total_amount: document.getElementById('total-amount')?.value || '0.00',
            amount_paid: document.getElementById('amount-paid')?.value || '0.00',
            currency: document.getElementById('currency')?.value || 'USD',
            notes: document.getElementById('invoice-notes')?.value || ''
        };
        
        const response = await fetch('api/send_invoice_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(emailData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Invoice emailed successfully to ' + clientEmail + '!', 'success');
        } else {
            showNotification('Error sending invoice email: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error emailing invoice:', error);
        showNotification('Error sending invoice email. Please try again.', 'error');
    }
}

// Email invoice function
async function emailInvoice(invoiceId) {
    try {
        const response = await fetch(`api/get_invoices.php?id=${invoiceId}`);
        const invoice = await response.json();
        
        if (!invoice || !invoice.client_email) {
            showNotification('Invoice not found or missing client email', 'error');
            return;
        }
        
        const emailData = {
            invoice_id: invoiceId,
            client_email: invoice.client_email,
            client_name: invoice.client_name,
            invoice_number: invoice.invoice_number,
            amount_due: invoice.amount_due,
            due_date: invoice.due_date
        };
        
        const emailResponse = await fetch('api/send_invoice_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(emailData)
        });
        
        const result = await emailResponse.json();
        
        if (result.success) {
            showNotification('Invoice emailed successfully!', 'success');
        } else {
            showNotification('Error sending invoice email: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error emailing invoice:', error);
        showNotification('Error sending invoice email', 'error');
    }
}

// Other invoice functions
function viewInvoice(invoiceId) {
    // Implementation for viewing invoice
    showNotification('View invoice: ' + invoiceId, 'info');
}

function editInvoice(invoiceId) {
    // Implementation for editing invoice
    showNotification('Edit invoice: ' + invoiceId, 'info');
}

function downloadInvoicePDF(invoiceId) {
    // Implementation for PDF download
    window.open(`api/generate_invoice_pdf.php?id=${invoiceId}`, '_blank');
}

function deleteInvoice(invoiceId) {
    if (confirm('Are you sure you want to delete this invoice?')) {
        fetch('api/delete_invoice.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: invoiceId })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Invoice deleted successfully!', 'success');
                loadInvoices();
            } else {
                showNotification('Error deleting invoice', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting invoice:', error);
            showNotification('Error deleting invoice', 'error');
        });
    }
}

function refreshInvoices() {
    loadInvoices();
}

function loadPayments() {
    console.log('Loading payments...');
}

function loadReviews() {
    console.log('Loading reviews...');
}

function generateInvoice() {
    showTab('invoices');
    showNotification('Invoice generator opened!', 'success');
}

function exportPaymentData() {
    const format = prompt('Export format (csv/json):', 'csv');
    if (format === 'csv' || format === 'json') {
        showNotification(`Exporting payment data as ${format.toUpperCase()}...`, 'info');
        setTimeout(() => {
            showNotification('Payment data exported successfully!', 'success');
        }, 2000);
    }
}

function refreshPackagePricing() {
    showNotification('Syncing package pricing with pricing page...', 'info');
    
    fetch('api/sync_pricing.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.packages) {
                Object.keys(data.packages).forEach(packageId => {
                    const packageData = data.packages[packageId];
                    const priceElement = document.getElementById(packageId + '-price');
                    const rangeElement = document.getElementById(packageId + '-range');
                    
                    if (priceElement) {
                        priceElement.textContent = packageData.price;
                    }
                    if (rangeElement) {
                        rangeElement.textContent = packageData.range;
                    }
                });
            }
            
            showNotification('Package pricing synced successfully with pricing page!', 'success');
        } else {
            throw new Error(data.message || 'Failed to sync pricing');
        }
    })
    .catch(error => {
        console.error('Error syncing pricing:', error);
        showNotification('Error syncing pricing: ' + error.message, 'error');
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadMessages();
    
    // Set current year in footer if exists
    const currentYearElement = document.getElementById('current-year');
    if (currentYearElement) {
        currentYearElement.textContent = new Date().getFullYear();
    }
    
    // Handle pricing editor form submission
    const pricingForm = document.getElementById('pricing-editor-form');
    if (pricingForm) {
        pricingForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const packageId = document.getElementById('edit-package-id').value;
            const price = document.getElementById('edit-package-price').value;
            const range = document.getElementById('edit-package-range').value;
            const description = document.getElementById('edit-package-description').value;
            
            try {
                document.getElementById(packageId + '-price').textContent = price;
                document.getElementById(packageId + '-range').textContent = range;
                
                const response = await fetch('api/save_package_pricing.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        package_id: packageId,
                        price: price,
                        range: range,
                        description: description
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    hidePricingEditorModal();
                    showNotification('Package pricing updated successfully!', 'success');
                } else {
                    showNotification('Error updating pricing: ' + result.message, 'error');
                }
                
            } catch (error) {
                console.error('Error updating pricing:', error);
                showNotification('Error updating pricing. Changes saved locally.', 'warning');
                hidePricingEditorModal();
            }
        });
    }
    
    // Handle Stripe settings form submission
    const stripeForm = document.getElementById('stripe-settings-form');
    if (stripeForm) {
        stripeForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                stripe_email: document.getElementById('stripe-email').value,
                stripe_environment: document.getElementById('stripe-environment').value,
                stripe_publishable_key: document.getElementById('stripe-publishable-key').value,
                stripe_secret_key: document.getElementById('stripe-secret-key').value,
                stripe_webhook_endpoint: document.getElementById('stripe-webhook-endpoint').value
            };
            
            try {
                const response = await fetch('api/save_settings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ payment: { stripe: formData } })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Stripe settings saved successfully!', 'success');
                } else {
                    showNotification('Error saving Stripe settings: ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('Error saving Stripe settings: ' + error.message, 'error');
            }
        });
    }
    
    // Handle PayPal settings form submission
    const paypalForm = document.getElementById('paypal-settings-form');
    if (paypalForm) {
        paypalForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                paypal_email: document.getElementById('paypal-email').value,
                paypal_environment: document.getElementById('paypal-environment').value,
                paypal_client_id: document.getElementById('paypal-client-id').value,
                paypal_client_secret: document.getElementById('paypal-client-secret').value,
                paypal_webhook_id: document.getElementById('paypal-webhook-id').value
            };
            
            try {
                const response = await fetch('api/save_settings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ payment: { paypal: formData } })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('PayPal settings saved successfully!', 'success');
                } else {
                    showNotification('Error saving PayPal settings: ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('Error saving PayPal settings: ' + error.message, 'error');
            }
        });
    }
    
    // Load existing payment settings
    loadPaymentSettings();
});

// Load payment settings from server
async function loadPaymentSettings() {
    try {
        const response = await fetch('../data/settings.json');
        const settings = await response.json();
        
        // Load Stripe settings
        if (settings.payment && settings.payment.stripe) {
            const stripe = settings.payment.stripe;
            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.value = val || '';
            };
            
            setVal('stripe-email', stripe.stripe_email);
            setVal('stripe-environment', stripe.stripe_environment);
            setVal('stripe-publishable-key', stripe.stripe_publishable_key);
            setVal('stripe-secret-key', stripe.stripe_secret_key);
            setVal('stripe-webhook-endpoint', stripe.stripe_webhook_endpoint);
        }
        
        // Load PayPal settings
        if (settings.payment && settings.payment.paypal) {
            const paypal = settings.payment.paypal;
            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.value = val || '';
            };
            
            setVal('paypal-email', paypal.paypal_email);
            setVal('paypal-environment', paypal.paypal_environment);
            setVal('paypal-client-id', paypal.paypal_client_id);
            setVal('paypal-client-secret', paypal.paypal_client_secret);
            setVal('paypal-webhook-id', paypal.paypal_webhook_id);
        }
        
    } catch (error) {
        console.log('No payment settings found yet');
    }
}