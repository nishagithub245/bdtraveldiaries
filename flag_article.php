<?php
require 'db_connection.php';


$username = $_POST['username'];
$articlenumber = $_POST['articlenumber'];
$flagabusive = $_POST['flagabusive'];
$flagspam = $_POST['flagspam'];
$flagcopyright = $_POST['flagcopyright'];
$time = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO flags 
(username, articlenumber, flagabusive, flagspam, flagcopyright, time) 
VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("siiiss", $username, $articlenumber, $flagabusive, $flagspam, $flagcopyright, $time);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}

$stmt->close();
$conn->close();
?>