# Quick Start Guide - App Craft Services

## 🚀 Getting Started

### Local Development
```
1. Clone/download the repository
2. Place in: C:\wamp64\www\appcraftservices\
3. Start WAMP server
4. Visit: http://localhost/appcraftservices/
```

### Production
```
1. Upload files to hosting root
2. Visit: https://appcraftservices.com/
3. No configuration needed - automatic!
```

---

## 📋 Admin Dashboard Access

### Login
- URL: `https://appcraftservices.com/admin/login.php`
- Username: (configured in admin/login.php)
- Password: (configured in admin/login.php)

### Dashboard Tabs

#### 1. **Dashboard** (Overview)
- Quick statistics
- Recent messages
- Quick action buttons

#### 2. **Messages** (Lead Management)
- View all contact form submissions
- Filter by: All, Unread, Today, Consultations
- See lead qualification score
- Reply via email
- Call directly
- Schedule meetings
- Mark as read
- Delete messages

#### 3. **Analytics** (Traffic Tracking)
- Real-time visitor statistics
- Traffic sources breakdown
- Device type analysis
- Browser statistics
- Recent visitor activity
- Filtering options:
  - Time period (7, 30, 90, 365 days)
  - Specific pages
  - Traffic sources

#### 4. **Content** (Website Content)
- Edit site information
- Customize hero section
- Manage value propositions
- Preview changes

#### 5. **Design** (Styling)
- Change color scheme
- Adjust typography
- Upload new logo
- Configure layout
- Apply theme presets

#### 6. **Reviews** (Client Reviews)
- Manage client reviews
- Approve/reject reviews
- View review statistics

#### 7. **Invoices** (Billing)
- Create invoices
- Track payment status
- View invoice history
- Export to PDF

#### 8. **Payments** (Payment Configuration)
- Configure Stripe
- Configure PayPal
- View transactions

#### 9. **Settings** (System)
- Create backups
- Restore backups
- Run system checks
- Toggle maintenance mode

---

## 📝 Contact Form Features

### Startup Qualification Fields
The contact form now includes:
- **Funding Stage**: Pre-seed, Seed, Series A, Series B, Bootstrapped
- **Investor Deadline**: Specific milestone dates
- **Project Type**: Essential App, Custom, Maintenance
- **Timeline**: ASAP, 1-month, 2-3 months, 3-6 months, Flexible
- **Budget**: $800-2K, Custom Quote, Discuss
- **Project Details**: Detailed description
- **CAPTCHA**: Math verification

### Lead Scoring
Each submission is automatically scored (0-100 points):
- **Highly Qualified** (80-100): Priority response
- **Qualified** (60-79): Standard response
- **Moderately Qualified** (40-59): Follow-up needed
- **Needs Qualification** (0-39): Qualification call needed

---

## 📊 Analytics Dashboard

### Key Metrics
- **Total Visitors**: Unique IP addresses
- **Page Views**: Total page visits
- **Bounce Rate**: Single-page sessions
- **Avg Load Time**: Page load performance
- **Traffic Sources**: Direct, Organic, Social, Referral

### Filtering
1. Select time period (7, 30, 90, 365 days)
2. Choose specific page (optional)
3. Select traffic source (optional)
4. Click "Refresh" to update

### Recent Visitors Table
Shows:
- Time of visit
- Page visited
- Traffic source
- Device type
- Location

---

## 💌 Email System

### Automatic Emails
1. **Admin Notification**: When contact form submitted
   - Sent to: geniusonen@gmail.com
   - Backup: williamsaonen@gmail.com
   - Contains: Full message details, lead score, admin link

2. **User Auto-Reply**: Confirmation to user
   - Sent from: hello@appcraftservices.com
   - Contains: Thank you message, next steps, portfolio links

### Email Configuration
File: `api/contact.php`
```php
$config = [
    'admin_email' => 'geniusonen@gmail.com',
    'backup_admin_email' => 'williamsaonen@gmail.com',
    'from_email' => 'hello@appcraftservices.com',
    'from_name' => 'App Craft Services'
];
```

---

## 🔄 Git & Deployment

### Sync to GitHub
```bash
# Run this batch file to sync changes
SYNC-NOW.bat
```

### What It Does
1. Stages all changes
2. Commits with timestamp
3. Pushes to GitHub
4. Triggers deployment to live server
5. Changes visible within seconds

### Manual Deployment
Visit: `https://appcraftservices.com/deploy.php?manual=true`

---

