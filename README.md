# App Craft Services Website

A complete, professional website for App Craft Services - a web development agency specializing in custom web applications for growing businesses.

## Features

- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices
- **Modern UI**: Clean, professional design using Tailwind CSS
- **5 Complete Pages**: Home, Process, Services, Pricing, and Contact
- **Admin Panel**: Real-time content management system
- **Professional Copy**: Business-focused content without AI/automation mentions
- **Contact Form**: Functional contact form with validation
- **SEO Optimized**: Proper meta tags and semantic HTML

## Pages

1. **Home (index.html)**: Hero section, value propositions, trust signals
2. **Process (process.html)**: 5-step development process
3. **Services (services.html)**: Custom web apps, MVP development, maintenance
4. **Pricing (pricing.html)**: Two-tier pricing structure with detailed information
5. **Contact (contact.html)**: Contact form and alternative contact methods

## Admin Panel

### Access Instructions

**Method 1 - Keyboard Shortcut**: Press `Ctrl + Shift + A` on any page to open the admin login
**Method 2 - Logo Triple-Click**: Triple-click on the App Craft Services logo to open admin login
**Method 3 - Test Button**: Use the red "Test Admin Login" button (temporary, for testing)

**Login Credentials**:
- Username: `kiwix`
- Password: `Admin123!`

### Admin Features

- **Real-time Content Editing**: Modify homepage content instantly
- **Color Customization**: Change primary and accent colors
- **Value Proposition Management**: Edit the three main value propositions
- **Preview Changes**: See changes before saving
- **Persistent Storage**: Changes are saved and persist across page reloads
- **Reset to Defaults**: Restore original content anytime

### How to Use Admin Panel

**Option 1**: Press `Ctrl + Shift + A` on any page
**Option 2**: Triple-click the App Craft Services logo
**Option 3**: Click the red "Test Admin Login" button (temporary)
1. Enter credentials: `kiwix` / `Admin123!`
2. Edit content in the admin panel
3. Click "Preview Changes" to see updates
4. Click "Save Changes" to make them permanent

## Technical Stack

- **HTML5**: Semantic markup
- **Tailwind CSS**: Modern utility-first CSS framework
- **Vanilla JavaScript**: No dependencies, fast loading
- **LocalStorage**: Client-side data persistence for admin changes

## File Structure

