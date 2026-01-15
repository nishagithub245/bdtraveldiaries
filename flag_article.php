<?php
require 'db_connection.php';




ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);






$username = $_POST['username'] ?? '';
$articlenumber = (int)($_POST['articlenumber'] ?? 0);
$flagabusive = (int)($_POST['flagabusive'] ?? 0);
$flagspam = (int)($_POST['flagspam'] ?? 0);
$flagcopyright = (int)($_POST['flagcopyright'] ?? 0);
$time = date('Y-m-d H:i:s');

if (!$username || !$articlenumber) {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
    exit;
}

/* Fetch current active record */
$checkSql = "
    SELECT flagabusive, flagspam, flagcopyright 
    FROM flags 
    WHERE username = ? AND articlenumber = ? AND recorded = 1
";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("si", $username, $articlenumber);
$checkStmt->execute();
$current = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

/* If flags are exactly the same, allow unflagging or reflagging */
$allowUpdate = true;

/* Only block if no change at all */
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

/* Deactivate previous record */
$updateSql = "
    UPDATE flags 
    SET recorded = 0 
    WHERE username = ? AND articlenumber = ? AND recorded = 1
";
$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("si", $username, $articlenumber);
$updateStmt->execute();
$updateStmt->close();

/* Insert new record with recorded=1 */
$insertSql = "
    INSERT INTO flags 
    (username, articlenumber, flagabusive, flagspam, flagcopyright, time, recorded) 
    VALUES (?, ?, ?, ?, ?, ?, 1)
";
$stmt = $conn->prepare($insertSql);
$stmt->bind_param(
    "siiiis",
    $username,
    $articlenumber,
    $flagabusive,
    $flagspam,
    $flagcopyright,
    $time
);

if ($stmt->execute()) {
    // Successful flag submission
    echo json_encode(["status" => "success", "message" => "Flags submitted/updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
