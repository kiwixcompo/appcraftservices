<?php
/**
 * Property-Based Test for Database Schema Validation
 * Feature: website-refactoring, Property 1: Database Schema Integrity
 * 
 * Validates: Requirements 2.1, 5.1, 4.1
 * - 2.1: Real-time reviews system database structure
 * - 5.1: Enhanced lead capture database structure  
 * - 4.1: Portfolio and case studies database structure
 */

namespace AppCraft\Tests\Property;

use PHPUnit\Framework\TestCase;
use Database;
use DatabaseUtils;
use Faker\Factory as Faker;

class DatabaseSchemaPropertyTest extends TestCase
{
    private $db;
    private $dbUtils;
    private $faker;
    private $conn;

    protected function setUp(): void
    {
        $this->faker = Faker::create();
        $this->db = new Database();
        $this->dbUtils = new DatabaseUtils();
        $this->conn = $this->db->getConnection();
        
        // Skip tests if database connection fails
        if (!$this->conn) {
            $this->markTestSkipped('Database connection not available');
        }
        
        // Initialize schema if needed
        $this->db->initializeSchema();
    }

    /**
     * Property 1: Database Schema Integrity
     * For any database operation, the schema should maintain referential integrity
     * and enforce all defined constraints across reviews, startup_leads, and case_studies tables
     * 
     * @test
     * @group property
     */
    public function testDatabaseSchemaIntegrityProperty()
    {
        // Run property test with 100 iterations as specified in design
        for ($i = 0; $i < 100; $i++) {
            $this->runSchemaIntegrityIteration();
        }
    }

    private function runSchemaIntegrityIteration()
    {
        // Test 1: Reviews table schema integrity (Requirement 2.1)
        $this->assertReviewsTableIntegrity();
        
        // Test 2: Startup leads table schema integrity (Requirement 5.1)
        $this->assertStartupLeadsTableIntegrity();
        
        // Test 3: Case studies table schema integrity (Requirement 4.1)
        $this->assertCaseStudiesTableIntegrity();
        
        // Test 4: Foreign key constraints integrity
        $this->assertForeignKeyIntegrity();
        
        // Test 5: Index integrity for performance
        $this->assertIndexIntegrity();
    }

    /**
     * Test reviews table schema integrity (Requirement 2.1)
     */
    private function assertReviewsTableIntegrity()
    {
        // Generate random review data
        $reviewData = [
            'reviewer_name' => $this->faker->name,
            'company' => $this->faker->company,
            'project_type' => $this->faker->randomElement(['MVP Development', 'Web Application', 'Mobile App', 'API Development']),
            'rating' => $this->faker->numberBetween(1, 5),
            'content' => $this->faker->paragraph,
            'project_completion_date' => $this->faker->date(),
            'reviewer_email' => $this->faker->email,
            'moderation_status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'contact_permission' => $this->faker->boolean,
            'verified' => $this->faker->boolean
        ];

        // Insert should succeed with valid data
        $reviewId = $this->dbUtils->insert('reviews', $reviewData);
        $this->assertNotFalse($reviewId, 'Valid review data should be insertable');

        // Verify all required fields are present
        $insertedReview = $this->dbUtils->findOne('reviews', ['id' => $reviewId]);
        $this->assertNotFalse($insertedReview, 'Inserted review should be retrievable');
        
        // Verify required fields exist and have correct types
        $this->assertArrayHasKey('reviewer_name', $insertedReview);
        $this->assertArrayHasKey('company', $insertedReview);
        $this->assertArrayHasKey('project_type', $insertedReview);
        $this->assertArrayHasKey('rating', $insertedReview);
        $this->assertArrayHasKey('content', $insertedReview);
        $this->assertArrayHasKey('moderation_status', $insertedReview);
        
        // Verify rating constraint (1-5)
        $this->assertGreaterThanOrEqual(1, $insertedReview['rating']);
        $this->assertLessThanOrEqual(5, $insertedReview['rating']);
        
        // Verify moderation status is valid enum
        $this->assertContains($insertedReview['moderation_status'], ['pending', 'approved', 'rejected']);
        
        // Clean up
        $this->dbUtils->delete('reviews', ['id' => $reviewId]);
    }

