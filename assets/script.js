// Dynamic year in footer
document.addEventListener('DOMContentLoaded', function() {
    const currentYear = new Date().getFullYear();
    const yearElements = document.querySelectorAll('#current-year');
    yearElements.forEach(element => {
        element.textContent = currentYear;
    });
});

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
});

// Contact form handling
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contact-form');
    const successMessage = document.getElementById('success-message');
    
    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            const submitIcon = document.getElementById('submit-icon');
            const loadingIcon = document.getElementById('loading-icon');
            
            // Show loading state
            submitBtn.disabled = true;
            submitText.textContent = 'Sending...';
            submitIcon.classList.add('hidden');
            loadingIcon.classList.remove('hidden');
            
            try {
                // Collect form data
                const formData = new FormData(contactForm);
                const data = {};
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }
                
                // Send to API
                const response = await fetch('/appcraftservices/api/contact.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Hide form and show success message
                    contactForm.style.display = 'none';
                    successMessage.classList.remove('hidden');
                    successMessage.innerHTML = `
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <div>
                                <p class="font-semibold">Message sent successfully!</p>
                                <p class="text-sm">We'll get back to you within 24 hours. Check your email for confirmation.</p>
                                <p class="text-xs mt-1">Reference ID: ${result.message_id}</p>
                            </div>
                        </div>
                    `;
                } else {
                    throw new Error(result.message || 'Failed to send message');
                }
                
            } catch (error) {
                // Show error message
                alert('Error sending message: ' + error.message + '. Please try again or contact us directly at williamsaonen@gmail.com');
                
                // Reset button state
                submitBtn.disabled = false;
                submitText.textContent = 'Send Message';
                submitIcon.classList.remove('hidden');
                loadingIcon.classList.add('hidden');
            }
        });
    }
});

// Admin Panel Functionality - Redirect to admin page

// Show admin login directly with key combination (Ctrl+Shift+A)
document.addEventListener('keydown', function(e) {
    // Debug: log all key combinations
    if (e.ctrlKey || e.shiftKey || e.altKey) {
        console.log('Key combination pressed:', {
            key: e.key,
            ctrl: e.ctrlKey,
            shift: e.shiftKey,
            alt: e.altKey,
            code: e.code
        });
    }
    
    if (e.ctrlKey && e.shiftKey && e.key === 'A') {
        e.preventDefault();
        console.log('Admin login triggered by Ctrl+Shift+A');
        showAdminLogin();
    }
});

// Alternative: Triple-click on logo to show admin login
document.addEventListener('DOMContentLoaded', function() {
    const logos = document.querySelectorAll('.logo-admin-trigger');
    console.log('Found logo elements:', logos.length);
    
    logos.forEach((logo, index) => {
        console.log('Setting up admin trigger for logo', index);
        let clickCount = 0;
        let clickTimer = null;
        
        logo.addEventListener('click', function(e) {
            clickCount++;
            console.log('Logo clicked, count:', clickCount);
            
            if (clickCount === 1) {
                clickTimer = setTimeout(() => {
                    clickCount = 0;
                }, 800);
            } else if (clickCount === 3) {
                clearTimeout(clickTimer);
                clickCount = 0;
                e.preventDefault();
                console.log('Admin login triggered by triple-click');
                showAdminLogin();
            }
        });
    });
});

// Admin login function - redirect to admin page
function showAdminLogin() {
    // Redirect directly to admin login page
    window.location.href = '/appcraftservices/admin/login.php';
}

// Dynamic year in footer
document.addEventListener('DOMContentLoaded', function() {
    const currentYear = new Date().getFullYear();
    const yearElement = document.getElementById('current-year');
    if (yearElement) {
        yearElement.textContent = currentYear;
    }
});

// Real-time Review System
class ReviewSystem {
    constructor() {
        this.reviews = [];
        this.currentOffset = 0;
        this.hasMore = true;
        this.eventSource = null;
        this.isLoading = false;
        
        this.init();
    }
    
    init() {
        // Only initialize on pages with reviews section
        if (!document.getElementById('reviews-container')) {
            return;
        }
        
        this.loadInitialReviews();
        this.setupEventListeners();
        this.startLiveUpdates();
    }
    
