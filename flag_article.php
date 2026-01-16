<?php
require 'db_connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- VALIDATE INPUT ---
if (!isset($_POST['username']) || trim($_POST['username']) === '' ||
    !isset($_POST['articlenumber']) || !is_numeric($_POST['articlenumber'])) {
    echo json_encode(["status" => "error", "message" => "Missing username or article number"]);
    exit;
}

$username = trim($_POST['username']);
$articlenumber = (int)$_POST['articlenumber'];
$flagabusive = isset($_POST['flagabusive']) ? (int)$_POST['flagabusive'] : 0;
$flagspam = isset($_POST['flagspam']) ? (int)$_POST['flagspam'] : 0;
$flagcopyright = isset($_POST['flagcopyright']) ? (int)$_POST['flagcopyright'] : 0;
$time = date('Y-m-d H:i:s');

// --- CHECK ARTICLE OWNER ---
$ownerStmt = $conn->prepare("SELECT username FROM articles WHERE articlenumber = ?");
$ownerStmt->bind_param("i", $articlenumber);
$ownerStmt->execute();
$owner = $ownerStmt->get_result()->fetch_assoc();
$ownerStmt->close();

if ($owner && $owner['username'] === $username) {
    echo json_encode(["status" => "error", "message" => "You cannot flag your own article"]);
    exit;
}

// --- DEACTIVATE PREVIOUS ACTIVE FLAG ---
$updateStmt = $conn->prepare("
    UPDATE flags 
    SET recorded = 0 
    WHERE username = ? AND articlenumber = ? AND recorded = 1
");
$updateStmt->bind_param("si", $username, $articlenumber);
$updateStmt->execute();
$updateStmt->close();

// --- INSERT NEW FLAG RECORD ---
$insertStmt = $conn->prepare("
    INSERT INTO flags 
    (username, articlenumber, flagabusive, flagspam, flagcopyright, time, recorded) 
    VALUES (?, ?, ?, ?, ?, ?, 1)
");
$insertStmt->bind_param("siiiis", $username, $articlenumber, $flagabusive, $flagspam, $flagcopyright, $time);

if ($insertStmt->execute()) {
    // Determine message
    if ($flagabusive || $flagspam || $flagcopyright) {
        // Check if this is first flag or update
        $checkPrev = $conn->prepare("SELECT COUNT(*) as count FROM flags WHERE username = ? AND articlenumber = ?");
        $checkPrev->bind_param("si", $username, $articlenumber);
        $checkPrev->execute();
        $count = $checkPrev->get_result()->fetch_assoc()['count'];
        $checkPrev->close();
        
        $message = ($count > 1) ? "Flags updated successfully" : "Article flagged successfully";
    } else {
        $message = "Article unflagged successfully";
    }
    
    echo json_encode(["status" => "success", "message" => $message]);
} else {
    // If still getting duplicate error, the constraint wasn't removed
    if (strpos($insertStmt->error, "Duplicate") !== false) {
        echo json_encode([
            "status" => "error", 
            "message" => "Database constraint error. Please run: ALTER TABLE flags DROP INDEX unique_flag;"
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $insertStmt->error]);
    }
}

$insertStmt->close();
$conn->close();
?>