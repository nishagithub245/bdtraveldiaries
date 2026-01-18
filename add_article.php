<?php
require 'db_connection.php';

// Get POST data safely
$username = $_POST['username'] ?? '';
$title = $_POST['title'] ?? '';
$articletext = $_POST['articletext'] ?? '';

// Validate input
if (empty($username) || empty($title) || empty($articletext)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
    exit;
}

// Get current time in GMT for DB
$now = new DateTime("now", new DateTimeZone("GMT"));
$publishtime_db = $now->format('Y-m-d H:i:s');  
$publishtime_display = $now->format('d/m/Y - H:i:s') . ' GMT'; 

// Prepare and execute
$sql = "INSERT INTO articles (username, title, articletext, publishtime) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}
$stmt->bind_param("ssss", $username, $title, $articletext, $publishtime_db);

if ($stmt->execute()) {
    $id = $stmt->insert_id; 
    echo json_encode([
        'status' => 'success',
        'id' => $id,
        'publishtime' => $publishtime_display
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>