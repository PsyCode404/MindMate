<?php 
$page_title = "MindMate Mood Tracker";
$additional_css = '<link rel="stylesheet" href="../css/mood-tracker.css">';
$additional_js = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
include '../includes/header.php'; 
?>

<div class="mood-container">
    <div class="mood-header">
        <div class="header-content">
            <h1>Mood Tracker</h1>
            <p class="subtitle">Track your emotional journey, one day at a time</p>
        </div>
        <button id="theme-toggle" class="theme-toggle">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <div class="mood-content">
        <div class="current-date">
            <h2></h2>
        </div>

        <div class="mood-selection">
            <div class="section-header">
                <h3>How are you feeling today?</h3>
                <div class="mood-date"></div>
            </div>
            <div class="mood-icons-wrapper">
                <div class="mood-icons">
                    <?php
                    $moods = [
                        ['name' => 'amazing', 'icon' => 'laugh-beam', 'label' => 'Amazing'],
                        ['name' => 'good', 'icon' => 'smile', 'label' => 'Good'],
                        ['name' => 'okay', 'icon' => 'meh', 'label' => 'Okay'],
                        ['name' => 'down', 'icon' => 'frown', 'label' => 'Down'],
                        ['name' => 'rough', 'icon' => 'sad-tear', 'label' => 'Rough']
                    ];

                    foreach($moods as $mood): ?>
                        <div class="mood-icon" data-mood="<?php echo $mood['name']; ?>">
                            <div class="icon-wrapper">
                                <i class="fas fa-<?php echo $mood['icon']; ?>"></i>
                            </div>
                            <span><?php echo $mood['label']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="reflection-section">
            <div class="section-header">
                <h3>Daily Reflection</h3>
                <div class="char-count">0/500</div>
            </div>
            <div class="reflection-wrapper">
                <textarea 
                    id="reflection-input" 
                    placeholder="Write a short note about your day and emotions..."
                    maxlength="500"
                ></textarea>
            </div>
        </div>

        <button id="save-mood" class="save-button" disabled>
            Save Today's Entry
        </button>

        <div class="mood-stats">
            <div class="stats-header">
                <div class="stats-title">
                    <h3>Mood Trends</h3>
                    <span class="stats-subtitle">Track your emotional patterns over time</span>
                </div>
                <div class="time-filters">
                    <button class="time-filter active" data-period="week">
                        <i class="fas fa-calendar-week"></i>
                        Week
                    </button>
                    <button class="time-filter" data-period="month">
                        <i class="fas fa-calendar-alt"></i>
                        Month
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="moodChart"></canvas>
            </div>
        </div>

        <div class="mood-history">
            <div class="section-header">
                <h3>Recent Entries</h3>
                <span class="history-subtitle">Your last 5 mood check-ins</span>
            </div>
            <div class="history-entries">
                <!-- Entries will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<script src="../js/mood-tracker.js?v=<?php echo time(); ?>"></script>

<?php include '../includes/footer.php'; ?>
