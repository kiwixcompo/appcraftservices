-- App Craft Services Database Schema
-- Creates tables for reviews, startup leads, and case studies

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS appcraft_services CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE appcraft_services;

-- Reviews table with moderation workflow fields
-- Requirements: 2.1, 2.3 - Real-time reviews with moderation
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_name VARCHAR(255) NOT NULL,
    company VARCHAR(255) NOT NULL,
    project_type VARCHAR(255) NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    content TEXT NOT NULL,
    project_completion_date DATE NOT NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    moderation_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    moderator_id INT NULL,
    moderation_date TIMESTAMP NULL,
    moderation_notes TEXT NULL,
    contact_permission BOOLEAN DEFAULT FALSE,
    verified BOOLEAN DEFAULT FALSE,
    reviewer_email VARCHAR(255) NOT NULL,
    project_id VARCHAR(100) NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_moderation_status (moderation_status),
    INDEX idx_rating (rating),
    INDEX idx_submission_date (submission_date),
    INDEX idx_display_order (display_order),
    INDEX idx_reviewer_email (reviewer_email)
);

-- Startup leads table with qualification scoring fields
-- Requirements: 5.1 - Enhanced lead capture and qualification
CREATE TABLE IF NOT EXISTS startup_leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Contact Information
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    company_name VARCHAR(255) NOT NULL,
    website VARCHAR(255) NULL,
    
    -- Funding Information
    funding_stage ENUM('pre-seed', 'seed', 'series-a', 'series-b', 'growth', 'other') NOT NULL,
    funding_timeline VARCHAR(100) NOT NULL,
    target_investors TEXT NULL,
    previous_funding DECIMAL(15,2) DEFAULT 0,
    target_funding DECIMAL(15,2) NULL,
    
    -- Project Information
    project_description TEXT NOT NULL,
    project_urgency TINYINT NOT NULL CHECK (project_urgency >= 1 AND project_urgency <= 10),
    budget_range VARCHAR(100) NOT NULL,
    technical_requirements TEXT NULL,
    timeline_requirements VARCHAR(255) NULL,
    
    -- Qualification Scoring
    overall_score DECIMAL(5,2) DEFAULT 0,
    investment_readiness_score DECIMAL(5,2) DEFAULT 0,
    project_urgency_score DECIMAL(5,2) DEFAULT 0,
    budget_qualification_score DECIMAL(5,2) DEFAULT 0,
    timeline_alignment_score DECIMAL(5,2) DEFAULT 0,
    
    -- Lead Management
    status ENUM('new', 'qualified', 'contacted', 'converted', 'rejected') DEFAULT 'new',
    assigned_to VARCHAR(255) NULL,
    priority_level ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    source VARCHAR(100) DEFAULT 'website',
    
    -- Timestamps
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_contact_date TIMESTAMP NULL,
    conversion_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_funding_stage (funding_stage),
    INDEX idx_overall_score (overall_score),
    INDEX idx_status (status),
    INDEX idx_priority_level (priority_level),
    INDEX idx_submission_date (submission_date),
    INDEX idx_assigned_to (assigned_to)
);

-- Case studies table for portfolio showcase
-- Requirements: 4.1 - Portfolio and case studies
CREATE TABLE IF NOT EXISTS case_studies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Basic Information
    project_name VARCHAR(255) NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    client_company VARCHAR(255) NOT NULL,
    industry VARCHAR(100) NOT NULL,
    project_url VARCHAR(500) NULL,
    
    -- Project Details
    project_description TEXT NOT NULL,
    challenge_description TEXT NOT NULL,
    solution_description TEXT NOT NULL,
    
    -- Business Impact Metrics
    funding_raised DECIMAL(15,2) DEFAULT 0,
    user_growth_metric VARCHAR(255) NULL,
    revenue_growth_metric VARCHAR(255) NULL,
    market_expansion_metric VARCHAR(255) NULL,
    other_metrics JSON NULL,
    
    -- Technical Information
    tech_stack JSON NOT NULL,
    scalability_approach TEXT NULL,
    architecture_notes TEXT NULL,
    
    -- Timeline Information
    start_date DATE NOT NULL,
    launch_date DATE NULL,
    project_duration_months INT NULL,
    milestones JSON NULL,
    
    -- Client Testimonial
    testimonial_content TEXT NULL,
    testimonial_author_name VARCHAR(255) NULL,
    testimonial_author_title VARCHAR(255) NULL,
    contact_permission BOOLEAN DEFAULT FALSE,
    
    -- Display Settings
    featured BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    
    -- Media
    featured_image VARCHAR(500) NULL,
    gallery_images JSON NULL,
    
    -- SEO
    slug VARCHAR(100) UNIQUE NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_display_order (display_order),
    INDEX idx_industry (industry),
    INDEX idx_funding_raised (funding_raised),
    INDEX idx_slug (slug),
    INDEX idx_published_at (published_at)
);

