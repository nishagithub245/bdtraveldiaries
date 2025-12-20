<?php
require 'db_connection.php';

// Get POST data safely
$username = $_POST['username'] ?? '';
$articletext = $_POST['articletext'] ?? '';
$time = date("Y-m-d H:i:s");

if($username && $articletext) {
    $stmt = $conn->prepare("INSERT INTO articles (username, articletext, publishtime) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $articletext, $time);

    if($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
} else {
    echo "error";
}

$conn->close();
