<?php
// Script to insert mock mood data for user ID 2 (Mohamed Guenidi)

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Get database connection
$conn = get_db_connection();
if (!$conn) {
    die("Database connection failed");
}

// User ID for Mohamed Guenidi
$userId = 2;

// Clear existing mood data for this user (optional - comment out if you want to keep existing data)
$clearSql = "DELETE FROM mood_logs WHERE user_id = ?";
$clearStmt = $conn->prepare($clearSql);
$clearStmt->bind_param("i", $userId);
$clearStmt->execute();
echo "Cleared existing mood data for user ID $userId\n";

// Create mock data for the past 30 days
$mockData = [
    // Format: [days_ago, mood_level, notes]
    [30, 3, "Just an average day. Nothing special happened."],
    [28, 4, "Had a productive day at work. Feeling good about my progress."],
    [27, 5, "Amazing day! Got great news about my project."],
    [25, 4, "Good day overall. Enjoyed time with friends."],
    [23, 3, "Feeling okay. A bit tired from yesterday."],
    [21, 2, "Not feeling great today. Stressed about deadlines."],
    [20, 1, "Really rough day. Nothing seems to be going right."],
    [19, 2, "Still feeling down but slightly better than yesterday."],
    [17, 3, "Back to normal. Taking things one step at a time."],
    [15, 4, "Good day! Made progress on my personal goals."],
    [14, 4, "Another good day. Feeling motivated."],
    [12, 5, "Excellent day! Everything is falling into place."],
    [10, 4, "Good productive day at work."],
    [9, 3, "Just an okay day. Nothing special."],
    [7, 2, "Feeling a bit down today. Weather is gloomy."],
    [6, 3, "Feeling better than yesterday. Made some progress."],
    [5, 4, "Good day! Had a nice conversation with an old friend."],
    [3, 5, "Amazing day! Accomplished all my tasks and more."],
    [2, 4, "Good day overall. Feeling positive about the future."],
    [1, 3, "Just an okay day. A bit tired but managing."],
    [0, 4, "Today has been good so far. Looking forward to the weekend."]
];

// Insert mock data
$insertSql = "INSERT INTO mood_logs (user_id, mood_level, notes, logged_at) VALUES (?, ?, ?, ?)";
$insertStmt = $conn->prepare($insertSql);

$successCount = 0;
foreach ($mockData as $entry) {
    $daysAgo = $entry[0];
    $moodLevel = $entry[1];
    $notes = $entry[2];
    
    // Calculate date
    $date = new DateTime();
    $date->modify("-$daysAgo days");
    
    // Add a random time component
    $hour = rand(8, 21);  // Between 8 AM and 9 PM
    $minute = rand(0, 59);
    $date->setTime($hour, $minute, 0);
    
    $loggedAt = $date->format('Y-m-d H:i:s');
    
    $insertStmt->bind_param("iiss", $userId, $moodLevel, $notes, $loggedAt);
    
    if ($insertStmt->execute()) {
        $successCount++;
    } else {
        echo "Error inserting entry for $loggedAt: " . $conn->error . "\n";
    }
}

echo "Successfully inserted $successCount mock mood entries for user ID $userId\n";

// Close connection
$conn->close();

echo "Done! You can now view the mood tracker to see the chart with mock data.";
?>
