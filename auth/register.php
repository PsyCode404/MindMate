<?php
require_once '../includes/auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Check if user is already logged in
if ($auth->isLoggedIn()) {
    header('Location: /pages/chat.php');
    exit();
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = $auth->register($fullName, $email, $password);
    
    if ($result['success']) {
        header('Location: /pages/chat.php');
        exit();
    } else {
        $error = $result['message'];
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MindMate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/auth.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <a href="/index.php" class="logo">
                <i class="fas fa-brain"></i>
                <span>MindMate</span>
            </a>
            <h1>Create Account</h1>
            <p>Join MindMate today</p>
        </div>

        <form action="/auth/register.php" method="POST">
            <div class="form-group">
                <label for="name">Name</label>
                <div class="input-wrapper">
                    <input type="text" id="name" name="name" required placeholder="Your name">
                    <i class="fas fa-user"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" required placeholder="Your email">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" required 
                           placeholder="Choose password"
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                    <i class="fas fa-eye-slash toggle-password"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm</label>
                <div class="input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Confirm password">
                    <i class="fas fa-eye-slash toggle-password"></i>
                </div>
            </div>

            <div class="form-group">
                <div class="remember-me">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="/terms.php">Terms</a> and <a href="/privacy.php">Privacy Policy</a></label>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <button type="submit" class="btn-auth">Create Account</button>

            <div class="auth-footer">
                <p>Already have an account? <a href="/auth/login.php">Log In</a></p>
            </div>

            <div class="social-login">
                <p>Or sign up with</p>
                <div class="social-buttons">
                    <button type="button" class="social-btn">
                        <i class="fab fa-google"></i> Google
                    </button>
                    <button type="button" class="social-btn">
                        <i class="fab fa-facebook-f"></i> FB
                    </button>
                </div>
            </div>
        </form>


    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility for both password fields
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });

    // Password confirmation validation
    const password = document.querySelector('#password');
    const confirmPassword = document.querySelector('#confirm_password');
    const form = document.querySelector('form');

    form.addEventListener('submit', function(e) {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Passwords do not match!');
            confirmPassword.focus();
        }
    });
});
</script>

</body>
</html>
