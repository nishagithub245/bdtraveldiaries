<?php
require 'db_connection.php';

$currentUser = $_GET['username'] ?? '';

$sql = "
SELECT a.articlenumber, a.username, a.title, a.articletext, a.publishtime,
       COALESCE(f.flagabusive,0) AS user_flagabusive,
       COALESCE(f.flagspam,0) AS user_flagspam,
       COALESCE(f.flagcopyright,0) AS user_flagcopyright
FROM articles a
LEFT JOIN flags f 
  ON a.articlenumber = f.articlenumber 
  AND f.username = '$currentUser' 
  AND f.recorded = 1
ORDER BY a.publishtime DESC
";

$result = $conn->query($sql);
$articles = [];

while($row = $result->fetch_assoc()){
    $articles[] = $row;
}

echo json_encode($articles);
$conn->close();
?>
