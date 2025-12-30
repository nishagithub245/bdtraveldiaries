<?php
require 'db_connection.php';

$lastFlagId = isset($_POST['lastFlagId']) ? intval($_POST['lastFlagId']) : 0;
$startTime = time();
$timeout = 25;

while(true){
    $sql = "
        SELECT f.flagnumber, f.username AS flagger, f.flagabusive, f.flagspam, f.flagcopyright, f.recorded,
               f.time, a.username AS author, a.title
        FROM flags f
        JOIN articles a ON f.articlenumber = a.articlenumber
        WHERE f.flagnumber > $lastFlagId
        ORDER BY f.flagnumber ASC
        LIMIT 1
    ";
    $result = $conn->query($sql);

    if($result && $result->num_rows>0){
        $row = $result->fetch_assoc();
        $prevFlags = [
            'abusive'=>0,'spam'=>0,'copyright'=>0
        ]; // fetch from DB previous recorded flags if needed

        $currentFlags = [
            'abusive'=>$row['flagabusive'],
            'spam'=>$row['flagspam'],
            'copyright'=>$row['flagcopyright']
        ];

        $reasonsFlagged = [];
        $reasonsUnflagged = [];

        foreach($currentFlags as $type=>$value){
            if($value && !$prevFlags[$type]) $reasonsFlagged[] = $type;
            if(!$value && $prevFlags[$type]) $reasonsUnflagged[] = $type;
        }

        $message = "<span class='highlight-user'>{$row['flagger']}</span> ";

        if($reasonsUnflagged){
            $message .= "has un-flagged the article <strong>{$row['title']}</strong> as ".implode(', ',$reasonsUnflagged);
            if($reasonsFlagged) $message .= " and flagged it as ".implode(', ',$reasonsFlagged);
        } else {
            $message .= "flagged <strong>{$row['title']}</strong> as ".implode(', ',$reasonsFlagged);
        }

        echo json_encode([
            'flagnumber'=>$row['flagnumber'],
            'time'=>$row['time'],
            'message'=>$message
        ]);

        flush();
        exit;
    }

    sleep(1);
    if(time()-$startTime>=$timeout){
        echo json_encode([]);
        flush();
        exit;
    }
}