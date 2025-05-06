// Journal Entry Class
class JournalEntry {
    constructor(id, title, content, mood, timestamp) {
        this.id = id || Math.random().toString(36).substring(2, 15);
        this.title = title || 'Journal Entry';
        this.content = content || '';
        this.mood = mood || 'neutral';
        this.timestamp = timestamp || new Date().toISOString();
    }
}

// Journal Manager Class
class JournalManager {
    constructor() {
        console.log('Initializing Journal Manager');
        this.entries = [];
        this.currentEntry = null;
        this.autoSaveTimeout = null;
        
        // DOM Elements
        this.elements = {
            entriesList: document.getElementById('entriesList'),
            entryTitle: document.getElementById('entryTitle'),
            entryContent: document.getElementById('entryContent'),
            moodSelect: document.getElementById('moodSelect'),
            deleteBtn: document.getElementById('deleteBtn'),
            newEntryBtn: document.getElementById('newEntryBtn'),
            journalEditor: document.querySelector('.journal-editor'),
            themeToggle: document.querySelector('.theme-toggle'),
            toastContainer: document.getElementById('toastContainer')
        };

        // Verify all elements are found and log any missing elements
        Object.entries(this.elements).forEach(([key, element]) => {
            if (!element) {
                console.error(`Missing element: ${key}`);
            }
        });

        // Initialize the application
        this.initializeApp();
    }
    
    // Initialize the application
    async initializeApp() {
        console.log('Initializing journal application...');
        
        // Set up theme and event listeners
        this.initializeTheme();
        this.setupEventListeners();
        
        // Clear input fields to ensure they're blank
        this.clearInputFields();
        
        // Load existing entries from database/localStorage
        try {
            await this.loadEntriesFromAPI();
            console.log(`Loaded ${this.entries.length} entries from API`);
        } catch (error) {
            console.error('Error loading entries from API:', error);
            // Fallback to localStorage
            this.loadEntriesFromLocalStorage();
        }
        
        // Ensure the UI is updated
        this.renderEntries();
        
        console.log('Journal initialization complete');
    }
    
    // Clear input fields
    clearInputFields() {
        console.log('Clearing input fields');
        if (this.elements.entryContent) {
            this.elements.entryContent.value = '';
        }
        if (this.elements.entryTitle) {
            this.elements.entryTitle.value = '';
        }
    }

