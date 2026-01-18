<?php
require 'db_connection.php';

$lastFlagId = isset($_POST['lastFlagId']) ? (int)$_POST['lastFlagId'] : 0;
$startTime = time();
$timeout = 25;

while (true) {
    // Get the latest NEW flag (recorded=1) 
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
        $author = $row['author'];
        $articleTitle = $row['title'];
        
        // Get the MOST RECENT flag BEFORE this one for the same user/article
        $prevSql = "
            SELECT flagabusive, flagspam, flagcopyright, recorded
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
        $prevResult = $prevStmt->get_result();
        $prevRow = $prevResult->fetch_assoc();
        $prevStmt->close();
        
        // Current flag values
        $currentAbusive = (int)$row['flagabusive'];
        $currentSpam = (int)$row['flagspam'];
        $currentCopyright = (int)$row['flagcopyright'];
        
        // Previous flag values (default to 0 if none)
        $prevAbusive = $prevRow ? (int)$prevRow['flagabusive'] : 0;
        $prevSpam = $prevRow ? (int)$prevRow['flagspam'] : 0;
        $prevCopyright = $prevRow ? (int)$prevRow['flagcopyright'] : 0;
        
        // Check if this is first flag (no previous row) or if previous row was not active
        $isFirstFlag = (!$prevRow);
       
        $message = "<strong><u>{$flagger}</u></strong> has flagged the article <strong>\"{$articleTitle}\"</strong> posted by <strong><u>{$author}</u></strong> as ";
        
        if ($isFirstFlag) {
            // First time flagging this article
            $flagTypes = [];
            if ($currentAbusive) $flagTypes[] = '<strong>abusive</strong>';
            if ($currentSpam) $flagTypes[] = '<strong>spam</strong>';
            if ($currentCopyright) $flagTypes[] = '<strong>copyrighted</strong>';
            
            if (count($flagTypes) > 1) {
                // Join with commas and "and" for the last one
                $lastFlag = array_pop($flagTypes);
                if (!empty($flagTypes)) {
                    $flagText = implode(', ', $flagTypes) . ' and ' . $lastFlag;
                } else {
                    $flagText = $lastFlag;
                }
                $message .= $flagText . '.';
            } elseif (!empty($flagTypes)) {
                $message .= $flagTypes[0] . '.';
            } else {
                $message .= "saved flag preferences.";
            }
        } else {
            // Determine what changed
            $added = [];
            $removed = [];
            
            // Check each flag type
            if ($currentAbusive == 1 && $prevAbusive == 0) {
                $added[] = '<strong>abusive</strong>';
            } elseif ($currentAbusive == 0 && $prevAbusive == 1) {
                $removed[] = '<strong>abusive</strong>';
            }
            
            if ($currentSpam == 1 && $prevSpam == 0) {
                $added[] = '<strong>spam</strong>';
            } elseif ($currentSpam == 0 && $prevSpam == 1) {
                $removed[] = '<strong>spam</strong>';
            }
            
            if ($currentCopyright == 1 && $prevCopyright == 0) {
                $added[] = '<strong>copyrighted</strong>';
            } elseif ($currentCopyright == 0 && $prevCopyright == 1) {
                $removed[] = '<strong>copyrighted</strong>';
            }
            
            // Build the message based on changes
            if (!empty($added) && !empty($removed)) {
                // Both added and removed flags
                $message = "<strong><u>{$flagger}</u></strong> has updated flags for article <strong>\"{$articleTitle}\"</strong> posted by <strong><u>{$author}</u></strong> ";
                $message .= "added " . implode(', ', $added);
                $message .= " and removed " . implode(', ', $removed) . '.';
            } elseif (!empty($added)) {
                // Only added flags
                // Check if user had no flags before (all zeros)
                if ($prevAbusive == 0 && $prevSpam == 0 && $prevCopyright == 0) {
                    $message = "<strong><u>{$flagger}</u></strong> has re-flagged the article <strong>\"{$articleTitle}\"</strong> posted by <strong><u>{$author}</u></strong> as ";
                    
                    if (count($added) > 1) {
                        $lastFlag = array_pop($added);
                        $flagText = implode(', ', $added) . ' and ' . $lastFlag;
                        $message .= $flagText . '.';
                    } else {
                        $message .= $added[0] . '.';
                    }
                } else {
                    $message = "<strong><u>{$flagger}</u></strong> has added flags to article <strong>\"{$articleTitle}\"</strong> posted by <strong><u>{$author}</u></strong> ";
                    
                    if (count($added) > 1) {
                        $lastFlag = array_pop($added);
                        $flagText = implode(', ', $added) . ' and ' . $lastFlag;
                        $message .= $flagText . '.';
                    } else {
                        $message .= $added[0] . '.';
                    }
                }
            } elseif (!empty($removed)) {
                // Only removed flags
                // Check if user removed all flags
                if ($currentAbusive == 0 && $currentSpam == 0 && $currentCopyright == 0) {
                    $message = "<strong><u>{$flagger}</u></strong> has un-flagged the article <strong>\"{$articleTitle}\"</strong> posted by <strong><u>{$author}</u></strong>.";
                } else {
                    $message = "<strong><u>{$flagger}</u></strong> has removed flags from article <strong>\"{$articleTitle}\"</strong> posted by <strong><u>{$author}</u></strong> ";
                    
                    if (count($removed) > 1) {
                        $lastFlag = array_pop($removed);
                        $flagText = implode(', ', $removed) . ' and ' . $lastFlag;
                        $message .= $flagText . '.';
                    } else {
                        $message .= $removed[0] . '.';
                    }
                }
            } else {
                // No changes (shouldn't happen with no-change check in flag_article.php)
               
                $lastFlagId = $currentId;
                continue;
            }
        }
        
        echo json_encode([
            'flagnumber' => $currentId,
            'time' => $row['time'],
            'message' => $message
        ], JSON_UNESCAPED_SLASHES);
        
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