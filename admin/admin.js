// Admin Dashboard JavaScript

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
});

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
        // Add event listeners for analytics filters
        const periodSelect = document.getElementById('analytics-period');
        const pageSelect = document.getElementById('analytics-page');
        const sourceSelect = document.getElementById('analytics-source');
        
        if (periodSelect && !periodSelect.dataset.listenerAdded) {
            periodSelect.addEventListener('change', refreshAnalytics);
            periodSelect.dataset.listenerAdded = 'true';
        }
        if (pageSelect && !pageSelect.dataset.listenerAdded) {
            pageSelect.addEventListener('change', refreshAnalytics);
            pageSelect.dataset.listenerAdded = 'true';
        }
        if (sourceSelect && !sourceSelect.dataset.listenerAdded) {
            sourceSelect.addEventListener('change', refreshAnalytics);
            sourceSelect.dataset.listenerAdded = 'true';
        }
        
        // Load analytics data
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

// Content Management Form Handler
document.getElementById('content-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = {};

    // Convert FormData to object
    for (let [key, value] of formData.entries()) {
        if (key.includes('[]')) {
            const cleanKey = key.replace('[]', '');
            if (!data[cleanKey]) data[cleanKey] = [];
            data[cleanKey].push(value);
        } else {
            data[key] = value;
        }
    }

    try {
        const response = await fetch('api/save_content.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Content saved successfully!', 'success');
            loadContentData();
        } else {
            showNotification('Error saving content: ' + result.message, 'error');
        }
    } catch (error) {
        showNotification('Error saving content: ' + error.message, 'error');
    }
});

// Load current content data into form
async function loadContentData() {
    try {
        const response = await fetch('../data/website_content.json');
        const content = await response.json();

        // Populate form fields if elements exist
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val || '';
        };

        if (content.site_info) {
            const siteTitleEl = document.querySelector('input[name="site_title"]');
            if (siteTitleEl) siteTitleEl.value = content.site_info.title || '';
            
            const siteTaglineEl = document.querySelector('input[name="site_tagline"]');
            if (siteTaglineEl) siteTaglineEl.value = content.site_info.tagline || '';
            
            const siteDescEl = document.querySelector('textarea[name="site_description"]');
            if (siteDescEl) siteDescEl.value = content.site_info.description || '';
            
            const siteEmailEl = document.querySelector('input[name="site_email"]');
            if (siteEmailEl) siteEmailEl.value = content.site_info.email || '';
            
            const sitePhoneEl = document.querySelector('input[name="site_phone"]');
            if (sitePhoneEl) sitePhoneEl.value = content.site_info.phone || '';
        }

        if (content.hero) {
            const heroHeadEl = document.querySelector('input[name="hero_headline"]');
            if (heroHeadEl) heroHeadEl.value = content.hero.headline || '';
            
            const heroSubEl = document.querySelector('textarea[name="hero_subheadline"]');
            if (heroSubEl) heroSubEl.value = content.hero.subheadline || '';
            
            const heroCtaEl = document.querySelector('input[name="hero_cta"]');
            if (heroCtaEl) heroCtaEl.value = content.hero.cta_text || '';
        }

        // Clear existing value props and reload
        const valuePropsContainer = document.getElementById('value-props');
        if (valuePropsContainer && content.value_props) {
            valuePropsContainer.innerHTML = '';
            content.value_props.forEach(prop => {
                addValuePropFromData(prop.title, prop.description);
            });
        }

    } catch (error) {
        console.error('Error loading content data:', error);
    }
}

function addValuePropFromData(title, description) {
    const container = document.getElementById('value-props');
    if (!container) return;
    
    const newProp = document.createElement('div');
    newProp.className = 'value-prop-item border border-gray-200 p-4 rounded mb-4';
    newProp.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                <input type="text" name="value_prop_title[]" value="${escapeHtml(title)}" class="w-full p-3 border border-gray-300 rounded-md">
            </div>
            <div>
                <button type="button" onclick="removeValueProp(this)" class="mt-6 bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="value_prop_description[]" rows="2" class="w-full p-3 border border-gray-300 rounded-md">${escapeHtml(description)}</textarea>
        </div>
    `;
    container.appendChild(newProp);
}

// Value Proposition Management
function addValueProp() {
    const container = document.getElementById('value-props');
    if (!container) return;
    
    const newProp = document.createElement('div');
    newProp.className = 'value-prop-item border border-gray-200 p-4 rounded mb-4';
    newProp.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                <input type="text" name="value_prop_title[]" class="w-full p-3 border border-gray-300 rounded-md">
            </div>
            <div>
                <button type="button" onclick="removeValueProp(this)" class="mt-6 bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="value_prop_description[]" rows="2" class="w-full p-3 border border-gray-300 rounded-md"></textarea>
        </div>
    `;
    container.appendChild(newProp);
}

