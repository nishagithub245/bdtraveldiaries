<?php
require 'db_connection.php';

header('Content-Type: application/json');

$result = $conn->query("SELECT username, articletext, publishtime FROM articles ORDER BY publishtime DESC");

$articles = [];
if($result) {
    while($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
}

echo json_encode($articles);

$conn->close();
