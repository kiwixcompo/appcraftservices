// Payment Processing JavaScript

let stripe;
let elements;
let cardElement;
let currentAmount = 0;

// Initialize payment systems
document.addEventListener('DOMContentLoaded', async function() {
    await initializeStripe();
    initializePayPal();
});

// Initialize Stripe
async function initializeStripe() {
    try {
        // In production, get this from your server
        const publishableKey = 'pk_test_your_publishable_key_here'; // Replace with actual key
        stripe = Stripe(publishableKey);
        
        elements = stripe.elements();
        
        // Create card element
        cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#424770',
                    '::placeholder': {
                        color: '#aab7c4',
                    },
                },
            },
        });
        
        cardElement.mount('#stripe-card-element');
        
        // Handle real-time validation errors from the card Element
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                showError(event.error.message);
            }
        });
        
        // Handle form submission
        document.getElementById('stripe-submit').addEventListener('click', handleStripePayment);
        
    } catch (error) {
        console.error('Error initializing Stripe:', error);
        showError('Failed to initialize Stripe payment system');
    }
}

// Initialize PayPal
function initializePayPal() {
    if (typeof paypal === 'undefined') {
        console.error('PayPal SDK not loaded');
        return;
    }
    
    paypal.Buttons({
        createOrder: function(data, actions) {
            if (currentAmount <= 0) {
                showError('Please select a service or enter an amount');
                return;
            }
            
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: currentAmount.toFixed(2)
                    },
                    description: document.getElementById('payment-description').value || 'App Craft Services Payment'
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                handlePaymentSuccess({
                    payment_method: 'paypal',
                    transaction_id: details.id,
                    amount: currentAmount,
                    customer_name: document.getElementById('customer-name').value,
                    customer_email: document.getElementById('customer-email').value
                });
            });
        },
        onError: function(err) {
            console.error('PayPal error:', err);
            showError('PayPal payment failed. Please try again.');
        }
    }).render('#paypal-button-container');
}

// Handle Stripe payment
async function handleStripePayment(event) {
    event.preventDefault();
    
    if (currentAmount <= 0) {
        showError('Please select a service or enter an amount');
        return;
    }
    
    const customerName = document.getElementById('customer-name').value;
    const customerEmail = document.getElementById('customer-email').value;
    
    if (!customerName || !customerEmail) {
        showError('Please fill in your name and email');
        return;
    }
    
    setLoading(true);
    
    try {
        // Create payment intent on server
        const response = await fetch('/appcraftservices/api/payments/create_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                amount: currentAmount,
                currency: 'usd',
                payment_method: 'stripe',
                customer_name: customerName,
                customer_email: customerEmail,
                description: document.getElementById('payment-description').value || 'App Craft Services Payment'
            })
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message);
        }
        
        // Confirm payment with Stripe
        const {error} = await stripe.confirmCardPayment(result.client_secret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: customerName,
                    email: customerEmail,
                }
            }
        });
        
        if (error) {
            throw new Error(error.message);
        } else {
            handlePaymentSuccess({
                payment_method: 'stripe',
                amount: currentAmount,
                customer_name: customerName,
                customer_email: customerEmail
            });
        }
        
    } catch (error) {
        console.error('Stripe payment error:', error);
        showError('Payment failed: ' + error.message);
    } finally {
        setLoading(false);
    }
}

// Handle successful payment
function handlePaymentSuccess(paymentData) {
    // Redirect to success page or show success message
    window.location.href = '/appcraftservices/payment/success.html?payment_method=' + paymentData.payment_method + '&amount=' + paymentData.amount;
}

// Update amount based on selection
function updateAmount() {
    const packageSelect = document.getElementById('service-package');
    const customAmountInput = document.getElementById('custom-amount');
    const summaryService = document.getElementById('summary-service');
    const summaryAmount = document.getElementById('summary-amount');
    const summaryTotal = document.getElementById('summary-total');
    const stripeButton = document.getElementById('stripe-submit');
    
    let amount = 0;
    let serviceName = 'Not selected';
    
    if (packageSelect.value) {
        const selectedOption = packageSelect.options[packageSelect.selectedIndex];
        amount = parseFloat(selectedOption.dataset.amount);
        serviceName = selectedOption.text;
        customAmountInput.value = '';
    } else if (customAmountInput.value) {
        amount = parseFloat(customAmountInput.value);
        serviceName = 'Custom Service';
    }
    
    currentAmount = amount;
    
    summaryService.textContent = serviceName;
    summaryAmount.textContent = '$' + amount.toFixed(2);
    summaryTotal.textContent = '$' + amount.toFixed(2);
    
    // Enable/disable payment buttons
    stripeButton.disabled = amount <= 0;
    
    // Update PayPal button (it will use the currentAmount variable)
    // PayPal buttons are recreated automatically when needed
}

// Utility functions
function setLoading(isLoading) {
    const button = document.getElementById('stripe-submit');
    const buttonText = document.getElementById('stripe-button-text');
    const spinner = document.getElementById('stripe-spinner');
    
    if (isLoading) {
        button.disabled = true;
        buttonText.textContent = 'Processing...';
        spinner.classList.remove('hidden');
    } else {
        button.disabled = currentAmount <= 0;
        buttonText.textContent = 'Pay with Stripe';
        spinner.classList.add('hidden');
    }
}

function showError(message) {
    // Create or update error message
    let errorDiv = document.getElementById('payment-error');
    
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'payment-error';
        errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white p-4 rounded-lg shadow-lg max-w-sm z-50';
        document.body.appendChild(errorDiv);
    }
    
    errorDiv.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 5000);
}

function showSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg max-w-sm z-50';
    successDiv.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(successDiv);
    
    setTimeout(() => {
        if (successDiv.parentElement) {
            successDiv.remove();
        }
    }, 5000);
}