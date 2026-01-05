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

// Contact form handling - UPDATED FIX
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
            if (submitIcon) submitIcon.classList.add('hidden');
            if (loadingIcon) loadingIcon.classList.remove('hidden');
            
            try {
                // Collect form data
                const formData = new FormData(contactForm);
                const data = {};
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }
                
                console.log('Submitting form data:', data);
                
                // Determine API path based on current location
                // If we are in /contact/, go up one level. If at root, go into api.
                const apiPath = window.location.pathname.includes('/contact') ? 
                    '../api/contact.php' : 'api/contact.php';
                
                console.log('Using API path:', apiPath);
                
                // Send to API
                const response = await fetch(apiPath, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                console.log('Response status:', response.status);
                
                // Robust JSON parsing
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
                    // Hide form and show success message
                    contactForm.style.display = 'none';
                    if (successMessage) {
                        successMessage.classList.remove('hidden');
                        successMessage.innerHTML = `
                            <div class="flex items-center justify-center flex-col text-center p-6">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Message Sent Successfully!</h3>
                                <p class="text-gray-600 mb-2">Thank you for reaching out. We'll get back to you within 24 hours.</p>
                                <p class="text-sm text-gray-500 mb-4">Your message has been received and saved to our system.</p>
                                <p class="text-xs text-gray-400 mb-4">Reference ID: ${result.message_id || 'N/A'}</p>
                                <button onclick="location.reload()" class="bg-electric-blue text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300 font-medium">Send Another Message</button>
                            </div>
                        `;
                    } else {
                        // Fallback if success message element doesn't exist
                        alert('Message sent successfully! We\'ll get back to you within 24 hours.');
                        contactForm.reset();
                        submitBtn.disabled = false;
                        submitText.textContent = 'Send Message';
                        if (submitIcon) submitIcon.classList.remove('hidden');
                        if (loadingIcon) loadingIcon.classList.add('hidden');
                    }
                } else {
                    throw new Error(result.message || 'Failed to send message');
                }
                
            } catch (error) {
                console.error('Submission Error:', error);
                
                // Show error message with better UX
                const errorMessage = error.message || 'An unexpected error occurred';
                
                // Create or update error display
                let errorDiv = document.getElementById('error-message');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.id = 'error-message';
                    errorDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg';
                    contactForm.parentNode.insertBefore(errorDiv, contactForm.nextSibling);
                }
                
                errorDiv.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span><strong>Error:</strong> ${errorMessage}</span>
                    </div>
                    <button onclick="this.parentElement.style.display='none'" class="mt-2 text-sm text-red-600 hover:text-red-800 underline">Dismiss</button>
                `;
                errorDiv.style.display = 'block';
                
                // Reset button state
                submitBtn.disabled = false;
                submitText.textContent = 'Send Message';
                if (submitIcon) submitIcon.classList.remove('hidden');
                if (loadingIcon) loadingIcon.classList.add('hidden');
            }
        });
    }
});

// Admin Panel Functionality - Redirect to admin page

// Show admin login directly with key combination (Ctrl+Shift+A)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.shiftKey && e.key === 'A') {
        e.preventDefault();
        console.log('Admin login triggered by Ctrl+Shift+A');
        showAdminLogin();
    }
});

// Alternative: Triple-click on logo to show admin login
document.addEventListener('DOMContentLoaded', function() {
    const logos = document.querySelectorAll('.logo-admin-trigger');
    
    logos.forEach((logo) => {
        let clickCount = 0;
        let clickTimer = null;
        
        logo.addEventListener('click', function(e) {
            clickCount++;
            
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
    // Determine path based on current location
    const adminPath = window.location.pathname.includes('/contact') ? 
        '../admin/login.php' : 'admin/login.php';
    window.location.href = adminPath;
}

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
            // Adjust path based on location
            const apiPath = window.location.pathname.includes('/contact') ? 
                '../api/reviews/get_approved_reviews.php' : 'api/reviews/get_approved_reviews.php';

            const response = await fetch(`${apiPath}?limit=6&offset=0`);
            const data = await response.json();
            
            if (data.success) {
                this.reviews = data.reviews;
                this.currentOffset = data.reviews.length;
                this.hasMore = data.pagination.has_more;
                
                this.renderReviews();
                this.updateStats(data.statistics);
                this.updateLoadMoreButton();
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
            const apiPath = window.location.pathname.includes('/contact') ? 
                '../api/reviews/get_approved_reviews.php' : 'api/reviews/get_approved_reviews.php';

            const response = await fetch(`${apiPath}?limit=6&offset=${this.currentOffset}`);
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
                        <p class="text-xs text-gray-500">${review.project_type} â€¢ ${review.project_completion_date_formatted}</p>
                        ${review.funding_stage ? `<span class="inline-block mt-1 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">${review.funding_stage}</span>` : ''}
                    </div>
                    <div class="flex flex-col items-end">
                        ${review.verified ? '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full mb-2">âœ“ Verified</span>' : ''}
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
            return;
        }
        
        try {
            const apiPath = window.location.pathname.includes('/contact') ? 
                '../api/reviews/live_updates.php' : 'api/reviews/live_updates.php';

            this.eventSource = new EventSource(apiPath);
            
            this.eventSource.addEventListener('review_update', (event) => {
                const data = JSON.parse(event.data);
                if (data.type === 'new_reviews' && data.reviews.length > 0) {
                    this.handleNewReviews(data.reviews);
                }
            });
            
            this.eventSource.addEventListener('error', (event) => {
                // Silently fail or retry
                if (this.eventSource.readyState === EventSource.CLOSED) {
                    // Optional: logic to reconnect
                }
            });
            
        } catch (error) {
            console.error('Error starting live updates:', error);
        }
    }
    
    handleNewReviews(newReviews) {
        this.reviews = [...newReviews, ...this.reviews];
        this.showNotification(`${newReviews.length} new client review${newReviews.length > 1 ? 's' : ''} added!`, 'success');
        
        const container = document.getElementById('reviews-container');
        if (container) {
            newReviews.reverse().forEach(review => {
                const reviewElement = document.createElement('div');
                reviewElement.innerHTML = this.createReviewHTML(review);
                const reviewDiv = reviewElement.firstElementChild;
                
                reviewDiv.classList.add('bg-blue-50', 'border-blue-300', 'ring-2', 'ring-blue-200');
                reviewDiv.style.opacity = '0';
                reviewDiv.style.transform = 'translateY(-20px)';
                
                container.insertBefore(reviewDiv, container.firstChild);
                
                setTimeout(() => {
                    reviewDiv.style.transition = 'all 0.5s ease';
                    reviewDiv.style.opacity = '1';
                    reviewDiv.style.transform = 'translateY(0)';
                }, 100);
                
                setTimeout(() => {
                    reviewDiv.classList.remove('bg-blue-50', 'border-blue-300', 'ring-2', 'ring-blue-200');
                }, 3000);
            });
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
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize review system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
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
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        } else {
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
        document.querySelectorAll('img:not([loading])').forEach(img => {
            img.setAttribute('loading', 'lazy');
        });
        
        document.querySelectorAll('img:not([alt])').forEach(img => {
            img.setAttribute('alt', '');
        });
    }
    
    setupProgressiveLoading() {
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
    }
    
    optimizeCSS() {
        const nonCriticalCSS = document.querySelectorAll('link[rel="stylesheet"][data-defer]');
        nonCriticalCSS.forEach(link => {
            link.media = 'print';
            link.onload = function() {
                this.media = 'all';
            };
        });
    }
    
    optimizeJavaScript() {
        this.debounceScrollEvents();
        this.optimizeEventListeners();
    }
    
    debounceScrollEvents() {
        let scrollTimeout;
        const originalScroll = window.onscroll;
        
        window.onscroll = function(e) {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                if (originalScroll) originalScroll.call(this, e);
            }, 16);
        };
    }
    
    optimizeEventListeners() {
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
        this.addPreconnect('https://cdn.tailwindcss.com');
        this.addPreconnect('https://cdnjs.cloudflare.com');
    }
    
    addPreconnect(url) {
        if (!document.querySelector(`link[href="${url}"]`)) {
            const link = document.createElement('link');
            link.rel = 'preconnect';
            link.href = url;
            document.head.appendChild(link);
        }
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
        if (this.isLowEndDevice()) {
            document.body.classList.add('reduce-motion');
        }
        
        this.optimizeMobileImages();
        this.deferNonEssentialScripts();
    }
    
    isLowEndDevice() {
        return navigator.hardwareConcurrency <= 2 || 
               navigator.deviceMemory <= 2 ||
               /Android.*Chrome\/[0-5]/.test(navigator.userAgent);
    }
    
    optimizeMobileImages() {
        document.querySelectorAll('img').forEach(img => {
            if (!img.hasAttribute('loading')) {
                img.setAttribute('loading', 'lazy');
            }
            if (!img.hasAttribute('sizes') && img.hasAttribute('srcset')) {
                img.setAttribute('sizes', '(max-width: 768px) 100vw, 50vw');
            }
        });
    }
    
    deferNonEssentialScripts() {
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
        document.addEventListener('touchstart', function() {}, { passive: true });
        
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            if (!input.hasAttribute('style')) {
                input.style.fontSize = '16px';
            }
        });
    }
    
    optimizeViewport() {
        let viewport = document.querySelector('meta[name="viewport"]');
        if (!viewport) {
            viewport = document.createElement('meta');
            viewport.name = 'viewport';
            document.head.appendChild(viewport);
        }
        viewport.content = 'width=device-width, initial-scale=1.0, viewport-fit=cover';
    }
}

// Initialize optimizations when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    new ImageOptimizer();
    new PerformanceOptimizer();
    new MobileOptimizer();
});

// Service Worker registration for caching (if available) - Chrome compatibility update
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        // Force unregister old service workers first
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for(let registration of registrations) {
                registration.unregister();
            }
        }).then(function() {
            // Register new service worker after clearing old ones
            const swPath = window.location.pathname.includes('/contact') ? '../sw.js' : 'sw.js';
            
            navigator.serviceWorker.register(swPath + '?v=' + Date.now())
                .then(function(registration) {
                    console.log('ServiceWorker registration successful');
                    // Force immediate activation
                    if (registration.waiting) {
                        registration.waiting.postMessage({command: 'skipWaiting'});
                    }
                })
                .catch(function(err) {
                    // Silently fail
                });
        });
    });
}

// Project Portfolio Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const projectCards = document.querySelectorAll('.project-card');
    const modal = document.getElementById('project-modal');
    const closeModal = document.getElementById('close-modal');
    
    // Verify elements exist
    if (!modal || !closeModal || projectCards.length === 0) {
        console.warn('Project modal elements not found or no project cards available');
        return;
    }
    
    // Handle image loading for project cards
    const projectImages = document.querySelectorAll('.project-card img');
    projectImages.forEach(img => {
        if (img.complete) {
            img.classList.add('loaded');
        } else {
            img.addEventListener('load', function() {
                this.classList.add('loaded');
            });
            img.addEventListener('error', function() {
                console.warn('Failed to load project image:', this.src);
                // Keep the gradient background as fallback
                this.style.display = 'none';
            });
        }
    });
    
    // Project data with logo configurations
    const projectData = {
        'mealmate': {
            title: 'MealMate',
            logo: {
                image: 'assets/projects/MealMate.png',
                alt: 'MealMate Logo'
            },
            description: 'A comprehensive meal planning and nutrition tracking application that helps users maintain healthy eating habits through intelligent recipe suggestions, automated grocery lists, and personalized dietary management.',
            tags: ['Health', 'Nutrition', 'Lifestyle'],
            features: [
                'Smart meal planning with AI-powered recipe suggestions',
                'Comprehensive nutrition tracking and analysis',
                'Automated grocery list generation',
                'Dietary preference and restriction management',
                'Calorie and macro tracking with visual analytics',
                'Integration with popular fitness apps'
            ],
            tech: ['React', 'Node.js', 'MongoDB', 'Nutrition API', 'PWA']
        },
        'notifyme': {
            title: 'Notify Me - Remote Job Alerts',
            logo: {
                image: 'assets/projects/Notify Me.png',
                alt: 'Notify Me - Remote Job Alerts Logo'
            },
            description: 'Get instant alerts for new remote jobs from your favorite sources. A comprehensive job alert system that manages RSS feeds, filters by category, and ensures you never miss an opportunity in the remote work market.',
            tags: ['Job Alerts', 'Remote Work', 'PWA'],
            features: [
                'User-managed RSS feeds with add, edit, delete functionality and optional API backup',
                'Fetches jobs from multiple remote job sources automatically',
                'Modern dashboard with category filtering, source grouping, and advanced search',
                'Progressive Web App (PWA) with installable interface and offline support',
                'Background sync for failed requests ensuring no missed opportunities',
                'Mobile-friendly, responsive design optimized for all devices',
                'Profile management and customizable user settings',
                'Real-time job notifications and alert management'
            ],
            tech: ['Node.js', 'MongoDB', 'PWA', 'RSS Feeds', 'Background Sync']
        },
        'automated-restaurant': {
            title: 'Automated Restaurant',
            logo: {
                image: 'assets/projects/Automated Restaurant.png',
                alt: 'Automated Restaurant Logo'
            },
            description: 'A complete restaurant management ecosystem that streamlines operations through automated ordering, intelligent inventory tracking, and optimized kitchen workflow management for enhanced efficiency.',
            tags: ['Restaurant', 'Automation', 'POS'],
            features: [
                'Automated order processing and kitchen display system',
                'Real-time inventory tracking with low-stock alerts',
                'Kitchen workflow optimization and timing coordination',
                'Customer ordering system with customization options',
                'Staff scheduling and performance analytics',
                'Integration with payment processors and delivery platforms'
            ],
            tech: ['Angular', 'Django', 'PostgreSQL', 'Redis', 'Payment APIs']
        },
        'quickbudgetai': {
            title: 'QuickBudgetAI',
            logo: {
                image: 'assets/projects/QuickBudgetAI.png',
                alt: 'QuickBudgetAI Logo'
            },
            description: 'An AI-powered personal finance application that automatically categorizes expenses, provides intelligent budget recommendations, and helps users achieve their financial goals through smart insights.',
            tags: ['FinTech', 'AI', 'Personal Finance'],
            features: [
                'AI-powered expense categorization and analysis',
                'Intelligent budget recommendations and adjustments',
                'Financial goal setting and progress tracking',
                'Bank account integration and transaction sync',
                'Spending pattern analysis and insights',
                'Bill reminder and payment scheduling'
            ],
            tech: ['React Native', 'Python', 'TensorFlow', 'Plaid API', 'AWS']
        },
        'clearpath': {
            title: 'ClearPath Client Services',
            logo: {
                image: 'assets/projects/ClearPath Client Services.png',
                alt: 'ClearPath Client Services Logo'
            },
            description: 'A comprehensive client relationship management platform designed to streamline service delivery through advanced project tracking, communication tools, and performance optimization features.',
            tags: ['CRM', 'Project Management', 'Client Services'],
            features: [
                'Advanced client relationship and project tracking',
                'Integrated communication and collaboration tools',
                'Service delivery optimization and workflow automation',
                'Performance analytics and reporting dashboard',
                'Document management and client portal access',
                'Billing integration and invoice management'
            ],
            tech: ['React', 'Laravel', 'MySQL', 'WebSocket', 'AWS S3']
        },
        'willpdf': {
            title: 'WillPDF',
            logo: {
                image: 'assets/projects/WillPDF.png',
                alt: 'WillPDF Logo'
            },
            description: 'A sophisticated legal document generation platform that creates customized wills and estate planning documents through guided workflows and intelligent form completion.',
            tags: ['LegalTech', 'Document Generation', 'Estate Planning'],
            features: [
                'Guided will creation with intelligent form completion',
                'Customizable estate planning document templates',
                'Legal compliance checking and validation',
                'Secure document storage and access management',
                'Digital signature integration and witness management',
                'State-specific legal requirement adaptation'
            ],
            tech: ['Vue.js', 'Node.js', 'MongoDB', 'PDF Generation', 'DocuSign API']
        },
        'tsu-staff': {
            title: 'TSU Staff Profile',
            logo: {
                image: 'assets/projects/TSU Staff Profile.png',
                alt: 'TSU Staff Profile Logo'
            },
            description: 'A comprehensive university staff directory and profile management system featuring role-based access control, organizational hierarchy visualization, and advanced search capabilities.',
            tags: ['Education', 'Directory', 'University'],
            features: [
                'Comprehensive staff directory with advanced search',
                'Role-based access control and permission management',
                'Organizational hierarchy visualization and navigation',
                'Profile management with photo and credential tracking',
                'Department and course assignment management',
                'Integration with university authentication systems'
            ],
            tech: ['Angular', 'ASP.NET Core', 'SQL Server', 'Active Directory', 'Azure']
        },
        'federal-leave': {
            title: 'Federal California Leave Assistant',
            logo: {
                image: 'assets/projects/Federal California Leave Assistant.png',
                alt: 'Federal California Leave Assistant Logo'
            },
            description: 'An advanced HR compliance tool that automates California state leave calculations and federal FMLA requirements, ensuring businesses maintain full compliance with complex employment regulations.',
            tags: ['HR Tech', 'Compliance', 'Legal'],
            features: [
                'Automated California state leave calculation and tracking',
                'Federal FMLA compliance monitoring and reporting',
                'Employee eligibility assessment and documentation',
                'Integration with payroll and HR management systems',
                'Compliance reporting and audit trail generation',
                'Real-time regulation updates and notifications'
            ],
            tech: ['React', 'Spring Boot', 'PostgreSQL', 'Government APIs', 'Docker']
        }
    };
    
    // Add click event to project cards
    projectCards.forEach((card, index) => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Always use currentTarget (the element the event listener is attached to)
            // instead of this, which might be different due to event bubbling
            const projectCard = e.currentTarget;
            const projectKey = projectCard.getAttribute('data-project');
            
            const project = projectData[projectKey];
            
            if (project) {
                showProjectModal(project);
            } else {
                console.error('Project data not found for:', projectKey);
            }
        });
    });
    
    // Close modal events
    if (closeModal) {
        closeModal.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            hideProjectModal();
        });
    }
    
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideProjectModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            hideProjectModal();
        }
    });
    
    function showProjectModal(project) {
        if (!modal) {
            console.error('Modal element not found');
            return;
        }
        
        // Update small modal logo in header
        const logoContainer = document.getElementById('modal-logo-container');
        if (logoContainer) {
            logoContainer.className = 'w-16 h-16 rounded-lg flex items-center justify-center bg-gray-50 p-2';
            logoContainer.innerHTML = `<img src="${project.logo.image}" alt="${project.logo.alt}" class="w-full h-full object-contain rounded-lg">`;
        }

        // Update large featured logo
        const largeLogo = document.getElementById('modal-large-logo');
        if (largeLogo) {
            largeLogo.innerHTML = `
                <img src="${project.logo.image}" 
                     alt="${project.logo.alt}" 
                     class="w-32 h-32 md:w-40 md:h-40 object-contain mx-auto relative z-10"
                     style="max-width: 200px; max-height: 200px;">
            `;
        }

        // Update project title below large logo
        const modalProjectTitle = document.getElementById('modal-project-title');
        if (modalProjectTitle) {
            modalProjectTitle.textContent = project.title;
        }
        
        // Update modal content
        const titleElement = document.getElementById('modal-title');
        if (titleElement) {
            titleElement.textContent = project.title;
        }
        
        const descriptionElement = document.getElementById('modal-description');
        if (descriptionElement) {
            descriptionElement.textContent = project.description;
        }
        
        // Update tags
        const tagsContainer = document.getElementById('modal-tags');
        if (tagsContainer) {
            tagsContainer.innerHTML = project.tags.map(tag => 
                `<span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">${tag}</span>`
            ).join('');
        }
        
        // Update features
        const featuresContainer = document.getElementById('modal-features');
        if (featuresContainer) {
            featuresContainer.innerHTML = `
                <div>
                    <h4 class="text-lg font-semibold text-navy mb-3">Key Features:</h4>
                    <ul class="space-y-3">
                        ${project.features.map(feature => 
                            `<li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700 leading-relaxed">${feature}</span>
                            </li>`
                        ).join('')}
                    </ul>
                </div>
            `;
        }
        
        // Update tech stack
        const techContainer = document.getElementById('modal-tech-list');
        if (techContainer) {
            techContainer.innerHTML = project.tech.map(tech => 
                `<span class="bg-gray-100 text-gray-800 text-sm px-3 py-1 rounded-full font-medium">${tech}</span>`
            ).join('');
        }
        
        // Show modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function hideProjectModal() {
        if (!modal) return;
        
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
});