    // Event Listeners
    setupEventListeners() {
        console.log('Setting up event listeners');
        
        // New Entry Button Handler
        this.elements.newEntryBtn.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent any default behavior
            console.log('New entry button clicked');
            
            // Get the current content from the input fields
            const currentTitle = this.elements.entryTitle.value.trim();
            const currentContent = this.elements.entryContent.value.trim();
            
            // If there's content in the current form, save it as an entry
            if (currentContent !== '') {
                console.log('Content found, saving as entry');
                this.saveNewEntry(currentTitle, currentContent);
            } else {
                console.log('No content to save, just clearing fields');
                this.clearInputFields();
            }
        });

        // Delete Entry
        this.elements.deleteBtn.addEventListener('click', () => {
            console.log('Delete button clicked');
            this.deleteCurrentEntry();
        });

        // Auto-save on input
        ['input', 'change'].forEach(event => {
            this.elements.entryTitle.addEventListener(event, () => {
                console.log('Title changed, starting auto-save');
                this.startAutoSave();
            });
            
            this.elements.entryContent.addEventListener(event, () => {
                console.log('Content changed, starting auto-save');
                this.startAutoSave();
            });
        });
        
        // Mood selection
        this.elements.moodSelect.addEventListener('change', (e) => {
            if (this.currentEntry) {
                console.log(`Mood changed to: ${e.target.value}`);
                this.currentEntry.mood = e.target.value;
                this.updateMoodTheme(e.target.value);
            }
        });

        // Theme toggle
        this.elements.themeToggle.addEventListener('click', () => this.toggleTheme());
    }

    // Save a new entry
    async saveNewEntry(title, content) {
        // Create a client-side ID for the entry
        const clientId = Math.random().toString(36).substring(2, 15);
        const currentMood = this.elements.moodSelect.value || 'neutral';
        
        // Create a new entry object
        const entry = new JournalEntry(
            clientId,
            title || 'Journal Entry',
            content,
            currentMood,
            new Date().toISOString()
        );
        
        // Add to beginning of entries array (local)
        this.entries.unshift(entry);
        
        // Save to localStorage as backup
        this.saveEntriesToLocalStorage();
        
        // Update the UI
        this.renderEntries();
        
        // Clear the form for a new entry
        this.clearInputFields();
        
        // Set focus to the title field
        this.elements.entryTitle.focus();
        
        // Show temporary success message
        this.showToast('Saving entry...', 'success');
        
        // Save to database via API
        try {
            console.log('Sending entry to API:', {
                id: clientId,
                title: entry.title,
                content: entry.content,
                mood: entry.mood
            });
            
            const response = await fetch('../api/journal.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: clientId, // Client-side ID for syncing
                    title: entry.title,
                    content: entry.content,
                    mood: entry.mood
                })
            });
            
            const responseText = await response.text();
            console.log('Raw API response:', responseText);
            
            let data;
            try {
                // Try to parse the response as JSON
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('Error parsing API response:', parseError);
                throw new Error('Invalid API response format');
            }
            
            if (response.ok) {
                console.log('Entry saved to database:', data);
                
                // Update the entry with the server ID if available
                if (data.entry && data.entry.id) {
                    entry.serverId = data.entry.id;
                    console.log('Updated entry with server ID:', data.entry.id);
                }
                
                this.showToast('Entry saved to database', 'success');
            } else {
                console.error('Failed to save entry to database:', data.error || 'Unknown error');
                this.showToast('Entry saved locally only', 'warning');
            }
        } catch (error) {
            console.error('Error saving entry to database:', error);
            this.showToast('Entry saved locally only', 'warning');
        }
    }

    // Load Entry
    loadEntry(entry) {
        console.log(`Loading entry: ${entry.id}`);
        
        // Set the current entry
        this.currentEntry = entry;
        
        // Update the UI with the entry's content
        this.elements.entryTitle.value = entry.title || 'Journal Entry';
        this.elements.entryContent.value = entry.content || '';
        this.elements.moodSelect.value = entry.mood || 'neutral';
        this.updateMoodTheme(entry.mood);
        
        // Update the entries list to highlight the current entry
        this.renderEntries();
        
        // Show a toast notification
        this.showToast('Entry loaded', 'success');
    }

    // Delete Entry
    deleteCurrentEntry() {
        if (!this.currentEntry) {
            console.log('No entry selected to delete');
            return;
        }
        
        console.log(`Deleting entry: ${this.currentEntry.id}`);
        const index = this.entries.findIndex(e => e.id === this.currentEntry.id);
        if (index !== -1) {
            this.entries.splice(index, 1);
            this.saveEntriesToLocalStorage();
            this.showToast('Entry deleted', 'success');
            
            if (this.entries.length === 0) {
                console.log('No entries left, clearing fields');
                this.clearInputFields();
                this.currentEntry = null;
            } else {
                console.log('Loading first available entry');
                this.loadEntry(this.entries[0]);
            }
            
            // Update the UI
            this.renderEntries();
        }
    }

    // Auto-save functionality
    startAutoSave() {
        if (this.autoSaveTimeout) clearTimeout(this.autoSaveTimeout);
        this.autoSaveTimeout = setTimeout(() => {
            console.log('Auto-saving current entry');
            this.saveCurrentEntry();
        }, 1000);
    }

    saveCurrentEntry() {
        if (!this.currentEntry) {
            console.log('No current entry to save');
            return;
        }

        console.log(`Saving current entry: ${this.currentEntry.id}`);
        this.currentEntry.title = this.elements.entryTitle.value;
        this.currentEntry.content = this.elements.entryContent.value;
        this.currentEntry.timestamp = new Date().toISOString(); // Update timestamp on save
        this.saveEntriesToLocalStorage();
        this.renderEntries();
        return true; // Return true to indicate successful save
    }

    // API Data Operations
    async loadEntriesFromAPI() {
        console.log('Fetching entries from API...');
        const response = await fetch('../api/journal.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        // Log the raw response for debugging
        const responseText = await response.text();
        console.log('Raw API response:', responseText);
        
        let data;
        try {
            // Try to parse the response as JSON
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Error parsing API response:', parseError);
            throw new Error('Invalid API response format');
        }
        
        if (response.ok && data.entries && Array.isArray(data.entries)) {
            console.log(`Loaded ${data.entries.length} entries from API`);
            
            // Convert API entries to JournalEntry objects
            this.entries = data.entries.map(entry => {
                return new JournalEntry(
                    entry.client_id || entry.id.toString(),
                    entry.title,
                    entry.content,
                    entry.mood || 'neutral',
                    entry.created_at
                );
            });
            
            // Also save to localStorage as backup
            this.saveEntriesToLocalStorage();
            
            // Update the UI immediately
            this.renderEntries();
        } else {
            console.warn('Failed to load entries from API or no entries returned');
            if (data.error) {
                console.error('API error:', data.error);
            }
            throw new Error('Failed to load entries from API');
        }
    }

    // Local Storage Operations
    loadEntriesFromLocalStorage() {
        console.log('Trying to load entries from localStorage...');
        try {
            const savedEntries = localStorage.getItem('journalEntries');
            if (savedEntries) {
                this.entries = JSON.parse(savedEntries).map(entry => {
                    return new JournalEntry(
                        entry.id,
                        entry.title,
                        entry.content,
                        entry.mood,
                        entry.timestamp
                    );
                });
                console.log(`Loaded ${this.entries.length} entries from localStorage`);
                
                // Update the UI immediately
                this.renderEntries();
            } else {
                console.log('No saved entries found in localStorage');
                this.entries = [];
            }
        } catch (error) {
            console.error('Error loading entries from localStorage:', error);
            this.entries = [];
            localStorage.removeItem('journalEntries');
        }
    }

    saveEntriesToLocalStorage() {
        // Save to localStorage as backup
        try {
            console.log(`Saving ${this.entries.length} entries to localStorage`);
            localStorage.setItem('journalEntries', JSON.stringify(this.entries));
        } catch (error) {
            console.error('Error saving entries to localStorage:', error);
        }
    }

    // Render Entries List
    renderEntries() {
        console.log('Rendering entries list, count:', this.entries.length);
        
        // Clear the entries list
        this.elements.entriesList.innerHTML = '';
        
        // Handle empty state
        if (!this.entries || this.entries.length === 0) {
            console.log('No entries to display, showing empty message');
            const emptyMessage = document.createElement('div');
            emptyMessage.className = 'empty-entries-message';
            emptyMessage.textContent = 'No journal entries yet. Create your first entry!';
            this.elements.entriesList.appendChild(emptyMessage);
            return;
        }
        
        // Add each entry to the list
        console.log('Adding entries to the list...');
        this.entries.forEach((entry, index) => {
            console.log(`Rendering entry ${index}:`, entry.id, entry.title);
            try {
                const entryElement = this.createEntryElement(entry);
                this.elements.entriesList.appendChild(entryElement);
            } catch (error) {
                console.error('Error rendering entry:', error, entry);
            }
        });
        
        // Log the entries for debugging
        console.log('Entries rendering complete');
    }

    createEntryElement(entry) {
        const div = document.createElement('div');
        div.className = `entry-item ${this.currentEntry && entry.id === this.currentEntry.id ? 'active' : ''}`;
        div.innerHTML = `
            <div class="entry-item-header">
                <span class="entry-item-title">${entry.title || 'Journal Entry'}</span>
                <span class="entry-item-date">${this.formatDate(entry.timestamp)}</span>
            </div>
            <p class="entry-item-preview">${entry.content.substring(0, 100)}${entry.content.length > 100 ? '...' : ''}</p>
        `;
        
        // Add click handler to load the entry
        div.addEventListener('click', () => {
            console.log(`Clicked entry: ${entry.id}`);
            this.loadEntry(entry);
        });
        
        return div;
    }

    // Theme Management
    initializeTheme() {
        const darkMode = localStorage.getItem('darkMode') === 'true';
        if (darkMode) {
            document.body.classList.add('dark-mode');
            this.elements.themeToggle.querySelector('i').classList.replace('fa-moon', 'fa-sun');
        }
    }

    toggleTheme() {
        const isDarkMode = document.body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', isDarkMode);
        const icon = this.elements.themeToggle.querySelector('i');
        icon.classList.toggle('fa-moon');
        icon.classList.toggle('fa-sun');
    }

    updateMoodTheme(mood) {
        this.elements.journalEditor.dataset.mood = mood;
    }

    // Toast Notifications
    showToast(message, type = 'success') {
        console.log(`Showing toast: ${message} (${type})`);
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        this.elements.toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Utility Functions
    formatDate(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }
}

// Initialize Journal
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded, initializing journal');
    window.journal = new JournalManager();
});
