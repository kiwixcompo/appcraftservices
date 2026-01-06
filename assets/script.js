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

// Project Slider Functionality - Fixed Version
class ProjectSlider {
    constructor() {
        this.currentSlide = 0;
        this.totalSlides = 2; // 2 slide groups
        this.slider = document.getElementById('projects-slider');
        this.prevBtn = document.getElementById('prev-btn');
        this.nextBtn = document.getElementById('next-btn');
        this.indicators = document.querySelectorAll('.slider-indicator');
        
        if (this.slider) {
            this.init();
        }
    }
    
    init() {
        console.log('ProjectSlider initialized');
        
        // Set initial slider width and positioning
        this.setupSlider();
        
        // Add event listeners for navigation buttons
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.prevSlide();
                console.log('Previous button clicked');
            });
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.nextSlide();
                console.log('Next button clicked');
            });
        }
        
        // Add event listeners for indicators
        this.indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', (e) => {
                e.preventDefault();
                this.goToSlide(index);
                console.log('Indicator clicked:', index);
            });
        });
        
        // Auto-slide every 5 seconds
        this.startAutoSlide();
        
        // Pause auto-slide on hover
        if (this.slider.parentElement) {
            this.slider.parentElement.addEventListener('mouseenter', () => this.stopAutoSlide());
            this.slider.parentElement.addEventListener('mouseleave', () => this.startAutoSlide());
        }
        
        // Update initial state
        this.updateSlider();
    }
    
    setupSlider() {
        // Ensure slider has proper width for 2 slide groups
        this.slider.style.width = '200%'; // 2 slide groups × 100% each
        this.slider.style.display = 'flex';
        this.slider.style.transition = 'transform 0.5s ease-in-out';
        
        // Set each slide group to 50% width (100% ÷ 2 slides)
        const slideGroups = this.slider.querySelectorAll('.slide-group');
        slideGroups.forEach(slideGroup => {
            slideGroup.style.width = '50%'; // Each slide group takes 50% of the 200% width
            slideGroup.style.flexShrink = '0';
        });
    }
    
    nextSlide() {
        if (this.currentSlide < this.totalSlides - 1) {
            this.currentSlide++;
        } else {
            this.currentSlide = 0; // Loop back to first slide
        }
        this.updateSlider();
        this.resetAutoSlide();
    }
    
    prevSlide() {
        if (this.currentSlide > 0) {
            this.currentSlide--;
        } else {
            this.currentSlide = this.totalSlides - 1; // Loop to last slide
        }
        this.updateSlider();
        this.resetAutoSlide();
    }
    
    goToSlide(slideIndex) {
        this.currentSlide = slideIndex;
        this.updateSlider();
        this.resetAutoSlide();
    }
    
    updateSlider() {
        if (!this.slider) return;
        
        // Calculate transform percentage
        const translateX = -this.currentSlide * 50; // Each slide is 50% of the container
        this.slider.style.transform = `translateX(${translateX}%)`;
        
        console.log(`Sliding to slide ${this.currentSlide}, translateX: ${translateX}%`);
        
        // Update indicators
        this.indicators.forEach((indicator, index) => {
            if (index === this.currentSlide) {
                indicator.classList.remove('bg-gray-300');
                indicator.classList.add('bg-electric-blue');
            } else {
                indicator.classList.remove('bg-electric-blue');
                indicator.classList.add('bg-gray-300');
            }
        });
        
        // Update button states
        if (this.prevBtn) {
            this.prevBtn.style.opacity = this.currentSlide === 0 ? '0.5' : '1';
        }
        
        if (this.nextBtn) {
            this.nextBtn.style.opacity = this.currentSlide === this.totalSlides - 1 ? '0.5' : '1';
        }
    }
    
    startAutoSlide() {
        this.stopAutoSlide(); // Clear any existing interval
        this.autoSlideInterval = setInterval(() => {
            this.nextSlide();
        }, 5000); // Change slide every 5 seconds
    }
    
    resetAutoSlide() {
        this.stopAutoSlide();
        this.startAutoSlide();
    }
    
    stopAutoSlide() {
        if (this.autoSlideInterval) {
            clearInterval(this.autoSlideInterval);
            this.autoSlideInterval = null;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('reviews-container')) {
        window.reviewSystem = new ReviewSystem();
    }
    
    // Initialize project slider
    if (document.getElementById('projects-slider')) {
        window.projectSlider = new ProjectSlider();
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
// Project Modal Functionality
const projectData = {
    'mealmate': {
        title: 'MealMate',
        description: 'A comprehensive meal planning and nutrition tracking application that helps users maintain healthy eating habits. Features include meal scheduling, nutritional analysis, grocery list generation, and progress tracking.',
        features: [
            'Personalized meal planning based on dietary preferences',
            'Comprehensive nutrition tracking and analysis',
            'Automated grocery list generation',
            'Recipe database with nutritional information',
            'Progress tracking and health insights',
            'Integration with fitness trackers'
        ],
        technologies: ['React', 'Node.js', 'MongoDB', 'Express', 'Chart.js'],
        logo: 'assets/projects/MealMate.png',
        category: 'Health & Fitness'
    },
    'notifyme': {
        title: 'Notify Me',
        description: 'A real-time notification system designed for business communications. Enables instant messaging, alerts, and notifications across multiple channels including email, SMS, and push notifications.',
        features: [
            'Real-time messaging and notifications',
            'Multi-channel delivery (Email, SMS, Push)',
            'Advanced scheduling and automation',
            'Analytics and delivery tracking',
            'Team collaboration tools',
            'API integration capabilities'
        ],
        technologies: ['Vue.js', 'Express', 'Redis', 'Socket.io', 'PostgreSQL'],
        logo: 'assets/projects/Notify Me.png',
        category: 'Communication'
    },
    'automated-restaurant': {
        title: 'Automated Restaurant',
        description: 'Complete restaurant management and automation system that streamlines operations from order taking to kitchen management. Includes POS integration, inventory tracking, and customer management.',
        features: [
            'Automated order processing and kitchen display',
            'Inventory management with low-stock alerts',
            'Customer relationship management',
            'Staff scheduling and payroll integration',
            'Sales analytics and reporting',
            'Multi-location support'
        ],
        technologies: ['Angular', 'Python', 'PostgreSQL', 'Django', 'Redis'],
        logo: 'assets/projects/Automated Restaurant.png',
        category: 'Restaurant & Food'
    },
    'quickbudgetai': {
        title: 'QuickBudgetAI',
        description: 'AI-powered personal finance and budgeting assistant that uses machine learning to provide personalized financial advice and automated budget optimization.',
        features: [
            'AI-driven budget recommendations',
            'Expense categorization and tracking',
            'Financial goal setting and monitoring',
            'Investment portfolio analysis',
            'Bill reminder and payment automation',
            'Predictive spending insights'
        ],
        technologies: ['React', 'Python', 'TensorFlow', 'FastAPI', 'PostgreSQL'],
        logo: 'assets/projects/QuickBudgetAI.png',
        category: 'Finance & AI'
    },
    'clearpath': {
        title: 'ClearPath Client Services',
        description: 'Client relationship management and service tracking platform designed for service-based businesses. Manages client interactions, project timelines, and service delivery.',
        features: [
            'Comprehensive client profile management',
            'Project timeline and milestone tracking',
            'Service delivery automation',
            'Client communication portal',
            'Invoice and payment processing',
            'Performance analytics and reporting'
        ],
        technologies: ['Laravel', 'MySQL', 'Vue.js', 'Bootstrap', 'Stripe API'],
        logo: 'assets/projects/ClearPath Client Services.png',
        category: 'Business Management'
    },
    'willpdf': {
        title: 'WillPDF',
        description: 'Legal document generation and PDF management system that automates the creation of wills, contracts, and other legal documents with built-in compliance checking.',
        features: [
            'Automated legal document generation',
            'Template management and customization',
            'Digital signature integration',
            'Compliance checking and validation',
            'Document version control',
            'Secure client portal access'
        ],
        technologies: ['PHP', 'JavaScript', 'PDF.js', 'MySQL', 'Bootstrap'],
        logo: 'assets/projects/WillPDF.png',
        category: 'Legal Tech'
    },
    'tsu-staff': {
        title: 'TSU Staff Profile',
        description: 'University staff management and profile system that centralizes employee information, manages academic credentials, and facilitates internal communication.',
        features: [
            'Comprehensive staff profile management',
            'Academic credential tracking',
            'Department and role management',
            'Internal directory and search',
            'Performance evaluation system',
            'Document management and storage'
        ],
        technologies: ['Django', 'PostgreSQL', 'Bootstrap', 'Python', 'Redis'],
        logo: 'assets/projects/TSU Staff Profile.png',
        category: 'Education'
    },
    'federal-leave': {
        title: 'Federal California Leave Assistant',
        description: 'Government leave management and compliance tracking system that ensures adherence to federal and state leave policies while streamlining the application process.',
        features: [
            'Automated leave policy compliance checking',
            'Employee leave balance tracking',
            'Manager approval workflow',
            'Integration with payroll systems',
            'Reporting and analytics dashboard',
            'Mobile-friendly employee portal'
        ],
        technologies: ['ASP.NET', 'SQL Server', 'Angular', 'C#', 'Azure'],
        logo: 'assets/projects/Federal California Leave Assistant.png',
        category: 'Government & HR'
    }
};

// Project modal event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add click listeners to project cards
    const projectCards = document.querySelectorAll('.project-card');
    projectCards.forEach(card => {
        card.addEventListener('click', function() {
            const projectId = this.getAttribute('data-project');
            if (projectId && projectData[projectId]) {
                showProjectModal(projectId);
            }
        });
    });
    
    // Close modal listeners
    const closeModalBtn = document.getElementById('close-modal');
    const projectModal = document.getElementById('project-modal');
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', hideProjectModal);
    }
    
    if (projectModal) {
        projectModal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideProjectModal();
            }
        });
    }
    
    // ESC key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideProjectModal();
        }
    });
});

