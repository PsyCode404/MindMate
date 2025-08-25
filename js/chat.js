class ChatManager {
    constructor() {
        // DOM Elements
        this.chatMessages = document.getElementById('chat-messages');
        this.chatInput = document.getElementById('chat-input');
        this.sendButton = document.getElementById('send-button');
        this.themeToggle = document.getElementById('theme-toggle');
        this.typingIndicator = document.getElementById('typing-indicator');
        this.clearChatButton = document.getElementById('clear-chat');
        this.charCounter = document.getElementById('char-counter');

        // State
        this.isDarkMode = false;
        this.isTyping = false;

        // Initialize
        this.init();
    }

    init() {
        // Load theme preference
        this.loadThemePreference();
        
        // Event Listeners
        this.chatInput.addEventListener('input', this.handleInput.bind(this));
        this.chatInput.addEventListener('keypress', this.handleKeyPress.bind(this));
        this.sendButton.addEventListener('click', this.handleSend.bind(this));
        this.themeToggle.addEventListener('click', this.toggleTheme.bind(this));
        this.clearChatButton.addEventListener('click', this.clearChat.bind(this));

        // Auto-resize textarea
        this.chatInput.addEventListener('input', this.autoResizeTextarea.bind(this));
        
        // Update character counter
        this.chatInput.addEventListener('input', this.updateCharCounter.bind(this));

        // Initialize character counter
        this.updateCharCounter();

        // Welcome message
        this.addBotMessage("Hello! I'm your MindMate assistant. How can I help you today?");
    }

    handleInput(event) {
        const isEmpty = event.target.value.trim() === '';
        this.sendButton.disabled = isEmpty;
    }

    handleKeyPress(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            if (!this.sendButton.disabled) {
                this.handleSend();
            }
        }
    }

    async handleSend() {
        const message = this.chatInput.value.trim();
        if (!message) return;

        // Add user message
        this.addUserMessage(message);

        // Clear input and reset height
        this.chatInput.value = '';
        this.chatInput.style.height = 'auto';
        this.sendButton.disabled = true;
        
        // Update character counter
        this.updateCharCounter();

        // Show typing indicator
        this.showTypingIndicator();

        // Send message to backend (Wit.ai)
        await this.fetchBotResponse(message);
    }

    addMessage(message, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user' : 'bot'}`;
        
        const messageText = document.createElement('div');
        messageText.className = 'message-text';
        messageText.textContent = message;
        
        const timeDiv = document.createElement('div');
        timeDiv.className = 'message-time';
        timeDiv.textContent = this.getCurrentTime();
        
        messageDiv.appendChild(messageText);
        messageDiv.appendChild(timeDiv);
        
        this.chatMessages.appendChild(messageDiv);
        this.scrollToBottom();
    }

    addUserMessage(message) {
        this.addMessage(message, true);
    }

    addBotMessage(message) {
        this.addMessage(message, false);
    }

    getCurrentTime() {
        return new Date().toLocaleTimeString([], { 
            hour: '2-digit', 
            minute: '2-digit'
        });
    }

    scrollToBottom() {
        this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
    }

    showTypingIndicator() {
        this.typingIndicator.classList.remove('hidden');
        this.scrollToBottom();
    }

    hideTypingIndicator() {
        this.typingIndicator.classList.add('hidden');
    }

    async fetchBotResponse(userMessage) {
        try {
            console.log('Sending message to server:', userMessage);
            const response = await fetch('/api/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: userMessage })
            });
            
            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);
            
            this.hideTypingIndicator();
            
            if (!response.ok) {
                throw new Error(data.error || 'Unknown error occurred');
            }
            
            if (data.error) {
                console.error('API Error:', data);
                this.addBotMessage(`I'm having trouble connecting to the chat service. Please try again later.`);
                if (data.debug) {
                    console.error('Debug info:', data.debug);
                }
            } else if (data.reply) {
                this.addBotMessage(data.reply);
            } else {
                console.warn('Unexpected response format:', data);
                this.addBotMessage("I'm here to help. Could you rephrase that?");
            }
        } catch (error) {
            console.error('Error in fetchBotResponse:', error);
            this.hideTypingIndicator();
            this.addBotMessage(`I'm having trouble connecting right now. Please try again in a moment. (${error.message})`);
        }
    }

    autoResizeTextarea() {
        this.chatInput.style.height = 'auto';
        this.chatInput.style.height = (this.chatInput.scrollHeight) + 'px';
    }

    toggleTheme() {
        this.isDarkMode = !this.isDarkMode;
        document.body.setAttribute('data-theme', this.isDarkMode ? 'dark' : 'light');
        this.themeToggle.innerHTML = this.isDarkMode ? 
            '<i class="fas fa-sun"></i>' : 
            '<i class="fas fa-moon"></i>';
        
        // Save theme preference
        localStorage.setItem('darkMode', this.isDarkMode);
    }
    
    clearChat() {
        // Confirm before clearing
        if (confirm('Are you sure you want to clear the entire conversation?')) {
            // Remove all messages except the welcome message
            while (this.chatMessages.children.length > 1) {
                this.chatMessages.removeChild(this.chatMessages.lastChild);
            }
            
            // If there are no messages, add the welcome message back
            if (this.chatMessages.children.length === 0) {
                this.addBotMessage("Hello! I'm your MindMate assistant. How can I help you today?");
            }
        }
    }
    
    updateCharCounter() {
        const currentLength = this.chatInput.value.length;
        this.charCounter.textContent = `${currentLength}/500`;
        
        // Visual feedback as approaching limit
        if (currentLength > 400) {
            this.charCounter.classList.add('near-limit');
        } else {
            this.charCounter.classList.remove('near-limit');
        }
    }

    loadThemePreference() {
        const savedDarkMode = localStorage.getItem('darkMode');
        if (savedDarkMode !== null) {
            this.isDarkMode = savedDarkMode === 'true';
            if (this.isDarkMode) {
                document.body.setAttribute('data-theme', 'dark');
                this.themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            }
        }
    }
}

// Initialize chat manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ChatManager();
});
