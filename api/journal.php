<?php
// API endpoint for journal entries

// Set headers first to ensure they're sent
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Disable displaying errors directly, log them instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Wrap everything in a try-catch to ensure we always return JSON
try {
    // Include required files
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/auth.php';

// Initialize auth and check if user is logged in
$auth = new Auth();
$isLoggedIn = $auth->isLoggedIn();

// Get user information from session
session_start();
$userId = $_SESSION['user_id'] ?? null;

// For debugging - temporarily use a default user ID if not logged in
if (!$isLoggedIn || !$userId) {
    // In production, you would uncomment this to require authentication
    /*
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
    */
    
    // For testing, use a default user ID that matches existing entries
    $userId = 2; // Changed from 1 to 2 to match existing entries
}

// Create a user array with the ID
$user = ['id' => $userId];

// Get database connection
$conn = get_db_connection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all journal entries for the current user
        try {
            $stmt = $conn->prepare("SELECT * FROM journal_entries WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $entries = [];
            while ($row = $result->fetch_assoc()) {
                // Convert numeric IDs to strings for JavaScript
                if (isset($row['id'])) {
                    $row['id'] = (string)$row['id'];
                }
                if (isset($row['user_id'])) {
                    $row['user_id'] = (string)$row['user_id'];
                }
                
                $entries[] = $row;
            }
            
            // Log the number of entries found
            error_log("Found {$result->num_rows} journal entries for user {$user['id']}");
            
            echo json_encode(['entries' => $entries, 'success' => true]);
        } catch (Exception $e) {
            error_log("Error fetching journal entries: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch entries', 'message' => $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Create a new journal entry
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Log the received data for debugging
        error_log('Received journal entry data: ' . print_r($data, true));
        
        // Check for required fields with more lenient validation
        if (empty($data['content'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Content is required']);
            exit();
        }
        
        // Set default values if not provided
        $title = $data['title'] ?? 'Journal Entry';
        $content = $data['content'];
        $mood = $data['mood'] ?? 'neutral';
        $client_id = $data['id'] ?? null; // Client-side ID for syncing
        
        try {
            // Insert the new entry
            $stmt = $conn->prepare("INSERT INTO journal_entries (user_id, client_id, title, content, mood) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user['id'], $client_id, $title, $content, $mood);
            
            if ($stmt->execute()) {
                $entry_id = $conn->insert_id;
                
                // Get the created entry
                $stmt = $conn->prepare("SELECT * FROM journal_entries WHERE id = ?");
                $stmt->bind_param("i", $entry_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $entry = $result->fetch_assoc();
                
                // Log success
                error_log('Successfully created journal entry with ID: ' . $entry_id);
                
                echo json_encode([
                    'success' => true, 
                    'entry' => $entry,
                    'message' => 'Entry saved successfully'
                ]);
            } else {
                // Log the error
                error_log('Failed to execute journal entry insert: ' . $conn->error);
                
                http_response_code(500);
                echo json_encode([
                    'error' => 'Failed to create entry', 
                    'details' => $conn->error,
                    'sql_error' => $stmt->error
                ]);
            }
        } catch (Exception $e) {
            // Log the exception
            error_log('Exception in journal entry creation: ' . $e->getMessage());
            
            http_response_code(500);
            echo json_encode([
                'error' => 'Exception occurred', 
                'message' => $e->getMessage()
            ]);
        }
        break;
        
    case 'PUT':
        // Update an existing journal entry
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id']) || !isset($data['title']) || !isset($data['content']) || !isset($data['mood'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit();
        }
        
        $id = $data['id'];
        $title = $data['title'];
        $content = $data['content'];
        $mood = $data['mood'];
        $client_id = $data['client_id'] ?? null;
        
        // Check if the entry belongs to the current user
        $stmt = $conn->prepare("SELECT * FROM journal_entries WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Entry not found or not owned by current user']);
            exit();
        }
        
        $stmt = $conn->prepare("UPDATE journal_entries SET title = ?, content = ?, mood = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $title, $content, $mood, $id, $user['id']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update entry', 'details' => $conn->error]);
        }
        break;
        
    case 'DELETE':
        // Delete a journal entry
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing entry ID']);
            exit();
        }
        
        $id = $data['id'];
        
        // Check if the entry belongs to the current user
        $stmt = $conn->prepare("SELECT * FROM journal_entries WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Entry not found or not owned by current user']);
            exit();
        }
        
        $stmt = $conn->prepare("DELETE FROM journal_entries WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user['id']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete entry', 'details' => $conn->error]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

// Close connection
$conn->close();

} catch (Exception $e) {
    // Log the exception
    error_log('Global exception in journal API: ' . $e->getMessage());
    
    // Return a JSON error response
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error', 
        'message' => 'An unexpected error occurred. Please try again.'
    ]);
}
