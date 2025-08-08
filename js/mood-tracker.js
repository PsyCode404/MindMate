class MoodTracker {
    constructor() {
        this.currentMood = null;
        this.moodValues = {
            'amazing': 5,
            'good': 4,
            'okay': 3,
            'down': 2,
            'rough': 1
        };
        this.chart = null;
        this.currentPeriod = 'week';
        // Use relative path for API endpoint to work from pages directory
        this.apiUrl = '../api/mood.php';
        
        this.initializeElements();
        this.setupEventListeners();
        this.initializeChart();
        this.loadTheme();
        this.updateCurrentDate();
        this.loadMoodEntries();
    }

    initializeElements() {
        this.moodIcons = document.querySelectorAll('.mood-icon');
        this.reflectionInput = document.getElementById('reflection-input');
        this.charCount = document.querySelector('.char-count');
        this.saveButton = document.getElementById('save-mood');
        this.timeFilters = document.querySelectorAll('.time-filter');
        this.themeToggle = document.getElementById('theme-toggle');
        this.currentDateElement = document.querySelector('.current-date h2');
    }

    setupEventListeners() {
        this.moodIcons.forEach(icon => {
            icon.addEventListener('click', () => this.selectMood(icon));
        });

        this.reflectionInput.addEventListener('input', () => this.updateCharCount());
        this.saveButton.addEventListener('click', () => this.saveMoodEntry());
        
        this.timeFilters.forEach(filter => {
            filter.addEventListener('click', () => this.changePeriod(filter));
        });

        this.themeToggle.addEventListener('click', () => this.toggleTheme());
    }

    selectMood(selectedIcon) {
        this.moodIcons.forEach(icon => icon.classList.remove('selected'));
        selectedIcon.classList.add('selected');
        this.currentMood = selectedIcon.dataset.mood;
        this.updateSaveButton();
    }

    updateCharCount() {
        const count = this.reflectionInput.value.length;
        this.charCount.textContent = `${count}/500`;
        this.updateSaveButton();
    }

    updateSaveButton() {
        this.saveButton.disabled = !this.currentMood || !this.reflectionInput.value.trim();
    }

    updateCurrentDate() {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const today = new Date().toLocaleDateString('en-US', options);
        this.currentDateElement.textContent = today;
    }

    async saveMoodEntry() {
        if (!this.currentMood || !this.reflectionInput.value.trim()) return;

        const entry = {
            mood: this.currentMood,
            moodValue: this.moodValues[this.currentMood],
            reflection: this.reflectionInput.value.trim()
        };

        try {
            // Show loading state
            const saveButton = this.saveButton;
            saveButton.textContent = 'Saving...';
            saveButton.disabled = true;
            
            // Send data to the server
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(entry)
            });
            
            if (!response.ok) {
                throw new Error('Failed to save mood entry');
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error || 'Unknown error occurred');
            }
            
            // Reset form
            this.moodIcons.forEach(icon => icon.classList.remove('selected'));
            this.reflectionInput.value = '';
            this.charCount.textContent = '0/500';
            this.currentMood = null;
            
            // Update UI
            await this.updateChart();
            await this.updateMoodHistory();
            
            // Show success animation
            saveButton.textContent = 'Saved!';
            saveButton.style.backgroundColor = '#2ecc71';
            setTimeout(() => {
                saveButton.textContent = 'Save Today\'s Entry';
                saveButton.style.backgroundColor = '';
                saveButton.disabled = true;
            }, 2000);
            
        } catch (error) {
            console.error('Error saving mood entry:', error);
            
            // Show error state
            this.saveButton.textContent = 'Error! Try Again';
            this.saveButton.style.backgroundColor = '#e74c3c';
            setTimeout(() => {
                this.saveButton.textContent = 'Save Today\'s Entry';
                this.saveButton.style.backgroundColor = '';
                this.saveButton.disabled = false;
            }, 2000);
        }
    }

    initializeChart() {
        const ctx = document.getElementById('moodChart').getContext('2d');
        
        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Mood',
                    data: [],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#3498db',
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 1,
                        max: 5,
                        grid: {
                            color: getComputedStyle(document.documentElement)
                                .getPropertyValue('--chart-grid')
                        },
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                const labels = {
                                    1: 'Rough',
                                    2: 'Down',
                                    3: 'Okay',
                                    4: 'Good',
                                    5: 'Amazing'
                                };
                                return labels[value];
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: getComputedStyle(document.documentElement)
                                .getPropertyValue('--chart-grid')
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const labels = {
                                    1: 'Rough',
                                    2: 'Down',
                                    3: 'Okay',
                                    4: 'Good',
                                    5: 'Amazing'
                                };
                                return `Mood: ${labels[context.parsed.y]}`;
                            }
                        }
                    }
                }
            }
        });

        this.updateChart();
    }

    async updateChart() {
        try {
            const period = this.currentPeriod;
            
            // Fetch data from the server
            const response = await fetch(`${this.apiUrl}?period=${period}`);
            
            if (!response.ok) {
                throw new Error('Failed to fetch mood data');
            }
            
            const result = await response.json();
            const entries = result.entries || [];
            
            // Sort entries by date (oldest first)
            const sortedEntries = entries.sort((a, b) => 
                new Date(a.logged_at) - new Date(b.logged_at)
            );
            
            const labels = sortedEntries.map(entry => 
                new Date(entry.logged_at).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                })
            );
            
            const data = sortedEntries.map(entry => entry.mood_level);
            
            this.chart.data.labels = labels;
            this.chart.data.datasets[0].data = data;
            this.chart.update();
            
        } catch (error) {
            console.error('Error updating chart:', error);
        }
    }

    async updateMoodHistory() {
        try {
            const historyContainer = document.querySelector('.history-entries');
            
            // Fetch recent entries from the server
            const response = await fetch(`${this.apiUrl}?limit=5`);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server response:', errorText);
                throw new Error(`Failed to fetch mood history: ${response.status} ${response.statusText}`);
            }
            
            const result = await response.json();
            console.log('Mood history response:', result); // Debug log
            
            if (result.error) {
                console.error('API Error:', result.error);
                throw new Error(result.error);
            }
            
            const entries = result.entries || [];
            
            if (entries.length === 0) {
                historyContainer.innerHTML = `
                    <div class="empty-history">
                        <p>No mood entries yet. Start tracking your mood today!</p>
                    </div>
                `;
                return;
            }
            
            const moodIcons = {
                'amazing': 'laugh-beam',
                'good': 'smile',
                'okay': 'meh',
                'down': 'frown',
                'rough': 'sad-tear'
            };
            
            historyContainer.innerHTML = entries.map(entry => {
                const date = new Date(entry.logged_at).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
                
                return `
                    <div class="history-entry">
                        <i class="fas fa-${moodIcons[entry.mood]}"></i>
                        <div class="entry-content">
                            <div class="entry-date">${date}</div>
                            <div class="entry-reflection">${entry.notes || ''}</div>
                        </div>
                    </div>
                `;
            }).join('');
            
        } catch (error) {
            console.error('Error updating mood history:', error);
            
            const historyContainer = document.querySelector('.history-entries');
            historyContainer.innerHTML = `
                <div class="error-message">
                    <p>Could not load mood history. Please try again later.</p>
                </div>
            `;
        }
    }

    changePeriod(selectedFilter) {
        this.timeFilters.forEach(filter => filter.classList.remove('active'));
        selectedFilter.classList.add('active');
        this.currentPeriod = selectedFilter.dataset.period;
        this.updateChart();
    }
    
    async loadMoodEntries() {
        try {
            await this.updateMoodHistory();
            await this.updateChart();
            
            // Check if there's a mood entry for today
            const today = new Date();
            const todayStr = today.toISOString().split('T')[0]; // Format: YYYY-MM-DD
            
            // Fetch all entries - we need to check for today's entry
            const response = await fetch(this.apiUrl);
            
            if (!response.ok) {
                throw new Error('Failed to check today\'s entry');
            }
            
            const result = await response.json();
            const entries = result.entries || [];
            
            // Filter for entries from today
            const todayEntries = entries.filter(entry => {
                const entryDate = new Date(entry.logged_at);
                return entryDate.toISOString().split('T')[0] === todayStr;
            });
            
            console.log('Today\'s entries:', todayEntries);
            
            // If there's an entry for today, disable the form or show edit mode
            if (todayEntries.length > 0) {
                const todayEntry = todayEntries[0];
                
                // You could implement edit functionality here
                // For now, just inform the user they already submitted today
                this.reflectionInput.value = todayEntry.notes || '';
                this.updateCharCount();
                
                // Select the mood icon
                const moodName = todayEntry.mood;
                this.moodIcons.forEach(icon => {
                    if (icon.dataset.mood === moodName) {
                        icon.classList.add('selected');
                        this.currentMood = moodName;
                    }
                });
                
                this.saveButton.textContent = 'Entry for today already saved';
                this.saveButton.disabled = true;
            }
            
        } catch (error) {
            console.error('Error loading mood entries:', error);
        }
    }

    loadTheme() {
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        document.body.classList.toggle('dark-mode', isDarkMode);
        this.themeToggle.innerHTML = isDarkMode ? 
            '<i class="fas fa-sun"></i>' : 
            '<i class="fas fa-moon"></i>';
    }

    toggleTheme() {
        const isDarkMode = document.body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', isDarkMode);
        this.themeToggle.innerHTML = isDarkMode ? 
            '<i class="fas fa-sun"></i>' : 
            '<i class="fas fa-moon"></i>';
        
        // Update chart colors
        if (this.chart) {
            this.chart.options.scales.y.grid.color = 
            this.chart.options.scales.x.grid.color = 
                getComputedStyle(document.documentElement)
                    .getPropertyValue('--chart-grid');
            this.chart.update();
        }
    }
}

// Initialize the mood tracker when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new MoodTracker();
});
