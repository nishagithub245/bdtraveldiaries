<?php
require 'db_connection.php';

$username = $_POST['username'];
$articlenumber = $_POST['articlenumber'];
$flagabusive = $_POST['flagabusive'] ?? 0;
$flagspam = $_POST['flagspam'] ?? 0;
$flagcopyright = $_POST['flagcopyright'] ?? 0;
$time = date('Y-m-d H:i:s');


if(!$username || !$articlenumber){
    echo json_encode(["status"=>"error","message"=>"Missing data"]);
    exit;
}


// Mark previous report as inactive
$conn->query("UPDATE flags SET recorded=0 
             WHERE username='$username' AND articlenumber=$articlenumber AND recorded=1");




// Insert new report
$insertSql = "INSERT INTO flags (username, articlenumber, flagabusive, flagspam, flagcopyright, time, recorded) VALUES (?, ?, ?, ?, ?, ?, 1)";
$stmt = $conn->prepare($insertSql);
$stmt->bind_param("siiiss", $username, $articlenumber, $flagabusive, $flagspam, $flagcopyright, $time);

if($stmt->execute()){
    echo json_encode(["status"=>"success"]);
}else{
    echo json_encode(["status"=>"error","message"=>$stmt->error]);
}

$stmt->close();
$conn->close();
?>