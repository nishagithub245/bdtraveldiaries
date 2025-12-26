<?php
require 'db_connection.php';

$lastFlagId = isset($_POST['lastFlagId']) ? intval($_POST['lastFlagId']) : 0;

$sql = "
SELECT 
    f.flagnumber,
    f.username AS flagger,
    f.flagabusive,
    f.flagspam,
    f.flagcopyright,
    f.time,
    a.username AS author,
    a.articletext
FROM flags f
JOIN articles a ON f.articlenumber = a.articlenumber
WHERE f.flagnumber > $lastFlagId
ORDER BY f.flagnumber ASC
";

$result = $conn->query($sql);

$response = [];

while ($row = $result->fetch_assoc()) {

    $reasons = [];
    if ($row['flagabusive']) $reasons[] = 'abusive';
    if ($row['flagspam']) $reasons[] = 'spam';
    if ($row['flagcopyright']) $reasons[] = 'copyrighted';

   $message =
"<span class='highlight-user'>{$row['flagger']}</span> has flagged the article
'<strong>{$row['articletext']}</strong>' posted by
<span class='highlight-user'>{$row['author']}</span> as " .
implode(', ', $reasons);

    $response[] = [
        'flagnumber' => $row['flagnumber'],
        'time' => $row['time'],
        'message' => $message
    ];
}

echo json_encode($response);
$conn->close();