    async loadInitialReviews() {
        this.isLoading = true;
        try {
            const response = await fetch('/appcraftservices/api/reviews/get_approved_reviews.php?limit=6&offset=0');
            const data = await response.json();
            
            if (data.success) {
                this.reviews = data.reviews;
                this.currentOffset = data.reviews.length;
                this.hasMore = data.pagination.has_more;
                
                this.renderReviews();
                this.updateStats(data.statistics);
                this.updateLoadMoreButton();
            } else {
                this.showError('Failed to load reviews');
            }
        } catch (error) {
            console.error('Error loading reviews:', error);
            this.showError('Error loading reviews');
        } finally {
            this.isLoading = false;
        }
    }
    
    async loadMoreReviews() {
        if (this.isLoading || !this.hasMore) return;
        
        this.isLoading = true;
        const loadMoreBtn = document.getElementById('load-more-reviews');
        const originalText = loadMoreBtn.textContent;
        loadMoreBtn.textContent = 'Loading...';
        loadMoreBtn.disabled = true;
        
        try {
            const response = await fetch(`/appcraftservices/api/reviews/get_approved_reviews.php?limit=6&offset=${this.currentOffset}`);
            const data = await response.json();
            
            if (data.success) {
                this.reviews = [...this.reviews, ...data.reviews];
                this.currentOffset += data.reviews.length;
                this.hasMore = data.pagination.has_more;
                
                this.renderNewReviews(data.reviews);
                this.updateLoadMoreButton();
            }
        } catch (error) {
            console.error('Error loading more reviews:', error);
            this.showNotification('Error loading more reviews', 'error');
        } finally {
            this.isLoading = false;
            loadMoreBtn.textContent = originalText;
            loadMoreBtn.disabled = false;
        }
    }
    