// Projects Slider Functionality
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('projects-slider');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const indicators = document.querySelectorAll('.slider-indicator');
    
    if (!slider || !prevBtn || !nextBtn) {
        return; // Exit if slider elements don't exist
    }
    
    let currentSlide = 0;
    const totalSlides = 2; // We have 8 projects, showing 4 at a time = 2 slides
    const slideWidth = 100; // 100% width per slide
    
    // Update slider position
    function updateSlider() {
        const translateX = -currentSlide * slideWidth;
        slider.style.transform = `translateX(${translateX}%)`;
        
        // Update navigation buttons
        prevBtn.disabled = currentSlide === 0;
        nextBtn.disabled = currentSlide === totalSlides - 1;
        
        // Update indicators
        indicators.forEach((indicator, index) => {
            if (index === currentSlide) {
                indicator.classList.remove('bg-gray-300');
                indicator.classList.add('bg-electric-blue');
            } else {
                indicator.classList.remove('bg-electric-blue');
                indicator.classList.add('bg-gray-300');
            }
        });
    }
    
    // Previous slide
    prevBtn.addEventListener('click', function() {
        if (currentSlide > 0) {
            currentSlide--;
            updateSlider();
        }
    });
    
    // Next slide
    nextBtn.addEventListener('click', function() {
        if (currentSlide < totalSlides - 1) {
            currentSlide++;
            updateSlider();
        }
    });
    
    // Indicator clicks
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', function() {
            currentSlide = index;
            updateSlider();
        });
    });
    
    // Auto-slide functionality (optional)
    let autoSlideInterval;
    
    function startAutoSlide() {
        autoSlideInterval = setInterval(() => {
            if (currentSlide < totalSlides - 1) {
                currentSlide++;
            } else {
                currentSlide = 0;
            }
            updateSlider();
        }, 5000); // Change slide every 5 seconds
    }
    
    function stopAutoSlide() {
        if (autoSlideInterval) {
            clearInterval(autoSlideInterval);
        }
    }
    
    // Start auto-slide
    startAutoSlide();
    
    // Pause auto-slide on hover
    const sliderContainer = slider.parentElement.parentElement;
    sliderContainer.addEventListener('mouseenter', stopAutoSlide);
    sliderContainer.addEventListener('mouseleave', startAutoSlide);
    
    // Pause auto-slide when user interacts
    prevBtn.addEventListener('click', () => {
        stopAutoSlide();
        setTimeout(startAutoSlide, 10000); // Resume after 10 seconds
    });
    
    nextBtn.addEventListener('click', () => {
        stopAutoSlide();
        setTimeout(startAutoSlide, 10000); // Resume after 10 seconds
    });
    
    indicators.forEach(indicator => {
        indicator.addEventListener('click', () => {
            stopAutoSlide();
            setTimeout(startAutoSlide, 10000); // Resume after 10 seconds
        });
    });
    
    // Touch/swipe support for mobile
    let startX = 0;
    let endX = 0;
    
    sliderContainer.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        stopAutoSlide();
    });
    
    sliderContainer.addEventListener('touchmove', function(e) {
        endX = e.touches[0].clientX;
    });
    
    sliderContainer.addEventListener('touchend', function() {
        const threshold = 50; // Minimum swipe distance
        const diff = startX - endX;
        
        if (Math.abs(diff) > threshold) {
            if (diff > 0 && currentSlide < totalSlides - 1) {
                // Swipe left - next slide
                currentSlide++;
                updateSlider();
            } else if (diff < 0 && currentSlide > 0) {
                // Swipe right - previous slide
                currentSlide--;
                updateSlider();
            }
        }
        
        setTimeout(startAutoSlide, 10000); // Resume auto-slide after 10 seconds
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        // Only handle keyboard navigation if the slider is visible
        const sliderSection = document.querySelector('.project-slider-section');
        if (!sliderSection || !isElementInViewport(sliderSection)) {
            return;
        }
        
        if (e.key === 'ArrowLeft' && currentSlide > 0) {
            currentSlide--;
            updateSlider();
            stopAutoSlide();
            setTimeout(startAutoSlide, 10000);
        } else if (e.key === 'ArrowRight' && currentSlide < totalSlides - 1) {
            currentSlide++;
            updateSlider();
            stopAutoSlide();
            setTimeout(startAutoSlide, 10000);
        }
    });
    
    // Helper function to check if element is in viewport
    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    // Initialize slider
    updateSlider();
    
    // Responsive handling
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            updateSlider();
        }, 250);
    });
});
// Captcha functionality for contact form
let captchaAnswer = 0;