function showProjectModal(projectId) {
    const project = projectData[projectId];
    if (!project) return;
    
    const modal = document.getElementById('project-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalProjectTitle = document.getElementById('modal-project-title');
    const modalDescription = document.getElementById('modal-description');
    const modalFeatures = document.getElementById('modal-features');
    const modalTechList = document.getElementById('modal-tech-list');
    const modalLogoContainer = document.getElementById('modal-logo-container');
    const modalLargeLogo = document.getElementById('modal-large-logo');
    const modalTags = document.getElementById('modal-tags');
    
    if (modalTitle) modalTitle.textContent = project.title;
    if (modalProjectTitle) modalProjectTitle.textContent = project.title;
    if (modalDescription) modalDescription.textContent = project.description;
    
    // Set logo
    if (modalLogoContainer && project.logo) {
        modalLogoContainer.innerHTML = `<img src="${project.logo}" alt="${project.title} Logo" class="w-full h-full object-contain">`;
    }
    
    if (modalLargeLogo && project.logo) {
        modalLargeLogo.innerHTML = `<img src="${project.logo}" alt="${project.title} Logo" class="w-32 h-32 object-contain relative z-10">`;
    }
    
    // Set category tag
    if (modalTags) {
        modalTags.innerHTML = `<span class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full">${project.category}</span>`;
    }
    
    // Set features
    if (modalFeatures && project.features) {
        const featuresHTML = `
            <div>
                <h4 class="text-lg font-semibold text-navy mb-3">Key Features:</h4>
                <ul class="space-y-2">
                    ${project.features.map(feature => `<li class="flex items-start"><span class="text-green-600 mr-2">✓</span><span class="text-gray-600">${feature}</span></li>`).join('')}
                </ul>
            </div>
        `;
        modalFeatures.innerHTML = featuresHTML;
    }
    
    // Set technologies
    if (modalTechList && project.technologies) {
        const techHTML = project.technologies.map(tech => 
            `<span class="bg-gray-100 text-gray-800 text-sm px-3 py-1 rounded-full">${tech}</span>`
        ).join('');
        modalTechList.innerHTML = techHTML;
    }
    
    // Show modal
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
}

function hideProjectModal() {
    const modal = document.getElementById('project-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = ''; // Restore scrolling
    }
}
// Admin Dashboard Access System
document.addEventListener('keydown', function(e) {
    // Method 1: Ctrl+Shift+D (or Cmd+Shift+D on Mac) - Changed from A to avoid Chrome conflict
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'd') {
        e.preventDefault();
        e.stopPropagation();
        window.location.href = '/admin/login.php';
        return;
    }
    
    // Method 2: Alt+Shift+A (Alternative shortcut that doesn't conflict)
    if (e.altKey && e.shiftKey && e.key.toLowerCase() === 'a') {
        e.preventDefault();
        e.stopPropagation();
        window.location.href = '/admin/login.php';
        return;
    }
    
    // Method 3: Type "kiwix" anywhere on the page
    if (!window.typedSequence) {
        window.typedSequence = '';
        window.typedTimer = null;
    }
    
    // Only capture regular letter keys (not special keys)
    if (e.key.length === 1 && e.key.match(/[a-zA-Z]/)) {
        window.typedSequence += e.key.toLowerCase();
        
        // Clear the sequence after 3 seconds of inactivity
        clearTimeout(window.typedTimer);
        window.typedTimer = setTimeout(() => {
            window.typedSequence = '';
        }, 3000);
        
        // Keep only the last 10 characters
        if (window.typedSequence.length > 10) {
            window.typedSequence = window.typedSequence.slice(-10);
        }
        
        // Check if "kiwix" was typed
        if (window.typedSequence.includes('kiwix')) {
            e.preventDefault();
            window.typedSequence = ''; // Reset sequence
            
            // Show confirmation message
            const adminMessage = document.createElement('div');
            adminMessage.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                padding: 20px 40px;
                border-radius: 10px;
                font-size: 18px;
                font-weight: bold;
                z-index: 10000;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                text-align: center;
            `;
            adminMessage.innerHTML = '🔑 "kiwix" detected!<br>Accessing Admin Panel...';
            document.body.appendChild(adminMessage);
            
            setTimeout(() => {
                window.location.href = '/admin/login.php';
            }, 1500);
        }
    }
    
    // Method 4: Konami Code for admin access (↑↑↓↓←→←→BA)
    if (!window.konamiSequence) {
        window.konamiSequence = [];
        window.konamiCode = [
            'ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown',
            'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight',
            'KeyB', 'KeyA'
        ];
    }
    
    window.konamiSequence.push(e.code);
    
    // Keep only the last 10 keys
    if (window.konamiSequence.length > 10) {
        window.konamiSequence = window.konamiSequence.slice(-10);
    }
    
    // Check if the sequence matches the Konami code
    if (window.konamiSequence.length === 10) {
        const matches = window.konamiCode.every((key, index) => 
            key === window.konamiSequence[index]
        );
        
        if (matches) {
            e.preventDefault();
            window.konamiSequence = []; // Reset sequence
            
            // Show a fun message before redirecting
            const adminMessage = document.createElement('div');
            adminMessage.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px 40px;
                border-radius: 10px;
                font-size: 18px;
                font-weight: bold;
                z-index: 10000;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                text-align: center;
            `;
            adminMessage.innerHTML = '🎮 Konami Code Activated!<br>Accessing Admin Panel...';
            document.body.appendChild(adminMessage);
            
            setTimeout(() => {
                window.location.href = '/admin/login.php';
            }, 1500);
        }
    }
});

