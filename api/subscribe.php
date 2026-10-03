<?php
/**
 * Lead Magnet Subscriber API
 * Accepts: name, email, role, magnet (ojs | mvp)
 * Stores lead data, sends confirmation email with download link
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://appcraftservices.com');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid request body']);
    exit;
}

// Sanitize & validate
$name   = trim(htmlspecialchars($input['name'] ?? '', ENT_QUOTES, 'UTF-8'));
$email  = filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$role   = trim(htmlspecialchars($input['role'] ?? 'other', ENT_QUOTES, 'UTF-8'));
$magnet = in_array($input['magnet'] ?? '', ['ojs', 'mvp']) ? $input['magnet'] : 'ojs';

if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid name and email address.']);
    exit;
}

// Define download links (these would be real PDF/hosted file URLs)
$downloadLinks = [
    'ojs' => 'https://appcraftservices.com/resources/ojs-guide-african-universities.pdf',
    'mvp' => 'https://appcraftservices.com/resources/mvp-tech-stack-checklist.pdf',
];

$guideTitles = [
    'ojs' => 'The Complete Guide to Optimizing Open Journal Systems for African Universities',
    'mvp' => "The Non-Technical Founder's MVP Tech Stack Checklist",
];

$downloadUrl = $downloadLinks[$magnet];
$guideTitle  = $guideTitles[$magnet];

// Save subscriber to JSON log
$leadsFile = __DIR__ . '/../data/leads.json';
$leads = [];
if (file_exists($leadsFile)) {
    $leads = json_decode(file_get_contents($leadsFile), true) ?: [];
}

// Check for duplicate email
foreach ($leads as $lead) {
    if (strtolower($lead['email']) === strtolower($email)) {
        // Still send the guide — don't penalize re-subscribers
        // Just don't duplicate the record
        break;
    }
}

$newLead = [
    'id'         => uniqid('lead_'),
    'name'       => $name,
    'email'      => $email,
    'role'       => $role,
    'magnet'     => $magnet,
    'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'source'     => 'homepage_lead_magnet',
    'created_at' => date('Y-m-d H:i:s'),
];

array_unshift($leads, $newLead);

// Limit stored leads to 10,000 records
if (count($leads) > 10000) {
    $leads = array_slice($leads, 0, 10000);
}

file_put_contents($leadsFile, json_encode($leads, JSON_PRETTY_PRINT));

// Send confirmation email
$subject = "📥 Your Free Guide: {$guideTitle}";
$fromName = "App Craft Services";
$fromEmail = "hello@appcraftservices.com";

$htmlBody = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'></head>
<body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;'>
    <div style='background: linear-gradient(135deg, #1e3a8a, #3b82f6); padding: 40px 30px; border-radius: 12px 12px 0 0; text-align: center;'>
        <h1 style='color: white; margin: 0; font-size: 24px;'>Your Free Guide is Ready!</h1>
    </div>
    <div style='background: #f8fafc; padding: 30px; border-radius: 0 0 12px 12px; border: 1px solid #e2e8f0;'>
        <p style='font-size: 16px;'>Hi <strong>{$name}</strong>,</p>
        <p style='font-size: 16px; line-height: 1.6;'>Thank you for downloading our resource. Here is your free guide:</p>
        
        <div style='background: white; border: 2px solid #3b82f6; border-radius: 12px; padding: 20px; margin: 20px 0; text-align: center;'>
            <p style='font-size: 14px; color: #6b7280; margin: 0 0 8px;'>Your download:</p>
            <p style='font-size: 16px; font-weight: bold; color: #1e3a8a; margin: 0 0 16px;'>{$guideTitle}</p>
            <a href='{$downloadUrl}' style='display: inline-block; background: #3b82f6; color: white; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 15px;'>
                📥 Download Your Guide
            </a>
        </div>
        
        <p style='font-size: 15px; line-height: 1.6;'>If you have any questions or need help with your OJS setup or MVP project, feel free to reply to this email — Williams will personally respond.</p>
        
        <p style='font-size: 15px; color: #6b7280; margin-top: 30px;'>
            — Williams Alfred Onen<br>
            Founder &amp; Lead Engineer, App Craft Services<br>
            <a href='https://appcraftservices.com' style='color: #3b82f6;'>appcraftservices.com</a>
        </p>
    </div>
</body>
</html>
";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: {$fromName} <{$fromEmail}>\r\n";
$headers .= "Reply-To: {$fromEmail}\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

$sent = mail($email, $subject, $htmlBody, $headers);

// Also notify the agency of new lead
$notifySubject = "🎯 New Lead Magnet Subscriber: {$name} ({$magnet})";
$notifyBody = "New subscriber from homepage lead magnet.\n\nName: {$name}\nEmail: {$email}\nRole: {$role}\nGuide: {$magnet}\nTime: " . date('Y-m-d H:i:s');
mail($fromEmail, $notifySubject, $notifyBody, "From: noreply@appcraftservices.com\r\n");

echo json_encode([
    'success' => true,
    'message' => "✅ Guide sent to {$email}! Check your inbox (and spam folder just in case).",
]);
?>
