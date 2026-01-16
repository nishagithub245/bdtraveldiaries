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
            f.recorded,
            a.username AS author,
            a.title
        FROM flags f
        JOIN articles a ON a.articlenumber = f.articlenumber
        WHERE f.flagnumber > ? AND f.recorded = 1
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

        /* Fetch previous ACTIVE flag record for comparison */
        $prevSql = "
            SELECT flagabusive, flagspam, flagcopyright
            FROM flags
            WHERE username = ? 
              AND articlenumber = ? 
              AND recorded = 1
              AND flagnumber < ?
            ORDER BY flagnumber DESC
            LIMIT 1
        ";

        $prevStmt = $conn->prepare($prevSql);
        $prevStmt->bind_param("sii", $flagger, $articlenumber, $currentId);
        $prevStmt->execute();
        $prevResult = $prevStmt->get_result();
        
        // If no previous active flag, treat as all flags are 0
        $prevFlags = $prevResult->fetch_assoc() ?: [
            'flagabusive' => 0,
            'flagspam' => 0,
            'flagcopyright' => 0
        ];

        $prevStmt->close();

        $currentFlags = [
            'abusive'   => (int)$row['flagabusive'],
            'spam'      => (int)$row['flagspam'],
            'copyright' => (int)$row['flagcopyright']
        ];

        $flagged = [];
        $unflagged = [];

        foreach ($currentFlags as $type => $value) {
            $prevValue = $prevFlags['flag'.$type] ?? 0;
            if ($value && !$prevValue) {
                $flagged[] = ucfirst($type);
            }
            if (!$value && $prevValue) {
                $unflagged[] = ucfirst($type);
            }
        }

        /* Build admin notification message */
        $message = "<span class='highlight-user'>{$flagger}</span> ";

        if (!empty($unflagged) && !empty($flagged)) {
            // Both unflagging and reflagging
            $message .= "has un-flagged the article <strong>{$row['title']}</strong> as "
                     . implode(', ', $unflagged)
                     . " and re-flagged it as " . implode(', ', $flagged);
        } elseif (!empty($unflagged)) {
            // Only unflagging
            $message .= "has un-flagged the article <strong>{$row['title']}</strong> as "
                     . implode(', ', $unflagged);
        } elseif (!empty($flagged)) {
            // Only flagging (first time or reflagging)
            if ($prevFlags['flagabusive'] == 0 && $prevFlags['flagspam'] == 0 && $prevFlags['flagcopyright'] == 0) {
                // First time flagging
                $message .= "flagged <strong>{$row['title']}</strong> as "
                         . implode(', ', $flagged);
            } else {
                // Reflagging different flags
                $message .= "has updated flags for <strong>{$row['title']}</strong> to "
                         . implode(', ', $flagged);
            }
        } else {
            // No changes - shouldn't happen since we check above
            $lastFlagId = $currentId;
            continue;
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