-- Lead interactions table for tracking communication
CREATE TABLE IF NOT EXISTS lead_interactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    interaction_type ENUM('email', 'phone', 'meeting', 'proposal', 'contract', 'note') NOT NULL,
    interaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    subject VARCHAR(255) NULL,
    content TEXT NULL,
    staff_member VARCHAR(255) NULL,
    follow_up_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (lead_id) REFERENCES startup_leads(id) ON DELETE CASCADE,
    INDEX idx_lead_id (lead_id),
    INDEX idx_interaction_type (interaction_type),
    INDEX idx_interaction_date (interaction_date)
);

-- Review moderation log for audit trail
CREATE TABLE IF NOT EXISTS review_moderation_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    action ENUM('submitted', 'approved', 'rejected', 'edited') NOT NULL,
    moderator_name VARCHAR(255) NULL,
    notes TEXT NULL,
    previous_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    INDEX idx_review_id (review_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Insert sample data for testing (optional)
-- This can be removed in production

-- Sample approved reviews
INSERT IGNORE INTO reviews (
    reviewer_name, company, project_type, rating, content,
    project_completion_date, moderation_status, contact_permission,
    verified, reviewer_email, project_id
) VALUES (
    'Sarah Johnson', 'TechStart Inc', 'MVP Development', 5,
    'App Craft Services delivered our MVP ahead of schedule and within budget. Their understanding of startup needs was exceptional.',
    '2024-11-15', 'approved', TRUE, TRUE, 'sarah@techstart.com', 'TS001'
);

INSERT IGNORE INTO reviews (
    reviewer_name, company, project_type, rating, content,
    project_completion_date, moderation_status, contact_permission,
    verified, reviewer_email, project_id
) VALUES (
    'Marcus Chen', 'InnovateLabs', 'Custom Web App', 5,
    'The team at App Craft Services transformed our complex workflow into a beautifully designed, user-friendly application. Their technical expertise and attention to detail exceeded our expectations.',
    '2025-01-22', 'approved', TRUE, TRUE, 'marcus@innovatelabs.com', 'IL002'
);

INSERT IGNORE INTO reviews (
    reviewer_name, company, project_type, rating, content,
    project_completion_date, moderation_status, contact_permission,
    verified, reviewer_email, project_id
) VALUES (
    'Elena Rodriguez', 'DataFlow Solutions', 'Enterprise Platform', 5,
    'Working with App Craft Services was a game-changer for our startup. They built a scalable platform that handled our rapid growth seamlessly. Highly recommend their services!',
    '2025-03-08', 'approved', TRUE, FALSE, 'elena@dataflowsolutions.com', 'DFS003'
);

INSERT IGNORE INTO reviews (
    reviewer_name, company, project_type, rating, content,
    project_completion_date, moderation_status, contact_permission,
    verified, reviewer_email, project_id
) VALUES (
    'David Thompson', 'AgileTech Systems', 'SaaS Application', 5,
    'From concept to launch in just 6 weeks! App Craft Services delivered a professional-grade SaaS platform that our investors loved. Their rapid development approach saved us months.',
    '2025-04-15', 'approved', TRUE, TRUE, 'david@agiletech.com', 'ATS004'
);

INSERT IGNORE INTO reviews (
    reviewer_name, company, project_type, rating, content,
    project_completion_date, moderation_status, contact_permission,
    verified, reviewer_email, project_id
) VALUES (
    'Rachel Kim', 'HealthTech Innovations', 'Healthcare Platform', 5,
    'The compliance and security features implemented by App Craft Services met all our healthcare industry requirements. Their expertise in regulated industries was invaluable.',
    '2025-06-03', 'approved', TRUE, FALSE, 'rachel@healthtechinnovations.com', 'HTI005'
);

INSERT IGNORE INTO reviews (
    reviewer_name, company, project_type, rating, content,
    project_completion_date, moderation_status, contact_permission,
    verified, reviewer_email, project_id
) VALUES (
    'James Wilson', 'EduLearn Platform', 'Educational Technology', 5,
    'Our e-learning platform now serves thousands of students daily. App Craft Services built a robust system that scales beautifully. Outstanding work!',
    '2025-07-19', 'approved', TRUE, TRUE, 'james@edulearnplatform.com', 'ELP006'
);

INSERT IGNORE INTO reviews (
    reviewer_name, company, project_type, rating, content,
    project_completion_date, moderation_status, contact_permission,
    verified, reviewer_email, project_id
) VALUES (
    'Lisa Park', 'FinServe Solutions', 'Financial Technology', 5,
    'The fintech application developed by App Craft Services handles complex transactions flawlessly. Their security-first approach gave us confidence with our users financial data.',
    '2025-08-27', 'approved', TRUE, FALSE, 'lisa@finservesolutions.com', 'FSS007'
);

INSERT IGNORE INTO reviews (
    reviewer_name, company, project_type, rating, content,
    project_completion_date, moderation_status, contact_permission,
    verified, reviewer_email, project_id
) VALUES (
    'Michael Okafor', 'LogiChain Systems', 'Supply Chain Platform', 5,
    'App Craft Services delivered a comprehensive supply chain management system that streamlined our operations. The ROI has been incredible. Best development team we\'ve worked with.',
    '2025-09-14', 'approved', TRUE, TRUE, 'michael@logichainsystems.com', 'LCS008'
);

INSERT IGNORE INTO reviews (
    reviewer_name, company, project_type, rating, content,
    project_completion_date, moderation_status, contact_permission,
    verified, reviewer_email, project_id
) VALUES (
    'Sophie Laurent', 'MarketReach Analytics', 'Business Intelligence', 5,
    'The analytics dashboard created by App Craft Services provides real-time insights that drive our business decisions. Their technical architecture is solid and scalable.',
    '2025-10-31', 'approved', TRUE, TRUE, 'sophie@marketreachanalytics.com', 'MRA009'
);

INSERT IGNORE INTO reviews (
    reviewer_name, company, project_type, rating, content,
    project_completion_date, moderation_status, contact_permission,
    verified, reviewer_email, project_id
) VALUES (
    'Alex Rivera', 'ConnectHub Network', 'Social Platform', 5,
    'Building a social networking platform was our biggest challenge yet, but App Craft Services made it look easy. The platform now has over 50,000 active users. Phenomenal results!',
    '2025-12-18', 'approved', TRUE, TRUE, 'alex@connecthubnetwork.com', 'CHN010'
);

-- Sample startup lead
INSERT IGNORE INTO startup_leads (
    first_name, last_name, email, company_name, funding_stage,
    funding_timeline, project_description, project_urgency, budget_range,
    overall_score, status, priority_level
) VALUES (
    'Michael', 'Chen', 'michael@innovate.com', 'Innovate Solutions', 'seed',
    '6 months', 'Need to build a scalable SaaS platform for B2B customers', 8,
    '$50,000 - $100,000', 8.5, 'qualified', 'high'
);

-- Sample case study
INSERT IGNORE INTO case_studies (
    project_name, client_name, client_company, industry, project_description,
    challenge_description, solution_description, funding_raised, tech_stack,
    start_date, launch_date, status, featured, testimonial_content,
    testimonial_author_name, testimonial_author_title, contact_permission
) VALUES (
    'FinTech MVP Platform', 'David Rodriguez', 'PayFlow Startup', 'Financial Technology',
    'Built a comprehensive payment processing platform for small businesses',
    'Client needed a secure, scalable payment solution to compete with established players',
    'Developed a modern web application with real-time processing and comprehensive dashboard',
    2500000.00, '["PHP", "JavaScript", "MySQL", "Stripe API", "AWS"]',
    '2024-08-01', '2024-10-15', 'published', TRUE,
    'The team at App Craft Services understood our vision and delivered beyond expectations. We raised $2.5M partly due to the solid technical foundation they built.',
    'David Rodriguez', 'CEO & Founder', TRUE
);
