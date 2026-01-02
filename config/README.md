# Database Setup for App Craft Services

This directory contains the database configuration and schema files for the App Craft Services website refactoring project.

## Files Overview

- `database.php` - Main database connection class
- `database_utils.php` - Utility functions for common database operations
- `schema.sql` - Complete MySQL schema with all required tables
- `init_database.php` - Database initialization script
- `test_database.php` - Database testing script
- `.env.example` - Environment configuration template

## Database Schema

The schema includes the following tables:

### Reviews Table
- Stores customer reviews with moderation workflow
- Fields: reviewer info, rating, content, moderation status
- Supports real-time review display (Requirement 2.1, 2.3)

### Startup Leads Table
- Captures and qualifies startup leads
- Fields: contact info, funding stage, project details, scoring
- Supports lead qualification and routing (Requirement 5.1)

### Case Studies Table
- Portfolio showcase with detailed project information
- Fields: project details, business metrics, tech stack, testimonials
- Supports investor-focused portfolio display (Requirement 4.1)

### Supporting Tables
- `lead_interactions` - Communication tracking
- `review_moderation_log` - Audit trail for review moderation

## Setup Instructions

1. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

2. **Initialize Database**
   ```bash
   php init_database.php
   ```

3. **Test Setup**
   ```bash
   php test_database.php
   ```

## Environment Variables

Required environment variables in `.env`:

```
DB_HOST=localhost
DB_NAME=appcraft_services
DB_USER=root
DB_PASS=your_password
```

## Usage Examples

### Basic Connection
```php
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
```

### Using Utilities
```php
require_once 'config/database_utils.php';
$dbUtils = new DatabaseUtils();

// Insert a review
$reviewId = $dbUtils->insert('reviews', [
    'reviewer_name' => 'John Doe',
    'company' => 'Startup Inc',
    'rating' => 5,
    // ... other fields
]);

// Find reviews
$reviews = $dbUtils->find('reviews', ['moderation_status' => 'approved']);
```

## Requirements Mapping

This database setup addresses the following requirements:

- **Requirement 2.1**: Real-time review display system
- **Requirement 2.3**: Review moderation workflow
- **Requirement 4.1**: Portfolio and case studies showcase
- **Requirement 5.1**: Enhanced lead capture and qualification

## Migration from JSON

The existing JSON-based data storage in the `data/` directory will be maintained for backward compatibility. New features (reviews, leads, case studies) will use MySQL for better performance and scalability.

## Security Considerations

- All database queries use prepared statements to prevent SQL injection
- Sensitive data is properly escaped and validated
- Database credentials should be stored in environment variables
- Regular backups should be implemented for production use

## Troubleshooting

1. **Connection Issues**: Check database credentials in `.env`
2. **Permission Errors**: Ensure database user has CREATE/INSERT/UPDATE/DELETE privileges
3. **Schema Issues**: Run `init_database.php` to recreate tables
4. **Testing Failures**: Run `test_database.php` to diagnose issues