<?php
require_once '../includes/auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Check if user is already logged in
if ($auth->isLoggedIn()) {
    header('Location: /mindmate/pages/chat.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = $auth->login($email, $password);
    
    if ($result['success']) {
        header('Location: /mindmate/pages/chat.php');
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
    <title>Login - MindMate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/mindmate/css/auth.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <a href="/mindmate/index.php" class="logo">
                <i class="fas fa-brain"></i>
                <span>MindMate</span>
            </a>
            <h1>Welcome Back</h1>
            <p>Sign in to continue</p>
        </div>

        <form action="/mindmate/auth/login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                    <i class="fas fa-eye-slash toggle-password"></i>
                </div>
            </div>

            <div class="remember-forgot">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                <a href="/mindmate/auth/forgot-password.php" class="forgot-password">Forgot Password?</a>
            </div>

            <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <button type="submit" class="btn-auth">Sign In</button>

            <div class="auth-footer">
                <p>Don't have an account? <a href="/mindmate/auth/register.php">Sign Up</a></p>
            </div>

            <div class="social-login">
                <p>Or continue with</p>
                <div class="social-buttons">
                    <button type="button" class="social-btn">
                        <i class="fab fa-google"></i>
                        Google
                    </button>
                    <button type="button" class="social-btn">
                        <i class="fab fa-facebook-f"></i>
                        Facebook
                    </button>
                </div>
            </div>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="/mindmate/auth/register.php">Sign Up</a></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.querySelector('.toggle-password');
    const passwordInput = document.querySelector('#password');

    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
});
</script>

</body>
</html>
