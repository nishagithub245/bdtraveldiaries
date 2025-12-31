<?php
require 'db_connection.php';

$lastFlagId = isset($_POST['lastFlagId']) ? intval($_POST['lastFlagId']) : 0;
$startTime = time();
$timeout = 25;

while(true){
    // Get next flag record
    $sql = "
        SELECT f.flagnumber, f.username AS flagger, f.articlenumber, f.flagabusive, f.flagspam, f.flagcopyright, f.time,
               a.username AS author, a.title
        FROM flags f
        JOIN articles a ON f.articlenumber = a.articlenumber
        WHERE f.flagnumber > $lastFlagId
        ORDER BY f.flagnumber ASC
        LIMIT 1
    ";
    $result = $conn->query($sql);

    if($result && $result->num_rows > 0){
        $row = $result->fetch_assoc();
        $flagger = $row['flagger'];
        $articlenumber = $row['articlenumber'];

        // Get last recorded flag for same user/article
        $prevSql = "
            SELECT flagabusive, flagspam, flagcopyright
            FROM flags
            WHERE username = ? AND articlenumber = ? AND flagnumber < ?
            ORDER BY flagnumber DESC
            LIMIT 1
        ";
        $stmt = $conn->prepare($prevSql);
        $stmt->bind_param("sii", $flagger, $articlenumber, $row['flagnumber']);
        $stmt->execute();
        $prevResult = $stmt->get_result();
        $prevFlags = $prevResult->fetch_assoc() ?: ['flagabusive'=>0,'flagspam'=>0,'flagcopyright'=>0];

        $currentFlags = [
            'abusive' => $row['flagabusive'],
            'spam' => $row['flagspam'],
            'copyright' => $row['flagcopyright']
        ];

        $reasonsFlagged = [];
        $reasonsUnflagged = [];

        foreach($currentFlags as $type => $value){
            if($value && !$prevFlags['flag'.$type]) $reasonsFlagged[] = $type;
            if(!$value && $prevFlags['flag'.$type]) $reasonsUnflagged[] = $type;
        }

        // If no changes, skip
        if(empty($reasonsFlagged) && empty($reasonsUnflagged)){
            sleep(1);
            if(time() - $startTime >= $timeout){
                echo json_encode([]);
                flush();
                exit;
            }
            continue;
        }

        $message = "<span class='highlight-user'>{$flagger}</span> ";

        if($reasonsUnflagged){
            $message .= "has un-flagged the article <strong>{$row['title']}</strong> as ".implode(', ',$reasonsUnflagged);
            if($reasonsFlagged) $message .= " and flagged it as ".implode(', ',$reasonsFlagged);
        } else {
            $message .= "flagged <strong>{$row['title']}</strong> as ".implode(', ',$reasonsFlagged);
        }

        echo json_encode([
            'flagnumber' => $row['flagnumber'],
            'time' => $row['time'],
            'message' => $message
        ]);

        flush();
        exit;
    }

    sleep(1);
    if(time() - $startTime >= $timeout){
        echo json_encode([]);
        flush();
        exit;
    }
}
?>