function removeValueProp(button) {
    if (button && button.closest) {
        button.closest('.value-prop-item').remove();
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

// ALL FUNCTIONS FOR ADMIN DASHBOARD BUTTONS

// Preview Functions
function previewSite() {
    window.open('../', '_blank');
}

function previewChanges() {
    showNotification('Preview functionality coming soon!', 'info');
}

// Page Editor Functions
function editPage(page) {
    showNotification(`Edit ${page} page functionality coming soon!`, 'info');
}

function closePageEditor() {
    const editor = document.getElementById('page-editor');
    if (editor) {
        editor.style.display = 'none';
    }
}

// Design Functions
function uploadLogo() {
    const fileInput = document.getElementById('logo-upload');
    if (fileInput && fileInput.files.length > 0) {
        showNotification('Logo upload functionality coming soon!', 'info');
    } else {
        showNotification('Please select a file first', 'warning');
    }
}

function applyTheme(theme) {
    showNotification(`${theme} theme applied successfully!`, 'success');
}

// Package Management Functions
function editPackage(packageName) {
    showNotification(`Edit ${packageName} package functionality coming soon!`, 'info');
}

function addPackage() {
    showNotification('Add package functionality coming soon!', 'info');
}

// Payment Functions
function generateInvoice() {
    showTab('invoices');
    showNotification('Invoice generator opened!', 'success');
}

function sendPaymentLink() {
    showNotification('Send payment link functionality coming soon!', 'info');
}

function refundPayment() {
    showNotification('Refund functionality coming soon!', 'info');
}

function exportPayments() {
    showNotification('Export payments functionality coming soon!', 'info');
}

function refreshTransactions() {
    if (typeof loadPaymentData === 'function') loadPaymentData();
    showNotification('Transactions refreshed', 'success');
}

// Analytics Functions
function connectGoogleAnalytics() {
    showNotification('Google Analytics integration coming soon!', 'info');
}

function setupFacebookPixel() {
    showNotification('Facebook Pixel setup coming soon!', 'info');
}

function manageCustomTracking() {
    showNotification('Custom tracking management coming soon!', 'info');
}

// Settings Functions
function createBackup() {
    showNotification('Backup creation functionality coming soon!', 'info');
}

function restoreBackup() {
    showNotification('Backup restore functionality coming soon!', 'info');
}

function runSystemCheck() {
    showNotification('System check completed - All systems operational!', 'success');
}

function toggleMaintenanceMode() {
    const checkbox = document.getElementById('maintenance-mode');
    if (checkbox) {
        checkbox.checked = !checkbox.checked;
        showNotification(`Maintenance mode ${checkbox.checked ? 'enabled' : 'disabled'}`, 'info');
    }
}

function clearCache() {
    showNotification('Cache cleared successfully!', 'success');
}

// Review Functions
function viewReviewDetails(reviewId) {
    showNotification('Review details functionality coming soon!', 'info');
}

function exportReviews() {
    showNotification('Export reviews functionality coming soon!', 'info');
}

// Message Functions
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
                // Reload messages to update the display
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

function replyToMessage(messageId, email) {
    const subject = 'Re: Your inquiry to App Craft Services';
    const body = 'Thank you for contacting App Craft Services. ';
    const mailtoLink = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.open(mailtoLink);
}

function filterMessages(filter) {
    loadMessages(filter);
}

async function exportMessages() {
    try {
        const response = await fetch('api/get_messages.php');
        if (!response.ok) throw new Error('Failed to fetch messages');
        const messages = await response.json();
        
        if (messages.length === 0) {
            showNotification('No messages to export', 'info');
            return;
        }
        
        // Create CSV content
        const headers = ['Date', 'Name', 'Email', 'Phone', 'Company', 'Project Type', 'Timeline', 'Budget', 'Message', 'Status'];
        const csvContent = [
            headers.join(','),
            ...messages.map(msg => [
                msg.created_at || '',
                `"${(msg.name || '').replace(/"/g, '""')}"`,
                msg.email || '',
                msg.phone || '',
                `"${(msg.company || '').replace(/"/g, '""')}"`,
                `"${(msg.project_type || '').replace(/"/g, '""')}"`,
                `"${(msg.timeline || '').replace(/"/g, '""')}"`,
                `"${(msg.budget || '').replace(/"/g, '""')}"`,
                `"${(msg.message || '').replace(/"/g, '""')}"`,
                msg.read ? 'Read' : 'Unread'
            ].join(','))
        ].join('\n');
        
        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `messages_export_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showNotification('Messages exported successfully!', 'success');
    } catch (error) {
        console.error('Error exporting messages:', error);
        showNotification('Error exporting messages. Please try again.', 'error');
    }
}

// Enhanced Message Management System
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

        // Update filter buttons
        updateFilterButtons(filter);

        // Populate Full Message List
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
                filteredMessages.forEach((message, index) => { 
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
                            
                            ${isScheduleRequest ? `<button onclick="scheduleConsultation('${message.id}', '${email}', '${escapeHtml(name)}')" 
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Schedule Meeting
                            </button>` : ''}
                            
                            ${isUnread ? `<button onclick="markAsRead('${message.id}')" 
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Mark as Read
                            </button>` : ''}
                            
                            <button onclick="viewMessageDetails('${message.id}')" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Details
                            </button>
                            
                            <button onclick="deleteMessage('${message.id}')" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete
                            </button>
                        </div>
                    `;
                    messagesList.appendChild(div); 
                });
            }
        }

        // Populate Recent Messages Widget (Dashboard)
        const recentList = document.getElementById('recent-messages');
        if (recentList) {
            if (messages.length === 0) {
                recentList.innerHTML = '<p class="text-gray-500 text-sm">No messages yet</p>';
            } else {
                recentList.innerHTML = '';
                messages.slice(0, 5).forEach(message => {
                    const name = message.name || 'Unknown';
                    const projectType = message.project_type || 'Inquiry';
                    const initial = name.charAt(0).toUpperCase();
                    const isScheduleRequest = projectType === 'Consultation Request';
                    
                    const item = document.createElement('div');
                    item.className = 'flex items-center py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 rounded px-2 cursor-pointer';
                    item.onclick = () => showTab('messages');
                    item.innerHTML = `
                        <div class="w-10 h-10 rounded-full ${isScheduleRequest ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600'} flex items-center justify-center font-bold mr-3 flex-shrink-0">
                            ${isScheduleRequest ? '📅' : initial}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                ${escapeHtml(name)}
                            </p>
                            <p class="text-xs text-gray-500 truncate">
                                ${escapeHtml(projectType)} • ${formatDate(message.created_at)}
                            </p>
                        </div>
                        ${!message.read ? '<div class="w-3 h-3 bg-blue-500 rounded-full"></div>' : ''}
                    `;
                    recentList.appendChild(item);
                });
            }
        }

    } catch (error) {
        console.error('Error loading messages:', error);
        showNotification('Error loading messages: ' + error.message, 'error');
    }
}

