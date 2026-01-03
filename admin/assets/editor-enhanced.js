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
        
        // Store original content and structure
        const originalHTML = element.innerHTML;
        const originalClasses = element.className;
        const originalStyles = element.getAttribute('style') || '';
        
        element.setAttribute('data-original-content', originalHTML);
        element.setAttribute('data-original-classes', originalClasses);
        element.setAttribute('data-original-styles', originalStyles);
        
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
        
        // Get clean text content (without editor elements)
        const cleanElement = element.cloneNode(true);
        const editOverlay = cleanElement.querySelector('.edit-overlay');
        const typeBadge = cleanElement.querySelector('.element-type-badge');
        if (editOverlay) editOverlay.remove();
        if (typeBadge) typeBadge.remove();
        
        const textContent = cleanElement.textContent.trim();
        const innerHTML = cleanElement.innerHTML;
        
        // Get current styles
        const computedStyles = window.getComputedStyle(element);
        const currentTextColor = this.rgbToHex(computedStyles.color);
        const currentBgColor = this.rgbToHex(computedStyles.backgroundColor);
        
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
                    <textarea id="element-content" class="editor-input editor-textarea w-full" rows="6" placeholder="Enter your content here...">${this.escapeHtml(textContent)}</textarea>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">HTML (Advanced)</label>
                    <textarea id="element-html" class="editor-input editor-textarea w-full font-mono text-sm" rows="4" placeholder="Advanced HTML editing...">${this.escapeHtml(innerHTML)}</textarea>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Element Style</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Text Color</label>
                            <input type="color" id="text-color" value="${currentTextColor}" class="w-full h-10 border border-gray-300 rounded cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Background</label>
                            <input type="color" id="bg-color" value="${currentBgColor}" class="w-full h-10 border border-gray-300 rounded cursor-pointer">
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
    
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    rgbToHex(rgb) {
        if (!rgb || rgb === 'rgba(0, 0, 0, 0)' || rgb === 'transparent') return '#000000';
        
        const result = rgb.match(/\d+/g);
        if (!result) return '#000000';
        
        const r = parseInt(result[0]);
        const g = parseInt(result[1]);
        const b = parseInt(result[2]);
        
        return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
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
        
        // Get original attributes
        const originalClasses = this.currentElement.getAttribute('data-original-classes') || '';
        const originalStyles = this.currentElement.getAttribute('data-original-styles') || '';
        
        // Remove editor elements before updating
        this.removeEditorElements(this.currentElement);
        
        // Update content while preserving structure
        if (newHTML.trim() !== '' && newHTML !== this.currentElement.innerHTML) {
            // If HTML was modified, use it but be careful about structure
            try {
                // Create a temporary element to validate HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = newHTML;
                
                // Only update if the HTML is valid
                if (tempDiv.innerHTML === newHTML) {
                    this.currentElement.innerHTML = newHTML;
                } else {
                    // Fall back to text content update
                    this.updateMainTextContent(this.currentElement, newContent);
                }
            } catch (error) {
                console.warn('Invalid HTML provided, using text content instead:', error);
                this.updateMainTextContent(this.currentElement, newContent);
            }
        } else if (newContent.trim() !== '') {
            // If only text content was changed, preserve inner HTML structure
            this.updateMainTextContent(this.currentElement, newContent);
        }
        
        // Restore and update classes properly
        let finalClasses = originalClasses;
        if (!finalClasses.includes('editable-element')) {
            finalClasses = (finalClasses + ' editable-element').trim();
        }
        this.currentElement.className = finalClasses;
        
        // Apply new styles while preserving original ones
        let newStyles = originalStyles;
        if (textColor && textColor !== '#000000') {
            newStyles = this.updateStyleProperty(newStyles, 'color', textColor);
        }
        if (bgColor && bgColor !== '#000000') {
            newStyles = this.updateStyleProperty(newStyles, 'background-color', bgColor);
        }
        
        if (newStyles.trim()) {
            this.currentElement.setAttribute('style', newStyles);
        } else {
            this.currentElement.removeAttribute('style');
        }
        
        // Re-add editor elements
        this.addEditorElements(this.currentElement);
        
        // Store change
        const elementId = this.generateElementId(this.currentElement);
        this.changes[elementId] = {
            content: newContent,
            html: newHTML,
            styles: {
                color: textColor,
                backgroundColor: bgColor
            },
            element: this.getCleanElementHTML(this.currentElement),
            timestamp: new Date().toISOString()
        };
        
        // Show save indicator
        setTimeout(() => {
            this.showLoading(false);
            this.showSaveIndicator('Element saved successfully!');
            this.closeEditPanel();
        }, 500);
    }
    
    updateStyleProperty(styleString, property, value) {
        // Parse existing styles
        const styles = {};
        if (styleString) {
            styleString.split(';').forEach(style => {
                const [prop, val] = style.split(':').map(s => s.trim());
                if (prop && val) {
                    styles[prop] = val;
                }
            });
        }
        
        // Update the specific property
        styles[property] = value;
        
        // Rebuild style string
        return Object.entries(styles)
            .filter(([prop, val]) => prop && val)
            .map(([prop, val]) => `${prop}: ${val}`)
            .join('; ');
    }
    
    getCleanElementHTML(element) {
        // Create a clean copy without editor elements
        const clone = element.cloneNode(true);
        this.removeEditorElements(clone);
        clone.classList.remove('editable-element', 'editing');
        clone.removeAttribute('data-editable');
        clone.removeAttribute('data-original-content');
        clone.removeAttribute('data-original-classes');
        clone.removeAttribute('data-original-styles');
        
        return clone.outerHTML;
    }
    
    getTextNodes(element) {
        const textNodes = [];
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: function(node) {
                    // Skip text nodes inside editor elements
                    if (node.parentElement.classList.contains('edit-overlay') || 
                        node.parentElement.classList.contains('element-type-badge')) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    return node.textContent.trim() ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
                }
            }
        );
        
        let node;
        while (node = walker.nextNode()) {
            textNodes.push(node);
        }
        return textNodes;
    }
    
    updateMainTextContent(element, newContent) {
        // Find the main text content and update it safely
        const textNodes = this.getTextNodes(element);
        if (textNodes.length > 0) {
            // Update the first significant text node
            textNodes[0].textContent = newContent;
        } else {
            // If no text nodes found, replace the entire text content
            // but preserve any child elements that aren't editor elements
            const children = Array.from(element.children).filter(child => 
                !child.classList.contains('edit-overlay') && 
                !child.classList.contains('element-type-badge')
            );
            
            if (children.length === 0) {
                // Safe to replace text content
                element.textContent = newContent;
            }
        }
    }
    
    addEditorElements(element) {
        // Remove any existing editor elements first
        this.removeEditorElements(element);
        
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
        
        // Re-add click event (remove existing first)
        element.removeEventListener('click', this.editElementHandler);
        this.editElementHandler = (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.editElement(element);
        };
        element.addEventListener('click', this.editElementHandler);
    }
    
    removeEditorElements(element) {
        // Remove edit overlay and type badge
        const editOverlay = element.querySelector('.edit-overlay');
        const typeBadge = element.querySelector('.element-type-badge');
        if (editOverlay) editOverlay.remove();
        if (typeBadge) typeBadge.remove();
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
        const originalClasses = element.getAttribute('data-original-classes');
        const originalStyles = element.getAttribute('data-original-styles');
        
        if (originalContent) {
            // Remove current editor elements
            const editOverlay = element.querySelector('.edit-overlay');
            const typeBadge = element.querySelector('.element-type-badge');
            if (editOverlay) editOverlay.remove();
            if (typeBadge) typeBadge.remove();
            
            // Restore original content
            element.innerHTML = originalContent;
            
            // Restore original classes
            if (originalClasses) {
                element.className = originalClasses + ' editable-element';
            }
            
            // Restore original styles
            if (originalStyles) {
                element.setAttribute('style', originalStyles);
            } else {
                element.removeAttribute('style');
            }
            
            // Re-add editor elements
            this.addEditorElements(element);
            
            this.showNotification('Element reset to original content', 'info');
        } else {
            this.showNotification('No original content found to restore', 'warning');
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
            
            // Create a clean copy of the document
            const cleanDoc = iframeDoc.cloneNode(true);
            
            // Remove all editor elements and clean up attributes
            this.cleanDocumentForSaving(cleanDoc);
            
            const htmlContent = '<!DOCTYPE html>\n' + cleanDoc.documentElement.outerHTML;
            
            console.log('Saving changes:', {
                page: document.getElementById('page-selector').value,
                changesCount: Object.keys(this.changes).length
            });
            
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
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Save response:', data);
            
            if (data.success) {
                this.changes = {};
                this.showSaveIndicator(`All changes saved! (${data.changes_count} elements updated)`);
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        } catch (error) {
            console.error('Error saving changes:', error);
            this.showNotification('Error saving changes: ' + error.message, 'error');
        } finally {
            this.showLoading(false);
        }
    }
    
    cleanDocumentForSaving(doc) {
        // Remove all editor elements
        const editOverlays = doc.querySelectorAll('.edit-overlay');
        const typeBadges = doc.querySelectorAll('.element-type-badge');
        
        editOverlays.forEach(overlay => overlay.remove());
        typeBadges.forEach(badge => badge.remove());
        
        // Clean up classes and attributes on all editable elements
        const editableElements = doc.querySelectorAll('.editable-element');
        editableElements.forEach(element => {
            // Remove editor classes
            element.classList.remove('editable-element', 'editing');
            
            // Remove editor attributes
            element.removeAttribute('data-editable');
            element.removeAttribute('data-original-content');
            element.removeAttribute('data-original-classes');
            element.removeAttribute('data-original-styles');
            
            // Clean up empty class attributes
            if (!element.className.trim()) {
                element.removeAttribute('class');
            }
        });
        
        // Remove editor mode class from body
        const body = doc.querySelector('body');
        if (body) {
            body.classList.remove('editor-mode');
        }
        
        // Remove editor-specific stylesheets
        const editorStylesheets = doc.querySelectorAll('link[href*="editor-styles.css"]');
        editorStylesheets.forEach(link => link.remove());
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

// Initialize the editor when the page loads
let editor;
document.addEventListener('DOMContentLoaded', function() {
    editor = new EnhancedRealtimeEditor();
});