// Method 5: Console access
window.adminAccess = function() {
    console.log('🔐 Admin access granted via console');
    window.location.href = '/admin/login.php';
};

// Method 6: Secret URL hint in console
console.log('%c🔧 Developer Tools Detected', 'color: #3b82f6; font-size: 16px; font-weight: bold;');
console.log('%cAdmin Access Methods:', 'color: #6b7280; font-size: 14px;');
console.log('%c• Keyboard: Ctrl+Shift+D or Alt+Shift+A', 'color: #6b7280; font-size: 12px;');
console.log('%c• Type: "kiwix" anywhere on the page', 'color: #6b7280; font-size: 12px;');
console.log('%c• Console: adminAccess()', 'color: #6b7280; font-size: 12px;');
console.log('%c• Secret URL: /secret-admin-access-2026', 'color: #6b7280; font-size: 12px;');
console.log('%c• Konami Code: ↑↑↓↓←→←→BA', 'color: #6b7280; font-size: 12px;');

// Method 7: Triple-click on logo/title for admin access
document.addEventListener('DOMContentLoaded', function() {
    // Add a small, nearly invisible element that indicates admin access is available
    const adminIndicator = document.createElement('div');
    adminIndicator.style.cssText = `
        position: fixed;
        bottom: 5px;
        right: 5px;
        width: 3px;
        height: 3px;
        background: rgba(59, 130, 246, 0.3);
        border-radius: 50%;
        z-index: 1000;
        pointer-events: none;
    `;
    adminIndicator.title = 'Admin access available';
    document.body.appendChild(adminIndicator);
    
    // Triple-click on main heading/logo for admin access
    const mainHeading = document.querySelector('h1') || document.querySelector('.logo') || document.querySelector('header h1');
    if (mainHeading) {
        let clickCount = 0;
        let clickTimer = null;
        
        mainHeading.addEventListener('click', function(e) {
            clickCount++;
            
            if (clickCount === 1) {
                clickTimer = setTimeout(() => {
                    clickCount = 0;
                }, 1000); // Reset after 1 second
            } else if (clickCount === 3) {
                clearTimeout(clickTimer);
                clickCount = 0;
                
                // Prevent default action and show admin access
                e.preventDefault();
                
                const confirmAccess = confirm('Access admin dashboard?');
                if (confirmAccess) {
                    window.location.href = '/admin/login.php';
                }
            }
        });
    }
});
// Show admin shortcut notification (temporary - can be removed later)
document.addEventListener('DOMContentLoaded', function() {
    // Only show on homepage
    if (window.location.pathname === '/' || window.location.pathname === '/index.html') {
        setTimeout(() => {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #3b82f6, #1e40af);
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                font-size: 14px;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                max-width: 300px;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            `;
            notification.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-weight: bold; margin-bottom: 4px;">🔧 Admin Access Updated</div>
                        <div style="font-size: 12px; opacity: 0.9;">Type "kiwix" or use Ctrl+Shift+D</div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer; margin-left: 10px;">&times;</button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // Auto-hide after 8 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }, 8000);
        }, 2000); // Show after 2 seconds
    }
});