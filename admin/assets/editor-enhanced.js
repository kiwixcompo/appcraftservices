/**
 * Enhanced Realtime Editor - Elementor-like functionality
 * Provides advanced editing capabilities with visual feedback
 */

class EnhancedRealtimeEditor {
    constructor() {
        this.editMode = true;
        this.currentElement = null;
        this.changes = {};
        this.history = [];
        this.historyIndex = -1;
        this.iframe = document.getElementById('website-frame');
        this.editPanel = document.getElementById('edit-panel');
        this.saveIndicator = document.getElementById('save-indicator');
        this.isLoading = false;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.waitForIframeLoad();
        this.setupKeyboardShortcuts();
        this.createEditorModeIndicator();
    }
    
    createEditorModeIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'editor-mode-indicator';
        indicator.innerHTML = `
            <i class="fas fa-magic"></i>
            <span>LIVE EDITING MODE</span>
        `;
        document.body.appendChild(indicator);
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+S or Cmd+S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                this.saveAllChanges();
            }
            
            // Ctrl+Z or Cmd+Z to undo
            if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
                e.preventDefault();
                this.undo();
            }
            
            // Ctrl+Y or Cmd+Shift+Z to redo
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.key === 'z' && e.shiftKey))) {
                e.preventDefault();
                this.redo();
            }
            
            // Escape to close panel
            if (e.key === 'Escape') {
                this.closeEditPanel();
            }
        });
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
        document.getElementById('toggle-edit-mode')?.addEventListener('click', () => {
            this.toggleEditMode();
        });
        
        // Save all changes
        document.getElementById('save-all-changes')?.addEventListener('click', () => {
            this.saveAllChanges();
        });
        
        // Preview changes
        document.getElementById('preview-changes')?.addEventListener('click', () => {
            this.previewChanges();
        });
        
        // Close panel
        document.getElementById('close-panel')?.addEventListener('click', () => {
            this.closeEditPanel();
        });
        
        // Page selector
        document.getElementById('page-selector')?.addEventListener('change', (e) => {
            this.switchPage(e.target.value);
        });
    }
    
    setupIframeEditor() {
        try {
            const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
            
            // Add enhanced editor styles to iframe
            const editorStyles = document.createElement('link');
            editorStyles.rel = 'stylesheet';
            editorStyles.href = 'admin/assets/editor-styles.css';
            iframeDoc.head.appendChild(editorStyles);
            
            // Make elements editable
            this.makeElementsEditable(iframeDoc);
            
            // Add context menu
            this.setupContextMenu(iframeDoc);
            
        } catch (error) {
            console.error('Error setting up iframe editor:', error);
            this.showNotification('Editor setup failed. Please refresh the page.', 'error');
        }
    }
    
    makeElementsEditable(doc) {
        // Enhanced editable selectors with more specific targeting
        const editableSelectors = [
            // Headings
            'h1:not(.no-edit)', 'h2:not(.no-edit)', 'h3:not(.no-edit)', 
            'h4:not(.no-edit)', 'h5:not(.no-edit)', 'h6:not(.no-edit)',
            
            // Text content
            'p:not(.no-edit)', 'span:not(.no-edit)', 
            'div.text-center:not(.no-edit)', 'div.text-left:not(.no-edit)', 'div.text-right:not(.no-edit)',
            
            // Hero sections
            '.hero h1', '.hero p', '.hero .text-xl', '.hero .text-2xl',
            
            // Value propositions
            '.value-prop-title', '.value-prop-desc',
            
            // Buttons and CTAs
            'button:not(.no-edit)', 'a.btn:not(.no-edit)', '.cta-text',
            
            // Services
            '.service-title', '.service-description', '.service-name',
            
            // Pricing
            '.price', '.pricing-feature', '.pricing-title',
            
            // Reviews and testimonials
            '.testimonial', '.review-content', '.client-name',
            
            // Contact information
            '.contact-info', '.address', '.phone', '.email',
            
            // Navigation
            'nav a:not(.logo):not(.no-edit)',
            
            // Footer content
            'footer p:not(.no-edit)', 'footer h3:not(.no-edit)', 'footer h4:not(.no-edit)'
        ];
        
        editableSelectors.forEach(selector => {
            const elements = doc.querySelectorAll(selector);
            elements.forEach(element => {
                if (this.isEditableElement(element)) {
                    this.makeElementEditable(element);
                }
            });
        });
    }
    
    isEditableElement(element) {
        // Skip elements that contain only images, SVGs, or are empty
        if (element.querySelector('img') || element.querySelector('svg')) {
            return false;
        }
        
        // Skip elements with no text content
        if (!element.textContent.trim()) {
            return false;
        }
        
        // Skip elements marked as non-editable
        if (element.classList.contains('no-edit') || element.hasAttribute('data-no-edit')) {
            return false;
        }
        
        // Skip script and style elements
        if (['SCRIPT', 'STYLE', 'NOSCRIPT'].includes(element.tagName)) {
            return false;
        }
        
        return true;
    }
    
    makeElementEditable(element) {
        element.classList.add('editable-element');
        element.setAttribute('data-editable', 'true');
        element.setAttribute('data-original-content', element.innerHTML);
        
        // Add edit overlay
        const overlay = element.ownerDocument.createElement('div');
        overlay.className = 'edit-overlay';
        overlay.innerHTML = `<i class="fas fa-edit"></i> Click to edit`;
        element.appendChild(overlay);
        
        // Add element type badge
        const badge = element.ownerDocument.createElement('div');
        badge.className = 'element-type-badge';
        badge.textContent = element.tagName.toLowerCase();
        element.appendChild(badge);
        
        // Add click event
        element.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.editElement(element);
        });
        
        // Add hover effects
        element.addEventListener('mouseenter', () => {
            if (!element.classList.contains('editing')) {
                element.style.transform = 'translateY(-1px)';
            }
        });
        
        element.addEventListener('mouseleave', () => {
            if (!element.classList.contains('editing')) {
                element.style.transform = '';
            }
        });
    }
    
    setupContextMenu(doc) {
        doc.addEventListener('contextmenu', (e) => {
            const element = e.target.closest('.editable-element');
            if (element && this.editMode) {
                e.preventDefault();
                this.showContextMenu(e, element);
            }
        });
    }
    
    showContextMenu(e, element) {
        // Remove existing context menu
        const existingMenu = document.querySelector('.editor-context-menu');
        if (existingMenu) {
            existingMenu.remove();
        }
        
        const menu = document.createElement('div');
        menu.className = 'editor-context-menu';
        menu.style.cssText = `
            position: fixed;
            top: ${e.clientY}px;
            left: ${e.clientX}px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            z-index: 10000;
            min-width: 180px;
            padding: 8px 0;
        `;
        
        const menuItems = [
            { icon: 'fas fa-edit', text: 'Edit Content', action: () => this.editElement(element) },
            { icon: 'fas fa-copy', text: 'Duplicate Element', action: () => this.duplicateElement(element) },
            { icon: 'fas fa-palette', text: 'Change Style', action: () => this.editElementStyle(element) },
            { icon: 'fas fa-undo', text: 'Reset to Original', action: () => this.resetElement(element) }
        ];
        
        menuItems.forEach(item => {
            const menuItem = document.createElement('div');
            menuItem.style.cssText = `
                padding: 10px 16px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 10px;
                font-size: 14px;
                color: #374151;
                transition: background-color 0.2s ease;
            `;
            
            menuItem.innerHTML = `<i class="${item.icon}"></i> ${item.text}`;
            
            menuItem.addEventListener('mouseenter', () => {
                menuItem.style.backgroundColor = '#f3f4f6';
            });
            
            menuItem.addEventListener('mouseleave', () => {
                menuItem.style.backgroundColor = '';
            });
            
            menuItem.addEventListener('click', () => {
                item.action();
                menu.remove();
            });
            
            menu.appendChild(menuItem);
        });
        
        document.body.appendChild(menu);
        
        // Remove menu when clicking elsewhere
        setTimeout(() => {
            document.addEventListener('click', () => {
                menu.remove();
            }, { once: true });
        }, 100);
    }
    
    editElement(element) {
        // Save current state for undo
        this.saveState();
        
        // Remove editing class from previous element
        if (this.currentElement) {
            this.currentElement.classList.remove('editing');
        }
        
        // Set current element
        this.currentElement = element;
        element.classList.add('editing');
        
        // Show edit panel with enhanced UI
        this.showEnhancedEditPanel(element);
    }
    
    showEnhancedEditPanel(element) {
        const panel = this.editPanel;
        const content = document.getElementById('edit-content');
        
        // Get element info
        const tagName = element.tagName.toLowerCase();
        const className = element.className.replace('editable-element', '').replace('editing', '').trim();
        const textContent = element.textContent.replace('Click to edit', '').trim();
        const innerHTML = element.innerHTML.replace(/<div class="edit-overlay">.*?<\/div>/s, '').replace(/<div class="element-type-badge">.*?<\/div>/s, '');
        
        // Create enhanced edit form
        content.innerHTML = `
            <div class="edit-panel-header">
                <h3 class="text-lg font-semibold">Edit ${tagName.toUpperCase()} Element</h3>
                <div class="element-path">${tagName}${className ? '.' + className.split(' ').join('.') : ''}</div>
            </div>
            
            <div class="edit-panel-content">
                <div class="quick-actions-toolbar">
                    <button class="quick-action" onclick="editor.formatText('bold')" data-tooltip="Bold">
                        <i class="fas fa-bold"></i>
                    </button>
                    <button class="quick-action" onclick="editor.formatText('italic')" data-tooltip="Italic">
                        <i class="fas fa-italic"></i>
                    </button>
                    <button class="quick-action" onclick="editor.formatText('underline')" data-tooltip="Underline">
                        <i class="fas fa-underline"></i>
                    </button>
                    <button class="quick-action" onclick="editor.insertLink()" data-tooltip="Insert Link">
                        <i class="fas fa-link"></i>
                    </button>
                    <button class="quick-action" onclick="editor.changeTextColor()" data-tooltip="Text Color">
                        <i class="fas fa-palette"></i>
                    </button>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Content</label>
                    <textarea id="element-content" class="editor-input editor-textarea w-full" rows="6" placeholder="Enter your content here...">${textContent}</textarea>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">HTML (Advanced)</label>
                    <textarea id="element-html" class="editor-input editor-textarea w-full font-mono text-sm" rows="4" placeholder="Advanced HTML editing...">${innerHTML}</textarea>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Element Style</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Text Color</label>
                            <input type="color" id="text-color" class="w-full h-10 border border-gray-300 rounded cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Background</label>
                            <input type="color" id="bg-color" class="w-full h-10 border border-gray-300 rounded cursor-pointer">
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button id="save-element" class="editor-btn editor-btn-primary flex-1">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                    <button id="cancel-edit" class="editor-btn editor-btn-secondary flex-1">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex space-x-2">
                        <button onclick="editor.duplicateElement(editor.currentElement)" class="editor-btn editor-btn-secondary text-sm">
                            <i class="fas fa-copy"></i>
                            Duplicate
                        </button>
                        <button onclick="editor.resetElement(editor.currentElement)" class="editor-btn editor-btn-secondary text-sm">
                            <i class="fas fa-undo"></i>
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Show panel with animation
        panel.classList.add('active', 'slide-in-right');
        document.getElementById('editor-content').classList.add('content-with-panel');
        
        // Setup save/cancel events
        document.getElementById('save-element').addEventListener('click', () => {
            this.saveElement();
        });
        
        document.getElementById('cancel-edit').addEventListener('click', () => {
            this.closeEditPanel();
        });
        
        // Auto-resize textareas
        this.setupAutoResize();
    }
    
    setupAutoResize() {
        const textareas = document.querySelectorAll('.editor-textarea');
        textareas.forEach(textarea => {
            textarea.addEventListener('input', () => {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            });
        });
    }
    
    saveElement() {
        if (!this.currentElement) return;
        
        this.showLoading(true);
        
        const newContent = document.getElementById('element-content').value;
        const newHTML = document.getElementById('element-html').value;
        const textColor = document.getElementById('text-color').value;
        const bgColor = document.getElementById('bg-color').value;
        
        // Update element content
        if (newHTML !== this.currentElement.innerHTML) {
            this.currentElement.innerHTML = newHTML;
        } else {
            this.currentElement.textContent = newContent;
        }
        
        // Apply styles
        if (textColor) {
            this.currentElement.style.color = textColor;
        }
        if (bgColor) {
            this.currentElement.style.backgroundColor = bgColor;
        }
        
        // Re-add editor elements
        this.makeElementEditable(this.currentElement);
        
        // Store change
        const elementId = this.generateElementId(this.currentElement);
        this.changes[elementId] = {
            content: newContent,
            html: newHTML,
            styles: {
                color: textColor,
                backgroundColor: bgColor
            },
            element: this.currentElement.outerHTML,
            timestamp: new Date().toISOString()
        };
        
        // Show save indicator
        setTimeout(() => {
            this.showLoading(false);
            this.showSaveIndicator('Element saved successfully!');
            this.closeEditPanel();
        }, 500);
    }
    
    duplicateElement(element) {
        const clone = element.cloneNode(true);
        clone.classList.remove('editing');
        element.parentNode.insertBefore(clone, element.nextSibling);
        this.makeElementEditable(clone);
        this.showNotification('Element duplicated successfully!', 'success');
    }
    
    resetElement(element) {
        const originalContent = element.getAttribute('data-original-content');
        if (originalContent) {
            element.innerHTML = originalContent;
            this.makeElementEditable(element);
            this.showNotification('Element reset to original content', 'info');
        }
    }
    
    showLoading(show) {
        this.isLoading = show;
        const existingOverlay = document.querySelector('.loading-overlay');
        
        if (show) {
            if (!existingOverlay) {
                const overlay = document.createElement('div');
                overlay.className = 'loading-overlay show';
                overlay.innerHTML = '<div class="loading-spinner"></div>';
                this.editPanel.appendChild(overlay);
            }
        } else {
            if (existingOverlay) {
                existingOverlay.remove();
            }
        }
    }
    
    showSaveIndicator(message = 'Changes saved successfully!') {
        this.saveIndicator.innerHTML = `
            <div class="icon">
                <i class="fas fa-check"></i>
            </div>
            <span>${message}</span>
        `;
        this.saveIndicator.classList.add('show', 'bounce-in');
        
        setTimeout(() => {
            this.saveIndicator.classList.remove('show', 'bounce-in');
        }, 3000);
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm fade-in ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-white' :
            'bg-blue-500 text-white'
        }`;

        notification.innerHTML = `
            <div class="flex items-center justify-between">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
    
    // History management
    saveState() {
        const state = {
            changes: { ...this.changes },
            timestamp: new Date().toISOString()
        };
        
        this.history = this.history.slice(0, this.historyIndex + 1);
        this.history.push(state);
        this.historyIndex++;
        
        // Limit history to 50 states
        if (this.history.length > 50) {
            this.history.shift();
            this.historyIndex--;
        }
    }
    
    undo() {
        if (this.historyIndex > 0) {
            this.historyIndex--;
            const state = this.history[this.historyIndex];
            this.changes = { ...state.changes };
            this.showNotification('Undo successful', 'info');
        }
    }
    
    redo() {
        if (this.historyIndex < this.history.length - 1) {
            this.historyIndex++;
            const state = this.history[this.historyIndex];
            this.changes = { ...state.changes };
            this.showNotification('Redo successful', 'info');
        }
    }
    
    // Rest of the methods remain the same as the original editor
    closeEditPanel() {
        this.editPanel.classList.remove('active', 'slide-in-right');
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
    
    async saveAllChanges() {
        if (Object.keys(this.changes).length === 0) {
            this.showNotification('No changes to save', 'warning');
            return;
        }
        
        this.showLoading(true);
        
        try {
            // Get current page content
            const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
            const htmlContent = iframeDoc.documentElement.outerHTML;
            
            const response = await fetch('api/save_realtime_changes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    page: document.getElementById('page-selector').value,
                    content: htmlContent,
                    changes: this.changes
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.changes = {};
                this.showSaveIndicator(`All changes saved! (${data.changes_count} elements updated)`);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error saving changes: ' + error.message, 'error');
        } finally {
            this.showLoading(false);
        }
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
    
    // Text formatting methods
    formatText(format) {
        const textarea = document.getElementById('element-content');
        if (!textarea) return;
        
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        
        if (selectedText) {
            let formattedText = selectedText;
            switch (format) {
                case 'bold':
                    formattedText = `<strong>${selectedText}</strong>`;
                    break;
                case 'italic':
                    formattedText = `<em>${selectedText}</em>`;
                    break;
                case 'underline':
                    formattedText = `<u>${selectedText}</u>`;
                    break;
            }
            
            textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
            textarea.focus();
        }
    }
    
    insertLink() {
        const url = prompt('Enter URL:');
        const text = prompt('Enter link text:');
        
        if (url && text) {
            const textarea = document.getElementById('element-content');
            const start = textarea.selectionStart;
            const linkHtml = `<a href="${url}" target="_blank">${text}</a>`;
            
            textarea.value = textarea.value.substring(0, start) + linkHtml + textarea.value.substring(start);
            textarea.focus();
        }
    }
    
    changeTextColor() {
        const color = prompt('Enter color (hex, rgb, or color name):');
        if (color) {
            const textarea = document.getElementById('element-content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            
            if (selectedText) {
                const coloredText = `<span style="color: ${color}">${selectedText}</span>`;
                textarea.value = textarea.value.substring(0, start) + coloredText + textarea.value.substring(end);
                textarea.focus();
            }
        }
    }
}