## 🛠️ Troubleshooting

### Messages Not Appearing
**Problem**: Contact form submissions not showing in admin
**Solution**:
1. Check `data/messages.json` exists
2. Verify file permissions (755)
3. Check browser console for errors
4. Clear browser cache

### Emails Not Sending
**Problem**: Admin not receiving email notifications
**Solution**:
1. Verify Gmail account in `api/contact.php`
2. Check spam folder
3. Verify SMTP settings
4. Check server error logs

### Analytics Not Tracking
**Problem**: No visitor data in analytics
**Solution**:
1. Ensure `assets/analytics.js` is loaded
2. Check browser console for errors
3. Verify `api/analytics.php` is accessible
4. Check `data/analytics.json` permissions

### Admin Dashboard Blank
**Problem**: Tabs not showing content
**Solution**:
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+F5)
3. Check browser console for JavaScript errors
4. Verify admin.js is loaded

---

## 📱 Mobile Optimization

### Features
- Responsive design for all screen sizes
- Touch-friendly buttons (44px minimum)
- Mobile-optimized forms
- Click-to-call functionality
- Optimized images

### Testing
1. Use Chrome DevTools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Test on actual mobile devices
4. Check performance in DevTools

---

## 🔐 Security Checklist

- ✅ Change admin login credentials
- ✅ Update email addresses in config
- ✅ Enable HTTPS (already configured)
- ✅ Regular backups (use admin dashboard)
- ✅ Monitor lead quality scores
- ✅ Review analytics regularly

---

## 📞 Common Tasks

### Add New Service Package
1. Go to Admin > Content
2. Edit service offerings
3. Add new package details
4. Save changes

### Update Homepage Content
1. Go to Admin > Content
2. Edit hero section
3. Update value propositions
4. Preview changes
5. Save

### View Lead Analytics
1. Go to Admin > Messages
2. Check lead scores
3. Filter by qualification level
4. Prioritize high-scoring leads

### Export Analytics
1. Go to Admin > Analytics
2. Select time period
3. Data automatically updates
4. Use browser's export feature

### Create Backup
1. Go to Admin > Settings
2. Click "Create Backup"
3. Backup saved automatically
4. Download if needed

---

## 🎯 Best Practices

### Lead Management
1. Check messages daily
2. Prioritize high-scoring leads (80+)
3. Respond within 24 hours
4. Track follow-ups
5. Update lead status

### Analytics Review
1. Check weekly traffic trends
2. Monitor bounce rate
3. Identify top pages
4. Analyze traffic sources
5. Optimize underperforming pages

### Email Management
1. Monitor spam folder
2. Verify email delivery
3. Update email addresses as needed
4. Test auto-reply functionality
5. Keep email templates updated

### Security
1. Change passwords regularly
2. Monitor admin access logs
3. Keep backups current
4. Review security headers
5. Test form validation

---

## 📚 Additional Resources

- `IMPLEMENTATION-SUMMARY.md` - Complete feature overview
- `FIXES-APPLIED.md` - Recent improvements
- `EMAIL-SETUP-GUIDE.md` - Email configuration
- `GIT-SETUP-README.md` - Git and deployment

---

## 💡 Tips & Tricks

### Speed Up Admin Dashboard
- Clear browser cache regularly
- Use Chrome for best performance
- Disable browser extensions
- Close unnecessary tabs

### Improve Lead Quality
- Ask specific questions in contact form
- Qualify leads before follow-up
- Use lead scores to prioritize
- Track conversion rates

### Optimize Analytics
- Review traffic sources
- Identify high-performing pages
- Analyze visitor behavior
- Test different CTAs

### Better Email Delivery
- Monitor spam folder
- Verify sender reputation
- Use professional templates
- Test on multiple email clients

---

## 🚨 Emergency Procedures

### Site Down
1. Check hosting status
2. Verify DNS settings
3. Check .htaccess file
4. Review error logs
5. Contact hosting support

### Lost Data
1. Restore from backup (Admin > Settings)
2. Check GitHub repository
3. Contact hosting support
4. Verify file permissions

### Security Breach
1. Change all passwords
2. Review access logs
3. Update security headers
4. Scan for malware
5. Contact hosting support

---

## 📞 Support

For issues or questions:
1. Check this guide first
2. Review error logs
3. Check browser console
4. Contact hosting support
5. Review GitHub issues

---

**Last Updated**: January 4, 2026
**Version**: 1.0
**Status**: Production Ready ✅