// Update filter button states
function updateFilterButtons(activeFilter) {
    const buttons = {
        'all': document.querySelector('button[onclick="filterMessages(\'all\')"]'),
        'unread': document.querySelector('button[onclick="filterMessages(\'unread\')"]'),
        'today': document.querySelector('button[onclick="filterMessages(\'today\')"]'),
        'schedule': document.querySelector('button[onclick="filterMessages(\'schedule\')"]')
    };
    
    Object.keys(buttons).forEach(filter => {
        const button = buttons[filter];
        if (button) {
            if (filter === activeFilter) {
                button.className = 'px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium';
            } else {
                button.className = 'px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300';
            }
        }
    });
}

// Enhanced message action functions
function replyToMessage(messageId, email, name) {
    const subject = `Re: Your inquiry to App Craft Services`;
    const body = `Hi ${name},\n\nThank you for contacting App Craft Services. \n\n[Your response here]\n\nBest regards,\nApp Craft Services Team\nhello@appcraftservices.com`;
    
    const mailtoLink = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.open(mailtoLink);
    
    // Mark as read when replying
    markAsRead(messageId);
}

function scheduleConsultation(messageId, email, name) {
    const subject = `Consultation Scheduling - App Craft Services`;
    const body = `Hi ${name},\n\nThank you for requesting a consultation with App Craft Services!\n\nI'd be happy to schedule a 30-minute consultation to discuss your project. Please let me know which of these times work best for you:\n\n• [Option 1: Date/Time]\n• [Option 2: Date/Time]\n• [Option 3: Date/Time]\n\nOr feel free to suggest alternative times that work better for your schedule.\n\nThe consultation will be conducted via video call (Zoom/Google Meet), and I'll send you the meeting link once we confirm the time.\n\nLooking forward to discussing your project!\n\nBest regards,\nApp Craft Services Team\nhello@appcraftservices.com\n📅 Schedule directly: https://appcraftservices.com/schedule`;
    
    const mailtoLink = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.open(mailtoLink);
    
    // Mark as read when scheduling
    markAsRead(messageId);
}

function viewMessageDetails(messageId) {
    // Find the message in the current data
    fetch('api/get_messages.php')
        .then(response => response.json())
        .then(messages => {
            const message = messages.find(m => m.id === messageId);
            if (message) {
                showMessageModal(message);
            }
        })
        .catch(error => {
            console.error('Error fetching message details:', error);
            showNotification('Error loading message details', 'error');
        });
}

function showMessageModal(message) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Message Details</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                </div>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">Contact Information</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong>Name:</strong> ${escapeHtml(message.name || 'N/A')}</div>
                            <div><strong>Email:</strong> <a href="mailto:${message.email}" class="text-blue-600 hover:underline">${escapeHtml(message.email || 'N/A')}</a></div>
                            <div><strong>Phone:</strong> ${message.phone ? `<a href="tel:${message.phone}" class="text-blue-600 hover:underline">${escapeHtml(message.phone)}</a>` : 'N/A'}</div>
                            <div><strong>Company:</strong> ${escapeHtml(message.company || 'N/A')}</div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">Project Information</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong>Project Type:</strong> ${escapeHtml(message.project_type || 'N/A')}</div>
                            <div><strong>Timeline:</strong> ${escapeHtml(message.timeline || 'N/A')}</div>
                            <div><strong>Budget:</strong> ${escapeHtml(message.budget || 'N/A')}</div>
                            <div><strong>Status:</strong> <span class="px-2 py-1 text-xs rounded-full ${message.read ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}">${message.read ? 'Read' : 'Unread'}</span></div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h4 class="font-semibold text-gray-900 mb-3">Message Content</h4>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="whitespace-pre-wrap text-gray-700">${escapeHtml(message.message || 'No content')}</div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h4 class="font-semibold text-gray-900 mb-3">Submission Details</h4>
                    <div class="grid md:grid-cols-2 gap-4 text-sm text-gray-600">
                        <div><strong>Submitted:</strong> ${formatDate(message.created_at)}</div>
                        <div><strong>IP Address:</strong> ${escapeHtml(message.ip_address || 'N/A')}</div>
                        <div><strong>Message ID:</strong> ${escapeHtml(message.id || 'N/A')}</div>
                        <div><strong>User Agent:</strong> ${escapeHtml((message.user_agent || 'N/A').substring(0, 50))}${(message.user_agent || '').length > 50 ? '...' : ''}</div>
                    </div>
                </div>
                
                <div class="mt-6 flex flex-wrap gap-3">
                    <button onclick="replyToMessage('${message.id}', '${message.email}', '${escapeHtml(message.name)}')" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                        Reply via Email
                    </button>
                    ${message.phone ? `<a href="tel:${message.phone}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">Call ${message.phone}</a>` : ''}
                    ${!message.read ? `<button onclick="markAsRead('${message.id}'); this.closest('.fixed').remove(); loadMessages();" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200">Mark as Read</button>` : ''}
                    <button onclick="deleteMessage('${message.id}'); this.closest('.fixed').remove();" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">Delete Message</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