    /**
     * Test startup leads table schema integrity (Requirement 5.1)
     */
    private function assertStartupLeadsTableIntegrity()
    {
        // Generate random startup lead data
        $leadData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'company_name' => $this->faker->company,
            'funding_stage' => $this->faker->randomElement(['pre-seed', 'seed', 'series-a', 'series-b', 'growth', 'other']),
            'funding_timeline' => $this->faker->randomElement(['3 months', '6 months', '12 months', '18+ months']),
            'project_description' => $this->faker->paragraph,
            'project_urgency' => $this->faker->numberBetween(1, 10),
            'budget_range' => $this->faker->randomElement(['$10,000 - $25,000', '$25,000 - $50,000', '$50,000 - $100,000', '$100,000+']),
            'overall_score' => $this->faker->randomFloat(2, 0, 10),
            'status' => $this->faker->randomElement(['new', 'qualified', 'contacted', 'converted', 'rejected']),
            'priority_level' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent'])
        ];

        // Insert should succeed with valid data
        $leadId = $this->dbUtils->insert('startup_leads', $leadData);
        $this->assertNotFalse($leadId, 'Valid startup lead data should be insertable');

        // Verify all required fields are present
        $insertedLead = $this->dbUtils->findOne('startup_leads', ['id' => $leadId]);
        $this->assertNotFalse($insertedLead, 'Inserted lead should be retrievable');
        
        // Verify required fields exist
        $this->assertArrayHasKey('first_name', $insertedLead);
        $this->assertArrayHasKey('last_name', $insertedLead);
        $this->assertArrayHasKey('email', $insertedLead);
        $this->assertArrayHasKey('company_name', $insertedLead);
        $this->assertArrayHasKey('funding_stage', $insertedLead);
        $this->assertArrayHasKey('project_urgency', $insertedLead);
        
        // Verify project urgency constraint (1-10)
        $this->assertGreaterThanOrEqual(1, $insertedLead['project_urgency']);
        $this->assertLessThanOrEqual(10, $insertedLead['project_urgency']);
        
        // Verify funding stage is valid enum
        $this->assertContains($insertedLead['funding_stage'], ['pre-seed', 'seed', 'series-a', 'series-b', 'growth', 'other']);
        
        // Verify status is valid enum
        $this->assertContains($insertedLead['status'], ['new', 'qualified', 'contacted', 'converted', 'rejected']);
        
