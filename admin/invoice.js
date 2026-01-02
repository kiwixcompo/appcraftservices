// Invoice Management Functions
let invoiceCounter = 1;

function generateInvoiceNumber() {
    const date = new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const counter = String(invoiceCounter).padStart(4, '0');
    return `INV-${year}${month}-${counter}`;
}

function calculateAmountDue() {
    const totalAmount = parseFloat(document.getElementById('total-amount').value) || 0;
    const amountPaid = parseFloat(document.getElementById('amount-paid').value) || 0;
    const taxRate = parseFloat(document.getElementById('tax-rate').value) || 0;
    
    const subtotal = totalAmount;
    const taxAmount = (subtotal * taxRate) / 100;
    const totalWithTax = subtotal + taxAmount;
    const amountDue = totalWithTax - amountPaid;
    
    document.getElementById('amount-due').value = Math.max(0, amountDue).toFixed(2);
    updateInvoicePreview();
}

function updateInvoicePreview() {
    const preview = document.getElementById('invoice-preview');
    
    const invoiceNumber = document.getElementById('invoice-number').value;
    const invoiceDate = document.getElementById('invoice-date').value;
    const dueDate = document.getElementById('due-date').value;
    const clientName = document.getElementById('client-name').value;
    const clientEmail = document.getElementById('client-email').value;
    const projectName = document.getElementById('project-name').value;
    const projectType = document.getElementById('project-type').value;
    const totalAmount = parseFloat(document.getElementById('total-amount').value) || 0;
    const amountPaid = parseFloat(document.getElementById('amount-paid').value) || 0;
    const amountDue = parseFloat(document.getElementById('amount-due').value) || 0;
    const taxRate = parseFloat(document.getElementById('tax-rate').value) || 0;
    const currency = document.getElementById('currency').value;
    
    if (!clientName || !projectName || !totalAmount) {
        preview.innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-file-invoice text-4xl mb-4"></i>
                <p>Fill out the form to see preview</p>
            </div>
        `;
        return;
    }
    
    const taxAmount = (totalAmount * taxRate) / 100;
    const totalWithTax = totalAmount + taxAmount;
    
    preview.innerHTML = `
        <div class="border border-gray-300 p-4 bg-white text-xs">
            <div class="text-center mb-4">
                <h2 class="text-lg font-bold text-navy">App Craft Services</h2>
                <p class="text-xs text-gray-600">Professional Web Development</p>
            </div>
            
            <div class="grid grid-cols-2 gap-2 mb-4 text-xs">
                <div>
                    <h3 class="font-semibold mb-1">INVOICE</h3>
                    <p><strong>Number:</strong> ${invoiceNumber}</p>
                    <p><strong>Date:</strong> ${invoiceDate}</p>
                    <p><strong>Due:</strong> ${dueDate}</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-1">BILL TO</h3>
                    <p class="font-medium">${clientName}</p>
                    <p>${clientEmail}</p>
                </div>
            </div>
            
            <div class="mb-4">
                <h3 class="font-semibold text-xs mb-1">PROJECT</h3>
                <p class="text-xs">${projectName} (${projectType})</p>
            </div>
            
            <div class="border-t border-b py-2 mb-2 text-xs">
                <div class="grid grid-cols-3 gap-2">
                    <div><strong>Item</strong></div>
                    <div class="text-right"><strong>Amount</strong></div>
                    <div class="text-right"><strong>Total</strong></div>
                </div>
                <div class="grid grid-cols-3 gap-2 mt-1">
                    <div>${projectType}</div>
                    <div class="text-right">${currency} ${totalAmount.toFixed(2)}</div>
                    <div class="text-right">${currency} ${totalAmount.toFixed(2)}</div>
                </div>
            </div>
            
            <div class="text-right text-xs">
                <p><strong>Subtotal: ${currency} ${totalAmount.toFixed(2)}</strong></p>
                ${taxRate > 0 ? `<p><strong>Tax: ${currency} ${taxAmount.toFixed(2)}</strong></p>` : ''}
                <p class="text-sm"><strong>Total: ${currency} ${totalWithTax.toFixed(2)}</strong></p>
                <p class="text-green-600"><strong>Paid: ${currency} ${amountPaid.toFixed(2)}</strong></p>
                <p class="text-red-600 text-sm"><strong>Due: ${currency} ${amountDue.toFixed(2)}</strong></p>
            </div>
        </div>
    `;
}

function previewInvoice() {
    updateInvoicePreview();
    showNotification('Invoice preview updated!', 'success');
}

async function saveInvoice() {
    const invoiceData = {
        invoice_number: document.getElementById('invoice-number').value,
        invoice_date: document.getElementById('invoice-date').value,
        due_date: document.getElementById('due-date').value,
        status: document.getElementById('invoice-status').value,
        client_name: document.getElementById('client-name').value,
        client_email: document.getElementById('client-email').value,
        client_address: document.getElementById('client-address').value,
        project_name: document.getElementById('project-name').value,
        project_type: document.getElementById('project-type').value,
        project_description: document.getElementById('project-description').value,
        total_amount: parseFloat(document.getElementById('total-amount').value),
        amount_paid: parseFloat(document.getElementById('amount-paid').value),
        amount_due: parseFloat(document.getElementById('amount-due').value),
        tax_rate: parseFloat(document.getElementById('tax-rate').value),
        currency: document.getElementById('currency').value,
        notes: document.getElementById('invoice-notes').value
    };
    
    try {
        const response = await fetch('api/save_invoice.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(invoiceData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Invoice saved successfully!', 'success');
            loadInvoices();
            invoiceCounter++;
            resetInvoiceForm();
        } else {
            showNotification('Error saving invoice: ' + result.message, 'error');
        }
    } catch (error) {
        showNotification('Error saving invoice: ' + error.message, 'error');
    }
}

function resetInvoiceForm() {
    document.getElementById('invoice-form').reset();
    document.getElementById('invoice-number').value = generateInvoiceNumber();
    document.getElementById('invoice-date').value = new Date().toISOString().split('T')[0];
    
    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + 30);
    document.getElementById('due-date').value = dueDate.toISOString().split('T')[0];
    
    document.getElementById('amount-due').value = '0.00';
    updateInvoicePreview();
}

async function loadInvoices() {
    try {
        const response = await fetch('api/get_invoices.php');
        const invoices = await response.json();
        
        const totalInvoices = invoices.length;
        const paidInvoices = invoices.filter(inv => inv.status === 'paid').length;
        const pendingInvoices = invoices.filter(inv => inv.status === 'sent').length;
        const overdueInvoices = invoices.filter(inv => inv.status === 'overdue').length;
        
        document.getElementById('total-invoices').textContent = totalInvoices;
        document.getElementById('paid-invoices').textContent = paidInvoices;
        document.getElementById('pending-invoices').textContent = pendingInvoices;
        document.getElementById('overdue-invoices').textContent = overdueInvoices;
        
        const invoicesList = document.getElementById('invoices-list');
        
        if (invoices.length === 0) {
            invoicesList.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-file-invoice text-4xl mb-4"></i>
                    <p>No invoices created yet</p>
                </div>
            `;
            return;
        }
        
        let html = '<div class="overflow-x-auto"><table class="w-full"><thead><tr class="border-b"><th class="text-left p-3">Invoice #</th><th class="text-left p-3">Client</th><th class="text-left p-3">Project</th><th class="text-left p-3">Amount Due</th><th class="text-left p-3">Status</th><th class="text-left p-3">Due Date</th><th class="text-left p-3">Actions</th></tr></thead><tbody>';
        
        invoices.forEach(invoice => {
            const statusColor = 
                invoice.status === 'paid' ? 'text-green-600' :
                invoice.status === 'sent' ? 'text-blue-600' :
                invoice.status === 'overdue' ? 'text-red-600' : 'text-gray-600';
            
            html += `
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-medium">${invoice.invoice_number}</td>
                    <td class="p-3">${invoice.client_name}</td>
                    <td class="p-3">${invoice.project_name}</td>
                    <td class="p-3 font-semibold">${invoice.currency} ${parseFloat(invoice.amount_due).toFixed(2)}</td>
                    <td class="p-3">
                        <span class="${statusColor} capitalize font-medium">${invoice.status}</span>
                    </td>
                    <td class="p-3">${invoice.due_date}</td>
                    <td class="p-3">
                        <button onclick="generateInvoicePDF('${invoice.invoice_number}')" class="text-red-600 hover:text-red-800 mr-2" title="Export PDF">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                        <button onclick="deleteInvoice('${invoice.id}')" class="text-red-600 hover:text-red-800" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        invoicesList.innerHTML = html;
        
    } catch (error) {
        document.getElementById('invoices-list').innerHTML = '<p class="text-red-600">Error loading invoices</p>';
    }
}

function generateInvoicePDF() {
    const invoiceData = {
        invoice_number: document.getElementById('invoice-number').value,
        invoice_date: document.getElementById('invoice-date').value,
        due_date: document.getElementById('due-date').value,
        client_name: document.getElementById('client-name').value,
        client_email: document.getElementById('client-email').value,
        client_address: document.getElementById('client-address').value,
        project_name: document.getElementById('project-name').value,
        project_type: document.getElementById('project-type').value,
        project_description: document.getElementById('project-description').value,
        total_amount: parseFloat(document.getElementById('total-amount').value) || 0,
        amount_paid: parseFloat(document.getElementById('amount-paid').value) || 0,
        amount_due: parseFloat(document.getElementById('amount-due').value) || 0,
        tax_rate: parseFloat(document.getElementById('tax-rate').value) || 0,
        currency: document.getElementById('currency').value,
        notes: document.getElementById('invoice-notes').value
    };
    
    if (!invoiceData.client_name || !invoiceData.project_name) {
        showNotification('Please fill in required fields first', 'warning');
        return;
    }
    
    const taxAmount = (invoiceData.total_amount * invoiceData.tax_rate) / 100;
    const totalWithTax = invoiceData.total_amount + taxAmount;
    
    const pdfContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Invoice ${invoiceData.invoice_number}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
                .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #1e3a8a; padding-bottom: 20px; }
                .company-name { font-size: 28px; font-weight: bold; color: #1e3a8a; margin-bottom: 5px; }
                .company-tagline { font-size: 14px; color: #666; margin-bottom: 10px; }
                .company-contact { font-size: 12px; color: #666; }
                .invoice-details { display: flex; justify-content: space-between; margin-bottom: 40px; }
                .invoice-info, .client-info { width: 45%; }
                .invoice-info h2 { color: #1e3a8a; font-size: 24px; margin-bottom: 15px; }
                .client-info h3 { color: #1e3a8a; font-size: 16px; margin-bottom: 10px; }
                .project-section { margin-bottom: 30px; padding: 15px; background-color: #f8fafc; border-left: 4px solid #3b82f6; }
                .invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                .invoice-table th { background-color: #1e3a8a; color: white; padding: 12px; text-align: left; }
                .invoice-table td { border: 1px solid #ddd; padding: 12px; }
                .invoice-table tr:nth-child(even) { background-color: #f9f9f9; }
                .totals { text-align: right; margin-top: 20px; }
                .total-line { margin: 8px 0; font-size: 14px; }
                .grand-total { font-size: 18px; font-weight: bold; color: #1e3a8a; border-top: 2px solid #1e3a8a; padding-top: 10px; }
                .amount-due { font-size: 20px; font-weight: bold; color: #dc2626; margin-top: 10px; }
                .notes { margin-top: 40px; padding: 20px; background-color: #f8fafc; border-radius: 8px; }
                .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
                @media print { 
                    body { margin: 0; } 
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="company-name">App Craft Services</div>
                <div class="company-tagline">Professional Web Development Services</div>
                <div class="company-contact">
                    Email: williamsaonen@gmail.com | Phone: +2348061581916
                </div>
            </div>
            
            <div class="invoice-details">
                <div class="invoice-info">
                    <h2>INVOICE</h2>
                    <p><strong>Invoice Number:</strong> ${invoiceData.invoice_number}</p>
                    <p><strong>Invoice Date:</strong> ${invoiceData.invoice_date}</p>
                    <p><strong>Due Date:</strong> ${invoiceData.due_date}</p>
                </div>
                <div class="client-info">
                    <h3>BILL TO</h3>
                    <p><strong>${invoiceData.client_name}</strong></p>
                    <p>${invoiceData.client_email}</p>
                    ${invoiceData.client_address ? `<p>${invoiceData.client_address.replace(/\n/g, '<br>')}</p>` : ''}
                </div>
            </div>
            
            <div class="project-section">
                <h3 style="color: #1e3a8a; margin-bottom: 10px;">PROJECT DETAILS</h3>
                <p><strong>Project Name:</strong> ${invoiceData.project_name}</p>
                <p><strong>Project Type:</strong> ${invoiceData.project_type}</p>
                ${invoiceData.project_description ? `<p><strong>Description:</strong> ${invoiceData.project_description}</p>` : ''}
            </div>
            
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: right;">Amount</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>${invoiceData.project_type} Development</td>
                        <td style="text-align: right;">${invoiceData.currency} ${invoiceData.total_amount.toFixed(2)}</td>
                        <td style="text-align: right;">${invoiceData.currency} ${invoiceData.total_amount.toFixed(2)}</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="totals">
                <div class="total-line"><strong>Subtotal: ${invoiceData.currency} ${invoiceData.total_amount.toFixed(2)}</strong></div>
                ${invoiceData.tax_rate > 0 ? `<div class="total-line"><strong>Tax (${invoiceData.tax_rate}%): ${invoiceData.currency} ${taxAmount.toFixed(2)}</strong></div>` : ''}
                <div class="grand-total">Total: ${invoiceData.currency} ${totalWithTax.toFixed(2)}</div>
                <div class="total-line" style="color: #059669;"><strong>Amount Paid: ${invoiceData.currency} ${invoiceData.amount_paid.toFixed(2)}</strong></div>
                <div class="amount-due">Amount Due: ${invoiceData.currency} ${invoiceData.amount_due.toFixed(2)}</div>
            </div>
            
            ${invoiceData.notes ? `
            <div class="notes">
                <h4 style="color: #1e3a8a; margin-bottom: 10px;">Additional Notes</h4>
                <p>${invoiceData.notes.replace(/\n/g, '<br>')}</p>
            </div>
            ` : ''}
            
            <div class="footer">
                <p>Thank you for choosing App Craft Services!</p>
                <p>Payment terms: Net 30 days. Late payments may incur additional charges.</p>
            </div>
            
            <script>
                window.onload = function() {
                    window.print();
                    window.onafterprint = function() {
                        window.close();
                    }
                }
            </script>
        </body>
        </html>
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(pdfContent);
    printWindow.document.close();
    
    showNotification('Professional invoice PDF generated and ready for printing/saving', 'success');
}

function refreshInvoices() {
    loadInvoices();
    showNotification('Invoices refreshed', 'success');
}

function deleteInvoice(invoiceId) {
    if (confirm('Are you sure you want to delete this invoice?')) {
        showNotification('Delete functionality would be implemented here', 'info');
    }
}

// Initialize invoice functionality
document.addEventListener('DOMContentLoaded', function() {
    // Set up invoice form if it exists
    const invoiceForm = document.getElementById('invoice-form');
    if (invoiceForm) {
        // Initialize form
        resetInvoiceForm();
        
        // Add event listeners for automatic calculations
        document.getElementById('total-amount').addEventListener('input', calculateAmountDue);
        document.getElementById('amount-paid').addEventListener('input', calc