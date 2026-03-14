class VoiceAssistant {
    constructor(appName = 'Simko') {
        this.appName = appName;
        this.isListening = false;
        this.recognition = null;
        this.modal = null;

        // Pre-warm recognition engine
        this.warmupRecognition();

        // Pre-compiled patterns for instant matching
        this.commandMap = new Map();
        this.setupCommandMap();

        // Cache DOM elements
        this.cacheElements();

        this.init();
    }

    warmupRecognition() {
        // Create recognition instance early to warm up
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SpeechRecognition();
            this.recognition.continuous = false;
            this.recognition.interimResults = false;
            this.recognition.maxAlternatives = 1;
            this.recognition.lang = 'en-US';
        }
    }

    cacheElements() {
        // Cache frequently used elements
        this.voiceBtn = document.getElementById('voiceAssistantBtn');
        this.indicator = document.querySelector('.voice-indicator');
    }

    setupCommandMap() {
        // Direct path mapping - O(1) lookup
        const commands = [
            // Main
            ['dashboard', '/admin/dashboard'],
            ['dash', '/admin/dashboard'],

            // Products
            ['products', '/admin/products'],
            ['product', '/admin/products'],
            ['product list', '/admin/products'],
            ['all products', '/admin/products'],

            // Attributes
            ['attributes', '/admin/attributes'],
            ['attribute', '/admin/attributes'],

            // Barcode
            ['barcode', '/admin/barcode'],
            ['bar code', '/admin/barcode'],
            ['print barcode', '/admin/barcode'],

            // Categories
            ['categories', '/admin/categories'],
            ['category', '/admin/categories'],

            // Warehouses
            ['warehouses', '/admin/warehouses'],
            ['warehouse', '/admin/warehouses'],

            // Customers
            ['customers', '/admin/parties/customer'],
            ['customer', '/admin/parties/customer'],

            // Dealers
            ['dealers', '/admin/parties/dealer'],
            ['dealer', '/admin/parties/dealer'],

            // Distributors
            ['distributors', '/admin/parties/distributor'],
            ['distributor', '/admin/parties/distributor'],

            // Salesmen
            ['salesmen', '/admin/salesmen'],
            ['salesman', '/admin/salesmen'],
            ['sales executive', '/admin/salesmen'],
            ['executive', '/admin/salesmen'],

            // Sales
            ['sales', '/admin/sales'],
            ['invoices', '/admin/sales'],
            ['sales invoice', '/admin/sales'],

            // Sales Returns
            ['sales returns', '/admin/sales-returns'],
            ['returns', '/admin/sales-returns'],
            ['return', '/admin/sales-returns'],

            // Credit Notes
            ['credit notes', '/admin/credit-notes'],
            ['credit note', '/admin/credit-notes'],

            // Quotations
            ['quotations', '/admin/quotations'],
            ['quotation', '/admin/quotations'],
            ['quotes', '/admin/quotations'],
            ['quote', '/admin/quotations'],

            // Payments
            ['payments', '/admin/payments'],
            ['payment in', '/admin/payments'],

            // Warranty
            ['warranty', '/admin/warranty'],
            ['claims', '/admin/warranty'],

            // Defective Stock
            ['defective stock', '/admin/defective-stock'],
            ['defective', '/admin/defective-stock'],
            ['damaged', '/admin/defective-stock'],

            // Purchases
            ['purchases', '/admin/purchases'],
            ['purchase orders', '/admin/purchases'],

            // Suppliers
            ['suppliers', '/admin/suppliers'],
            ['supplier', '/admin/suppliers'],

            // Supplier Payments
            ['supplier payments', '/admin/supplier-payments'],
            ['supplier payment', '/admin/supplier-payments'],

            // Reports
            ['reports', '/admin/reports'],
            ['report', '/admin/reports'],

            // Settings
            ['invoice settings', '/admin/invoice-settings'],
            ['invoice setting', '/admin/invoice-settings'],
            ['cash memo settings', '/admin/cashmemo-invoice-settings'],
            ['cash memo', '/admin/cashmemo-invoice-settings'],

            // Create Actions
            ['create product', '/admin/products/create'],
            ['new product', '/admin/products/create'],
            ['add product', '/admin/products/create'],

            ['create customer', '/admin/parties/create/new?type=customer'],
            ['new customer', '/admin/parties/create/new?type=customer'],
            ['add customer', '/admin/parties/create/new?type=customer'],

            ['create invoice', '/admin/sales/create'],
            ['new invoice', '/admin/sales/create'],
            ['create sale', '/admin/sales/create'],
            ['new sale', '/admin/sales/create'],

            ['create quotation', '/admin/quotations/create'],
            ['new quotation', '/admin/quotations/create'],
            ['create quote', '/admin/quotations/create'],

            ['create warranty', '/admin/warranty/create'],
            ['new claim', '/admin/warranty/create'],
            ['warranty claim', '/admin/warranty/create']
        ];

        // Add all commands to map
        commands.forEach(([keyword, path]) => {
            this.commandMap.set(keyword.toLowerCase(), path);
        });

        // Add navigation actions
        this.backAction = () => window.history.back();
        this.refreshAction = () => window.location.reload();

    }

    init() {
        if (!this.recognition) {
            console.error('❌ Speech recognition not supported');
            return;
        }

        this.setupRecognitionEvents();
        this.createModal();
        this.setupButton();

        // Pre-warm by starting and immediately stopping
        this.prewarm();
    }

    prewarm() {
        try {
            this.recognition.start();
            setTimeout(() => {
                try { this.recognition.stop(); } catch (e) {}
            }, 10);
        } catch (e) {
            // Ignore - just warming up
        }
    }

    setupButton() {
        if (this.voiceBtn) {
            const newBtn = this.voiceBtn.cloneNode(true);
            this.voiceBtn.parentNode.replaceChild(newBtn, this.voiceBtn);
            this.voiceBtn = newBtn;

            this.voiceBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleModal();
            });
        }
    }

    createModal() {
        const existingModal = document.querySelector('.assistant-modal-overlay');
        if (existingModal) existingModal.remove();

        this.modal = document.createElement('div');
        this.modal.className = 'assistant-modal-overlay';
        this.modal.innerHTML = `
            <div class="assistant-modal" style="transform: scale(1);">
                <div class="assistant-header">
                    <div class="app-icon">🛒</div>
                    <div class="app-info">
                        <div class="app-name">${this.appName}</div>
                        <div class="welcome-text">Hi <span>Admin</span> · How can I help?</div>
                    </div>
                    <button class="close-btn" id="closeAssistant">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="listening-circle" id="listeningCircle">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/>
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                        <line x1="12" y1="19" x2="12" y2="22"/>
                    </svg>
                </div>

                <div class="wave-bars" id="waveBars">
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                </div>

                <div class="status-text" id="statusText">READY</div>

                <div class="transcription-box" id="transcriptionBox">
                    <span class="placeholder-text">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M19 11a7 7 0 0 1-7 7m0 0a7 7 0 0 1-7-7m7 7v4m0 0H8m4 0h4"/>
                        </svg>
                        Say something...
                    </span>
                </div>

                <div class="action-buttons" id="actionButtons">
                    <button class="action-btn secondary" id="cancelBtn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(this.modal);

        document.getElementById('closeAssistant').addEventListener('click', () => this.closeModal());
        document.getElementById('cancelBtn').addEventListener('click', () => this.closeModal());
    }

    setupRecognitionEvents() {
        this.recognition.onstart = () => {
            this.isListening = true;
            this.updateUIForListening(true);
            this.updateStatus('LISTENING', 'listening');
            this.clearTranscription();
        };

        this.recognition.onend = () => {
            this.isListening = false;
            this.updateUIForListening(false);
            this.updateStatus('READY', '');
        };

        this.recognition.onresult = (event) => {
            const result = event.results[0];
            const transcript = result[0].transcript.toLowerCase().trim();

            // Show what was heard
            const box = document.getElementById('transcriptionBox');
            if (box) {
                box.innerHTML = `<span class="transcript-text">"${transcript}"</span>`;
            }

            // Process immediately - ZERO DELAY
            this.processCommand(transcript);
        };

        this.recognition.onerror = (event) => {
            console.error('Error:', event.error);
            this.updateStatus('ERROR', 'error');
            this.updateActionButtons(true);
        };
    }

    // INSTANT processing - no loops, no delays
    processCommand(transcript) {

        // Check for navigation commands first
        if (transcript.includes('go back') || transcript.includes('back') || transcript.includes('previous')) {
            this.updateStatus('✅', 'success');
            window.history.back();
            this.closeModal();
            return;
        }

        if (transcript.includes('refresh') || transcript.includes('reload')) {
            this.updateStatus('✅', 'success');
            window.location.reload();
            this.closeModal();
            return;
        }

        // Direct map lookup - O(1) instant
        for (const [keyword, path] of this.commandMap) {
            if (transcript.includes(keyword)) {
                this.updateStatus('✅', 'success');
                this.showToast(`Opening...`, 'success');

                // INSTANT navigation - no setTimeout
                window.location.href = path;
                this.closeModal();
                return;
            }
        }

        // No match found
        this.showToast('Try: products, sales, customers...', 'warning');
        this.updateActionButtons(true);
    }

    updateStatus(text, className) {
        const status = document.getElementById('statusText');
        if (status) {
            status.textContent = text;
            status.className = 'status-text';
            if (className) status.classList.add(className);
        }
    }

    updateUIForListening(isListening) {
        if (this.voiceBtn) {
            if (isListening) {
                this.voiceBtn.classList.add('listening');
                if (this.indicator) this.indicator.classList.add('active');

                const circle = document.getElementById('listeningCircle');
                if (circle) circle.classList.add('listening');

                const waveBars = document.querySelectorAll('.wave-bar');
                waveBars.forEach(bar => bar.style.animation = 'soundWave 1.2s ease-in-out infinite');
            } else {
                this.voiceBtn.classList.remove('listening');
                if (this.indicator) this.indicator.classList.remove('active');

                const circle = document.getElementById('listeningCircle');
                if (circle) circle.classList.remove('listening');

                const waveBars = document.querySelectorAll('.wave-bar');
                waveBars.forEach(bar => bar.style.animation = '');
            }
        }
    }

    updateActionButtons(showTryAgain = false) {
        const buttons = document.getElementById('actionButtons');
        if (!buttons) return;

        if (showTryAgain) {
            buttons.innerHTML = `
                <button class="action-btn secondary" id="cancelBtn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                </button>
                <button class="action-btn try-again" id="tryAgainBtn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    Try Again
                </button>
            `;
            document.getElementById('cancelBtn').addEventListener('click', () => this.closeModal());
            document.getElementById('tryAgainBtn').addEventListener('click', () => {
                this.clearTranscription();
                this.startListening();
            });
        } else {
            buttons.innerHTML = `
                <button class="action-btn secondary" id="cancelBtn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                </button>
            `;
            document.getElementById('cancelBtn').addEventListener('click', () => this.closeModal());
        }
    }

    clearTranscription() {
        const box = document.getElementById('transcriptionBox');
        if (box) {
            box.innerHTML = `
                <span class="placeholder-text">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M19 11a7 7 0 0 1-7 7m0 0a7 7 0 0 1-7-7m7 7v4m0 0H8m4 0h4"/>
                    </svg>
                    Say something...
                </span>
            `;
        }
        this.updateStatus('READY', '');
        this.updateActionButtons(false);
    }

    toggleModal() {
        if (this.modal.classList.contains('show')) {
            this.closeModal();
        } else {
            this.openModal();
        }
    }

    openModal() {
        this.modal.classList.add('show');
        this.clearTranscription();
        this.startListening();
    }

    closeModal() {
        this.modal.classList.remove('show');
        this.stopListening();
        this.clearTranscription();
    }

    startListening() {
        if (!this.recognition) return;

        try {
            this.recognition.start();
        } catch (error) {
            console.error('Failed to start:', error);
        }
    }

    stopListening() {
        if (this.recognition && this.isListening) {
            try {
                this.recognition.stop();
            } catch (error) {}
        }
        this.isListening = false;
    }

    showToast(message, type = 'info') {
        // Remove existing toast
        const existingToast = document.querySelector('.assistant-toast');
        if (existingToast) existingToast.remove();

        // Create new toast
        const toast = document.createElement('div');
        toast.className = `assistant-toast ${type}`;
        toast.innerHTML = `<span>${message}</span>`;
        document.body.appendChild(toast);

        // Show and hide immediately
        setTimeout(() => toast.classList.add('show'), 1);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast?.remove(), 200);
        }, 2500);
    }
}

// Initialize immediately with pre-warming
document.addEventListener('DOMContentLoaded', () => {
    const appName = document.querySelector('meta[name="app-name"]')?.content || 'Simko';
    window.voiceAssistant = new VoiceAssistant(appName);
});
