<?php
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $conn;

    public function __construct() {
        $this->conn = get_db_connection();
    }

    public function register($fullName, $email, $password) {
        try {
            // Validate input
            if (empty($fullName) || empty($email) || empty($password)) {
                return ['success' => false, 'message' => 'All fields are required'];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            // Check if email already exists
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            if ($stmt === false) {
                throw new Exception("Database error: " . $this->conn->error);
            }
            
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                throw new Exception("Database error: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            if ($result === false) {
                throw new Exception("Database error: " . $this->conn->error);
            }
            
            if ($result->num_rows > 0) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $this->conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            if ($stmt === false) {
                throw new Exception("Database error: " . $this->conn->error);
            }
            
            $stmt->bind_param("sss", $fullName, $email, $hashedPassword);
            
            if ($stmt->execute()) {
                $userId = $this->conn->insert_id;
                // Start session
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
                $_SESSION['user_id'] = $userId;
                $_SESSION['full_name'] = $fullName;
                $_SESSION['email'] = $email;
                
                return ['success' => true, 'message' => 'Registration successful'];
            } else {
                return ['success' => false, 'message' => 'Registration failed'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred'];
        }
    }

    public function login($email, $password) {
        try {
            // Validate input
            if (empty($email) || empty($password)) {
                return ['success' => false, 'message' => 'All fields are required'];
            }

            // Get user by email
            $stmt = $this->conn->prepare("SELECT id, full_name, email, password FROM users WHERE email = ?");
            if ($stmt === false) {
                throw new Exception("Database error: " . $this->conn->error);
            }
            
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                throw new Exception("Database error: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            if ($result === false) {
                throw new Exception("Database error: " . $this->conn->error);
            }
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Start session
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                
                return ['success' => true, 'message' => 'Login successful'];
            } else {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred'];
        }
    }

    public function logout() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_destroy();
        return ['success' => true, 'message' => 'Logout successful'];
    }

    public function isLoggedIn() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
}
