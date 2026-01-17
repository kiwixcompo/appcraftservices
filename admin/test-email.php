<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$testResult = '';
$testEmail = '';

if ($_POST && isset($_POST['test_email'])) {
    $testEmail = filter_var($_POST['test_email'], FILTER_VALIDATE_EMAIL);
    
    if ($testEmail) {
        // Create test payment link
        $testLink = 'https://appcraftservices.com/payment/pay.php?token=test123&amount=500&description=Test%20Service&email=' . urlencode($testEmail);
        
        // Prepare test data
        $testData = [
            'email' => $testEmail,
            'paymentLink' => $testLink,
            'amount' => '500',
            'description' => 'Test Service',
            'stage' => 'initial',
            'totalAmount' => '1000'
        ];
        
        // Send test email
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/admin/api/send_payment_email.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Cookie: ' . session_name() . '=' . session_id()
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['success']) && $result['success']) {
            $testResult = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <strong>Success!</strong> Test email sent to ' . htmlspecialchars($testEmail) . '
                <br><small>Check your inbox (and spam folder) for the test email.</small>
            </div>';
        } else {
            $testResult = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <strong>Error!</strong> Failed to send test email.
                <br><small>' . htmlspecialchars($result['message'] ?? 'Unknown error') . '</small>
            </div>';
        }
    } else {
        $testResult = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <strong>Error!</strong> Invalid email address.
        </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Deliverability Test - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Email Deliverability Test</h1>
                    <a href="index.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>

            <?php if ($testResult): ?>
                <?php echo $testResult; ?>
            <?php endif; ?>

            <!-- Test Email Form -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Send Test Email</h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Test Email Address
                        </label>
                        <input type="email" name="test_email" required 
                               value="<?php echo htmlspecialchars($testEmail); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="your.email@example.com">
                        <p class="text-sm text-gray-500 mt-1">
                            Enter your email address to receive a test payment email
                        </p>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium">
                        <i class="fas fa-paper-plane mr-2"></i>Send Test Email
                    </button>
                </form>
            </div>

            <!-- Email Configuration Status -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Email Configuration Status</h2>
                
                <div class="space-y-4">
                    <!-- SPF Check -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shield-alt text-2xl text-yellow-500"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-medium text-gray-900">SPF Record</h3>
                            <p class="text-sm text-gray-600">
                                Sender Policy Framework - Prevents email spoofing
                            </p>
                            <p class="text-sm text-yellow-600 mt-1">
                                <strong>Action Required:</strong> Add SPF record to your DNS
                            </p>
                        </div>
                    </div>

                    <!-- DKIM Check -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-key text-2xl text-yellow-500"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-medium text-gray-900">DKIM Signature</h3>
                            <p class="text-sm text-gray-600">
                                DomainKeys Identified Mail - Authenticates your emails
                            </p>
                            <p class="text-sm text-yellow-600 mt-1">
                                <strong>Action Required:</strong> Configure DKIM on your server
                            </p>
                        </div>
                    </div>

                    <!-- DMARC Check -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-2xl text-yellow-500"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-medium text-gray-900">DMARC Policy</h3>
                            <p class="text-sm text-gray-600">
                                Domain-based Message Authentication - Protects your domain
                            </p>
                            <p class="text-sm text-yellow-600 mt-1">
                                <strong>Action Required:</strong> Add DMARC record to your DNS
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Important:</strong> To ensure emails reach the inbox, you must configure SPF, DKIM, and DMARC records. 
                        See <code>EMAIL-AUTHENTICATION-SETUP.md</code> for detailed instructions.
                    </p>
                </div>
            </div>

            <!-- Testing Tools -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Email Testing Tools</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="https://www.mail-tester.com" target="_blank" 
                       class="block p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-md transition">
                        <h3 class="font-medium text-gray-900 mb-2">
                            <i class="fas fa-envelope-open-text mr-2 text-blue-600"></i>Mail Tester
                        </h3>
                        <p class="text-sm text-gray-600">
                            Test your email spam score (aim for 10/10)
                        </p>
                    </a>

                    <a href="https://mxtoolbox.com/SuperTool.aspx" target="_blank" 
                       class="block p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-md transition">
                        <h3 class="font-medium text-gray-900 mb-2">
                            <i class="fas fa-tools mr-2 text-blue-600"></i>MXToolbox
                        </h3>
                        <p class="text-sm text-gray-600">
                            Check your domain's email configuration
                        </p>
                    </a>

                    <a href="https://www.senderscore.org" target="_blank" 
                       class="block p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-md transition">
                        <h3 class="font-medium text-gray-900 mb-2">
                            <i class="fas fa-chart-line mr-2 text-blue-600"></i>Sender Score
                        </h3>
                        <p class="text-sm text-gray-600">
                            Check your IP reputation score
                        </p>
                    </a>

                    <a href="https://postmaster.google.com" target="_blank" 
                       class="block p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-md transition">
                        <h3 class="font-medium text-gray-900 mb-2">
                            <i class="fab fa-google mr-2 text-blue-600"></i>Google Postmaster
                        </h3>
                        <p class="text-sm text-gray-600">
                            Monitor Gmail delivery and reputation
                        </p>
                    </a>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Quick Tips to Avoid Spam</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start">
                        <i class="fas fa-check text-green-600 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-gray-900">Use Professional Subject Lines</h4>
                            <p class="text-sm text-gray-600">Avoid ALL CAPS and excessive punctuation!!!</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <i class="fas fa-check text-green-600 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-gray-900">Include Plain Text Version</h4>
                            <p class="text-sm text-gray-600">Always provide a text alternative to HTML</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <i class="fas fa-check text-green-600 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-gray-900">Authenticate Your Domain</h4>
                            <p class="text-sm text-gray-600">Configure SPF, DKIM, and DMARC records</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <i class="fas fa-check text-green-600 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-gray-900">Monitor Bounce Rates</h4>
                            <p class="text-sm text-gray-600">Keep bounce rate below 5% for good reputation</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <i class="fas fa-check text-green-600 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-gray-900">Use Consistent Sender Info</h4>
                            <p class="text-sm text-gray-600">Always send from the same verified address</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <i class="fas fa-check text-green-600 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-gray-900">Include Unsubscribe Link</h4>
                            <p class="text-sm text-gray-600">Required by law and improves deliverability</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