```
/
├── index.html              # Homepage
├── .htaccess              # URL rewriting and security
├── error.html             # Custom error page
├── test.php               # System diagnostics
├── error_monitor.php      # Error logging system
├── TROUBLESHOOTING.md     # Detailed troubleshooting guide
├── assets/                # Static assets
│   ├── logo.png          # Company logo
│   ├── favicon.ico       # Browser icon
│   ├── styles.css        # Custom CSS styles
│   └── script.js         # JavaScript functionality
├── process/               # Clean URL structure
│   └── index.html        # Process page
├── services/
│   └── index.html        # Services page
├── pricing/
│   └── index.html        # Pricing page
├── contact/
│   └── index.html        # Contact page
└── logs/                 # Error and access logs
    ├── php_errors.log    # PHP errors
    ├── error_log.txt     # Application errors
    ├── 404_errors.txt    # Missing page requests
    └── access_log.txt    # Access logs
```
├── assets/
│   ├── script.js          # JavaScript functionality
│   ├── styles.css         # Custom CSS styles
│   ├── logo.png           # Company logo
│   └── favicon.ico        # Website favicon
├── .htaccess              # Security and URL rewriting rules
└── README.md              # This file
```

## Clean URLs

The website now uses clean URLs without .html extensions:
- Homepage: `/appcraftservices/` or `/appcraftservices/index`
- Process: `/appcraftservices/process`
- Services: `/appcraftservices/services`
- Pricing: `/appcraftservices/pricing`
- Contact: `/appcraftservices/contact`

## Security Features

- **Clean URLs**: No file extensions visible in URLs
- **Security Headers**: X-Frame-Options, CSP, XSS Protection
- **File Protection**: Sensitive files are blocked from access
- **Attack Prevention**: Common attack patterns are blocked
- **Rate Limiting**: Basic DOS protection (if mod_evasive is available)
- **Asset Optimization**: Compression and caching for better performance

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Customization

The website is built with customization in mind:

- **Colors**: Easily change via CSS variables or admin panel
- **Content**: All text content can be modified through the admin panel
- **Layout**: Tailwind classes make layout adjustments simple
- **Branding**: Replace "App Craft Services" with your brand name

## Performance Features

- **CDN Delivery**: Tailwind CSS loaded from CDN
- **Optimized Images**: SVG icons for crisp display
- **Minimal JavaScript**: Fast loading and execution
- **Responsive Images**: Proper scaling across devices

## Accessibility

- **Keyboard Navigation**: Full keyboard support
- **Screen Reader Friendly**: Proper ARIA labels and semantic HTML
- **High Contrast Support**: Respects user preferences
- **Focus Indicators**: Clear focus states for all interactive elements

## SEO Features

- **Meta Tags**: Proper title and description tags
- **Semantic HTML**: Proper heading hierarchy
- **Fast Loading**: Optimized for Core Web Vitals
- **Mobile Friendly**: Responsive design

## New Advanced Features

### **🔧 Enhanced Admin Dashboard**
- **Full Content Management**: Edit all website content from one interface
- **Message Management**: View, reply to, and manage contact form submissions
- **Payment Integration**: Stripe and PayPal payment processing
- **Analytics Dashboard**: Track website performance and user engagement
- **User-Friendly Interface**: Modern, responsive admin panel

### **📧 Email Integration**
- **Admin Notifications**: Automatic emails to williamsaonen@gmail.com for new inquiries
- **Client Confirmations**: Professional confirmation emails with next steps
- **Email Templates**: Branded email templates with company information
- **Message Tracking**: All messages stored and tracked with unique IDs

### **💳 Payment Processing**
- **Stripe Integration**: Secure credit card processing
- **PayPal Integration**: Alternative payment method
- **Multiple Packages**: Pre-defined service packages with pricing
- **Custom Amounts**: Flexible payment amounts for custom projects
- **Payment Tracking**: Complete payment history and status tracking

### **🚀 Access Information**

#### **Admin Dashboard Access:**
- **URL**: `/appcraftservices/admin/`
- **Username**: `kiwix`
- **Password**: `Admin123!`

#### **Payment Page:**
- **URL**: `/appcraftservices/payment/`
- **Supports**: Stripe and PayPal payments
- **Features**: Real-time processing, email confirmations

#### **API Endpoints:**
- **Contact Form**: `/appcraftservices/api/contact.php`
- **Payment Processing**: `/appcraftservices/api/payments/create_payment.php`
- **Admin APIs**: Various endpoints for content management

## Logo Issues and Solutions

### Current Status:
- **Favicon**: ✅ Working correctly (shows ACS logo)
- **Logo.png**: ❌ File corrupted or too large (2.3MB)
- **Solution**: Using favicon.ico as logo source

### Available Logo Options:
1. **Current**: Uses favicon.ico as logo (working)
2. **Text Logo**: `/appcraftservices/index_text_logo.html` - CSS-based text logo
3. **Logo Creator**: `/appcraftservices/create_logo.html` - Create new logo

### If Logo Still Doesn't Show:
1. Visit `/appcraftservices/logo_test.html` to test different logo sources
2. Use the text logo version: `/appcraftservices/index_text_logo.html`
3. Create a new logo: `/appcraftservices/create_logo.html`

## Emergency Access

If you're getting 500 errors and can't access the website:

### Immediate Solutions:
1. **Emergency Page**: Visit `/appcraftservices/emergency.html` for direct access links
2. **Diagnostic Tool**: Visit `/appcraftservices/diagnostic.html` for detailed testing
3. **Disable .htaccess**: Run `disable_htaccess.bat` or rename `.htaccess` to `.htaccess-disabled`

### Direct HTML Access:
- Homepage: `/appcraftservices/index.html`
- Process: `/appcraftservices/process/index.html`
- Services: `/appcraftservices/services/index.html`
- Pricing: `/appcraftservices/pricing/index.html`
- Contact: `/appcraftservices/contact/index.html`

## Troubleshooting

### Quick Diagnostics
1. **System Test**: Visit `/test.php` to check server configuration
2. **Error Monitor**: Visit `/error_monitor.php` for detailed diagnostics
3. **Check Logs**: Look in the `logs/` directory for error details

### Common Issues

**Internal Server Error (500)**
- Check `logs/php_errors.log` for PHP errors
- Temporarily rename `.htaccess` to `.htaccess-backup` to test
- Verify file permissions (644 for files, 755 for directories)

**Clean URLs Not Working**
- Ensure mod_rewrite is enabled on your server
- Check that `.htaccess` file exists and is readable
- Try accessing pages with trailing slash (e.g., `/process/`)

**Assets Not Loading**
- Verify files exist in `assets/` directory
- Check browser developer tools for 404 errors
- Clear browser cache

**Admin Panel Issues**
- Try both access methods: `Alt + Shift + L` or triple-click logo
- Check browser console for JavaScript errors
- Verify script.js is loading correctly

For detailed troubleshooting, see `TROUBLESHOOTING.md`

## Deployment

Simply upload all files to your web server. The website is static and requires no server-side processing.

## Support

For questions about customization or additional features, refer to the code comments or modify the admin panel functionality in `script.js`.

## License

This website template is provided as-is for the App Craft Services project.