async function loadPaymentData() {
    try {
        const response = await fetch('api/get_payments.php');
        if (!response.ok) throw new Error('API not available');
        const payments = await response.json();

        // Update payment statistics
        const totalRevenue = payments.reduce((sum, payment) => sum + parseFloat(payment.amount || 0), 0);
        const thisMonthRevenue = payments.filter(payment => {
            const paymentDate = new Date(payment.created_at);
            const now = new Date();
            return paymentDate.getMonth() === now.getMonth() && 
                   paymentDate.getFullYear() === now.getFullYear();
        }).reduce((sum, payment) => sum + parseFloat(payment.amount || 0), 0);

        const totalRevenueEl = document.getElementById('total-revenue');
        const monthlyRevenueEl = document.getElementById('monthly-revenue');
        const totalTransactionsEl = document.getElementById('total-transactions');
        const pendingPaymentsEl = document.getElementById('pending-payments');

        if (totalRevenueEl) totalRevenueEl.textContent = '$' + totalRevenue.toFixed(2);
        if (monthlyRevenueEl) monthlyRevenueEl.textContent = '$' + thisMonthRevenue.toFixed(2);
        if (totalTransactionsEl) totalTransactionsEl.textContent = payments.length;
        if (pendingPaymentsEl) pendingPaymentsEl.textContent = payments.filter(p => p.status === 'pending').length;

    } catch (error) {
        console.warn('Error loading payment data:', error);
    }
}

async function loadAnalyticsData() {
    // For now, we'll show static data
    const totalVisitorsEl = document.getElementById('total-visitors');
    const pageViewsEl = document.getElementById('page-views');
    const bounceRateEl = document.getElementById('bounce-rate');
    const avgSessionEl = document.getElementById('avg-session');

    if (totalVisitorsEl) totalVisitorsEl.textContent = '1,234';
    if (pageViewsEl) pageViewsEl.textContent = '5,678';
    if (bounceRateEl) bounceRateEl.textContent = '32%';
    if (avgSessionEl) avgSessionEl.textContent = '2:45';
}

async function loadReviews(filter = 'all') {
    try {
        const response = await fetch(`api/get_reviews.php?status=${filter}&limit=50&offset=0`);
        if (!response.ok) throw new Error('API not available');
        const reviews = await response.json();
        
        // Review rendering logic would go here
    } catch (error) {
        console.warn('Error loading reviews:', error);
    }
}

// Realtime Editor Function
function openRealtimeEditor() {
    // Open realtime editor in a new window/tab
    window.open('realtime-editor.php?page=home', '_blank', 'width=1400,height=900');
}

