<?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Your Mental Health Journey<br>Starts Here</h1>
                <p>Track your moods, journal your thoughts, and get the support you need with MindMate's comprehensive mental health tools</p>
                <div class="hero-buttons">
                    <a href="auth/login.php" class="btn-primary">Get Started</a>
                    <button class="btn-outline" onclick="scrollToFeatures()">Learn More</button>
                </div>
            </div>
            <div class="hero-image">
                <img src="images/mindfulness-illustration.svg" alt="Mental health and mindfulness illustration">
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section class="features" id="features">
        <h2>Features to Support Your Journey</h2>
        <p>Everything you need to maintain and improve your mental well-being</p>
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-chart-line"></i>
                <h3>Mood Tracking</h3>
                <p>Monitor your emotional well-being and identify patterns in your mood over time</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-book"></i>
                <h3>Journaling</h3>
                <p>Express your thoughts and feelings in a private, secure digital journal</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-heart"></i>
                <h3>Wellness Exercises</h3>
                <p>Access guided meditation, breathing exercises, and mindfulness activities</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-comments"></i>
                <h3>AI Chat Support</h3>
                <p>Get 24/7 support and guidance from our AI-powered chat companion</p>
            </div>
        </div>
    </section>

<script>
function scrollToFeatures() {
    document.getElementById('features').scrollIntoView({
        behavior: 'smooth'
    });
}
</script>

<?php include 'includes/footer.php'; ?>
