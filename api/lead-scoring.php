<?php
/**
 * Lead Scoring System for Startup Qualification
 * Calculates investment readiness score based on multiple factors
 */

header('Content-Type: application/json');

class LeadScorer {
    private $score = 0;
    private $maxScore = 100;
    private $factors = [];
    
    /**
     * Calculate lead score based on startup qualification criteria
     */
    public function calculateScore($leadData) {
        $this->score = 0;
        $this->factors = [];
        
        // Funding Stage Score (0-25 points)
        $this->scoreFundingStage($leadData['funding_stage'] ?? '');
        
        // Timeline Urgency Score (0-20 points)
        $this->scoreTimeline($leadData['timeline'] ?? '', $leadData['investor_deadline'] ?? '');
        
        // Budget Qualification Score (0-20 points)
        $this->scoreBudget($leadData['budget'] ?? '');
        
        // Project Clarity Score (0-15 points)
        $this->scoreProjectClarity($leadData['project_details'] ?? '');
        
        // Contact Quality Score (0-10 points)
        $this->scoreContactQuality($leadData['email'] ?? '', $leadData['phone'] ?? '', $leadData['company'] ?? '');
        
        // Investor Readiness Score (0-10 points)
        $this->scoreInvestorReadiness($leadData['investor_deadline'] ?? '', $leadData['funding_stage'] ?? '');
        
        return [
            'total_score' => $this->score,
            'max_score' => $this->maxScore,
            'percentage' => round(($this->score / $this->maxScore) * 100, 1),
            'qualification_level' => $this->getQualificationLevel(),
            'factors' => $this->factors,
            'recommendation' => $this->getRecommendation()
        ];
    }
    
    /**
     * Score based on funding stage
     */
    private function scoreFundingStage($fundingStage) {
        $scores = [
            'pre-seed' => 20,      // High priority - needs MVP
            'seed' => 25,          // Very high priority - scaling
            'series-a' => 25,      // Very high priority - platform ready
            'series-b' => 15,      // Medium priority - likely has dev team
            'bootstrapped' => 18,  // High priority - cost conscious
            'not-applicable' => 10 // Lower priority
        ];
        
        $points = $scores[$fundingStage] ?? 0;
        $this->score += $points;
        $this->factors[] = [
            'name' => 'Funding Stage',
            'value' => ucfirst(str_replace('-', ' ', $fundingStage)),
            'points' => $points,
            'max' => 25
        ];
    }
    
    /**
     * Score based on timeline urgency
     */
    private function scoreTimeline($timeline, $investorDeadline) {
        $points = 0;
        
        // Timeline score
        $timelineScores = [
            'asap' => 20,
            '1-month' => 18,
            '2-3-months' => 15,
            '3-6-months' => 10,
            'flexible' => 5
        ];
        
        $points += $timelineScores[$timeline] ?? 0;
        
        // Investor deadline bonus
        if (!empty($investorDeadline)) {
            $points += 5; // Bonus for having specific deadline
        }
        
        $this->score += $points;
        $this->factors[] = [
            'name' => 'Timeline Urgency',
            'value' => ucfirst(str_replace('-', ' ', $timeline)) . ($investorDeadline ? " + Investor Deadline" : ""),
            'points' => $points,
            'max' => 20
        ];
    }
    
    /**
     * Score based on budget
     */
    private function scoreBudget($budget) {
        $points = 0;
        
        $budgetScores = [
            '800-2k' => 15,        // Essential app budget
            'custom-quote' => 20,  // Enterprise/custom budget
            'discuss' => 10        // Uncertain budget
        ];
        
        $points = $budgetScores[$budget] ?? 0;
        
        $this->score += $points;
        $this->factors[] = [
            'name' => 'Budget Qualification',
            'value' => ucfirst(str_replace('-', ' ', $budget)),
            'points' => $points,
            'max' => 20
        ];
    }
    
    /**
     * Score based on project clarity
     */
    private function scoreProjectClarity($projectDetails) {
        $points = 0;
        $wordCount = str_word_count($projectDetails);
        
        if ($wordCount >= 100) {
            $points = 15; // Detailed project description
        } elseif ($wordCount >= 50) {
            $points = 10; // Moderate detail
        } elseif ($wordCount >= 20) {
            $points = 5;  // Brief description
        }
        
        $this->score += $points;
        $this->factors[] = [
            'name' => 'Project Clarity',
            'value' => "$wordCount words provided",
            'points' => $points,
            'max' => 15
        ];
    }
    
    /**
     * Score based on contact quality
     */
    private function scoreContactQuality($email, $phone, $company) {
        $points = 0;
        
        // Email validation
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $points += 3;
        }
        
        // Phone provided
        if (!empty($phone) && strlen($phone) >= 10) {
            $points += 3;
        }
        
        // Company provided
        if (!empty($company) && strlen($company) >= 3) {
            $points += 4;
        }
        
        $this->score += $points;
        $this->factors[] = [
            'name' => 'Contact Quality',
            'value' => implode(', ', array_filter([
                'Email' => filter_var($email, FILTER_VALIDATE_EMAIL) ? '✓' : '',
                'Phone' => !empty($phone) ? '✓' : '',
                'Company' => !empty($company) ? '✓' : ''
            ])),
            'points' => $points,
            'max' => 10
        ];
    }
    
    /**
     * Score based on investor readiness indicators
     */
    private function scoreInvestorReadiness($investorDeadline, $fundingStage) {
        $points = 0;
        
        // Has investor deadline
        if (!empty($investorDeadline)) {
            $points += 5;
        }
        
        // Is in active funding stage
        if (in_array($fundingStage, ['pre-seed', 'seed', 'series-a', 'series-b'])) {
            $points += 5;
        }
        
        $this->score += $points;
        $this->factors[] = [
            'name' => 'Investor Readiness',
            'value' => ($points > 0 ? 'High' : 'Low'),
            'points' => $points,
            'max' => 10
        ];
    }
    
    /**
     * Get qualification level based on score
     */
    private function getQualificationLevel() {
        $percentage = ($this->score / $this->maxScore) * 100;
        
        if ($percentage >= 80) {
            return 'Highly Qualified';
        } elseif ($percentage >= 60) {
            return 'Qualified';
        } elseif ($percentage >= 40) {
            return 'Moderately Qualified';
        } else {
            return 'Needs Qualification';
        }
    }
    
    /**
     * Get recommendation based on score
     */
    private function getRecommendation() {
        $percentage = ($this->score / $this->maxScore) * 100;
        
        if ($percentage >= 80) {
            return 'Priority follow-up within 24 hours. High investment readiness indicators.';
        } elseif ($percentage >= 60) {
            return 'Follow-up within 48 hours. Good fit for startup packages.';
        } elseif ($percentage >= 40) {
            return 'Standard follow-up. May need qualification call to assess fit.';
        } else {
            return 'Requires qualification call. Determine specific needs and timeline.';
        }
    }
}

// Handle scoring request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $scorer = new LeadScorer();
        $result = $scorer->calculateScore($input);
        
        echo json_encode([
            'success' => true,
            'scoring' => $result
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method allowed'
    ]);
}
?>