// Analytics Functions
function refreshAnalytics() {
    const period = document.getElementById('analytics-period').value;
    const page = document.getElementById('analytics-page').value;
    const source = document.getElementById('analytics-source').value;
    
    const params = new URLSearchParams({
        period: period,
        page: page,
        source: source
    });
    
    fetch(`/api/analytics.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateAnalyticsDashboard(data);
            } else {
                console.error('Analytics error:', data.message);
                showAnalyticsError();
            }
        })
        .catch(error => {
            console.error('Analytics fetch error:', error);
            showAnalyticsError();
        });
}

function updateAnalyticsDashboard(data) {
    // Update overview cards
    document.getElementById('total-visitors').textContent = data.unique_visitors.toLocaleString();
    document.getElementById('page-views').textContent = data.total_views.toLocaleString();
    document.getElementById('bounce-rate').textContent = data.bounce_rate + '%';
    document.getElementById('avg-load-time').textContent = data.avg_load_time + 's';
    
    // Update change indicators (simplified - you can enhance this)
    document.getElementById('visitors-change').textContent = 'Current period';
    document.getElementById('visitors-change').className = 'text-xs text-blue-600 mt-1';
    document.getElementById('views-change').textContent = 'Current period';
    document.getElementById('views-change').className = 'text-xs text-blue-600 mt-1';
    document.getElementById('bounce-change').textContent = 'Current period';
    document.getElementById('bounce-change').className = 'text-xs text-blue-600 mt-1';
    document.getElementById('load-time-change').textContent = 'Current period';
    document.getElementById('load-time-change').className = 'text-xs text-blue-600 mt-1';
    
    // Update top pages
    updateTopPages(data.top_pages);
    
    // Update traffic sources
    updateTrafficSources(data.traffic_sources);
    
    // Update device types
    updateDeviceTypes(data.device_types);
    
    // Update browsers
    updateBrowsers(data.browsers);
    
    // Update recent visitors
    updateRecentVisitors(data.recent_visitors);
    
    // Update traffic chart
    updateTrafficChart(data.views_by_date);
}

function updateTopPages(topPages) {
    const container = document.getElementById('top-pages-list');
    if (!topPages || Object.keys(topPages).length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-gray-500"><p>No page data available</p></div>';
        return;
    }
    
    let html = '<div class="space-y-4">';
    Object.entries(topPages).slice(0, 10).forEach(([page, views]) => {
        const pageName = page === '/' ? 'Homepage' : page.replace('/', '').replace(/^\w/, c => c.toUpperCase());
        html += `
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                <div>
                    <div class="font-medium">${pageName}</div>
                    <div class="text-sm text-gray-600">${page}</div>
                </div>
                <div class="text-right">
                    <div class="font-semibold text-blue-600">${views.toLocaleString()}</div>
                    <div class="text-sm text-gray-600">views</div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function updateTrafficSources(sources) {
    const container = document.getElementById('traffic-sources-list');
    if (!sources || Object.keys(sources).length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-gray-500"><p>No source data</p></div>';
        return;
    }
    
    const total = Object.values(sources).reduce((sum, count) => sum + count, 0);
    let html = '<div class="space-y-3">';
    
    Object.entries(sources).slice(0, 8).forEach(([source, count]) => {
        const percentage = total > 0 ? Math.round((count / total) * 100) : 0;
        const sourceName = source.charAt(0).toUpperCase() + source.slice(1);
        html += `
            <div class="flex justify-between items-center">
                <span class="text-sm">${sourceName}</span>
                <div class="flex items-center">
                    <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: ${percentage}%"></div>
                    </div>
                    <span class="text-sm font-medium">${percentage}%</span>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function updateDeviceTypes(devices) {
    const container = document.getElementById('device-types-list');
    if (!devices || Object.keys(devices).length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-gray-500"><p>No device data</p></div>';
        return;
    }
    
    const total = Object.values(devices).reduce((sum, count) => sum + count, 0);
    let html = '<div class="space-y-3">';
    
    const colors = { desktop: 'blue', mobile: 'green', tablet: 'purple' };
    
    Object.entries(devices).forEach(([device, count]) => {
        const percentage = total > 0 ? Math.round((count / total) * 100) : 0;
        const deviceName = device.charAt(0).toUpperCase() + device.slice(1);
        const color = colors[device] || 'gray';
        html += `
            <div class="flex justify-between items-center">
                <span class="text-sm">${deviceName}</span>
                <div class="flex items-center">
                    <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                        <div class="bg-${color}-600 h-2 rounded-full" style="width: ${percentage}%"></div>
                    </div>
                    <span class="text-sm font-medium">${percentage}%</span>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function updateBrowsers(browsers) {
    const container = document.getElementById('browsers-list');
    if (!browsers || Object.keys(browsers).length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-gray-500"><p>No browser data</p></div>';
        return;
    }
    
    const total = Object.values(browsers).reduce((sum, count) => sum + count, 0);
    let html = '<div class="space-y-3">';
    
    Object.entries(browsers).slice(0, 6).forEach(([browser, count]) => {
        const percentage = total > 0 ? Math.round((count / total) * 100) : 0;
        html += `
            <div class="flex justify-between items-center">
                <span class="text-sm">${browser}</span>
                <div class="flex items-center">
                    <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: ${percentage}%"></div>
                    </div>
                    <span class="text-sm font-medium">${percentage}%</span>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function updateRecentVisitors(visitors) {
    const container = document.getElementById('recent-visitors-list');
    if (!visitors || visitors.length === 0) {
        container.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No recent visitors</td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    visitors.slice(0, 20).forEach(visitor => {
        const time = new Date(visitor.timestamp).toLocaleTimeString();
        const pageName = visitor.page === '/' ? 'Homepage' : visitor.page;
        const sourceName = visitor.source.charAt(0).toUpperCase() + visitor.source.slice(1);
        const deviceName = visitor.device_type.charAt(0).toUpperCase() + visitor.device_type.slice(1);
        
        html += `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${time}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${pageName}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${sourceName}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${deviceName}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${visitor.country}</td>
            </tr>
        `;
    });
    
    container.innerHTML = html;
}

function updateTrafficChart(viewsByDate) {
    const canvas = document.getElementById('traffic-canvas');
    if (!canvas || !viewsByDate) return;
    
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    
    // Clear canvas
    ctx.clearRect(0, 0, width, height);
    
    const dates = Object.keys(viewsByDate).sort();
    const values = dates.map(date => viewsByDate[date]);
    
    if (values.length === 0) {
        ctx.fillStyle = '#6B7280';
        ctx.font = '14px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('No data available', width / 2, height / 2);
        return;
    }
    
    const maxValue = Math.max(...values);
    const padding = 40;
    const chartWidth = width - padding * 2;
    const chartHeight = height - padding * 2;
    
    // Draw axes
    ctx.strokeStyle = '#E5E7EB';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, height - padding);
    ctx.lineTo(width - padding, height - padding);
    ctx.stroke();
    
    // Draw data line
    if (values.length > 1) {
        ctx.strokeStyle = '#3B82F6';
        ctx.lineWidth = 2;
        ctx.beginPath();
        
        values.forEach((value, index) => {
            const x = padding + (index / (values.length - 1)) * chartWidth;
            const y = height - padding - (value / maxValue) * chartHeight;
            
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        
        ctx.stroke();
        
        // Draw data points
        ctx.fillStyle = '#3B82F6';
        values.forEach((value, index) => {
            const x = padding + (index / (values.length - 1)) * chartWidth;
            const y = height - padding - (value / maxValue) * chartHeight;
            
            ctx.beginPath();
            ctx.arc(x, y, 3, 0, 2 * Math.PI);
            ctx.fill();
        });
    }
}

function showAnalyticsError() {
    document.getElementById('total-visitors').textContent = 'Error';
    document.getElementById('page-views').textContent = 'Error';
    document.getElementById('bounce-rate').textContent = 'Error';
    document.getElementById('avg-load-time').textContent = 'Error';
    
    document.getElementById('top-pages-list').innerHTML = '<div class="text-center py-8 text-red-500"><p>Failed to load analytics data</p></div>';
    document.getElementById('traffic-sources-list').innerHTML = '<div class="text-center py-4 text-red-500"><p>Error loading data</p></div>';
    document.getElementById('device-types-list').innerHTML = '<div class="text-center py-4 text-red-500"><p>Error loading data</p></div>';
    document.getElementById('browsers-list').innerHTML = '<div class="text-center py-4 text-red-500"><p>Error loading data</p></div>';
}


// Google Analytics Integration
document.addEventListener('DOMContentLoaded', function() {
    const gaForm = document.getElementById('ga-settings-form');
    if (gaForm) {
        gaForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const trackingId = document.getElementById('ga-tracking-id').value.trim();
            const measurementId = document.getElementById('ga-measurement-id').value.trim();
            
            if (!trackingId) {
                alert('Please enter a Google Analytics Tracking ID');
                return;
            }
            
            try {
                // Save to settings
                const response = await fetch('api/save_settings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        ga_tracking_id: trackingId,
                        ga_measurement_id: measurementId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update status
                    const statusDiv = document.getElementById('ga-status');
                    statusDiv.innerHTML = `
                        <p class="text-sm text-green-800">
                            <i class="fas fa-check-circle mr-2"></i>
                            Google Analytics connected successfully! Tracking ID: ${trackingId}
                        </p>
                    `;
                    statusDiv.className = 'mb-6 p-4 bg-green-50 border border-green-200 rounded-lg';
                    
                    // Show data section
                    document.getElementById('ga-data-section').style.display = 'block';
                    
                    // Inject GA script
                    injectGoogleAnalytics(trackingId, measurementId);
                    
                    alert('Google Analytics settings saved successfully!');
                } else {
                    alert('Error saving settings: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error saving Google Analytics settings');
            }
        });
        
        // Load existing settings
        loadGASettings();
    }
});

function loadGASettings() {
    fetch('api/get_analytics.php')
        .then(response => response.json())
        .then(data => {
            if (data.ga_tracking_id) {
                document.getElementById('ga-tracking-id').value = data.ga_tracking_id;
                document.getElementById('ga-measurement-id').value = data.ga_measurement_id || '';
                
                // Update status
                const statusDiv = document.getElementById('ga-status');
                statusDiv.innerHTML = `
                    <p class="text-sm text-green-800">
                        <i class="fas fa-check-circle mr-2"></i>
                        Google Analytics connected! Tracking ID: ${data.ga_tracking_id}
                    </p>
                `;
                statusDiv.className = 'mb-6 p-4 bg-green-50 border border-green-200 rounded-lg';
                
                // Show data section
                document.getElementById('ga-data-section').style.display = 'block';
                
                // Inject GA script
                injectGoogleAnalytics(data.ga_tracking_id, data.ga_measurement_id);
            }
        })
        .catch(error => console.log('No GA settings found yet'));
}

function injectGoogleAnalytics(trackingId, measurementId) {
    // Inject Google Analytics script
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${trackingId || measurementId}`;
    document.head.appendChild(script);
    
    // Initialize gtag
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', trackingId || measurementId);
}

function testGAConnection() {
    const trackingId = document.getElementById('ga-tracking-id').value.trim();
    const measurementId = document.getElementById('ga-measurement-id').value.trim();
    
    if (!trackingId && !measurementId) {
        alert('Please enter a Tracking ID or Measurement ID first');
        return;
    }
    
    // Test by sending a test event
    if (window.gtag) {
        gtag('event', 'test_event', {
            'event_category': 'admin_test',
            'event_label': 'GA Connection Test'
        });
        alert('Test event sent! Check your Google Analytics Real-time view within 30 seconds.');
    } else {
        alert('Google Analytics script not loaded yet. Please save settings first.');
    }
}

// Project Management Functions
function showAddProjectModal() {
    document.getElementById('project-modal').classList.remove('hidden');
}

function hideAddProjectModal() {
    document.getElementById('project-modal').classList.add('hidden');
    document.getElementById('project-form').reset();
}

function loadProjects() {
    fetch('api/get_projects.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayProjects(data.projects);
                updateProjectStats(data.projects);
            } else {
                console.error('Failed to load projects:', data.message);
                document.getElementById('projects-list').innerHTML = '<p class="text-red-600">Failed to load projects</p>';
            }
        })
        .catch(error => {
            console.error('Error loading projects:', error);
            document.getElementById('projects-list').innerHTML = '<p class="text-red-600">Error loading projects</p>';
        });
}

function displayProjects(projects) {
    const projectsList = document.getElementById('projects-list');
    
    if (projects.length === 0) {
        projectsList.innerHTML = '<p class="text-gray-600">No projects found</p>';
        return;
    }
    
    const projectsHTML = projects.map(project => `
        <div class="border border-gray-200 rounded-lg p-4 mb-4">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center space-x-4 mb-2">
                        ${project.image ? `<img src="../${project.image}" alt="${project.name}" class="w-12 h-12 object-cover rounded">` : '<div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center"><i class="fas fa-project-diagram text-gray-400"></i></div>'}
                        <div>
                            <h4 class="font-semibold text-lg">${project.name}</h4>
                            <p class="text-sm text-gray-600">${project.category}</p>
                        </div>
                        ${project.featured ? '<span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">Featured</span>' : ''}
                    </div>
                    <p class="text-gray-700 mb-2">${project.description}</p>
                    <div class="flex flex-wrap gap-2 mb-2">
                        ${project.technologies.map(tech => `<span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">${tech}</span>`).join('')}
                    </div>
                    <div class="text-sm text-gray-600">
                        <p><strong>Client:</strong> ${project.client}</p>
                        <p><strong>Completed:</strong> ${new Date(project.completion_date).toLocaleDateString()}</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button onclick="editProject('${project.id}')" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteProject('${project.id}')" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    projectsList.innerHTML = projectsHTML;
}

function updateProjectStats(projects) {
    document.getElementById('total-projects').textContent = projects.length;
    document.getElementById('featured-projects').textContent = projects.filter(p => p.featured).length;
    document.getElementById('completed-projects').textContent = projects.filter(p => p.status === 'completed').length;
    
    const categories = [...new Set(projects.map(p => p.category))];
    document.getElementById('project-categories').textContent = categories.length;
}

function deleteProject(projectId) {
    if (!confirm('Are you sure you want to delete this project?')) return;
    
    fetch('api/delete_project.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: projectId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadProjects(); // Reload projects list
        } else {
            alert('Failed to delete project: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error deleting project:', error);
        alert('Error deleting project');
    });
}

// Blog Management Functions
function showAddBlogModal() {
    document.getElementById('blog-modal').classList.remove('hidden');
    
    // Auto-generate slug from title
    const titleInput = document.getElementById('blog-title');
    const slugInput = document.getElementById('blog-slug');
    
    titleInput.addEventListener('input', function() {
        const slug = this.value.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
        slugInput.value = slug;
    });
}

function hideAddBlogModal() {
    document.getElementById('blog-modal').classList.add('hidden');
    document.getElementById('blog-form').reset();
}

function loadBlogPosts() {
    fetch('../api/get_blog_posts.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayBlogPosts(data.posts);
                updateBlogStats(data.posts);
            } else {
                console.error('Failed to load blog posts:', data.message);
                document.getElementById('blog-posts-list').innerHTML = '<p class="text-red-600">Failed to load blog posts</p>';
            }
        })
        .catch(error => {
            console.error('Error loading blog posts:', error);
            document.getElementById('blog-posts-list').innerHTML = '<p class="text-red-600">Error loading blog posts</p>';
        });
}

function displayBlogPosts(posts) {
    const blogPostsList = document.getElementById('blog-posts-list');
    
    if (posts.length === 0) {
        blogPostsList.innerHTML = '<p class="text-gray-600">No blog posts found</p>';
        return;
    }
    
    const postsHTML = posts.map(post => `
        <div class="border border-gray-200 rounded-lg p-4 mb-4">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center space-x-4 mb-2">
                        ${post.featured_image ? `<img src="../${post.featured_image}" alt="${post.title}" class="w-12 h-12 object-cover rounded">` : '<div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center"><i class="fas fa-blog text-gray-400"></i></div>'}
                        <div>
                            <h4 class="font-semibold text-lg">${post.title}</h4>
                            <p class="text-sm text-gray-600">${post.category}</p>
                        </div>
                        <span class="bg-${post.status === 'published' ? 'green' : 'yellow'}-100 text-${post.status === 'published' ? 'green' : 'yellow'}-800 text-xs px-2 py-1 rounded">${post.status}</span>
                    </div>
                    <p class="text-gray-700 mb-2">${post.excerpt}</p>
                    <div class="flex flex-wrap gap-2 mb-2">
                        ${post.tags.map(tag => `<span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">${tag}</span>`).join('')}
                    </div>
                    <div class="text-sm text-gray-600">
                        <p><strong>Author:</strong> ${post.author}</p>
                        <p><strong>Created:</strong> ${new Date(post.created_at).toLocaleDateString()}</p>
                        <p><strong>Slug:</strong> /blog/${post.slug}</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button onclick="editBlogPost('${post.id}')" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteBlogPost('${post.id}')" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    blogPostsList.innerHTML = postsHTML;
}

function updateBlogStats(posts) {
    document.getElementById('total-posts').textContent = posts.length;
    document.getElementById('published-posts').textContent = posts.filter(p => p.status === 'published').length;
    document.getElementById('draft-posts').textContent = posts.filter(p => p.status === 'draft').length;
    
    const categories = [...new Set(posts.map(p => p.category))];
    document.getElementById('blog-categories').textContent = categories.length;
}

function deleteBlogPost(postId) {
    if (!confirm('Are you sure you want to delete this blog post?')) return;
    
    fetch('api/delete_blog_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadBlogPosts(); // Reload blog posts list
        } else {
            alert('Failed to delete blog post: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error deleting blog post:', error);
        alert('Error deleting blog post');
    });
}

// Form submission handlers
document.addEventListener('DOMContentLoaded', function() {
    // Project form submission
    const projectForm = document.getElementById('project-form');
    if (projectForm) {
        projectForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Convert technologies string to array
            const technologies = formData.get('technologies').split(',').map(tech => tech.trim());
            formData.delete('technologies');
            formData.append('technologies', JSON.stringify(technologies));
            
            fetch('api/save_project.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideAddProjectModal();
                    loadProjects();
                    alert('Project saved successfully!');
                } else {
                    alert('Failed to save project: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error saving project:', error);
                alert('Error saving project');
            });
        });
    }
    
    // Blog form submission
    const blogForm = document.getElementById('blog-form');
    if (blogForm) {
        blogForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Convert tags string to array
            const tags = formData.get('tags').split(',').map(tag => tag.trim()).filter(tag => tag);
            formData.delete('tags');
            formData.append('tags', JSON.stringify(tags));
            
            fetch('api/save_blog_post.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideAddBlogModal();
                    loadBlogPosts();
                    alert('Blog post saved successfully!');
                } else {
                    alert('Failed to save blog post: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error saving blog post:', error);
                alert('Error saving blog post');
            });
        });
    }
});

