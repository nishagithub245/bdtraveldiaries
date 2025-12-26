<?php
require 'db_connection.php';

$username = $_POST['username'];
$articletext = $_POST['articletext'];

$stmt = $conn->prepare("INSERT INTO articles (username, articletext, publishtime) VALUES (?, ?, NOW())");
$stmt->bind_param("ss", $username, $articletext);

if ($stmt->execute()) {
    $newID = $stmt->insert_id;
    echo json_encode(['status' => 'success', 'id' => $newID]);
} else {
    echo json_encode(['status' => 'error']);
}
$stmt->close();
$conn->close();
