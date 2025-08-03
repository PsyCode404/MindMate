// Simple script to load journal entries directly from the database
document.addEventListener('DOMContentLoaded', function() {
    console.log('Journal loader script initialized');
    
    // Get the entries list element
    const entriesList = document.getElementById('entriesList');
    if (!entriesList) {
        console.error('Could not find entriesList element');
        return;
    }
    
    // Track the currently selected entry
    let currentEntry = null;
    
    // Function to format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }
    
    // Function to create an entry element
    function createEntryElement(entry) {
        const div = document.createElement('div');
        div.className = 'entry-item';
        div.innerHTML = `
            <div class="entry-item-header">
                <span class="entry-item-title">${entry.title || 'Journal Entry'}</span>
                <span class="entry-item-date">${formatDate(entry.created_at)}</span>
            </div>
            <p class="entry-item-preview">${entry.content.substring(0, 100)}${entry.content.length > 100 ? '...' : ''}</p>
        `;
        
        // Add click handler to load the entry content
        div.addEventListener('click', function() {
            // Get the entry content and title elements
            const entryTitle = document.getElementById('entryTitle');
            const entryContent = document.getElementById('entryContent');
            const moodSelect = document.getElementById('moodSelect');
            const deleteBtn = document.getElementById('deleteBtn');
            
            // Remove active class from all entries
            const entryItems = document.querySelectorAll('.entry-item');
            entryItems.forEach(item => item.classList.remove('active'));
            
            // Add active class to this entry
            div.classList.add('active');
            
            // Store the current entry for delete functionality
            currentEntry = entry;
            
            if (entryTitle && entryContent) {
                entryTitle.value = entry.title || 'Journal Entry';
                entryContent.value = entry.content || '';
                
                if (moodSelect) {
                    moodSelect.value = entry.mood || 'neutral';
                }
                
                // Enable delete button
                if (deleteBtn) {
                    deleteBtn.removeAttribute('disabled');
                }
                
                // Highlight the selected entry
                document.querySelectorAll('.entry-item').forEach(item => {
                    item.classList.remove('active');
                });
                div.classList.add('active');
            }
        });
        
        return div;
    }
    
    // Function to display a message when no entries are found
    function showEmptyMessage() {
        entriesList.innerHTML = '';
        const emptyMessage = document.createElement('div');
        emptyMessage.className = 'empty-entries-message';
        emptyMessage.textContent = 'No journal entries yet. Create your first entry!';
        entriesList.appendChild(emptyMessage);
    }
    
    // Function to load entries from the API
    function loadEntries() {
        console.log('Loading journal entries from API...');
        
        // Show loading message
        entriesList.innerHTML = '<div class="loading-message">Loading entries...</div>';
        
        // Fetch entries from the API
        fetch('../api/journal.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(text => {
                console.log('Raw API response:', text);
                return JSON.parse(text);
            })
            .then(data => {
                console.log('Parsed API response:', data);
                
                if (data.entries && Array.isArray(data.entries) && data.entries.length > 0) {
                    console.log(`Found ${data.entries.length} entries`);
                    
                    // Clear the entries list
                    entriesList.innerHTML = '';
                    
                    // Add each entry to the list
                    data.entries.forEach(entry => {
                        const entryElement = createEntryElement(entry);
                        entriesList.appendChild(entryElement);
                    });
                } else {
                    console.log('No entries found');
                    showEmptyMessage();
                }
            })
            .catch(error => {
                console.error('Error loading entries:', error);
                showEmptyMessage();
            });
    }
    
    // Load entries when the page loads
    loadEntries();
    
    // Handle save button click
    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the entry content and title elements
            const entryTitle = document.getElementById('entryTitle');
            const entryContent = document.getElementById('entryContent');
            const moodSelect = document.getElementById('moodSelect');
            
            if (entryTitle && entryContent) {
                const title = entryTitle.value.trim();
                const content = entryContent.value.trim();
                const mood = moodSelect ? moodSelect.value : 'neutral';
                
                if (content) {
                    // Determine if we're updating an existing entry or creating a new one
                    const isUpdate = currentEntry !== null;
                    const entryId = isUpdate ? currentEntry.id : Math.random().toString(36).substring(2, 15);
                    
                    // Save the entry to the database
                    fetch('../api/journal.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: entryId,
                            title: title || 'Journal Entry',
                            content: content,
                            mood: mood,
                            update: isUpdate
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Entry saved:', data);
                        
                        // If it's a new entry, clear the form
                        if (!isUpdate) {
                            entryTitle.value = '';
                            entryContent.value = '';
                            currentEntry = null;
                        } else {
                            // Update the current entry with new values
                            currentEntry.title = title || 'Journal Entry';
                            currentEntry.content = content;
                            currentEntry.mood = mood;
                        }
                        
                        // Reload the entries
                        loadEntries();
                        
                        // Show success message
                        const toastContainer = document.getElementById('toastContainer');
                        if (toastContainer) {
                            const toast = document.createElement('div');
                            toast.className = 'toast success';
                            toast.innerHTML = `
                                <i class="fas fa-check-circle"></i>
                                <span>Entry ${isUpdate ? 'updated' : 'saved'} successfully</span>
                            `;
                            toastContainer.appendChild(toast);
                            
                            setTimeout(() => {
                                toast.style.opacity = '0';
                                setTimeout(() => toast.remove(), 300);
                            }, 3000);
                        }
                    })
                    .catch(error => {
                        console.error('Error saving entry:', error);
                        
                        // Show error message
                        const toastContainer = document.getElementById('toastContainer');
                        if (toastContainer) {
                            const toast = document.createElement('div');
                            toast.className = 'toast error';
                            toast.innerHTML = `
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Error saving entry</span>
                            `;
                            toastContainer.appendChild(toast);
                            
                            setTimeout(() => {
                                toast.style.opacity = '0';
                                setTimeout(() => toast.remove(), 300);
                            }, 3000);
                        }
                    });
                }
            }
        });
    }
    
    // Handle delete button click
    const deleteBtn = document.getElementById('deleteBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!currentEntry) {
                return;
            }
            
            // Delete the entry without confirmation
            console.log('Deleting entry:', currentEntry.id);
            
            // Send delete request to the API
            fetch('../api/journal.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: currentEntry.id
                })
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Entry deleted:', data);
                    
                    // Clear the form
                    const entryTitle = document.getElementById('entryTitle');
                    const entryContent = document.getElementById('entryContent');
                    if (entryTitle && entryContent) {
                        entryTitle.value = '';
                        entryContent.value = '';
                    }
                    
                    // Disable delete button
                    deleteBtn.setAttribute('disabled', 'disabled');
                    
                    // Reset current entry
                    currentEntry = null;
                    
                    // Reload the entries
                    loadEntries();
                    
                    // Show success message
                    const toastContainer = document.getElementById('toastContainer');
                    if (toastContainer) {
                        const toast = document.createElement('div');
                        toast.className = 'toast success';
                        toast.innerHTML = `
                            <i class="fas fa-check-circle"></i>
                            <span>Entry deleted successfully</span>
                        `;
                        toastContainer.appendChild(toast);
                        
                        setTimeout(() => {
                            toast.style.opacity = '0';
                            setTimeout(() => toast.remove(), 300);
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error deleting entry:', error);
                    
                    // Show error message
                    const toastContainer = document.getElementById('toastContainer');
                    if (toastContainer) {
                        const toast = document.createElement('div');
                        toast.className = 'toast error';
                        toast.innerHTML = `
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Error deleting entry</span>
                        `;
                        toastContainer.appendChild(toast);
                        
                        setTimeout(() => {
                            toast.style.opacity = '0';
                            setTimeout(() => toast.remove(), 300);
                        }, 3000);
                    }
                });
        });
    }
    
    // Handle new entry button click
    const newEntryBtn = document.getElementById('newEntryBtn');
    if (newEntryBtn) {
        newEntryBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Clear the current entry
            currentEntry = null;
            
            // Clear the form
            const entryTitle = document.getElementById('entryTitle');
            const entryContent = document.getElementById('entryContent');
            const moodSelect = document.getElementById('moodSelect');
            
            if (entryTitle && entryContent) {
                entryTitle.value = '';
                entryContent.value = '';
                if (moodSelect) {
                    moodSelect.value = 'neutral';
                }
                
                // Remove active class from all entries
                const entryItems = document.querySelectorAll('.entry-item');
                entryItems.forEach(item => item.classList.remove('active'));
                
                // Focus on the title field
                entryTitle.focus();
            }
        });
    }
});