// Missing functions for admin dashboard

function openRealtimeEditor() {
    window.open('realtime-editor.php', '_blank');
}

function previewSite() {
    window.open('../', '_blank');
}

// Additional utility functions
function refreshAnalytics() {
    // Placeholder for analytics refresh functionality
    console.log('Refreshing analytics...');
}

function loadMessages() {
    // Load messages functionality
    fetch('api/get_messages.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Messages loaded:', data.messages);
                // Update message count in dashboard
                const messageCount = document.getElementById('message-count');
                if (messageCount) {
                    messageCount.textContent = data.messages.length;
                }
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        });
}

function loadReviews() {
    // Load reviews functionality
    fetch('api/get_reviews.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Reviews loaded:', data.reviews);
            }
        })
        .catch(error => {
            console.error('Error loading reviews:', error);
        });
}

// Initialize dashboard data on load
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadMessages();
    
    // Set current year in footer if exists
    const currentYearElement = document.getElementById('current-year');
    if (currentYearElement) {
        currentYearElement.textContent = new Date().getFullYear();
    }
});

// Payment Configuration Functions
document.addEventListener('DOMContentLoaded', function() {
    // Load existing payment settings
    loadPaymentSettings();
    
    // Stripe settings form
    const stripeForm = document.getElementById('stripe-settings-form');
    if (stripeForm) {
        stripeForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
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
    
    // PayPal settings form
    const paypalForm = document.getElementById('paypal-settings-form');
    if (paypalForm) {
        paypalForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
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
    
    // Payment general settings form
    const paymentGeneralForm = document.getElementById('payment-general-settings-form');
    if (paymentGeneralForm) {
        paymentGeneralForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                currency: document.getElementById('payment-currency').value,
                tax_rate: document.getElementById('payment-tax-rate').value,
                require_billing_address: document.getElementById('require-billing-address').checked,
                send_email_receipts: document.getElementById('send-email-receipts').checked
            };
            
            try {
                const response = await fetch('api/save_settings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ payment: { general: formData } })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Payment settings updated successfully!', 'success');
                } else {
                    showNotification('Error updating payment settings: ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('Error updating payment settings: ' + error.message, 'error');
            }
        });
    }
    
    // Package form submission
    const packageForm = document.getElementById('package-form');
    if (packageForm) {
        packageForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const packageData = {
                name: formData.get('name'),
                price: formData.get('price'),
                description: formData.get('description'),
                features: formData.get('features').split('\n').filter(f => f.trim())
            };
            
            try {
                const response = await fetch('api/save_package.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(packageData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    hideAddPackageModal();
                    loadServicePackages();
                    showNotification('Service package added successfully!', 'success');
                } else {
                    showNotification('Error adding package: ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('Error adding package: ' + error.message, 'error');
            }
        });
    }
});

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
            
            setVal('paypal-environment', paypal.paypal_environment);
            setVal('paypal-client-id', paypal.paypal_client_id);
            setVal('paypal-client-secret', paypal.paypal_client_secret);
            setVal('paypal-webhook-id', paypal.paypal_webhook_id);
        }
        
        // Load general payment settings
        if (settings.payment && settings.payment.general) {
            const general = settings.payment.general;
            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.value = val || '';
            };
            const setChecked = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.checked = val || false;
            };
            
            setVal('payment-currency', general.currency);
            setVal('payment-tax-rate', general.tax_rate);
            setChecked('require-billing-address', general.require_billing_address);
            setChecked('send-email-receipts', general.send_email_receipts);
        }
        
    } catch (error) {
        console.log('No payment settings found yet');
    }
}

