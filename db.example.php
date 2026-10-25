<?php
$conn = new mysqli(
    "127.0.0.1",
    "YOUR_DB_USERNAME",
    "YOUR_DB_PASSWORD",
    "movie_booking",
    3306
);

if ($conn->connect_error) {
    die("Database connection failed.");
}
?>