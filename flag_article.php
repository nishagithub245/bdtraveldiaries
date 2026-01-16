<?php
require 'db_connection.php';

// Enable errors for debugging
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

// --- CHECK ARTICLE OWNER (user cannot flag own article) ---
$ownerStmt = $conn->prepare("SELECT username FROM articles WHERE articlenumber = ?");
$ownerStmt->bind_param("i", $articlenumber);
$ownerStmt->execute();
$owner = $ownerStmt->get_result()->fetch_assoc();
$ownerStmt->close();

if ($owner && $owner['username'] === $username) {
    echo json_encode(["status" => "error", "message" => "You cannot flag your own article"]);
    exit;
}

// --- FETCH CURRENT ACTIVE FLAG RECORD ---
$checkStmt = $conn->prepare("
    SELECT flagabusive, flagspam, flagcopyright
    FROM flags
    WHERE username = ? AND articlenumber = ? AND recorded = 1
");
$checkStmt->bind_param("si", $username, $articlenumber);
$checkStmt->execute();
$current = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

// --- CHECK IF ANY CHANGE OCCURRED ---
$allowUpdate = true;
if ($current &&
    $current['flagabusive'] == $flagabusive &&
    $current['flagspam'] == $flagspam &&
    $current['flagcopyright'] == $flagcopyright) {
    $allowUpdate = false;
}

if (!$allowUpdate) {
    echo json_encode(["status" => "error", "message" => "No changes in flags"]);
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
    $message = ($flagabusive || $flagspam || $flagcopyright)
        ? "Flags submitted/updated successfully"
        : "Article unflagged successfully";

    echo json_encode(["status" => "success", "message" => $message]);
} else {
    echo json_encode(["status" => "error", "message" => $insertStmt->error]);
}

$insertStmt->close();
$conn->close();
?>
