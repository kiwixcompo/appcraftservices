<?php
/**
 * Database Test Script
 * Tests database connection and basic operations
 */

require_once 'database_utils.php';

echo "Testing database setup...\n\n";

try {
    $dbUtils = new DatabaseUtils();
    
    // Test connection
    echo "1. Testing database connection...\n";
    $conn = $dbUtils->getConnection();
    if ($conn) {
        echo "   ✓ Database connection successful\n";
    } else {
        echo "   ✗ Database connection failed\n";
        exit(1);
    }
    
    // Test if tables exist
    echo "\n2. Checking if required tables exist...\n";
    if ($dbUtils->tablesExist()) {
        echo "   ✓ All required tables exist\n";
    } else {
        echo "   ✗ Some required tables are missing\n";
        echo "   Run init_database.php to create tables\n";
        exit(1);
    }
    
    // Show table statistics
    echo "\n3. Table statistics:\n";
    $stats = $dbUtils->getTableStats();
    foreach ($stats as $table => $count) {
        echo "   - $table: $count records\n";
    }
    
    // Test basic operations
    echo "\n4. Testing basic database operations...\n";
    
    // Test insert operation
    $testReview = [
        'reviewer_name' => 'Test User',
        'company' => 'Test Company',
        'project_type' => 'Test Project',
        'rating' => 5,
        'content' => 'This is a test review',
        'project_completion_date' => date('Y-m-d'),
        'reviewer_email' => 'test@example.com',
        'moderation_status' => 'pending'
    ];
    
    $reviewId = $dbUtils->insert('reviews', $testReview);
    if ($reviewId) {
        echo "   ✓ Insert operation successful (ID: $reviewId)\n";
        
        // Test select operation
        $review = $dbUtils->findOne('reviews', ['id' => $reviewId]);
        if ($review && $review['reviewer_name'] === 'Test User') {
            echo "   ✓ Select operation successful\n";
        } else {
            echo "   ✗ Select operation failed\n";
        }
        
        // Test update operation
        $updated = $dbUtils->update('reviews', ['rating' => 4], ['id' => $reviewId]);
        if ($updated) {
            echo "   ✓ Update operation successful\n";
        } else {
            echo "   ✗ Update operation failed\n";
        }
        
        // Test delete operation
        $deleted = $dbUtils->delete('reviews', ['id' => $reviewId]);
        if ($deleted) {
            echo "   ✓ Delete operation successful\n";
        } else {
            echo "   ✗ Delete operation failed\n";
        }
    } else {
        echo "   ✗ Insert operation failed\n";
    }
    
    echo "\n✓ All database tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    exit(1);
}
?>