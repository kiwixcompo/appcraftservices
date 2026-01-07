<?php
// Get payment link parameters
$token = $_GET['token'] ?? '';
$amount = $_GET['amount'] ?? '';
$description = $_GET['description'] ?? '';
$email = $_GET['email'] ?? '';

// Validate token (in a real app, you'd validate this against a database)
if (!$token || !$amount || !$description) {
    header('Location: ../');
    exit;
}

// Decode amount and description
$amount = urldecode($amount);
$description = urldecode($description);
$email = urldecode($email);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - App Craft Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://www.paypal.com/sdk/js?client-id=YOUR_PAYPAL_CLIENT_ID&currency=USD"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Complete Your Payment</h1>
                <p class="text-gray-600">App Craft Services</p>
            </div>

            <!-- Payment Details Card -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Payment Details</h2>
                <div class="border-b pb-4 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">Service:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($description); ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">Amount:</span>
                        <span class="text-2xl font-bold text-green-600"><?php echo htmlspecialchars($amount); ?></span>
                    </div>
                    <?php if ($email): ?>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Email:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($email); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-6">Choose Payment Method</h2>
                
                <!-- Payment Method Tabs -->
                <div class="flex border-b mb-6">
                    <button onclick="showPaymentMethod('stripe')" id="stripe-tab" class="px-6 py-3 border-b-2 border-blue-600 text-blue-600 font-medium">
                        <i class="fab fa-stripe mr-2"></i>Credit Card
                    </button>
                    <button onclick="showPaymentMethod('paypal')" id="paypal-tab" class="px-6 py-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium">
                        <i class="fab fa-paypal mr-2"></i>PayPal
                    </button>
                    <button onclick="showPaymentMethod('bank')" id="bank-tab" class="px-6 py-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium">
                        <i class="fas fa-university mr-2"></i>Bank Transfer
                    </button>
                </div>

                <!-- Stripe Payment Form -->
                <div id="stripe-payment" class="payment-method">
                    <div class="mb-4">
                        <p class="text-gray-600 mb-4">Pay securely with your credit or debit card</p>
                        <div id="stripe-card-element" class="p-3 border border-gray-300 rounded-md">
                            <!-- Stripe Elements will create form elements here -->
                        </div>
                        <div id="stripe-card-errors" class="text-red-600 text-sm mt-2"></div>
                    </div>
                    <button id="stripe-submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 font-medium">
                        <i class="fas fa-lock mr-2"></i>Pay <?php echo htmlspecialchars($amount); ?> Securely
                    </button>
                </div>

                <!-- PayPal Payment -->
                <div id="paypal-payment" class="payment-method hidden">
                    <div class="mb-4">
                        <p class="text-gray-600 mb-4">Pay with your PayPal account</p>
                        <div id="paypal-button-container"></div>
                    </div>
                </div>

                <!-- Bank Transfer -->
                <div id="bank-payment" class="payment-method hidden">
                    <div class="mb-4">
                        <p class="text-gray-600 mb-4">Transfer funds directly to our bank account</p>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-900 mb-3">Bank Transfer Details</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Account Name:</span>
                                    <span class="font-medium">Williams Alfred Onen</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Account Number:</span>
                                    <span class="font-medium">214720533676</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">ACH Routing:</span>
                                    <span class="font-medium">101019644</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Account Type:</span>
                                    <span class="font-medium">Individual Checking</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Bank Name:</span>
                                    <span class="font-medium">Lead Bank</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Bank Address:</span>
                                    <span class="font-medium">1801 Main St., Kansas City, MO 64108</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex">
                                <i class="fas fa-info-circle text-yellow-600 mt-1 mr-3"></i>
                                <div>
                                    <h4 class="font-medium text-yellow-800">Important Instructions</h4>
                                    <p class="text-yellow-700 text-sm mt-1">
                                        Please include your email address (<?php echo htmlspecialchars($email); ?>) in the transfer reference/memo field. 
                                        Once the transfer is complete, please email us at talk2char@gmail.com with the transaction details.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <button onclick="confirmBankTransfer()" class="w-full mt-4 bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700 font-medium">
                            <i class="fas fa-check mr-2"></i>I've Completed the Bank Transfer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="text-center mt-6 text-sm text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>
                Your payment information is secure and encrypted
            </div>
        </div>
    </div>

    <script>
        // Payment method switching
        function showPaymentMethod(method) {
            // Hide all payment methods
            document.querySelectorAll('.payment-method').forEach(el => el.classList.add('hidden'));
            
            // Remove active state from all tabs
            document.querySelectorAll('[id$="-tab"]').forEach(tab => {
                tab.classList.remove('border-blue-600', 'text-blue-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected payment method
            document.getElementById(method + '-payment').classList.remove('hidden');
            
            // Activate selected tab
            const activeTab = document.getElementById(method + '-tab');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-blue-600', 'text-blue-600');
        }

        // Stripe Integration
        const stripe = Stripe('pk_test_YOUR_STRIPE_PUBLISHABLE_KEY'); // Replace with your actual key
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#stripe-card-element');

        cardElement.on('change', ({error}) => {
            const displayError = document.getElementById('stripe-card-errors');
            if (error) {
                displayError.textContent = error.message;
            } else {
                displayError.textContent = '';
            }
        });

        document.getElementById('stripe-submit').addEventListener('click', async (event) => {
            event.preventDefault();
            
            const {token, error} = await stripe.createToken(cardElement);
            
            if (error) {
                document.getElementById('stripe-card-errors').textContent = error.message;
            } else {
                // Submit token to your server
                processStripePayment(token);
            }
        });

        function processStripePayment(token) {
            // Here you would send the token to your server to process the payment
            alert('Stripe payment processing... (Demo mode)');
            window.location.href = 'success.html?method=stripe&amount=<?php echo urlencode($amount); ?>';
        }

        // PayPal Integration
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo preg_replace('/[^0-9.]/', '', $amount); ?>' // Extract numeric value
                        },
                        payee: {
                            email_address: 'talk2char@gmail.com'
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    window.location.href = 'success.html?method=paypal&amount=<?php echo urlencode($amount); ?>&transaction=' + details.id;
                });
            },
            onError: function(err) {
                console.error('PayPal error:', err);
                alert('PayPal payment failed. Please try again.');
            }
        }).render('#paypal-button-container');

        // Bank Transfer Confirmation
        function confirmBankTransfer() {
            if (confirm('Have you completed the bank transfer and included your email in the reference field?')) {
                // Send notification email
                fetch('../api/notify_bank_transfer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: '<?php echo htmlspecialchars($email); ?>',
                        amount: '<?php echo htmlspecialchars($amount); ?>',
                        description: '<?php echo htmlspecialchars($description); ?>'
                    })
                });
                
                window.location.href = 'success.html?method=bank&amount=<?php echo urlencode($amount); ?>';
            }
        }

        // Initialize with Stripe as default
        showPaymentMethod('stripe');
    </script>
</body>
</html>