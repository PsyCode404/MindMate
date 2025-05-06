<?php 
$page_title = "MindMate Journal - Express Your Thoughts";
$additional_css = '<link rel="stylesheet" href="../css/journal.css">';
include '../includes/header.php'; 
?>

<!-- Journal Interface -->
<main class="journal-container">
    <!-- Entries List -->
    <aside class="entries-sidebar">
        <div class="entries-header">
            <h2>Your Journal</h2>
            <button id="newEntryBtn" class="btn-primary new-entry-btn">
                <i class="fas fa-plus"></i> New Entry
            </button>
        </div>
        <div class="entries-search">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="searchEntries" placeholder="Search entries...">
            </div>
        </div>
        <div class="entries-list" id="entriesList">
            <!-- Entries will be dynamically added here -->
        </div>
    </aside>

    <!-- Journal Editor -->
    <section class="journal-editor">
        <div class="editor-header">
            <div class="editor-date" id="entryDate">
                <i class="far fa-calendar-alt"></i>
                <span>Today, April 26, 2025</span>
            </div>
            <div class="mood-selector">
                <label for="moodSelect">How are you feeling?</label>
                <select id="moodSelect">
                    <?php
                    $moods = [
                        'happy' => '😊 Happy',
                        'calm' => '😌 Calm',
                        'sad' => '😢 Sad',
                        'anxious' => '😰 Anxious',
                        'angry' => '😠 Angry',
                        'neutral' => '😐 Neutral'
                    ];

                    foreach($moods as $value => $label): ?>
                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="entry-actions">
                <button id="saveBtn" class="btn-primary save-btn" title="Save Entry">
                    <i class="fas fa-save"></i> <span>Save</span>
                </button>
                <button id="deleteBtn" class="btn-outline delete-btn" title="Delete Entry">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="editor-content">
            <input type="text" id="entryTitle" class="entry-title" placeholder="Entry Title">
            <textarea id="entryContent" class="entry-content" placeholder="Start writing your thoughts..."></textarea>
        </div>
    </section>

    <!-- AI Insights Panel -->
    <aside class="insights-panel">
        <div class="insights-header">
            <h3>Insights</h3>
            <i class="fas fa-lightbulb"></i>
        </div>
        <div class="insights-content">
            <?php
            $insights = [
                [
                    'title' => 'Mood Pattern',
                    'icon' => 'chart-line',
                    'content' => ' Keep up the great work!'
                ],
                [
                    'title' => 'Writing Suggestion',
                    'icon' => 'pen-fancy',
                    'content' => 'Try exploring what made you feel particularly [mood] today.'
                ],
                [
                    'title' => 'Reflection Prompt',
                    'icon' => 'brain',
                    'content' => 'Consider writing about your goals for tomorrow .'
                ]
            ];

            foreach($insights as $insight): ?>
                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="fas fa-<?php echo $insight['icon']; ?>"></i>
                    </div>
                    <div class="insight-content">
                        <h4><?php echo $insight['title']; ?></h4>
                        <p><?php echo $insight['content']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </aside>
</main>

<!-- Toast Notifications -->
<div class="toast-container" id="toastContainer"></div>

<!-- Journal Loader Script -->
<script src="../js/journal-loader.js"></script>

<!-- Original journal script (commented out for now) -->
<!-- <script src="../js/journal.js"></script> -->

<?php include '../includes/footer.php'; ?>