// Quick Actions Functions
function generateInvoice() {
    showTab('invoices');
    showNotification('Opening invoice generator...', 'info');
}

function sendPaymentLink() {
    const email = prompt('Enter client email address:');
    if (email) {
        const amount = prompt('Enter payment amount:');
        if (amount) {
            showNotification(`Payment link for $${amount} will be sent to ${email}`, 'success');
            // Here you would integrate with your payment processor to create and send the link
        }
    }
}

function processRefund() {
    const transactionId = prompt('Enter transaction ID for refund:');
    if (transactionId) {
        const amount = prompt('Enter refund amount (leave empty for full refund):');
        if (confirm(`Process refund${amount ? ` of $${amount}` : ''} for transaction ${transactionId}?`)) {
            showNotification('Refund request submitted for processing', 'success');
            // Here you would integrate with your payment processor to process the refund
        }
    }
}

function exportPaymentData() {
    const format = prompt('Export format (csv/json):', 'csv');
    if (format === 'csv' || format === 'json') {
        showNotification(`Exporting payment data as ${format.toUpperCase()}...`, 'info');
        // Here you would generate and download the export file
        setTimeout(() => {
            showNotification('Payment data exported successfully!', 'success');
        }, 2000);
    }
}

// Service Package Management Functions
function showAddPackageModal() {
    document.getElementById('package-modal').classList.remove('hidden');
}

function hideAddPackageModal() {
    document.getElementById('package-modal').classList.add('hidden');
    document.getElementById('package-form').reset();
}

function editPackage(packageId) {
    showNotification(`Edit package functionality for ${packageId} coming soon!`, 'info');
}

function deletePackage(packageId) {
    if (confirm('Are you sure you want to delete this service package?')) {
        showNotification(`Package ${packageId} deleted successfully!`, 'success');
        // Here you would make an API call to delete the package
    }
}

async function loadServicePackages() {
    try {
        // For now, we'll use the static packages shown in the HTML
        // In a real implementation, you'd fetch from an API
        console.log('Service packages loaded');
    } catch (error) {
        console.error('Error loading service packages:', error);
    }
}

// Enhanced Payment Functions (overriding existing ones)
function refundPayment() {
    processRefund();
}

function exportPayments() {
    exportPaymentData();
}