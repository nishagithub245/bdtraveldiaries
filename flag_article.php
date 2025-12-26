<?php
require 'db_connection.php';

$articlenumber = $_POST['articlenumber'];
$abusive = $_POST['abusive'] ?? 0;
$spam = $_POST['spam'] ?? 0;
$copyright = $_POST['copyright'] ?? 0;

$stmt = $conn->prepare("INSERT INTO flags (articlenumber, abusive, spam, copyright) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiii", $articlenumber, $abusive, $spam, $copyright);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error';
}

$stmt->close();
$conn->close();
