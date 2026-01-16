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
  AND f.username = ?
  AND f.recorded = 1
ORDER BY a.publishtime DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Prepare failed: " . $conn->error]);
    exit;
}
$stmt->bind_param("s", $currentUser);
$stmt->execute();
$result = $stmt->get_result();

$articles = [];

while($row = $result->fetch_assoc()){
    $userFlags = [
        'abusive' => (int)$row['user_flagabusive'],
        'spam' => (int)$row['user_flagspam'],
        'copyright' => (int)$row['user_flagcopyright'],
        'any' => ($row['user_flagabusive'] || $row['user_flagspam'] || $row['user_flagcopyright']) ? 1 : 0
    ];

    $articles[] = [
        'articlenumber' => $row['articlenumber'],
        'username' => $row['username'],
        'title' => $row['title'],
        'articletext' => $row['articletext'],
        'publishtime' => $row['publishtime'],
        'userFlags' => $userFlags
    ];
}

echo json_encode($articles);
$conn->close();
?>