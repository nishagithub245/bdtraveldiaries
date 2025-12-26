<?php
require 'db_connection.php';

header('Content-Type: application/json');

// Fetch articles with title and flag count
$sql = "
SELECT 
    a.articlenumber,
    a.username,
    a.title,
    a.articletext,
    a.publishtime,
    COUNT(f.flagnumber) AS flag_count
FROM articles a
LEFT JOIN flags f ON a.articlenumber = f.articlenumber
GROUP BY a.articlenumber
ORDER BY a.articlenumber DESC
";

$result = $conn->query($sql);
$articles = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $articles[] = [
            'articlenumber' => $row['articlenumber'],
            'username' => $row['username'],
            'title' => $row['title'],
            'articletext' => $row['articletext'],
            'publishtime' => $row['publishtime'],
            'flag_count' => $row['flag_count']
        ];
    }
}

echo json_encode($articles);
$conn->close();
?>
