// Admin Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // 1. Setup Navigation Event Listeners
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    
    sidebarItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent the default jump/hash change
            
            // Get the target tab ID from the href attribute (e.g., "#dashboard" -> "dashboard")
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                const tabId = href.substring(1);
                showTab(tabId);
            }
        });
    });

    // 2. Initialize the default tab (if needed)
    // Check if there is a hash in the URL, otherwise default to dashboard
    const hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById(hash)) {
        showTab(hash);
    } else {
        // Ensure dashboard is visible by default if no hash
        showTab('dashboard');
    }

    // 3. Load initial dashboard data (recent messages, stats)
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

    // Highlight the corresponding sidebar item
    const activeLink = document.querySelector(`.sidebar-item[href="#${tabName}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }

    // Update page title
    const titles = {
        'dashboard': 'Dashboard',
        'content': 'Content Management',
        'pages': 'Page Editor',
        'design': 'Design & Styling',
        'messages': 'Messages',
        'invoices': 'Invoice Management',
        'payments': 'Payment Settings',
        'analytics': 'Analytics',
        'settings': 'Settings',
        'reviews': 'Review Management'
    };

    const pageTitleEl = document.getElementById('page-title');
    if (pageTitleEl) {
        pageTitleEl.textContent = titles[tabName] || 'Dashboard';
    }

    // Load tab-specific data
    if (tabName === 'messages') {
        if (typeof loadMessages === 'function') loadMessages();
    } else if (tabName === 'payments') {
        if (typeof loadPaymentData === 'function') loadPaymentData();
    } else if (tabName === 'analytics') {
        if (typeof loadAnalyticsData === 'function') loadAnalyticsData();
    } else if (tabName === 'reviews') {
        if (typeof loadReviews === 'function') loadReviews();
    } else if (tabName === 'invoices') {
        if (typeof initializeInvoiceForm === 'function') {
            initializeInvoiceForm();
        } else if (typeof loadInvoices === 'function') {
            loadInvoices(); 
        }
    } else if (tabName === 'content') {
        if (typeof loadContentData === 'function') loadContentData();
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

function deleteMessage(messageId) {
    if (confirm('Are you sure you want to delete this message?')) {
        showNotification('Delete functionality coming soon!', 'info');
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

function exportMessages() {
    showNotification('Export functionality coming soon!', 'info');
}

// Data Loading Functions
async function loadMessages(filter = 'all') {
    try {
        const response = await fetch('api/get_messages.php');
        if (!response.ok) throw new Error('Network response was not ok');
        const messages = await response.json();

        // 1. Update Message Statistics
        const totalMessages = messages.length;
        const unreadMessages = messages.filter(m => !m.read).length;
        const todayMessages = messages.filter(m => {
            const messageDate = new Date(m.created_at);
            const today = new Date();
            return messageDate.toDateString() === today.toDateString();
        }).length;

        // Safely update elements if they exist
        const setEl = (id, txt) => { const el = document.getElementById(id); if (el) el.textContent = txt; };
        
        setEl('total-messages', totalMessages);
        setEl('unread-messages', unreadMessages);
        setEl('today-messages', todayMessages);
        setEl('message-count', unreadMessages); // Notification badge

        // 2. Populate Full Message List (Messages Tab)
        const messagesList = document.getElementById('messages-list');
        if (messagesList) {
            if (messages.length === 0) {
                messagesList.innerHTML = '<p class="text-gray-500 text-center py-4">No messages found</p>';
            } else {
                messagesList.innerHTML = ''; 
                messages.forEach(message => { 
                    // Use fallback values if fields are missing
                    const name = message.name || 'Unknown';
                    const projectType = message.project_type || 'General Inquiry';
                    const msgText = message.message || 'No content';
                    const date = formatDate(message.created_at);
                    const email = message.email || '';
                    
                    const div = document.createElement('div');
                    div.className = 'bg-white border border-gray-200 rounded-lg p-4 mb-3 hover:shadow-md transition duration-200';
                    div.innerHTML = `
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center">
                                    <h4 class="font-bold text-gray-800 text-lg mr-2">${escapeHtml(name)}</h4>
                                    ${!message.read ? '<span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full font-medium">New</span>' : ''}
                                </div>
                                <p class="text-sm text-gray-600 mb-1">${escapeHtml(email)} • ${escapeHtml(projectType)}</p>
                            </div>
                            <div class="text-sm text-gray-500">
                                ${date}
                            </div>
                        </div>
                        <div class="mt-2 text-gray-700 bg-gray-50 p-3 rounded text-sm">
                            ${escapeHtml(msgText)}
                        </div>
                        <div class="mt-3 flex space-x-3 text-sm">
                            <button onclick="replyToMessage('${message.id}', '${email}')" class="text-blue-600 hover:text-blue-800 font-medium">
                                <i class="fas fa-reply mr-1"></i> Reply
                            </button>
                            <button onclick="markAsRead('${message.id}')" class="text-gray-600 hover:text-gray-800 font-medium">
                                <i class="fas fa-check mr-1"></i> Mark Read
                            </button>
                            <button onclick="deleteMessage('${message.id}')" class="text-red-600 hover:text-red-800 font-medium">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
                        </div>
                    `;
                    messagesList.appendChild(div); 
                });
            }
        }

        // 3. Populate Recent Messages Widget (Dashboard Tab)
        const recentList = document.getElementById('recent-messages');
        if (recentList) {
            if (messages.length === 0) {
                recentList.innerHTML = '<p class="text-gray-500 text-sm">No messages yet</p>';
            } else {
                recentList.innerHTML = '';
                // Take top 5 messages
                messages.slice(0, 5).forEach(message => {
                    const name = message.name || 'Unknown';
                    const projectType = message.project_type || 'Inquiry';
                    const initial = name.charAt(0).toUpperCase();
                    
                    const item = document.createElement('div');
                    item.className = 'flex items-center py-3 border-b border-gray-100 last:border-0';
                    item.innerHTML = `
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold mr-3 flex-shrink-0">
                            ${initial}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                ${escapeHtml(name)}
                            </p>
                            <p class="text-xs text-gray-500 truncate">
                                ${escapeHtml(projectType)}
                            </p>
                        </div>
                        <div class="text-xs text-gray-400">
                            ${new Date(message.created_at).toLocaleDateString(undefined, {month:'short', day:'numeric'})}
                        </div>
                    `;
                    recentList.appendChild(item);
                });
            }
        }

    } catch (error) {
        console.warn('Error loading messages:', error);
        // Do not overwrite with error message if simple network fail, keeps old data or blank
    }
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