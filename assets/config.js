/**
 * Environment Configuration for Client-Side
 * Automatically detects whether running locally or on production
 */

(function() {
    // Detect if running locally
    const isLocal = (
        window.location.hostname === 'localhost' ||
        window.location.hostname === '127.0.0.1' ||
        window.location.hostname.startsWith('192.168.')
    );

    // Set base URLs based on environment
    window.CONFIG = {
        isLocal: isLocal,
        environment: isLocal ? 'local' : 'production',
        baseUrl: isLocal ? '/appcraftservices/' : '/',
        apiUrl: isLocal ? '/appcraftservices/api/' : '/api/',
        adminUrl: isLocal ? '/appcraftservices/admin/' : '/admin/',
        assetsUrl: isLocal ? '/appcraftservices/assets/' : '/assets/',
        
        // Helper function to get correct URL
        getUrl: function(path, type = 'base') {
            const baseUrls = {
                'base': this.baseUrl,
                'api': this.apiUrl,
                'admin': this.adminUrl,
                'assets': this.assetsUrl
            };
            
            const base = baseUrls[type] || this.baseUrl;
            
            // Remove leading slash from path if present
            if (path.startsWith('/')) {
                path = path.substring(1);
            }
            
            return base + path;
        },
        
        // Helper to navigate to a page
        navigate: function(path) {
            window.location.href = this.getUrl(path);
        }
    };

    // Log configuration in debug mode
    if (isLocal) {
        console.log('Environment: LOCAL', window.CONFIG);
    }
})();
