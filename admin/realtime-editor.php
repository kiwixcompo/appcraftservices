<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get the page to edit (default to homepage)
$page = $_GET['page'] ?? 'home';
$pageUrls = [
    'home' => '../index.html',
    'services' => '../services/index.html',
    'pricing' => '../pricing/index.html',
    'contact' => '../contact/index.html',
    'process' => '../process/index.html',
    'startup-packages' => '../startup-packages/index.html'
];

$pageUrl = $pageUrls[$page] ?? '../index.html';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realtime Editor - App Craft Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Editor Styles */
        .editor-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .editor-content {
            margin-top: 70px;
        }
        
        .editable-element {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .editable-element:hover {
            outline: 2px dashed #3b82f6;
            outline-offset: 2px;
            cursor: pointer;
        }
        
        .editable-element.editing {
            outline: 2px solid #10b981;
            outline-offset: 2px;
            background: rgba(16, 185, 129, 0.05);
        }
        
        .edit-overlay {
            position: absolute;
            top: -35px;
            left: 0;
            background: #10b981;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            z-index: 1000;
            opacity: 0;
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        
        .editable-element:hover .edit-overlay {
            opacity: 1;
            transform: translateY(0);
        }
        
        .edit-panel {
            position: fixed;
            top: 70px;
            right: -400px;
            width: 400px;
            height: calc(100vh - 70px);
            background: white;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            z-index: 9998;
            overflow-y: auto;
        }
        
        .edit-panel.active {
            right: 0;
        }
        
        .content-with-panel {
            margin-right: 400px;
        }
        
        .inline-editor {
            background: white;
            border: 2px solid #10b981;
            border-radius: 4px;
            padding: 10px;
            min-height: 100px;
            font-family: inherit;
            font-size: inherit;
            line-height: inherit;
            color: inherit;
            resize: vertical;
            width: 100%;
            box-sizing: border-box;
        }
        
        .save-indicator {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #10b981;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            font-weight: 500;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .save-indicator.show {
            opacity: 1;
        }
        
        .element-path {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 10px;
            font-family: monospace;
        }
        
        .editor-mode-badge {
            background: linear-gradient(45deg, #10b981, #059669);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .quick-action-btn {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            color: #374151;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .quick-action-btn:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }
        
        .quick-action-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Editor Toolbar -->
    <div class="editor-toolbar">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-magic text-white"></i>
                    <span class="text-white font-semibold">Realtime Editor</span>
                    <span class="editor-mode-badge">LIVE EDITING</span>
                </div>
                <div class="text-white text-sm opacity-75">
                    Editing: <span id="current-page-name"><?php echo ucfirst($page); ?></span>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <!-- Page Selector -->
                <select id="page-selector" class="bg-white bg-opacity-20 text-white border border-white border-opacity-30 rounded px-3 py-2 text-sm">
                    <option value="home" <?php echo $page === 'home' ? 'selected' : ''; ?>>Homepage</option>
                    <option value="services" <?php echo $page === 'services' ? 'selected' : ''; ?>>Services</option>
                    <option value="pricing" <?php echo $page === 'pricing' ? 'selected' : ''; ?>>Pricing</option>
                    <option value="startup-packages" <?php echo $page === 'startup-packages' ? 'selected' : ''; ?>>Startup Packages</option>
                    <option value="contact" <?php echo $page === 'contact' ? 'selected' : ''; ?>>Contact</option>
                    <option value="process" <?php echo $page === 'process' ? 'selected' : ''; ?>>Process</option>
                </select>
                
                <!-- Actions -->
                <button id="toggle-edit-mode" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm font-medium">
                    <i class="fas fa-edit mr-2"></i>Edit Mode: ON
                </button>
                
                <button id="save-all-changes" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm font-medium">
                    <i class="fas fa-save mr-2"></i>Save All
                </button>
                
                <button id="preview-changes" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium">
                    <i class="fas fa-eye mr-2"></i>Preview
                </button>
                
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Admin
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Panel -->
    <div id="edit-panel" class="edit-panel">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Edit Element</h3>
                <button id="close-panel" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="edit-content">
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-mouse-pointer text-3xl mb-3"></i>
                    <p>Click on any element to start editing</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Indicator -->
    <div id="save-indicator" class="save-indicator">
        <i class="fas fa-check mr-2"></i>
        <span>Changes saved successfully!</span>
    </div>

    <!-- Main Content -->
    <div id="editor-content" class="editor-content">
        <iframe id="website-frame" src="<?php echo $pageUrl; ?>?editor=1" style="width: 100%; height: calc(100vh - 70px); border: none;"></iframe>
    </div>

    <script>
        class RealtimeEditor {
            constructor() {
                this.editMode = true;
                this.currentElement = null;
                this.changes = {};
                this.iframe = document.getElementById('website-frame');
                this.editPanel = document.getElementById('edit-panel');
                this.saveIndicator = document.getElementById('save-indicator');
                
                this.init();
            }
            
            init() {
                this.setupEventListeners();
                this.waitForIframeLoad();
            }
            
            waitForIframeLoad() {
                this.iframe.addEventListener('load', () => {
                    setTimeout(() => {
                        this.setupIframeEditor();
                    }, 500);
                });
            }
            
            setupEventListeners() {
                // Toggle edit mode
                document.getElementById('toggle-edit-mode').addEventListener('click', () => {
                    this.toggleEditMode();
                });
                
                // Save all changes
                document.getElementById('save-all-changes').addEventListener('click', () => {
                    this.saveAllChanges();
                });
                
                // Preview changes
                document.getElementById('preview-changes').addEventListener('click', () => {
                    this.previewChanges();
                });
                
                // Close panel
                document.getElementById('close-panel').addEventListener('click', () => {
                    this.closeEditPanel();
                });
                
                // Page selector
                document.getElementById('page-selector').addEventListener('change', (e) => {
                    this.switchPage(e.target.value);
                });
            }
            
            setupIframeEditor() {
                try {
                    const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
                    
                    // Add editor styles to iframe
                    const editorStyles = document.createElement('style');
                    editorStyles.textContent = `
                        .editable-element {
                            position: relative;
                            transition: all 0.3s ease;
                        }
                        
                        .editable-element:hover {
                            outline: 2px dashed #3b82f6 !important;
                            outline-offset: 2px;
                            cursor: pointer;
                        }
                        
                        .editable-element.editing {
                            outline: 2px solid #10b981 !important;
                            outline-offset: 2px;
                            background: rgba(16, 185, 129, 0.05) !important;
                        }
                        
                        .edit-overlay {
                            position: absolute;
                            top: -35px;
                            left: 0;
                            background: #10b981;
                            color: white;
                            padding: 5px 10px;
                            border-radius: 4px;
                            font-size: 12px;
                            font-weight: 500;
                            z-index: 1000;
                            opacity: 0;
                            transform: translateY(-5px);
                            transition: all 0.3s ease;
                            pointer-events: none;
                        }
                        
                        .editable-element:hover .edit-overlay {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    `;
                    iframeDoc.head.appendChild(editorStyles);
                    
                    // Make elements editable
                    this.makeElementsEditable(iframeDoc);
                    
                } catch (error) {
                    console.error('Error setting up iframe editor:', error);
                }
            }
            
            makeElementsEditable(doc) {
                // Define editable selectors
                const editableSelectors = [
                    'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                    'p', 'span', 'div.text-center', 'div.text-left', 'div.text-right',
                    '.hero h1', '.hero p', '.hero .text-xl',
                    '.value-prop-title', '.value-prop-desc',
                    'button', 'a.btn', '.cta-text',
                    '.service-title', '.service-description',
                    '.price', '.pricing-feature',
                    '.testimonial', '.review-content',
                    '.contact-info', '.address'
                ];
                
                editableSelectors.forEach(selector => {
                    const elements = doc.querySelectorAll(selector);
                    elements.forEach(element => {
                        if (!element.querySelector('img') && !element.querySelector('svg') && element.textContent.trim()) {
                            this.makeElementEditable(element);
                        }
                    });
                });
            }
            
            makeElementEditable(element) {
                element.classList.add('editable-element');
                element.setAttribute('data-editable', 'true');
                
                // Add edit overlay
                const overlay = element.ownerDocument.createElement('div');
                overlay.className = 'edit-overlay';
                overlay.textContent = 'Click to edit';
                element.appendChild(overlay);
                
                // Add click event
                element.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.editElement(element);
                });
            }
            
            editElement(element) {
                // Remove editing class from previous element
                if (this.currentElement) {
                    this.currentElement.classList.remove('editing');
                }
                
                // Set current element
                this.currentElement = element;
                element.classList.add('editing');
                
                // Show edit panel
                this.showEditPanel(element);
            }
            
            showEditPanel(element) {
                const panel = this.editPanel;
                const content = document.getElementById('edit-content');
                
                // Get element info
                const tagName = element.tagName.toLowerCase();
                const className = element.className;
                const textContent = element.textContent.trim();
                const innerHTML = element.innerHTML;
                
                // Create edit form
                content.innerHTML = `
                    <div class="element-path">
                        ${tagName}${className ? '.' + className.split(' ').join('.') : ''}
                    </div>
                    
                    <div class="quick-actions">
                        <button class="quick-action-btn" onclick="editor.formatText('bold')">
                            <i class="fas fa-bold"></i>
                        </button>
                        <button class="quick-action-btn" onclick="editor.formatText('italic')">
                            <i class="fas fa-italic"></i>
                        </button>
                        <button class="quick-action-btn" onclick="editor.formatText('underline')">
                            <i class="fas fa-underline"></i>
                        </button>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                        <textarea id="element-content" class="w-full p-3 border border-gray-300 rounded-md" rows="6">${textContent}</textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">HTML (Advanced)</label>
                        <textarea id="element-html" class="w-full p-3 border border-gray-300 rounded-md font-mono text-sm" rows="4">${innerHTML}</textarea>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button id="save-element" class="flex-1 bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                            <i class="fas fa-save mr-2"></i>Save
                        </button>
                        <button id="cancel-edit" class="flex-1 bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </button>
                    </div>
                `;
                
                // Show panel
                panel.classList.add('active');
                document.getElementById('editor-content').classList.add('content-with-panel');
                
                // Setup save/cancel events
                document.getElementById('save-element').addEventListener('click', () => {
                    this.saveElement();
                });
                
                document.getElementById('cancel-edit').addEventListener('click', () => {
                    this.closeEditPanel();
                });
            }
            
            saveElement() {
                if (!this.currentElement) return;
                
                const newContent = document.getElementById('element-content').value;
                const newHTML = document.getElementById('element-html').value;
                
                // Update element content
                if (newHTML !== this.currentElement.innerHTML) {
                    this.currentElement.innerHTML = newHTML;
                } else {
                    this.currentElement.textContent = newContent;
                }
                
                // Store change
                const elementId = this.generateElementId(this.currentElement);
                this.changes[elementId] = {
                    content: newContent,
                    html: newHTML,
                    element: this.currentElement.outerHTML
                };
                
                // Show save indicator
                this.showSaveIndicator();
                
                // Close panel
                this.closeEditPanel();
            }
            
            closeEditPanel() {
                this.editPanel.classList.remove('active');
                document.getElementById('editor-content').classList.remove('content-with-panel');
                
                if (this.currentElement) {
                    this.currentElement.classList.remove('editing');
                    this.currentElement = null;
                }
            }
            
            generateElementId(element) {
                const tagName = element.tagName.toLowerCase();
                const className = element.className;
                const textContent = element.textContent.trim().substring(0, 50);
                return `${tagName}-${className}-${btoa(textContent).substring(0, 10)}`;
            }
            
            showSaveIndicator() {
                this.saveIndicator.classList.add('show');
                setTimeout(() => {
                    this.saveIndicator.classList.remove('show');
                }, 2000);
            }
            
            saveAllChanges() {
                if (Object.keys(this.changes).length === 0) {
                    alert('No changes to save');
                    return;
                }
                
                // Get current page content
                const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
                const htmlContent = iframeDoc.documentElement.outerHTML;
                
                // Send to server
                fetch('api/save_realtime_changes.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        page: document.getElementById('page-selector').value,
                        content: htmlContent,
                        changes: this.changes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.changes = {};
                        this.showSaveIndicator();
                        alert('All changes saved successfully!');
                    } else {
                        alert('Error saving changes: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error saving changes');
                });
            }
            
            previewChanges() {
                window.open(this.iframe.src.replace('?editor=1', ''), '_blank');
            }
            
            switchPage(page) {
                const pageUrls = {
                    'home': '../index.html',
                    'services': '../services/index.html',
                    'pricing': '../pricing/index.html',
                    'contact': '../contact/index.html',
                    'process': '../process/index.html',
                    'startup-packages': '../startup-packages/index.html'
                };
                
                this.iframe.src = pageUrls[page] + '?editor=1';
                document.getElementById('current-page-name').textContent = page.charAt(0).toUpperCase() + page.slice(1);
                this.closeEditPanel();
                this.changes = {};
            }
            
            toggleEditMode() {
                this.editMode = !this.editMode;
                const button = document.getElementById('toggle-edit-mode');
                
                if (this.editMode) {
                    button.innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Mode: ON';
                    button.className = 'bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm font-medium';
                } else {
                    button.innerHTML = '<i class="fas fa-eye mr-2"></i>Edit Mode: OFF';
                    button.className = 'bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium';
                    this.closeEditPanel();
                }
            }
            
            formatText(format) {
                // This would implement text formatting
                console.log('Format text:', format);
            }
        }
        
        // Initialize editor
        const editor = new RealtimeEditor();
    </script>
</body>
</html>