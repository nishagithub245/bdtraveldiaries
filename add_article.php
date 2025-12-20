<?php

require 'db_connection.php';




$username = $_POST['username'];
$articletext = $_POST['articletext'];
$time = date("Y-m-d H:i:s");

$stmt = $conn->prepare(
  "INSERT INTO articles (username, articletext, publishtime)
   VALUES (?, ?, ?)"
);

$stmt->bind_param("sss", $username, $articletext, $time);
$stmt->execute();

echo "success";



?>