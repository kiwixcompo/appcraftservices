// Website Analytics Tracking System
// Tracks page views, user behavior, and traffic sources

class WebsiteAnalytics {
    constructor() {
        this.sessionId = this.getOrCreateSessionId();
        this.startTime = Date.now();
        this.isNewVisitor = this.checkIfNewVisitor();
        this.init();
    }
    
    init() {
        // Track page view when page loads
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.trackPageView());
        } else {
            this.trackPageView();
        }
        
        // Track page unload for session duration
        window.addEventListener('beforeunload', () => this.trackPageUnload());
        
        // Track scroll depth
        this.setupScrollTracking();
        
        // Track clicks on important elements
        this.setupClickTracking();
    }
    
    getOrCreateSessionId() {
        let sessionId = sessionStorage.getItem('analytics_session_id');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('analytics_session_id', sessionId);
        }
        return sessionId;
    }
    
    checkIfNewVisitor() {
        const hasVisited = localStorage.getItem('analytics_has_visited');
        if (!hasVisited) {
            localStorage.setItem('analytics_has_visited', 'true');
            return true;
        }
        return false;
    }
    
    getTrafficSource() {
        const referrer = document.referrer;
        const urlParams = new URLSearchParams(window.location.search);
        
        // Check for UTM parameters
        const utmSource = urlParams.get('utm_source');
        const utmMedium = urlParams.get('utm_medium');
        const utmCampaign = urlParams.get('utm_campaign');
        
        if (utmSource) {
            return {
                source: utmSource,
                medium: utmMedium || 'unknown',
                campaign: utmCampaign || ''
            };
        }
        
        // Analyze referrer
        if (!referrer || referrer.includes(window.location.hostname)) {
            return { source: 'direct', medium: 'none', campaign: '' };
        }
        
        const referrerHost = new URL(referrer).hostname.toLowerCase();
        
        if (referrerHost.includes('google.')) {
            return { source: 'google', medium: 'organic', campaign: '' };
        } else if (referrerHost.includes('facebook.') || referrerHost.includes('fb.')) {
            return { source: 'facebook', medium: 'social', campaign: '' };
        } else if (referrerHost.includes('twitter.') || referrerHost.includes('t.co')) {
            return { source: 'twitter', medium: 'social', campaign: '' };
        } else if (referrerHost.includes('linkedin.')) {
            return { source: 'linkedin', medium: 'social', campaign: '' };
        } else if (referrerHost.includes('github.')) {
            return { source: 'github', medium: 'referral', campaign: '' };
        } else {
            return { source: referrerHost, medium: 'referral', campaign: '' };
        }
    }
    
    trackPageView() {
        // Temporarily disabled to fix Chrome security warnings
        // Automatic API calls can trigger Chrome's security flags
        console.log('Analytics temporarily disabled for Chrome compatibility');
        return;
        
        const data = {
            page: window.location.pathname,
            title: document.title,
            referrer: document.referrer,
            session_id: this.sessionId,
            is_new_visitor: this.isNewVisitor,
            screen_resolution: `${screen.width}x${screen.height}`,
            viewport_size: `${window.innerWidth}x${window.innerHeight}`,
            load_time: Date.now() - this.startTime,
            ...this.getTrafficSource()
        };
        
        this.sendAnalytics(data);
    }
    
    trackPageUnload() {
        // Temporarily disabled to fix Chrome security warnings
        return;
        
        const sessionDuration = Date.now() - this.startTime;
        const data = {
            event: 'page_unload',
            page: window.location.pathname,
            session_id: this.sessionId,
            session_duration: sessionDuration
        };
        
        // Use sendBeacon for reliable tracking on page unload
        if (navigator.sendBeacon) {
            navigator.sendBeacon('/api/analytics.php', JSON.stringify(data));
        }
    }
    
    setupScrollTracking() {
        let maxScroll = 0;
        let scrollTimeout;
        
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                const scrollPercent = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
                if (scrollPercent > maxScroll) {
                    maxScroll = scrollPercent;
                    
                    // Track scroll milestones
                    if (scrollPercent >= 25 && scrollPercent < 50 && maxScroll >= 25) {
                        this.trackEvent('scroll', '25%');
                    } else if (scrollPercent >= 50 && scrollPercent < 75 && maxScroll >= 50) {
                        this.trackEvent('scroll', '50%');
                    } else if (scrollPercent >= 75 && scrollPercent < 100 && maxScroll >= 75) {
                        this.trackEvent('scroll', '75%');
                    } else if (scrollPercent >= 100 && maxScroll >= 100) {
                        this.trackEvent('scroll', '100%');
                    }
                }
            }, 250);
        });
    }
    
    setupClickTracking() {
        document.addEventListener('click', (e) => {
            const element = e.target.closest('a, button, [data-track]');
            if (element) {
                let eventData = {
                    event: 'click',
                    element_type: element.tagName.toLowerCase(),
                    page: window.location.pathname,
                    session_id: this.sessionId
                };
                
                if (element.tagName === 'A') {
                    eventData.link_url = element.href;
                    eventData.link_text = element.textContent.trim();
                } else if (element.tagName === 'BUTTON') {
                    eventData.button_text = element.textContent.trim();
                    eventData.button_type = element.type || 'button';
                }
                
                if (element.dataset.track) {
                    eventData.track_id = element.dataset.track;
                }
                
                this.sendAnalytics(eventData);
            }
        });
    }
    
    trackEvent(category, action, label = '', value = 0) {
        const data = {
            event: 'custom_event',
            category: category,
            action: action,
            label: label,
            value: value,
            page: window.location.pathname,
            session_id: this.sessionId,
            timestamp: new Date().toISOString()
        };
        
        this.sendAnalytics(data);
    }
    
    sendAnalytics(data) {
        fetch('/api/analytics.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        }).catch(error => {
            console.warn('Analytics tracking failed:', error);
        });
    }
}

// Initialize analytics when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.analytics = new WebsiteAnalytics();
    });
} else {
    window.analytics = new WebsiteAnalytics();
}