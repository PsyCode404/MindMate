// Exercise Manager Class
class ExerciseManager {
    constructor() {
        this.currentExercise = null;
        this.isExerciseActive = false;
        this.timer = null;
        this.remainingTime = 0;
        this.breathingInterval = null;

        // DOM Elements
        this.elements = {
            categoryBtns: document.querySelectorAll('.category-btn'),
            sections: document.querySelectorAll('.exercise-section'),
            startBtns: document.querySelectorAll('.start-exercise'),
            modal: document.getElementById('exerciseModal'),
            modalTitle: document.getElementById('modalTitle'),
            modalBody: document.querySelector('.modal-body'),
            closeModal: document.querySelector('.close-modal'),
            meditationSound: document.getElementById('meditationSound'),
            bellSound: document.getElementById('bellSound')
        };

        this.setupEventListeners();
        this.loadTheme();
    }

    setupEventListeners() {
        // Category switching
        this.elements.categoryBtns.forEach(btn => {
            btn.addEventListener('click', () => this.switchCategory(btn));
        });

        // Start exercise buttons
        this.elements.startBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const card = e.target.closest('.exercise-card');
                if (card) {
                    const exerciseType = card.dataset.exercise;
                    this.startExercise(exerciseType);
                }
            });
        });

        // Close modal
        this.elements.closeModal.addEventListener('click', () => this.closeExercise());
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.closeExercise();
        });

        // Theme toggle
        const themeToggle = document.querySelector('.theme-toggle');
        themeToggle.addEventListener('click', () => this.toggleTheme());
    }

    loadTheme() {
        const darkMode = localStorage.getItem('darkMode') === 'true';
        document.body.classList.toggle('dark-mode', darkMode);
    }

    toggleTheme() {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
    }

    switchCategory(selectedBtn) {
        // Update buttons
        this.elements.categoryBtns.forEach(btn => {
            btn.classList.remove('active');
        });
        selectedBtn.classList.add('active');

        // Update sections with animation
        const category = selectedBtn.dataset.category;
        this.elements.sections.forEach(section => {
            if (section.id === category) {
                section.style.opacity = '0';
                section.classList.add('active');
                setTimeout(() => {
                    section.style.opacity = '1';
                }, 50);
            } else {
                section.classList.remove('active');
            }
        });
    }

    startExercise(exerciseType) {
        this.currentExercise = exerciseType;
        this.elements.modalTitle.textContent = this.getExerciseTitle(exerciseType);
        this.elements.modalBody.innerHTML = this.getExerciseContent(exerciseType);
        this.elements.modal.classList.add('active');
        
        // Initialize exercise-specific functionality
        switch (exerciseType) {
            case 'box-breathing':
            case '4-7-8':
            case 'deep-breathing':
                this.initializeBreathingExercise(exerciseType);
                break;
            case 'body-scan':
            case 'loving-kindness':
            case 'mindful-observation':
                this.initializeMeditation(exerciseType);
                break;
            case 'thought-record':
            case 'behavioral-activation':
            case 'cognitive-restructuring':
                this.initializeCBTExercise(exerciseType);
                break;
        }
    }

    closeExercise() {
        this.isExerciseActive = false;
        if (this.timer) clearInterval(this.timer);
        if (this.breathingInterval) clearInterval(this.breathingInterval);
        this.elements.meditationSound.pause();
        this.elements.meditationSound.currentTime = 0;
        this.elements.modal.classList.remove('active');
        this.currentExercise = null;
    }

    getExerciseTitle(exerciseType) {
        const titles = {
            'box-breathing': 'Box Breathing',
            '4-7-8': '4-7-8 Breathing',
            'deep-breathing': 'Deep Breathing',
            'body-scan': 'Body Scan Meditation',
            'loving-kindness': 'Loving Kindness Meditation',
            'mindful-observation': 'Mindful Observation',
            'thought-record': 'Thought Record',
            'behavioral-activation': 'Behavioral Activation',
            'cognitive-restructuring': 'Cognitive Restructuring'
        };
        return titles[exerciseType] || 'Exercise';
    }

    getExerciseContent(exerciseType) {
        switch (exerciseType) {
            case 'box-breathing':
            case '4-7-8':
            case 'deep-breathing':
                return `
                    <div class="breathing-instructions"></div>
                    <div class="breathing-circle">Breathe</div>
                    <div class="timer-display">5:00</div>
                    <div class="progress-bar">
                        <div class="progress-bar-fill"></div>
                    </div>
                    <div class="exercise-controls">
                        <button class="control-btn start">Start</button>
                        <button class="control-btn pause" style="display: none;">Pause</button>
                    </div>
                `;
            case 'body-scan':
            case 'loving-kindness':
            case 'mindful-observation':
                return `
                    <div class="meditation-instructions"></div>
                    <div class="timer-display">10:00</div>
                    <div class="progress-bar">
                        <div class="progress-bar-fill"></div>
                    </div>
                    <div class="exercise-controls">
                        <button class="control-btn start">Start</button>
                        <button class="control-btn pause" style="display: none;">Pause</button>
                    </div>
                `;
            case 'thought-record':
                return `
                    <form class="cbt-form" id="thoughtRecordForm">
                        <div class="form-group">
                            <label for="situation">Situation</label>
                            <textarea id="situation" rows="3" placeholder="Describe the situation that triggered your thoughts..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="thoughts">Automatic Thoughts</label>
                            <textarea id="thoughts" rows="3" placeholder="What thoughts went through your mind?"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="emotions">Emotions & Intensity</label>
                            <textarea id="emotions" rows="2" placeholder="What emotions did you feel? Rate intensity 0-100%"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="evidence-for">Evidence Supporting</label>
                            <textarea id="evidence-for" rows="3" placeholder="What evidence supports this thought?"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="evidence-against">Evidence Not Supporting</label>
                            <textarea id="evidence-against" rows="3" placeholder="What evidence does not support this thought?"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="balanced-thought">Balanced Perspective</label>
                            <textarea id="balanced-thought" rows="3" placeholder="What's a more balanced way to think about this?"></textarea>
                        </div>
                        <div class="exercise-controls">
                            <button type="button" class="control-btn save">Save Entry</button>
                        </div>
                    </form>
                `;
            case 'behavioral-activation':
                return `
                    <form class="cbt-form" id="behavioralActivationForm">
                        <div class="form-group">
                            <label for="activity">Planned Activity</label>
                            <input type="text" id="activity" placeholder="What activity would you like to do?">
                        </div>
                        <div class="form-group">
                            <label for="when">When?</label>
                            <input type="datetime-local" id="when">
                        </div>
                        <div class="form-group">
                            <label for="difficulty">Difficulty (1-10)</label>
                            <input type="range" id="difficulty" min="1" max="10" value="5">
                            <span id="difficultyValue">5</span>
                        </div>
                        <div class="form-group">
                            <label for="enjoyment">Expected Enjoyment (1-10)</label>
                            <input type="range" id="enjoyment" min="1" max="10" value="5">
                            <span id="enjoymentValue">5</span>
                        </div>
                        <div class="exercise-controls">
                            <button type="button" class="control-btn save">Schedule Activity</button>
                        </div>
                    </form>
                `;
            case 'cognitive-restructuring':
                return `
                    <form class="cbt-form" id="cognitiveRestructuringForm">
                        <div class="form-group">
                            <label for="negative-thought">Negative Thought</label>
                            <textarea id="negative-thought" rows="3" placeholder="What's the negative thought you'd like to work on?"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Thinking Patterns</label>
                            <div class="checkbox-group">
                                <label><input type="checkbox" value="all-or-nothing"> All-or-Nothing Thinking</label>
                                <label><input type="checkbox" value="overgeneralization"> Overgeneralization</label>
                                <label><input type="checkbox" value="catastrophizing"> Catastrophizing</label>
                                <label><input type="checkbox" value="mind-reading"> Mind Reading</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="challenge">Challenge the Thought</label>
                            <textarea id="challenge" rows="3" placeholder="What evidence contradicts this thought?"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="alternative">Alternative Thought</label>
                            <textarea id="alternative" rows="3" placeholder="What's a more realistic way to think about this?"></textarea>
                        </div>
                        <div class="exercise-controls">
                            <button type="button" class="control-btn save">Save Analysis</button>
                        </div>
                    </form>
                `;
            default:
                return '<p>Exercise content coming soon...</p>';
        }
    }

    initializeBreathingExercise(type) {
        const circle = this.elements.modalBody.querySelector('.breathing-circle');
        const instructions = this.elements.modalBody.querySelector('.breathing-instructions');
        const timerDisplay = this.elements.modalBody.querySelector('.timer-display');
        const progressBar = this.elements.modalBody.querySelector('.progress-bar-fill');
        const startBtn = this.elements.modalBody.querySelector('.control-btn.start');
        const pauseBtn = this.elements.modalBody.querySelector('.control-btn.pause');

        let totalTime;
        let breathPattern;

        switch (type) {
            case 'box-breathing':
                totalTime = 300; // 5 minutes
                breathPattern = {
                    inhale: 4,
                    holdIn: 4,
                    exhale: 4,
                    holdOut: 4
                };
                instructions.textContent = 'Inhale for 4, hold for 4, exhale for 4, hold for 4';
                break;
            case '4-7-8':
                totalTime = 420; // 7 minutes
                breathPattern = {
                    inhale: 4,
                    holdIn: 7,
                    exhale: 8,
                    holdOut: 0
                };
                instructions.textContent = 'Inhale for 4, hold for 7, exhale for 8';
                break;
            case 'deep-breathing':
                totalTime = 180; // 3 minutes
                breathPattern = {
                    inhale: 5,
                    holdIn: 2,
                    exhale: 5,
                    holdOut: 0
                };
                instructions.textContent = 'Inhale deeply for 5, hold for 2, exhale fully for 5';
                break;
        }

        let isPaused = false;
        this.remainingTime = totalTime;

        const updateTimer = () => {
            const minutes = Math.floor(this.remainingTime / 60);
            const seconds = this.remainingTime % 60;
            timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            progressBar.style.width = `${(this.remainingTime / totalTime) * 100}%`;
        };

        const breathingCycle = async () => {
            // Inhale
            circle.textContent = 'Inhale';
            circle.classList.add('inhale');
            await this.wait(breathPattern.inhale * 1000);
            
            // Hold after inhale
            if (breathPattern.holdIn > 0) {
                circle.textContent = 'Hold';
                circle.classList.add('hold');
                await this.wait(breathPattern.holdIn * 1000);
            }
            
            // Exhale
            circle.textContent = 'Exhale';
            circle.classList.remove('inhale', 'hold');
            circle.classList.add('exhale');
            await this.wait(breathPattern.exhale * 1000);
            
            // Hold after exhale
            if (breathPattern.holdOut > 0) {
                circle.textContent = 'Hold';
                circle.classList.add('hold');
                await this.wait(breathPattern.holdOut * 1000);
            }
            
            circle.classList.remove('exhale', 'hold');
        };

        startBtn.addEventListener('click', () => {
            if (!this.isExerciseActive) {
                this.isExerciseActive = true;
                startBtn.style.display = 'none';
                pauseBtn.style.display = 'block';

                // Start breathing cycle
                breathingCycle();
                this.breathingInterval = setInterval(() => {
                    if (!isPaused) breathingCycle();
                }, (breathPattern.inhale + breathPattern.holdIn + breathPattern.exhale + breathPattern.holdOut) * 1000);

                // Start timer
                this.timer = setInterval(() => {
                    if (!isPaused && this.remainingTime > 0) {
                        this.remainingTime--;
                        updateTimer();
                        if (this.remainingTime === 0) {
                            this.closeExercise();
                        }
                    }
                }, 1000);
            }
        });

        pauseBtn.addEventListener('click', () => {
            isPaused = !isPaused;
            pauseBtn.textContent = isPaused ? 'Resume' : 'Pause';
            circle.style.animationPlayState = isPaused ? 'paused' : 'running';
        });

        updateTimer();
    }

    initializeMeditation(type) {
        const instructions = this.elements.modalBody.querySelector('.meditation-instructions');
        const timerDisplay = this.elements.modalBody.querySelector('.timer-display');
        const progressBar = this.elements.modalBody.querySelector('.progress-bar-fill');
        const startBtn = this.elements.modalBody.querySelector('.control-btn.start');
        const pauseBtn = this.elements.modalBody.querySelector('.control-btn.pause');

        let totalTime;
        let meditationInstructions;

        switch (type) {
            case 'body-scan':
                totalTime = 600; // 10 minutes
                meditationInstructions = "Find a comfortable position. We will gradually scan through your body, releasing tension and promoting relaxation.";
                break;
            case 'loving-kindness':
                totalTime = 900; // 15 minutes
                meditationInstructions = "Focus on feelings of love and compassion, first for yourself, then for others.";
                break;
            case 'mindful-observation':
                totalTime = 480; // 8 minutes
                meditationInstructions = "Choose an object to focus on. Observe its details mindfully, returning your attention whenever it wanders.";
                break;
        }

        instructions.textContent = meditationInstructions;
        this.remainingTime = totalTime;
        let isPaused = false;

        const updateTimer = () => {
            const minutes = Math.floor(this.remainingTime / 60);
            const seconds = this.remainingTime % 60;
            timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            progressBar.style.width = `${(this.remainingTime / totalTime) * 100}%`;
        };

        startBtn.addEventListener('click', () => {
            if (!this.isExerciseActive) {
                this.isExerciseActive = true;
                startBtn.style.display = 'none';
                pauseBtn.style.display = 'block';
                
                // Play meditation sound
                this.elements.meditationSound.play();
                
                // Start timer
                this.timer = setInterval(() => {
                    if (!isPaused && this.remainingTime > 0) {
                        this.remainingTime--;
                        updateTimer();
                        if (this.remainingTime === 0) {
                            this.elements.bellSound.play();
                            setTimeout(() => this.closeExercise(), 3000);
                        }
                    }
                }, 1000);
            }
        });

        pauseBtn.addEventListener('click', () => {
            isPaused = !isPaused;
            pauseBtn.textContent = isPaused ? 'Resume' : 'Pause';
            if (isPaused) {
                this.elements.meditationSound.pause();
            } else {
                this.elements.meditationSound.play();
            }
        });

        updateTimer();
    }

    initializeCBTExercise(type) {
        const form = this.elements.modalBody.querySelector('.cbt-form');
        const saveBtn = form.querySelector('.control-btn.save');

        // Handle range input updates for behavioral activation
        if (type === 'behavioral-activation') {
            const difficultyInput = form.querySelector('#difficulty');
            const enjoymentInput = form.querySelector('#enjoyment');
            const difficultyValue = form.querySelector('#difficultyValue');
            const enjoymentValue = form.querySelector('#enjoymentValue');

            difficultyInput.addEventListener('input', () => {
                difficultyValue.textContent = difficultyInput.value;
            });

            enjoymentInput.addEventListener('input', () => {
                enjoymentValue.textContent = enjoymentInput.value;
            });
        }

        saveBtn.addEventListener('click', () => {
            const formData = {};
            form.querySelectorAll('input, textarea').forEach(input => {
                if (input.type === 'checkbox') {
                    if (input.checked) {
                        formData[input.value] = true;
                    }
                } else {
                    formData[input.id] = input.value;
                }
            });

            // Save to local storage
            const savedEntries = JSON.parse(localStorage.getItem(`${type}-entries`) || '[]');
            savedEntries.push({
                ...formData,
                timestamp: new Date().toISOString()
            });
            localStorage.setItem(`${type}-entries`, JSON.stringify(savedEntries));

            // Show success message
            const message = document.createElement('div');
            message.className = 'success-message';
            message.textContent = 'Entry saved successfully!';
            form.appendChild(message);
            setTimeout(() => {
                message.remove();
                this.closeExercise();
            }, 2000);
        });
    }

    async wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Initialize Exercise Manager
document.addEventListener('DOMContentLoaded', () => {
    window.exerciseManager = new ExerciseManager();
    
    // Direct fix for meditation button
    const meditationBtn = document.querySelector('button[data-category="meditation-exercises"]');
    if (meditationBtn) {
        console.log('Found meditation button, adding direct event listener');
        meditationBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Remove active class from all category buttons
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to meditation button
            this.classList.add('active');
            
            // Hide all sections
            document.querySelectorAll('.exercise-section').forEach(section => {
                section.classList.remove('active');
                section.style.display = 'none';
            });
            
            // Show meditation section
            const meditationSection = document.getElementById('meditation-exercises');
            if (meditationSection) {
                meditationSection.classList.add('active');
                meditationSection.style.display = 'block';
                setTimeout(() => {
                    meditationSection.style.opacity = '1';
                }, 50);
            } else {
                console.error('Meditation section not found!');
            }
        });
    } else {
        console.error('Meditation button not found!');
    }
});
