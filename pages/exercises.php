<?php 
$page_title = "MindMate Exercises - Guided Mental Wellness";
$additional_css = '<link rel="stylesheet" href="../css/exercises.css">';
include '../includes/header.php'; 

$exercises = [
    'breathing-exercises' => [
        'title' => 'Breathing Exercises',
        'icon' => 'wind',
        'exercises' => [
            [
                'id' => 'box-breathing',
                'title' => 'Box Breathing',
                'duration' => '5 minutes',
                'description' => 'A powerful technique used by Navy SEALs to reduce stress and improve focus through controlled breathing. Inhale, hold, exhale, and hold, each for 4 seconds.'
            ],
            [
                'id' => '4-7-8',
                'title' => '4-7-8 Breathing',
                'duration' => '7 minutes',
                'description' => 'A relaxing breathing pattern that helps reduce anxiety and promotes better sleep. Inhale for 4, hold for 7, and exhale for 8 seconds.'
            ],
            [
                'id' => 'deep-breathing',
                'title' => 'Deep Breathing',
                'duration' => '3 minutes',
                'description' => 'Simple yet effective deep breathing exercise for instant calm and relaxation. Focus on taking slow, deep breaths from your diaphragm.'
            ]
        ]
    ],
    'meditation-exercises' => [
        'title' => 'Meditation Sessions',
        'icon' => 'om',
        'exercises' => [
            [
                'id' => 'body-scan',
                'title' => 'Body Scan',
                'duration' => '10 minutes',
                'description' => 'A guided meditation that helps you release tension and promote body awareness by systematically focusing attention on different parts of your body.'
            ],
            [
                'id' => 'loving-kindness',
                'title' => 'Loving Kindness',
                'duration' => '15 minutes',
                'description' => 'Cultivate compassion and positive emotions through guided meditation. Direct well-wishes to yourself and others to foster emotional connection.'
            ],
            [
                'id' => 'mindful-observation',
                'title' => 'Mindful Observation',
                'duration' => '8 minutes',
                'description' => 'Practice mindfulness by focusing on the present moment. Choose an object and observe its details with full attention and curiosity.'
            ]
        ]
    ]
];
?>

<!-- Main Content -->
<main class="exercise-container">
    <!-- Introduction Section -->
    <div class="intro-section">
        <h1>Wellness Exercises</h1>
        <p>Discover simple yet powerful exercises to improve your mental wellbeing. Regular practice can help reduce stress, improve focus, and promote emotional balance.</p>
    </div>
    
    <!-- Categories Navigation -->
    <nav class="category-nav">
        <?php foreach($exercises as $category_id => $category): ?>
            <button class="category-btn <?php echo $category_id === 'breathing-exercises' ? 'active' : ''; ?>" data-category="<?php echo $category_id; ?>">
                <i class="fas fa-<?php echo $category['icon']; ?>"></i>
                <?php echo $category['title']; ?>
            </button>
        <?php endforeach; ?>
    </nav>

    <!-- Exercise Content -->
    <div class="exercise-content">
        <?php foreach($exercises as $category_id => $category): ?>
            <section id="<?php echo $category_id; ?>" class="exercise-section <?php echo $category_id === 'breathing-exercises' ? 'active' : ''; ?>">
                <h2><?php echo $category['title']; ?></h2>
                <div class="exercise-grid">
                    <?php foreach($category['exercises'] as $exercise): ?>
                        <div class="exercise-card" data-exercise="<?php echo $exercise['id']; ?>">
                            <div class="exercise-header">
                                <h3><?php echo $exercise['title']; ?></h3>
                                <span class="duration"><?php echo $exercise['duration']; ?></span>
                            </div>
                            <p><?php echo $exercise['description']; ?></p>
                            <button class="start-exercise">Start Exercise</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <!-- Exercise Modal -->
    <div class="exercise-modal" id="exerciseModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Exercise Title</h2>
                <button class="close-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <!-- Exercise content will be dynamically inserted here -->
            </div>
        </div>
    </div>
</main>

<!-- Audio Elements -->
<audio id="meditationSound" loop>
    <source src="../sounds/meditation-background.mp3" type="audio/mpeg">
</audio>
<audio id="bellSound">
    <source src="../sounds/bell.mp3" type="audio/mpeg">
</audio>

<script src="../js/exercises.js"></script>

<?php include '../includes/footer.php'; ?>
