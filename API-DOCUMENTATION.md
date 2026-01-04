# API Documentation - App Craft Services

**Version**: 1.0  
**Last Updated**: January 4, 2026  
**Base URL**: `https://appcraftservices.com/api/`

---

## Table of Contents

1. [Contact API](#contact-api)
2. [Lead Scoring API](#lead-scoring-api)
3. [Analytics API](#analytics-api)
4. [Schedule API](#schedule-api)
5. [Admin APIs](#admin-apis)
6. [Error Handling](#error-handling)
7. [Rate Limiting](#rate-limiting)
8. [Authentication](#authentication)

---

## Contact API

### Submit Contact Form

**Endpoint**: `POST /api/contact.php`

**Description**: Submits a contact form with startup qualification fields and triggers lead scoring.

**Request Headers**:
```
Content-Type: application/json
```

**Request Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1-555-123-4567",
  "company": "TechStartup Inc",
  "project-type": "essential-app",
  "funding-stage": "seed",
  "investor-deadline": "Demo Day in 3 months",
  "timeline": "2-3-months",
  "budget": "custom-quote",
  "project-details": "We need a mobile app for managing remote teams...",
  "captcha_answer": "15",
  "captcha_correct": "15"
}
```

**Response (Success)**:
```json
{
  "success": true,
  "message": "Thank you for your message! We'll get back to you within 24 hours.",
  "message_id": "msg_1234567890.abc123",
  "email_status": "sent_to_both_admins_and_client",
  "auto_reply_sent": true
}
```

**Response (Error)**:
```json
{
  "success": false,
  "message": "Please provide a valid email address"
}
```

**Status Codes**:
- `200`: Success
- `400`: Bad request (validation error)
- `500`: Server error

**Validation Rules**:
- `name`: Required, min 2 characters
- `email`: Required, valid email format
- `message`: Required, min 10 characters
- `captcha_answer`: Must match `captcha_correct`

**Side Effects**:
- Message saved to `data/messages.json`
- Lead score calculated
- Email sent to admin
- Auto-reply sent to user
- Analytics tracked

---

## Lead Scoring API

### Calculate Lead Score

**Endpoint**: `POST /api/lead-scoring.php`

**Description**: Calculates investment readiness score for a lead.

**Request Headers**:
```
Content-Type: application/json
```

**Request Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1-555-123-4567",
  "company": "TechStartup Inc",
  "funding_stage": "seed",
  "investor_deadline": "Demo Day in 3 months",
  "timeline": "2-3-months",
  "budget": "custom-quote",
  "project_details": "We need a mobile app for managing remote teams..."
}
```

**Response**:
```json
{
  "success": true,
  "scoring": {
    "total_score": 82,
    "max_score": 100,
    "percentage": 82.0,
    "qualification_level": "Highly Qualified",
    "factors": [
      {
        "name": "Funding Stage",
        "value": "Seed",
        "points": 25,
        "max": 25
      },
      {
        "name": "Timeline Urgency",
        "value": "2-3 months + Investor Deadline",
        "points": 20,
        "max": 20
      },
      {
        "name": "Budget Qualification",
        "value": "Custom Quote",
        "points": 20,
        "max": 20
      },
      {
        "name": "Project Clarity",
        "value": "45 words provided",
        "points": 10,
        "max": 15
      },
      {
        "name": "Contact Quality",
        "value": "Email ✓, Phone ✓, Company ✓",
        "points": 10,
        "max": 10
      },
      {
        "name": "Investor Readiness",
        "value": "High",
        "points": 10,
        "max": 10
      }
    ],
    "recommendation": "Priority follow-up within 24 hours. High investment readiness indicators."
  }
}
```

**Scoring Breakdown**:
- **Funding Stage** (0-25): Pre-seed (20), Seed (25), Series A (25), Series B (15), Bootstrapped (18)
- **Timeline Urgency** (0-20): ASAP (20), 1-month (18), 2-3 months (15), 3-6 months (10), Flexible (5)
- **Budget** (0-20): Essential App (15), Custom Quote (20), Discuss (10)
- **Project Clarity** (0-15): Based on word count (100+ = 15 points)
- **Contact Quality** (0-10): Email (3), Phone (3), Company (4)
- **Investor Readiness** (0-10): Investor deadline (5), Active funding stage (5)

**Qualification Levels**:
- **Highly Qualified** (80-100): Priority follow-up within 24 hours
- **Qualified** (60-79): Follow-up within 48 hours
- **Moderately Qualified** (40-59): Standard follow-up
- **Needs Qualification** (0-39): Requires qualification call

---

## Analytics API

### Get Analytics Data

**Endpoint**: `GET /api/analytics.php`

**Description**: Retrieves analytics data with optional filtering.

**Query Parameters**:
```
?period=30&page=/services&source=google
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `period` | int | 30 | Days to analyze (7, 30, 90, 365) |
| `page` | string | '' | Filter by page path |
| `source` | string | '' | Filter by traffic source |

**Response**:
```json
{
  "success": true,
  "period": 30,
  "total_views": 1234,
  "unique_visitors": 456,
  "bounce_rate": 32.5,
  "avg_load_time": 2.3,
  "views_by_date": {
    "2026-01-01": 45,
    "2026-01-02": 52,
    "2026-01-03": 48
  },
  "top_pages": {
    "/": 234,
    "/services": 156,
    "/pricing": 98
  },
  "traffic_sources": {
    "direct": 450,
    "google": 200,
    "facebook": 50,
    "other": 156
  },
  "device_types": {
    "desktop": 300,
    "mobile": 140,
    "tablet": 16
  },
  "browsers": {
    "Chrome": 350,
    "Firefox": 60,
    "Safari": 46
  },
  "countries": {
    "United States": 300,
    "Canada": 50,
    "Unknown": 106
  },
  "hourly_distribution": [0, 0, 0, 5, 12, 25, 45, 60, 75, 80, 85, 90, 95, 100, 95, 85, 75, 65, 55, 45, 35, 25, 15, 5],
  "recent_visitors": [
    {
      "timestamp": "2026-01-04 14:30:45",
      "page": "/services",
      "source": "google",
      "device_type": "mobile",
      "browser": "Chrome",
      "country": "United States"
    }
  ]
}
```

### Track Page View

**Endpoint**: `POST /api/analytics.php`

**Description**: Tracks a page view (called automatically by analytics.js).

**Request Body**:
```json
{
  "page": "/services",
  "title": "Services - App Craft Services",
  "referrer": "https://google.com",
  "session_id": "session_1234567890_abc123",
  "is_new_visitor": false,
  "screen_resolution": "1920x1080",
  "viewport_size": "1920x1080",
  "load_time": 2.3,
  "source": "google",
  "medium": "organic",
  "campaign": ""
}
```

**Response**:
```json
{
  "success": true,
  "message": "Page view tracked"
}
```

---

## Schedule API

### Get Available Slots

**Endpoint**: `GET /api/schedule.php?action=get_slots`

**Description**: Retrieves available consultation time slots.

**Query Parameters**:
```
?action=get_slots&date=2026-01-15
```

**Response**:
```json
{
  "success": true,
  "date": "2026-01-15",
  "available_slots": [
    "09:00 AM",
    "10:00 AM",
    "11:00 AM",
    "02:00 PM",
    "03:00 PM",
    "04:00 PM"
  ]
}
```

### Book Consultation

**Endpoint**: `POST /api/schedule.php`

**Description**: Books a consultation appointment.

**Request Body**:
```json
{
  "action": "book",
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1-555-123-4567",
  "date": "2026-01-15",
  "time": "10:00 AM",
  "topic": "MVP Development Discussion",
  "notes": "Interested in Series A ready package"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Consultation booked successfully",
  "booking_id": "booking_1234567890",
  "confirmation_sent": true
}
```

---

## Admin APIs

### Get Messages

**Endpoint**: `GET /admin/api/get_messages.php`

**Description**: Retrieves all contact form messages (admin only).

**Authentication**: Session required

**Response**:
```json
[
  {
    "id": "msg_1234567890.abc123",
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1-555-123-4567",
    "company": "TechStartup Inc",
    "project_type": "essential-app",
    "funding_stage": "seed",
    "investor_deadline": "Demo Day in 3 months",
    "timeline": "2-3-months",
    "budget": "custom-quote",
    "message": "We need a mobile app...",
    "created_at": "2026-01-04 14:30:45",
    "read": false,
    "lead_score": {
      "total_score": 82,
      "percentage": 82.0,
      "qualification_level": "Highly Qualified",
      "recommendation": "Priority follow-up within 24 hours..."
    }
  }
]
```

### Mark Message as Read

**Endpoint**: `POST /admin/api/mark_message_read.php`

**Description**: Marks a message as read.

**Request Body**:
```json
{
  "message_id": "msg_1234567890.abc123"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Message marked as read"
}
```

### Delete Message

**Endpoint**: `POST /admin/api/delete_message.php`

**Description**: Deletes a message.

**Request Body**:
```json
{
  "message_id": "msg_1234567890.abc123"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Message deleted"
}
```

### Get Lead Analytics

**Endpoint**: `GET /admin/api/get_lead_analytics.php`

**Description**: Retrieves lead analytics and statistics.

**Response**:
```json
{
  "success": true,
  "total_leads": 45,
  "qualified_leads": 28,
  "qualification_rate": 62.2,
  "average_score": 68.5,
  "leads_by_stage": {
    "pre-seed": 12,
    "seed": 18,
    "series-a": 10,
    "bootstrapped": 5
  },
  "leads_by_qualification": {
    "Highly Qualified": 15,
    "Qualified": 13,
    "Moderately Qualified": 12,
    "Needs Qualification": 5
  },
  "top_leads": [
    {
      "id": "msg_1234567890.abc123",
      "name": "John Doe",
      "company": "TechStartup Inc",
      "score": 92,
      "qualification": "Highly Qualified",
      "funding_stage": "seed",
      "created_at": "2026-01-04 14:30:45"
    }
  ]
}
```

---

## Error Handling

### Error Response Format

All errors follow this format:

```json
{
  "success": false,
  "message": "Error description"
}
```

### Common Error Codes

| Code | Message | Solution |
|------|---------|----------|
| 400 | Bad request | Check request format and parameters |
| 401 | Unauthorized | Login required for admin endpoints |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not found | Resource doesn't exist |
| 405 | Method not allowed | Use correct HTTP method |
| 500 | Server error | Contact support |

### Validation Errors

```json
{
  "success": false,
  "message": "Name, email, and message are required"
}
```

---

## Rate Limiting

### Limits

- **Contact Form**: 5 submissions per IP per hour
- **Analytics**: 100 requests per minute
- **Admin APIs**: 1000 requests per hour (authenticated)

### Rate Limit Headers

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1609862400
```

### Rate Limit Exceeded

```json
{
  "success": false,
  "message": "Rate limit exceeded. Please try again later.",
  "retry_after": 3600
}
```

---

## Authentication

### Admin Authentication

Admin APIs require session authentication:

1. Login at `/admin/login.php`
2. Session cookie automatically set
3. Include cookie in subsequent requests
4. Session expires after 30 minutes of inactivity

### API Keys

Currently not implemented. Future enhancement planned.

### CORS

CORS is enabled for:
- Contact form submissions
- Analytics tracking
- Public APIs

CORS is disabled for:
- Admin APIs
- Sensitive endpoints

---

## Best Practices

### Request Handling
1. Always validate input on client side
2. Handle errors gracefully
3. Implement retry logic for failed requests
4. Use appropriate HTTP methods
5. Include proper headers

### Response Handling
1. Check `success` field first
2. Handle error messages appropriately
3. Parse JSON responses correctly
4. Implement timeout handling
5. Log errors for debugging

### Security
1. Never expose API keys in client code
2. Always use HTTPS
3. Validate all inputs server-side
4. Implement rate limiting
5. Monitor for suspicious activity

### Performance
1. Cache responses when appropriate
2. Minimize request payload
3. Use compression
4. Implement pagination for large datasets
5. Monitor response times

---

## Examples

### JavaScript Example

```javascript
// Submit contact form
async function submitContact(formData) {
  try {
    const response = await fetch('/api/contact.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData)
    });
    
    const result = await response.json();
    
    if (result.success) {
      console.log('Message sent:', result.message_id);
      // Show success message
    } else {
      console.error('Error:', result.message);
      // Show error message
    }
  } catch (error) {
    console.error('Request failed:', error);
  }
}
```

### PHP Example

```php
// Get analytics data
$ch = curl_init('https://appcraftservices.com/api/analytics.php?period=30');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$data = json_decode($response, true);

if ($data['success']) {
  echo "Total views: " . $data['total_views'];
}
```

---

## Changelog

### Version 1.0 (January 4, 2026)
- Initial API documentation
- Contact API
- Lead Scoring API
- Analytics API
- Schedule API
- Admin APIs
- Error handling
- Rate limiting
- Authentication

---

## Support

For API issues or questions:
1. Check this documentation
2. Review error messages
3. Check browser console
4. Contact support
5. Review GitHub issues

---

**Last Updated**: January 4, 2026  
**Status**: Production Ready ✅