    renderReviews() {
        const container = document.getElementById('reviews-container');
        if (!container) return;
        
        if (this.reviews.length === 0) {
            container.innerHTML = `
                <div class="text-center col-span-full py-8">
                    <div class="text-gray-400 text-4xl mb-4">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <p class="text-gray-600">No client reviews yet. Be the first founder to share your success story!</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = this.reviews.map(review => this.createReviewHTML(review)).join('');
        this.animateReviews();
    }
    
    renderNewReviews(newReviews) {
        const container = document.getElementById('reviews-container');
        if (!container) return;
        
        newReviews.forEach(review => {
            const reviewElement = document.createElement('div');
            reviewElement.innerHTML = this.createReviewHTML(review);
            reviewElement.firstElementChild.style.opacity = '0';
            reviewElement.firstElementChild.style.transform = 'translateY(20px)';
            
            container.appendChild(reviewElement.firstElementChild);
            
            // Animate in
            setTimeout(() => {
                reviewElement.firstElementChild.style.transition = 'all 0.5s ease';
                reviewElement.firstElementChild.style.opacity = '1';
                reviewElement.firstElementChild.style.transform = 'translateY(0)';
            }, 100);
        });
    }
    
    createReviewHTML(review) {
        return `
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow duration-300 startup-review-card" data-review-id="${review.id}">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h4 class="font-semibold text-gray-900">${this.escapeHtml(review.reviewer_name)}</h4>
                        <p class="text-sm text-gray-600 font-medium">${this.escapeHtml(review.company)}</p>
                        <p class="text-xs text-gray-500">${review.project_type} • ${review.project_completion_date_formatted}</p>
                        ${review.funding_stage ? `<span class="inline-block mt-1 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">${review.funding_stage}</span>` : ''}
                    </div>
                    <div class="flex flex-col items-end">
                        ${review.verified ? '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full mb-2">✓ Verified</span>' : ''}
                        <div class="text-yellow-500 text-lg">${review.rating_stars}</div>
                    </div>
                </div>
                
                <blockquote class="text-gray-700 italic mb-4 leading-relaxed">
                    "${this.escapeHtml(review.content)}"
                </blockquote>
                
                <div class="flex items-center justify-between text-xs text-gray-500 border-t pt-3">
                    <span>Reviewed ${review.submission_date_formatted}</span>
                    <div class="flex items-center space-x-2">
                        ${review.contact_permission ? '<span class="text-green-600 flex items-center"><i class="fas fa-check-circle mr-1"></i>Reference available</span>' : ''}
                        ${review.funding_raised ? `<span class="text-blue-600 font-medium">Raised: ${review.funding_raised}</span>` : ''}
                    </div>
                </div>
            </div>
        `;
    }
    
    updateStats(statistics) {
        const totalCountEl = document.getElementById('total-review-count');
        const avgRatingEl = document.getElementById('average-rating-display');
        
        if (totalCountEl) {
            totalCountEl.textContent = statistics.total_reviews;
        }
        
        if (avgRatingEl) {
            avgRatingEl.textContent = statistics.average_rating_stars;
            avgRatingEl.title = `${statistics.average_rating}/5 stars`;
        }
    }
    
    updateLoadMoreButton() {
        const loadMoreBtn = document.getElementById('load-more-reviews');
        if (!loadMoreBtn) return;
        
        if (this.hasMore && this.reviews.length > 0) {
            loadMoreBtn.style.display = 'inline-block';
        } else {
            loadMoreBtn.style.display = 'none';
        }
    }
    
    setupEventListeners() {
        const loadMoreBtn = document.getElementById('load-more-reviews');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => this.loadMoreReviews());
        }
    }
    
    startLiveUpdates() {
        if (typeof EventSource === 'undefined') {
            console.warn('Server-Sent Events not supported');
            return;
        }
        
        try {
            this.eventSource = new EventSource('/appcraftservices/api/reviews/live_updates.php');
            
            this.eventSource.addEventListener('connected', (event) => {
                console.log('Connected to review updates');
                this.showLiveIndicator(true);
            });
            
            this.eventSource.addEventListener('review_update', (event) => {
                const data = JSON.parse(event.data);
                if (data.type === 'new_reviews' && data.reviews.length > 0) {
                    this.handleNewReviews(data.reviews);
                }
            });
            
            this.eventSource.addEventListener('error', (event) => {
                console.warn('Live updates connection error');
                this.showLiveIndicator(false);
                
                // Retry connection after 30 seconds
                setTimeout(() => {
                    if (this.eventSource.readyState === EventSource.CLOSED) {
                        this.startLiveUpdates();
                    }
                }, 30000);
            });
            
            this.eventSource.addEventListener('disconnected', (event) => {
                console.log('Disconnected from review updates');
                this.showLiveIndicator(false);
            });
            
        } catch (error) {
            console.error('Error starting live updates:', error);
        }
    }
    
    handleNewReviews(newReviews) {
        // Add new reviews to the beginning of the array
        this.reviews = [...newReviews, ...this.reviews];
        
        // Show notification
        this.showNotification(`${newReviews.length} new client review${newReviews.length > 1 ? 's' : ''} added!`, 'success');
        
        // Add new reviews to the top of the container
        const container = document.getElementById('reviews-container');
        if (container) {
            newReviews.reverse().forEach(review => {
                const reviewElement = document.createElement('div');
                reviewElement.innerHTML = this.createReviewHTML(review);
                const reviewDiv = reviewElement.firstElementChild;
                
                // Add highlight animation with startup-focused styling
                reviewDiv.classList.add('bg-blue-50', 'border-blue-300', 'ring-2', 'ring-blue-200');
                reviewDiv.style.opacity = '0';
                reviewDiv.style.transform = 'translateY(-20px)';
                
                container.insertBefore(reviewDiv, container.firstChild);
                
                // Animate in
                setTimeout(() => {
                    reviewDiv.style.transition = 'all 0.5s ease';
                    reviewDiv.style.opacity = '1';
                    reviewDiv.style.transform = 'translateY(0)';
                }, 100);
                
                // Remove highlight after animation
                setTimeout(() => {
                    reviewDiv.classList.remove('bg-blue-50', 'border-blue-300', 'ring-2', 'ring-blue-200');
                }, 3000);
            });
        }
    }
    
    showLiveIndicator(show) {
        const indicator = document.getElementById('live-indicator');
        if (indicator) {
            indicator.style.display = show ? 'block' : 'none';
        }
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
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
    
    showError(message) {
        const container = document.getElementById('reviews-container');
        if (container) {
            container.innerHTML = `
                <div class="text-center col-span-full py-8">
                    <div class="text-red-400 text-4xl mb-4">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <p class="text-red-600">${message}</p>
                    <button onclick="reviewSystem.loadInitialReviews()" class="mt-4 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Try Again
                    </button>
                </div>
            `;
        }
    }
    
    animateReviews() {
        const reviews = document.querySelectorAll('[data-review-id]');
        reviews.forEach((review, index) => {
            review.style.opacity = '0';
            review.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                review.style.transition = 'all 0.5s ease';
                review.style.opacity = '1';
                review.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    destroy() {
        if (this.eventSource) {
            this.eventSource.close();
        }
    }
}

// Initialize review system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on pages with reviews section
    if (document.getElementById('reviews-container')) {
        window.reviewSystem = new ReviewSystem();
    }
});

// Image lazy loading and optimization
class ImageOptimizer {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupLazyLoading();
        this.optimizeImages();
        this.setupProgressiveLoading();
    }
    
    setupLazyLoading() {
        // Use Intersection Observer for lazy loading
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });
            
            // Observe all images with data-src attribute
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        } else {
            // Fallback for older browsers
            this.loadAllImages();
        }
    }
    
    loadImage(img) {
        const src = img.getAttribute('data-src');
        if (src) {
            img.src = src;
            img.classList.add('loaded');
            img.removeAttribute('data-src');
        }
    }
    
    loadAllImages() {
        document.querySelectorAll('img[data-src]').forEach(img => {
            this.loadImage(img);
        });
    }
    
    optimizeImages() {
        // Add loading="lazy" to images that don't have it
        document.querySelectorAll('img:not([loading])').forEach(img => {
            img.setAttribute('loading', 'lazy');
        });
        
        // Add proper alt attributes for accessibility
        document.querySelectorAll('img:not([alt])').forEach(img => {
            img.setAttribute('alt', '');
        });
    }
    
    setupProgressiveLoading() {
        // Progressive loading for heavy content sections
        const contentSections = document.querySelectorAll('.progressive-load');
        
        if ('IntersectionObserver' in window) {
            const contentObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('loaded');
                        contentObserver.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '100px 0px'
            });
            
            contentSections.forEach(section => {
                contentObserver.observe(section);
            });
        }
    }
}

// Performance optimization utilities
class PerformanceOptimizer {
    constructor() {
        this.init();
    }
    
    init() {
        this.optimizeCSS();
        this.optimizeJavaScript();
        this.setupResourceHints();
        this.monitorPerformance();
    }
    
    optimizeCSS() {
        // Remove unused CSS classes (basic implementation)
        this.deferNonCriticalCSS();
    }
    
    deferNonCriticalCSS() {
        // Defer non-critical CSS loading
        const nonCriticalCSS = document.querySelectorAll('link[rel="stylesheet"][data-defer]');
        nonCriticalCSS.forEach(link => {
            link.media = 'print';
            link.onload = function() {
                this.media = 'all';
            };
        });
    }
    
    optimizeJavaScript() {
        // Debounce scroll events
        this.debounceScrollEvents();
        
        // Optimize event listeners
        this.optimizeEventListeners();
    }
    
    debounceScrollEvents() {
        let scrollTimeout;
        const originalScroll = window.onscroll;
        
        window.onscroll = function(e) {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                if (originalScroll) originalScroll.call(this, e);
            }, 16); // ~60fps
        };
    }
    
    optimizeEventListeners() {
        // Use passive listeners for better performance
        const passiveEvents = ['scroll', 'touchstart', 'touchmove', 'wheel'];
        
        passiveEvents.forEach(eventType => {
            const elements = document.querySelectorAll(`[data-${eventType}]`);
            elements.forEach(element => {
                const handler = element.getAttribute(`data-${eventType}`);
                if (handler && window[handler]) {
                    element.addEventListener(eventType, window[handler], { passive: true });
                }
            });
        });
    }
    
    setupResourceHints() {
        // Add preconnect for external resources
        this.addPreconnect('https://cdn.tailwindcss.com');
        this.addPreconnect('https://cdnjs.cloudflare.com');
        
        // Preload critical resources
        this.preloadCriticalResources();
    }
    
    addPreconnect(url) {
        const link = document.createElement('link');
        link.rel = 'preconnect';
        link.href = url;
        document.head.appendChild(link);
    }
    
    preloadCriticalResources() {
        // Preload critical fonts
        const criticalFonts = [
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
        ];
        
        criticalFonts.forEach(fontUrl => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = fontUrl;
            link.as = 'style';
            document.head.appendChild(link);
        });
    }
    
    monitorPerformance() {
        // Monitor Core Web Vitals
        if ('PerformanceObserver' in window) {
            this.observeLCP();
            this.observeFID();
            this.observeCLS();
        }
    }
    
    observeLCP() {
        // Largest Contentful Paint
        const observer = new PerformanceObserver((list) => {
            const entries = list.getEntries();
            const lastEntry = entries[entries.length - 1];
            console.log('LCP:', lastEntry.startTime);
        });
        observer.observe({ entryTypes: ['largest-contentful-paint'] });
    }
    
    observeFID() {
        // First Input Delay
        const observer = new PerformanceObserver((list) => {
            const entries = list.getEntries();
            entries.forEach(entry => {
                console.log('FID:', entry.processingStart - entry.startTime);
            });
        });
        observer.observe({ entryTypes: ['first-input'] });
    }
    
    observeCLS() {
        // Cumulative Layout Shift
        let clsValue = 0;
        const observer = new PerformanceObserver((list) => {
            const entries = list.getEntries();
            entries.forEach(entry => {
                if (!entry.hadRecentInput) {
                    clsValue += entry.value;
                }
            });
            console.log('CLS:', clsValue);
        });
        observer.observe({ entryTypes: ['layout-shift'] });
    }
}

// Mobile-specific optimizations
class MobileOptimizer {
    constructor() {
        this.isMobile = this.detectMobile();
        if (this.isMobile) {
            this.init();
        }
    }
    
    detectMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               window.innerWidth <= 768;
    }
    
    init() {
        this.optimizeForMobile();
        this.setupTouchOptimizations();
        this.optimizeViewport();
    }
    
    optimizeForMobile() {
        // Reduce animations on mobile for better performance
        if (this.isLowEndDevice()) {
            document.body.classList.add('reduce-motion');
        }
        
        // Optimize images for mobile
        this.optimizeMobileImages();
        
        // Defer non-essential scripts
        this.deferNonEssentialScripts();
    }
    
    isLowEndDevice() {
        // Simple heuristic for low-end devices
        return navigator.hardwareConcurrency <= 2 || 
               navigator.deviceMemory <= 2 ||
               /Android.*Chrome\/[0-5]/.test(navigator.userAgent);
    }
    
    optimizeMobileImages() {
        document.querySelectorAll('img').forEach(img => {
            // Add mobile-specific optimizations
            if (!img.hasAttribute('loading')) {
                img.setAttribute('loading', 'lazy');
            }
            
            // Set appropriate sizes for responsive images
            if (!img.hasAttribute('sizes') && img.hasAttribute('srcset')) {
                img.setAttribute('sizes', '(max-width: 768px) 100vw, 50vw');
            }
        });
    }
    
    deferNonEssentialScripts() {
        // Defer analytics and non-critical scripts on mobile
        const nonEssentialScripts = document.querySelectorAll('script[data-defer-mobile]');
        nonEssentialScripts.forEach(script => {
            if (script.src) {
                const newScript = document.createElement('script');
                newScript.src = script.src;
                newScript.defer = true;
                script.parentNode.replaceChild(newScript, script);
            }
        });
    }
    
    setupTouchOptimizations() {
        // Improve touch responsiveness
        document.addEventListener('touchstart', function() {}, { passive: true });
        
        // Prevent zoom on input focus (iOS)
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            if (!input.hasAttribute('style')) {
                input.style.fontSize = '16px';
            }
        });
    }
    
    optimizeViewport() {
        // Ensure proper viewport configuration
        let viewport = document.querySelector('meta[name="viewport"]');
        if (!viewport) {
            viewport = document.createElement('meta');
            viewport.name = 'viewport';
            document.head.appendChild(viewport);
        }
        
        // Optimize viewport for mobile
        viewport.content = 'width=device-width, initial-scale=1.0, viewport-fit=cover';
    }
}

// Initialize optimizations when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize performance optimizations
    new ImageOptimizer();
    new PerformanceOptimizer();
    new MobileOptimizer();
});

// Service Worker registration for caching (if available)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/appcraftservices/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registration successful');
            })
            .catch(function(err) {
                console.log('ServiceWorker registration failed');
            });
    });
}