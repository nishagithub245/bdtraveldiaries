<?php
require 'db_connection.php';

$lastFlagId = isset($_POST['lastFlagId']) ? (int)$_POST['lastFlagId'] : 0;
$startTime = time();
$timeout = 25;

while (true) {

    $sql = "
        SELECT 
            f.flagnumber,
            f.username AS flagger,
            f.articlenumber,
            f.flagabusive,
            f.flagspam,
            f.flagcopyright,
            f.time,
            a.username AS author,
            a.title
        FROM flags f
        JOIN articles a ON a.articlenumber = f.articlenumber
        WHERE f.flagnumber > ?
        ORDER BY f.flagnumber ASC
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lastFlagId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        $flagger = $row['flagger'];
        $articlenumber = $row['articlenumber'];
        $currentId = $row['flagnumber'];

        /* Fetch previous active record for comparison */
        $prevSql = "
            SELECT flagabusive, flagspam, flagcopyright
            FROM flags
            WHERE username = ? 
              AND articlenumber = ? 
              AND flagnumber < ?
            ORDER BY flagnumber DESC
            LIMIT 1
        ";

        $prevStmt = $conn->prepare($prevSql);
        $prevStmt->bind_param("sii", $flagger, $articlenumber, $currentId);
        $prevStmt->execute();
        $prevFlags = $prevStmt->get_result()->fetch_assoc()
            ?: ['flagabusive'=>0,'flagspam'=>0,'flagcopyright'=>0];

        $prevStmt->close();

        $currentFlags = [
            'abusive'   => (int)$row['flagabusive'],
            'spam'      => (int)$row['flagspam'],
            'copyright' => (int)$row['flagcopyright']
        ];

        $flagged = [];
        $unflagged = [];

        foreach ($currentFlags as $type => $value) {
            if ($value && empty($prevFlags['flag'.$type])) {
                $flagged[] = ucfirst($type);
            }
            if (!$value && !empty($prevFlags['flag'.$type])) {
                $unflagged[] = ucfirst($type);
            }
        }

        /* Ignore no-change rows */
        if (empty($flagged) && empty($unflagged)) {
            $lastFlagId = $currentId;
            continue;
        }

        /* Build admin notification message */
        $message = "<span class='highlight-user'>{$flagger}</span> ";

        if (!empty($unflagged)) {
            $message .= "has un-flagged the article <strong>{$row['title']}</strong> as "
                     . implode(', ', $unflagged);
            if (!empty($flagged)) {
                $message .= " and flagged it as " . implode(', ', $flagged);
            }
        } else {
            $message .= "flagged <strong>{$row['title']}</strong> as "
                     . implode(', ', $flagged);
        }

        echo json_encode([
            'flagnumber' => $currentId,
            'time'       => $row['time'],
            'message'    => $message
        ]);

        flush();
        exit;
    }

    sleep(1);

    if (time() - $startTime >= $timeout) {
        echo json_encode([]);
        flush();
        exit;
    }
}
?>