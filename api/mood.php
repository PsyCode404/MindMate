<?php
// API endpoint for mood entries

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
        
        // For testing, use a default user ID
        $userId = 2;
    }

    // Create a user array with the ID
    $user = ['id' => $userId];

    // Get database connection with detailed error handling
    $conn = get_db_connection();
    if (!$conn) {
        $error = 'Database connection failed';
        $errorDetails = [
            'error' => $error,
            'details' => [
                'db_host' => defined('DB_HOST') ? DB_HOST : 'Not defined',
                'db_name' => defined('DB_NAME') ? DB_NAME : 'Not defined',
                'error_info' => $conn ? $conn->error : 'Connection object is null',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
        error_log(print_r($errorDetails, true));
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode($errorDetails);
        exit();
    }

    // Handle different request methods
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Get mood entries for the current user
            try {
                error_log('GET request received. User ID: ' . $userId);
                
                // Check if a specific period is requested
                $period = isset($_GET['period']) ? $_GET['period'] : 'all';
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
                
                error_log("Fetching mood entries. Period: $period, Limit: " . ($limit ?: 'none'));
                
                // Build the SQL query based on the period
                $sql = "SELECT * FROM mood_logs WHERE user_id = ?";
                
                // Add date filtering if period is specified
                if ($period !== 'all') {
                    $cutoffDate = new DateTime();
                    if ($period === 'day') {
                        // For 'day', get entries from today only
                        $today = new DateTime('today');
                        $tomorrow = new DateTime('tomorrow');
                        $sql .= " AND logged_at >= '" . $today->format('Y-m-d') . "' AND logged_at < '" . $tomorrow->format('Y-m-d') . "'";
                    } elseif ($period === 'week') {
                        $cutoffDate->modify('-7 days');
                        $sql .= " AND logged_at >= '" . $cutoffDate->format('Y-m-d H:i:s') . "'";
                    } elseif ($period === 'month') {
                        $cutoffDate->modify('-30 days');
                        $sql .= " AND logged_at >= '" . $cutoffDate->format('Y-m-d H:i:s') . "'";
                    }
                }
                
                // Add ordering
                $sql .= " ORDER BY logged_at DESC";
                
                // Add limit if specified
                if ($limit) {
                    $sql .= " LIMIT " . $limit;
                }
                
                error_log("Executing SQL: $sql");
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $bindResult = $stmt->bind_param("i", $user['id']);
                if (!$bindResult) {
                    throw new Exception("Bind param failed: " . $stmt->error);
                }
                
                $executeResult = $stmt->execute();
                if (!$executeResult) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                $result = $stmt->get_result();
                if (!$result) {
                    throw new Exception("Get result failed: " . $stmt->error);
                }
                
                $entries = [];
                while ($row = $result->fetch_assoc()) {
                    // Convert numeric IDs to strings for JavaScript
                    if (isset($row['id'])) {
                        $row['id'] = (string)$row['id'];
                    }
                    if (isset($row['user_id'])) {
                        $row['user_id'] = (string)$row['user_id'];
                    }
                    
                    // Convert mood_level to mood name for frontend
                    $moodNames = [
                        1 => 'rough',
                        2 => 'down',
                        3 => 'okay',
                        4 => 'good',
                        5 => 'amazing'
                    ];
                    
                    // Safely get mood name with fallback to 'unknown'
                    $moodLevel = (int)$row['mood_level'];
                    if ($moodLevel >= 1 && $moodLevel <= 5) {
                        $row['mood'] = $moodNames[$moodLevel];
                    } else {
                        // For values outside 1-5, map to the closest valid mood
                        if ($moodLevel < 1) $row['mood'] = 'rough';
                        elseif ($moodLevel > 5) $row['mood'] = 'amazing';
                        else $row['mood'] = 'unknown';
                    }
                    
                    $entries[] = $row;
                }
                
                // Log the number of entries found
                error_log("Found {$result->num_rows} mood entries for user {$user['id']}");
                
                echo json_encode(['entries' => $entries, 'success' => true]);
            } catch (Exception $e) {
                error_log("Error fetching mood entries: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to fetch entries', 'message' => $e->getMessage()]);
            }
            break;
            
        case 'POST':
            // Create a new mood entry
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Log the received data for debugging
            error_log('Received mood entry data: ' . print_r($data, true));
            
            // Check for required fields
            if (!isset($data['mood']) || !isset($data['moodValue'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Mood and mood value are required']);
                exit();
            }
            
            // Map mood names to values if needed
            $moodValue = $data['moodValue'];
            $notes = $data['reflection'] ?? '';
            
            try {
                // Insert the new entry
                $stmt = $conn->prepare("INSERT INTO mood_logs (user_id, mood_level, notes) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $user['id'], $moodValue, $notes);
                
                if ($stmt->execute()) {
                    $entry_id = $conn->insert_id;
                    
                    // Get the created entry
                    $stmt = $conn->prepare("SELECT * FROM mood_logs WHERE id = ?");
                    $stmt->bind_param("i", $entry_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $entry = $result->fetch_assoc();
                    
                    // Log success
                    error_log('Successfully created mood entry with ID: ' . $entry_id);
                    
                    echo json_encode([
                        'success' => true, 
                        'entry' => $entry,
                        'message' => 'Mood entry saved successfully'
                    ]);
                } else {
                    // Log the error
                    error_log('Failed to execute mood entry insert: ' . $conn->error);
                    
                    http_response_code(500);
                    echo json_encode([
                        'error' => 'Failed to create entry', 
                        'details' => $conn->error,
                        'sql_error' => $stmt->error
                    ]);
                }
            } catch (Exception $e) {
                // Log the exception
                error_log('Exception in mood entry creation: ' . $e->getMessage());
                
                http_response_code(500);
                echo json_encode([
                    'error' => 'Exception occurred', 
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'PUT':
            // Update an existing mood entry
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id']) || !isset($data['moodValue']) || !isset($data['reflection'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit();
            }
            
            $id = $data['id'];
            $moodValue = $data['moodValue'];
            $notes = $data['reflection'];
            
            // Check if the entry belongs to the current user
            $stmt = $conn->prepare("SELECT * FROM mood_logs WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Entry not found or not owned by current user']);
                exit();
            }
            
            $stmt = $conn->prepare("UPDATE mood_logs SET mood_level = ?, notes = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("isii", $moodValue, $notes, $id, $user['id']);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Mood entry updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update entry', 'details' => $conn->error]);
            }
            break;
            
        case 'DELETE':
            // Delete a mood entry
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing entry ID']);
                exit();
            }
            
            $id = $data['id'];
            
            // Check if the entry belongs to the current user
            $stmt = $conn->prepare("SELECT * FROM mood_logs WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Entry not found or not owned by current user']);
                exit();
            }
            
            $stmt = $conn->prepare("DELETE FROM mood_logs WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user['id']);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Mood entry deleted successfully']);
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
    error_log("Unhandled exception in mood API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}
?>