        // Clean up
        $this->dbUtils->delete('startup_leads', ['id' => $leadId]);
    }

    /**
     * Test case studies table schema integrity (Requirement 4.1)
     */
    private function assertCaseStudiesTableIntegrity()
    {
        // Generate random case study data
        $caseStudyData = [
            'project_name' => $this->faker->sentence(3),
            'client_name' => $this->faker->name,
            'client_company' => $this->faker->company,
            'industry' => $this->faker->randomElement(['FinTech', 'HealthTech', 'EdTech', 'E-commerce', 'SaaS']),
            'project_description' => $this->faker->paragraph,
            'challenge_description' => $this->faker->paragraph,
            'solution_description' => $this->faker->paragraph,
            'funding_raised' => $this->faker->randomFloat(2, 0, 10000000),
            'tech_stack' => json_encode($this->faker->randomElements(['PHP', 'JavaScript', 'MySQL', 'React', 'Node.js'], 3)),
            'start_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'featured' => $this->faker->boolean
        ];

        // Insert should succeed with valid data
        $caseStudyId = $this->dbUtils->insert('case_studies', $caseStudyData);
        $this->assertNotFalse($caseStudyId, 'Valid case study data should be insertable');

        // Verify all required fields are present
        $insertedCaseStudy = $this->dbUtils->findOne('case_studies', ['id' => $caseStudyId]);
        $this->assertNotFalse($insertedCaseStudy, 'Inserted case study should be retrievable');
        
        // Verify required fields exist
        $this->assertArrayHasKey('project_name', $insertedCaseStudy);
        $this->assertArrayHasKey('client_name', $insertedCaseStudy);
        $this->assertArrayHasKey('client_company', $insertedCaseStudy);
        $this->assertArrayHasKey('industry', $insertedCaseStudy);
        $this->assertArrayHasKey('tech_stack', $insertedCaseStudy);
        $this->assertArrayHasKey('status', $insertedCaseStudy);
        
        // Verify status is valid enum
        $this->assertContains($insertedCaseStudy['status'], ['draft', 'published', 'archived']);
        
        // Verify tech_stack is valid JSON
        $techStack = json_decode($insertedCaseStudy['tech_stack'], true);
        $this->assertNotNull($techStack, 'Tech stack should be valid JSON');
        $this->assertIsArray($techStack, 'Tech stack should decode to array');
        
        // Clean up
        $this->dbUtils->delete('case_studies', ['id' => $caseStudyId]);
    }

    /**
     * Test foreign key constraints integrity
     */
    private function assertForeignKeyIntegrity()
    {
        // Create a test lead first
        $leadData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'company_name' => $this->faker->company,
            'funding_stage' => 'seed',
            'funding_timeline' => '6 months',
            'project_description' => $this->faker->paragraph,
            'project_urgency' => 5,
            'budget_range' => '$50,000 - $100,000'
        ];
        
        $leadId = $this->dbUtils->insert('startup_leads', $leadData);
        $this->assertNotFalse($leadId, 'Test lead should be created');

        // Test lead_interactions foreign key constraint
        $interactionData = [
            'lead_id' => $leadId,
            'interaction_type' => 'email',
            'subject' => 'Test interaction',
            'content' => 'Test content',
            'staff_member' => 'Test Staff'
        ];
        
        $interactionId = $this->dbUtils->insert('lead_interactions', $interactionData);
        $this->assertNotFalse($interactionId, 'Lead interaction with valid foreign key should be insertable');
        
        // Verify the interaction exists
        $interaction = $this->dbUtils->findOne('lead_interactions', ['id' => $interactionId]);
        $this->assertNotFalse($interaction, 'Lead interaction should be retrievable');
        $this->assertEquals($leadId, $interaction['lead_id'], 'Foreign key should match');
        
        // Clean up
        $this->dbUtils->delete('lead_interactions', ['id' => $interactionId]);
        $this->dbUtils->delete('startup_leads', ['id' => $leadId]);
    }

    /**
     * Test index integrity for performance requirements
     */
    private function assertIndexIntegrity()
    {
        // Verify that required indexes exist for performance
        $indexes = [
            'reviews' => ['idx_moderation_status', 'idx_rating', 'idx_submission_date'],
            'startup_leads' => ['idx_email', 'idx_funding_stage', 'idx_overall_score', 'idx_status'],
            'case_studies' => ['idx_status', 'idx_featured', 'idx_industry']
        ];
        
        foreach ($indexes as $table => $expectedIndexes) {
            $stmt = $this->conn->query("SHOW INDEX FROM $table");
            $actualIndexes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $indexNames = array_column($actualIndexes, 'Key_name');
            
            foreach ($expectedIndexes as $expectedIndex) {
                $this->assertContains($expectedIndex, $indexNames, 
                    "Index $expectedIndex should exist on table $table for performance");
            }
        }
    }

    /**
     * Test constraint violations are properly handled
     * 
     * @test
     * @group property
     */
    public function testConstraintViolationsProperty()
    {
        // Test rating constraint violation (should fail)
        $invalidReview = [
            'reviewer_name' => 'Test User',
            'company' => 'Test Company',
            'project_type' => 'Test Project',
            'rating' => 6, // Invalid: should be 1-5
            'content' => 'Test content',
            'project_completion_date' => date('Y-m-d'),
            'reviewer_email' => 'test@example.com'
        ];
        
        $result = $this->dbUtils->insert('reviews', $invalidReview);
        $this->assertFalse($result, 'Invalid rating should be rejected by constraint');
        
        // Test project urgency constraint violation (should fail)
        $invalidLead = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'company_name' => 'Test Company',
            'funding_stage' => 'seed',
            'funding_timeline' => '6 months',
            'project_description' => 'Test description',
            'project_urgency' => 11, // Invalid: should be 1-10
            'budget_range' => '$50,000'
        ];
        
        $result = $this->dbUtils->insert('startup_leads', $invalidLead);
        $this->assertFalse($result, 'Invalid project urgency should be rejected by constraint');
    }

    /**
     * Test that all required tables exist
     * 
     * @test
     * @group property
     */
    public function testRequiredTablesExistProperty()
    {
        $requiredTables = ['reviews', 'startup_leads', 'case_studies', 'lead_interactions', 'review_moderation_log'];
        
        $stmt = $this->conn->query("SHOW TABLES");
        $existingTables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        foreach ($requiredTables as $table) {
            $this->assertContains($table, $existingTables, 
                "Required table $table should exist in database schema");
        }
    }
}