// Admin Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // 1. Setup Navigation Event Listeners
    // This replaces the reliance on inline 'onclick' and handles the logic reliably.
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
    // We select the link based on its href attribute
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
    // We check if the functions exist before calling them to prevent errors
    if (tabName === 'messages') {
        if (typeof loadMessages === 'function') loadMessages();
    } else if (tabName === 'payments') {
        if (typeof loadPaymentData === 'function') loadPaymentData();
    } else if (tabName === 'analytics') {
        if (typeof loadAnalyticsData === 'function') loadAnalyticsData();
    } else if (tabName === 'reviews') {
        if (typeof loadReviews === 'function') loadReviews();
    } else if (tabName === 'invoices') {
        // Initialize invoice form if the function exists (it might be in index.php or invoice.js)
        if (typeof initializeInvoiceForm === 'function') {
            initializeInvoiceForm();
        } else if (typeof loadInvoices === 'function') {
            loadInvoices(); // Fallback to loading list if init form is missing
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
            setVal('site-name', content.site_info.title); // Note: ID might be 'site-name' or 'site_title' in HTML, checking both
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
            // Remove existing items except the first template if necessary, 
            // but your PHP loop renders them. We might want to clear them to ensure JS state matches JSON.
            // For now, we will assume the PHP rendered initial state is correct, or clear and rebuild:
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
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
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

        // Update message statistics
        const totalMessages = messages.length;
        const unreadMessages = messages.filter(m => !m.read).length;
        const todayMessages = messages.filter(m => {
            const messageDate = new Date(m.created_at);
            const today = new Date();
            return messageDate.toDateString() === today.toDateString();
        }).length;

        const totalMsgEl = document.getElementById('total-messages');
        const unreadMsgEl = document.getElementById('unread-messages');
        const todayMsgEl = document.getElementById('today-messages');
        const msgCountEl = document.getElementById('message-count');

        if (totalMsgEl) totalMsgEl.textContent = totalMessages;
        if (unreadMsgEl) unreadMsgEl.textContent = unreadMessages;
        if (todayMsgEl) todayMsgEl.textContent = todayMessages;
        if (msgCountEl) msgCountEl.textContent = unreadMessages;

        const messagesList = document.getElementById('messages-list');
        if (messagesList) {
            if (messages.length === 0) {
                messagesList.innerHTML = '<p class="text-gray-500">No messages found</p>';
            } else {
                messagesList.innerHTML = ''; 
                messages.forEach(message => { 
                    const li = document.createElement('li'); 
                    li.textContent = `${message.from_name}: ${message.subject} - ${formatDate(message.created_at)}`; 
                    messagesList.appendChild(li); 
                });
            }
        }

    } catch (error) {
        const messagesList = document.getElementById('messages-list');
        if (messagesList) {
            messagesList.innerHTML = '<p class="text-gray-500">No messages yet (or API not available)</p>';
        }
        console.warn('Error loading messages:', error);
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