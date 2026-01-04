<?php
/**
 * Lead Analytics API
 * Provides analytics on lead quality, scoring, and qualification
 */

header('Content-Type: application/json');

try {
    $messagesFile = __DIR__ . '/../../data/messages.json';
    
    if (!file_exists($messagesFile)) {
        echo json_encode([
            'success' => true,
            'total_leads' => 0,
            'qualified_leads' => 0,
            'average_score' => 0,
            'leads_by_stage' => [],
            'leads_by_qualification' => [],
            'top_leads' => []
        ]);
        exit;
    }
    
    $messages = json_decode(file_get_contents($messagesFile), true);
    
    if (!is_array($messages)) {
        echo json_encode([
            'success' => true,
            'total_leads' => 0,
            'qualified_leads' => 0,
            'average_score' => 0,
            'leads_by_stage' => [],
            'leads_by_qualification' => [],
            'top_leads' => []
        ]);
        exit;
    }
    
    // Calculate analytics
    $totalLeads = count($messages);
    $qualifiedLeads = 0;
    $totalScore = 0;
    $leadsByStage = [];
    $leadsByQualification = [];
    $topLeads = [];
    
    foreach ($messages as $message) {
        // Count qualified leads
        if (isset($message['lead_score']['percentage']) && $message['lead_score']['percentage'] >= 60) {
            $qualifiedLeads++;
        }
        
        // Sum scores
        if (isset($message['lead_score']['total_score'])) {
            $totalScore += $message['lead_score']['total_score'];
        }
        
        // Group by funding stage
        $stage = $message['funding_stage'] ?? 'Not Specified';
        $leadsByStage[$stage] = ($leadsByStage[$stage] ?? 0) + 1;
        
        // Group by qualification level
        $qualification = $message['lead_score']['qualification_level'] ?? 'Needs Qualification';
        $leadsByQualification[$qualification] = ($leadsByQualification[$qualification] ?? 0) + 1;
        
        // Collect top leads
        if (isset($message['lead_score']['total_score'])) {
            $topLeads[] = [
                'id' => $message['id'],
                'name' => $message['name'],
                'company' => $message['company'] ?? 'N/A',
                'score' => $message['lead_score']['total_score'],
                'qualification' => $message['lead_score']['qualification_level'],
                'funding_stage' => $message['funding_stage'] ?? 'Not Specified',
                'created_at' => $message['created_at']
            ];
        }
    }
    
    // Sort top leads by score
    usort($topLeads, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    $topLeads = array_slice($topLeads, 0, 10);
    
    // Calculate average score
    $averageScore = $totalLeads > 0 ? round($totalScore / $totalLeads, 1) : 0;
    
    echo json_encode([
        'success' => true,
        'total_leads' => $totalLeads,
        'qualified_leads' => $qualifiedLeads,
        'qualification_rate' => $totalLeads > 0 ? round(($qualifiedLeads / $totalLeads) * 100, 1) : 0,
        'average_score' => $averageScore,
        'leads_by_stage' => $leadsByStage,
        'leads_by_qualification' => $leadsByQualification,
        'top_leads' => $topLeads
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
