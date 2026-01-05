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
            if (submitIcon) submitIcon.classList.add('hidden');
            if (loadingIcon) loadingIcon.classList.remove('hidden');
            
            try {
                // Collect form data
                const formData = new FormData(contactForm);
                const data = {};
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }
                
                // Determine API path based on current location
                const apiPath = window.location.pathname.includes('/contact') ? 
                    '../api/contact.php' : 'api/contact.php';
                
                // Send to API
                const response = await fetch(apiPath, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success message
                    contactForm.style.display = 'none';
                    if (successMessage) {
                        successMessage.style.display = 'block';
                    }
                } else {
                    throw new Error(result.message || 'Failed to send message');
                }
                
            } catch (error) {
                console.error('Error submitting form:', error);
                alert('There was an error sending your message. Please try again or contact us directly.');
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                submitText.textContent = 'Send Message';
                if (submitIcon) submitIcon.classList.remove('hidden');
                if (loadingIcon) loadingIcon.classList.add('hidden');
            }
        });
    }
});

// Admin login function
function showAdminLogin() {
    const adminPath = window.location.pathname.includes('/contact') ? 
        '../admin/login.php' : 'admin/login.php';
    window.location.href = adminPath;
}

// Simple Review System (Static version for safety)
class ReviewSystem {
    constructor() {
        this.reviews = [];
        this.init();
    }
    
    init() {
        if (!document.getElementById('reviews-container')) {
            return;
        }
        this.loadStaticReviews();
    }
    
    loadStaticReviews() {
        const staticReviews = [
            {
                id: 1,
                reviewer_name: "Sarah Johnson",
                company: "TechStart Inc",
                project_type: "MVP Development",
                rating: 5,
                rating_stars: "★★★★★",
                content: "App Craft Services helped us build our MVP in record time. Their expertise in startup development is unmatched.",
                project_completion_date_formatted: "Mar 2024",
                verified: true
            },
            {
                id: 2,
                reviewer_name: "Michael Chen",
                company: "InnovateLab",
                project_type: "Web Application",
                rating: 5,
                rating_stars: "★★★★★",
                content: "Professional, efficient, and delivered exactly what we needed. Highly recommend for any startup looking to build their platform.",
                project_completion_date_formatted: "Feb 2024",
                verified: true
            },
            {
                id: 3,
                reviewer_name: "Emily Rodriguez",
                company: "GrowthCo",
                project_type: "Custom Development",
                rating: 5,
                rating_stars: "★★★★★",
                content: "Outstanding work on our custom application. The team understood our vision and brought it to life perfectly.",
                project_completion_date_formatted: "Jan 2024",
                verified: true
            }
        ];
        
        this.reviews = staticReviews;
        this.renderReviews(staticReviews);
    }
    
    renderReviews(reviews) {
        const container = document.getElementById('reviews-container');
        if (!container) return;
        
        container.innerHTML = '';
        
        reviews.forEach(review => {
            const reviewElement = document.createElement('div');
            reviewElement.className = 'bg-white p-6 rounded-lg shadow-md border border-gray-200';
            reviewElement.innerHTML = this.createReviewHTML(review);
            container.appendChild(reviewElement);
        });
    }
    
    createReviewHTML(review) {
        return `
            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center font-bold text-blue-600">
                    ${review.reviewer_name.charAt(0)}
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h4 class="font-semibold text-gray-900">${this.escapeHtml(review.reviewer_name)}</h4>
                            <p class="text-sm text-gray-600">${this.escapeHtml(review.company)} • ${this.escapeHtml(review.project_type)}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-yellow-400">${review.rating_stars}</span>
                            ${review.verified ? '<span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">✓ Verified</span>' : ''}
                        </div>
                    </div>
                    <p class="text-gray-700 mb-3">${this.escapeHtml(review.content)}</p>
                    <p class="text-xs text-gray-500">Completed: ${review.project_completion_date_formatted}</p>
                </div>
            </div>
        `;
    }
    
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('reviews-container')) {
        window.reviewSystem = new ReviewSystem();
    }
});

// Image optimization utilities
class ImageOptimizer {
    constructor() {
        this.init();
    }
    
    init() {
        this.lazyLoadImages();
        this.optimizeImages();
    }
    
    lazyLoadImages() {
        const images = document.querySelectorAll('img[data-src]');
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            images.forEach(img => imageObserver.observe(img));
        } else {
            images.forEach(img => {
                img.src = img.dataset.src;
                img.classList.remove('lazy');
            });
        }
    }
    
    optimizeImages() {
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            img.addEventListener('load', function() {
                this.classList.add('loaded');
            });
        });
    }
}

// Performance optimization utilities
class PerformanceOptimizer {
    constructor() {
        this.init();
    }
    
    init() {
        this.preloadCriticalResources();
        this.optimizeScrolling();
        this.deferNonCriticalScripts();
    }
    
    preloadCriticalResources() {
        const criticalResources = [
            '/assets/styles.css',
            '/assets/script.js'
        ];
        criticalResources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = resource;
            link.as = resource.endsWith('.css') ? 'style' : 'script';
            document.head.appendChild(link);
        });
    }
    
    optimizeScrolling() {
        let ticking = false;
        function updateScrollPosition() { ticking = false; }
        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(updateScrollPosition);
                ticking = true;
            }
        }
        window.addEventListener('scroll', requestTick);
    }
    
    deferNonCriticalScripts() {
        const nonCriticalScripts = document.querySelectorAll('script[data-defer]');
        window.addEventListener('load', () => {
            nonCriticalScripts.forEach(script => {
                const newScript = document.createElement('script');
                newScript.src = script.dataset.src;
                document.body.appendChild(newScript);
            });
        });
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
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }
    
    init() {
        this.optimizeTouchEvents();
        this.adjustViewport();
        this.optimizeImages();
    }
    
    optimizeTouchEvents() {
        const buttons = document.querySelectorAll('button, .btn, a[role="button"]');
        buttons.forEach(button => {
            button.addEventListener('touchstart', function() {
                this.classList.add('touch-active');
            });
            button.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.classList.remove('touch-active');
                }, 150);
            });
        });
    }
    
    adjustViewport() {
        const viewport = document.querySelector('meta[name="viewport"]');
        if (viewport) {
            viewport.content = 'width=device-width, initial-scale=1.0, viewport-fit=cover';
        }
    }
    
    optimizeImages() {
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            if (!img.hasAttribute('loading')) {
                img.setAttribute('loading', 'lazy');
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    new ImageOptimizer();
    new PerformanceOptimizer();
    new MobileOptimizer();
});

// =========================================================
// CRITICAL FIX: FORCE UNREGISTER BROKEN SERVICE WORKERS
// =========================================================
// This block detects the old "dangerous" Service Worker and kills it.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            if (registrations.length > 0) {
                console.log('Detected ' + registrations.length + ' old Service Worker(s). Unregistering now...');
                
                for(let registration of registrations) {
                    registration.unregister().then(function(boolean) {
                        if(boolean) {
                            console.log('Service Worker successfully unregistered.');
                            // Optional: Force a reload if it's the first time running this fix
                            // to ensure the user instantly sees the clean version.
                            if (!sessionStorage.getItem('sw_fix_applied')) {
                                sessionStorage.setItem('sw_fix_applied', 'true');
                                console.log('Reloading to clear cache...');
                                window.location.reload();
                            }
                        }
                    });
                }
            } else {
                console.log('No Service Workers found. Site is clean.');
            }
        });
    });
}