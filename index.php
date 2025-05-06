<?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Your Mental Health Journey<br>Starts Here</h1>
                <p>Track your moods, journal your thoughts, and get the support you need with MindMate's comprehensive mental health tools</p>
                <div class="hero-buttons">
                    <button class="btn-primary">Get Started</button>
                    <button class="btn-outline">Learn More</button>
                </div>
            </div>
            <div class="hero-image">
                <img src="images/mindfulness-illustration.svg" alt="Mental health and mindfulness illustration">
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section class="features">
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

    <!-- Reviews Section -->
    <section class="reviews">
        <h2>What Our Users Say</h2>
        <p>Join thousands of satisfied users on their mental wellness journey</p>
        <div class="reviews-grid">
            <div class="review-card">
                <div class="review-header">
                    <img src="images/user1.jpg" alt="Sarah M." class="review-avatar">
                    <div class="review-info">
                        <h4>Iyed Gouia</h4>
                        <div class="stars">
                            <?php for($i = 0; $i < 5; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <p>"MindMate has transformed how I manage my mental health. The mood tracking and journaling features have helped me identify patterns I never noticed before."</p>
            </div>
            <div class="review-card">
                <div class="review-header">
                    <img src="images/user2.jpg" alt="James L." class="review-avatar">
                    <div class="review-info">
                        <h4>Mouhib Boubakri</h4>
                        <div class="stars">
                            <?php for($i = 0; $i < 5; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <p>"The AI chat support is incredibly helpful when I need someone to talk to late at night. It's like having a supportive friend available 24/7."</p>
            </div>
            <div class="review-card">
                <div class="review-header">
                    <img src="images/user3.jpg" alt="Emily R." class="review-avatar">
                    <div class="review-info">
                        <h4>Anakin Skywalker</h4>
                        <div class="stars">
                            <?php for($i = 0; $i < 4; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
                <p>"As a family therapist, I recommend MindMate to my clients. The family plan helps parents stay connected with their children's emotional well-being."</p>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing">
        <h2>Choose Your Plan</h2>
        <p>Find the perfect plan for your mental wellness journey</p>
        <div class="pricing-grid">
            <?php
            $plans = [
                [
                    'name' => 'Free',
                    'price' => 0,
                    'features' => [
                        'Basic mood tracking',
                        'Simple journal entries',
                        'Limited exercises',
                        'Community support'
                    ],
                    'button' => ['text' => 'Get Started', 'class' => 'btn-outline']
                ],
                [
                    'name' => 'Premium',
                    'price' => 9.99,
                    'features' => [
                        'Advanced mood analytics',
                        'Unlimited journaling',
                        'Full exercise library',
                        'AI chat support',
                        'Progress reports'
                    ],
                    'popular' => true,
                    'button' => ['text' => 'Start Free Trial', 'class' => 'btn-primary']
                ],
                [
                    'name' => 'Family',
                    'price' => 19.99,
                    'features' => [
                        'Up to 5 family members',
                        'All Premium features',
                        'Family analytics',
                        'Priority support'
                    ],
                    'button' => ['text' => 'Choose Family Plan', 'class' => 'btn-outline']
                ]
            ];

            foreach($plans as $plan): ?>
                <div class="pricing-card<?php echo isset($plan['popular']) ? ' featured' : ''; ?>">
                    <?php if(isset($plan['popular'])): ?>
                        <div class="popular-tag">Most Popular</div>
                    <?php endif; ?>
                    <h3><?php echo $plan['name']; ?></h3>
                    <div class="price">$<?php echo number_format($plan['price'], 2); ?><span>/month</span></div>
                    <ul class="features-list">
                        <?php foreach($plan['features'] as $feature): ?>
                            <li><i class="fas fa-check"></i> <?php echo $feature; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button class="<?php echo $plan['button']['class']; ?>"><?php echo $plan['button']['text']; ?></button>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