function generateCaptcha() {
    const num1 = Math.floor(Math.random() * 20) + 1;
    const num2 = Math.floor(Math.random() * 20) + 1;
    const operations = ['+', '-', '*'];
    const operation = operations[Math.floor(Math.random() * operations.length)];
    
    let question, answer;
    
    switch(operation) {
        case '+':
            question = `${num1} + ${num2}`;
            answer = num1 + num2;
            break;
        case '-':
            // Ensure positive result
            const larger = Math.max(num1, num2);
            const smaller = Math.min(num1, num2);
            question = `${larger} - ${smaller}`;
            answer = larger - smaller;
            break;
        case '*':
            // Use smaller numbers for multiplication
            const smallNum1 = Math.floor(Math.random() * 10) + 1;
            const smallNum2 = Math.floor(Math.random() * 10) + 1;
            question = `${smallNum1} Ã— ${smallNum2}`;
            answer = smallNum1 * smallNum2;
            break;
    }
    
    document.getElementById('captcha-question').textContent = question;
    document.getElementById('captcha-correct').value = answer;
    captchaAnswer = answer;
    
    // Clear previous answer
    const answerInput = document.getElementById('captcha-answer');
    if (answerInput) {
        answerInput.value = '';
        answerInput.classList.remove('border-green-500', 'border-red-500');
    }
}

// Validate captcha answer
function validateCaptcha() {
    const userAnswer = parseInt(document.getElementById('captcha-answer').value);
    const correctAnswer = parseInt(document.getElementById('captcha-correct').value);
    const answerInput = document.getElementById('captcha-answer');
    
    if (userAnswer === correctAnswer) {
        answerInput.classList.remove('border-red-500');
        answerInput.classList.add('border-green-500');
        return true;
    } else {
        answerInput.classList.remove('border-green-500');
        answerInput.classList.add('border-red-500');
        return false;
    }
}

// Initialize captcha when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Generate initial captcha
    if (document.getElementById('captcha-question')) {
        generateCaptcha();
        
        // Validate captcha on input change
        const captchaInput = document.getElementById('captcha-answer');
        if (captchaInput) {
            captchaInput.addEventListener('input', validateCaptcha);
        }
    }
});


