# Email Authentication Setup Guide

## Critical: Prevent Emails from Going to Spam

To ensure payment emails reach the inbox instead of spam, you MUST configure proper email authentication for your domain `appcraftservices.com`.

## Required DNS Records

### 1. SPF (Sender Policy Framework) Record

Add this TXT record to your DNS:

```
Type: TXT
Name: @
Value: v=spf1 a mx ip4:YOUR_SERVER_IP ~all
TTL: 3600
```

**Replace `YOUR_SERVER_IP` with your actual server IP address.**

If you're using additional email services (like SendGrid, Mailgun, etc.), update to:
```
v=spf1 a mx ip4:YOUR_SERVER_IP include:_spf.google.com include:sendgrid.net ~all
```

### 2. DKIM (DomainKeys Identified Mail) Record

#### Step 1: Generate DKIM Keys on Your Server

```bash
# Install OpenDKIM
sudo apt-get install opendkim opendkim-tools

# Generate keys
sudo opendkim-genkey -t -s mail -d appcraftservices.com

# View the public key
sudo cat /etc/opendkim/keys/appcraftservices.com/mail.txt
```

#### Step 2: Add DKIM DNS Record

```
Type: TXT
Name: mail._domainkey
Value: v=DKIM1; k=rsa; p=YOUR_PUBLIC_KEY_HERE
TTL: 3600
```

### 3. DMARC (Domain-based Message Authentication) Record

Add this TXT record:

```
Type: TXT
Name: _dmarc
Value: v=DMARC1; p=quarantine; rua=mailto:hello@appcraftservices.com; ruf=mailto:hello@appcraftservices.com; fo=1
TTL: 3600
```

### 4. Reverse DNS (PTR) Record

Contact your hosting provider to set up reverse DNS:
- PTR record should point from your server IP to `mail.appcraftservices.com`
- This is critical for email deliverability

## Alternative: Use a Transactional Email Service

For better deliverability, consider using a professional email service:

### Option 1: SendGrid (Recommended)

1. Sign up at https://sendgrid.com
2. Verify your domain
3. Install SendGrid PHP library:
```bash
composer require sendgrid/sendgrid
```

4. Update `admin/api/send_payment_email.php` to use SendGrid API

### Option 2: Mailgun

1. Sign up at https://www.mailgun.com
2. Verify your domain
3. Install Mailgun PHP SDK:
```bash
composer require mailgun/mailgun-php
```

### Option 3: Amazon SES

1. Sign up for AWS SES
2. Verify your domain
3. Install AWS SDK:
```bash
composer require aws/aws-sdk-php
```

## Current Email Improvements Already Implemented

✅ Professional HTML email template
✅ Plain text alternative for better compatibility
✅ Proper MIME multipart structure
✅ Anti-spam headers
✅ Personalized subject line
✅ Clear sender information
✅ Unsubscribe link
✅ Message-ID header
✅ Proper date formatting
✅ Reduced spam trigger words
✅ Balanced text-to-image ratio
✅ No URL shorteners
✅ Professional footer with contact info

## Testing Email Deliverability

### 1. Mail Tester
Send a test email to: test-xxxxx@mail-tester.com
Visit: https://www.mail-tester.com
Check your score (aim for 10/10)

### 2. GlockApps
https://glockapps.com
Test inbox placement across major email providers

### 3. MXToolbox
https://mxtoolbox.com/SuperTool.aspx
Check your domain's email configuration

## Common Spam Triggers to Avoid

❌ ALL CAPS IN SUBJECT
❌ Multiple exclamation marks!!!
❌ Words like: FREE, URGENT, ACT NOW, LIMITED TIME
❌ Too many links
❌ Suspicious URLs
❌ Missing unsubscribe link
❌ No physical address
❌ Broken HTML
❌ Large images without text
❌ Attachments in first email

## Monitoring Email Reputation

### Check Your IP Reputation:
- https://www.senderscore.org
- https://www.senderbase.org
- https://www.barracudacentral.org/lookups

### Check Domain Reputation:
- https://postmaster.google.com
- https://postmaster.yahoo.com
- https://postmaster.live.com

## Immediate Actions Required

1. **Add SPF Record** - Prevents email spoofing
2. **Configure DKIM** - Authenticates your emails
3. **Add DMARC Record** - Protects your domain
4. **Set up Reverse DNS** - Improves deliverability
5. **Test with Mail Tester** - Verify configuration

## Long-term Recommendations

1. **Use a dedicated IP** for sending emails
2. **Warm up your IP** gradually increase sending volume
3. **Monitor bounce rates** keep below 5%
4. **Handle unsubscribes** promptly
5. **Maintain clean email list** remove invalid addresses
6. **Send consistently** don't send in bursts
7. **Monitor engagement** track opens and clicks
8. **Avoid spam traps** use double opt-in

## PHP mail() Function Limitations

The current implementation uses PHP's `mail()` function, which has limitations:

- No built-in authentication
- Relies on server configuration
- Limited tracking capabilities
- May be blocked by some ISPs

**Recommendation:** Migrate to a transactional email service for production use.

## Quick Fix: Update php.ini

If you must use PHP mail(), configure your php.ini:

```ini
[mail function]
SMTP = localhost
smtp_port = 25
sendmail_from = hello@appcraftservices.com
sendmail_path = /usr/sbin/sendmail -t -i -f hello@appcraftservices.com
```

## Verification Checklist

- [ ] SPF record added and verified
- [ ] DKIM configured and keys generated
- [ ] DMARC policy set
- [ ] Reverse DNS configured
- [ ] Mail tester score 8+/10
- [ ] Test email received in inbox (not spam)
- [ ] Reply-to address works
- [ ] Unsubscribe link functional
- [ ] Email displays correctly on mobile
- [ ] Plain text version readable

## Support Resources

- SPF Wizard: https://www.spfwizard.net
- DKIM Wizard: https://www.dmarcanalyzer.com/dkim/dkim-wizard/
- DMARC Wizard: https://www.dmarcanalyzer.com/dmarc/dmarc-record-wizard/

## Contact

For email deliverability issues:
- Check server logs: `/var/log/mail.log`
- Check PHP error logs
- Contact your hosting provider
- Consider hiring an email deliverability consultant

---

**Note:** Email authentication setup typically takes 24-48 hours to propagate globally. Test thoroughly before sending to clients.
