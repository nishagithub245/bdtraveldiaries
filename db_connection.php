<?php
$conn = new mysqli("localhost", "root", "", "bdtravediaries");

if ($conn->connect_error) {
    die("Database connection failed");
}
?>