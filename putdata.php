<?php
include "config.php";

// Get and validate input
$weight = isset($_GET['weight']) ? floatval($_GET['weight']) : 0.0;
$status = isset($_GET['status']) ? $_GET['status'] : 'unknown';

// Constants
$itemWeight = 0.5; // kg per item
$tolerance = 0.05; // 50 grams

// Current timestamp
date_default_timezone_set("Asia/Manila");
$date = date("Y-m-d");
$time = date("H:i:s");

// Connect to database
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get last recorded weight
$lastWeightSql = "SELECT weight FROM weight ORDER BY created_at DESC LIMIT 1";
$lastResult = mysqli_query($conn, $lastWeightSql);
$lastWeight = 0.0;

if ($lastResult && mysqli_num_rows($lastResult) > 0) {
    $lastWeight = floatval(mysqli_fetch_assoc($lastResult)['weight']);
}

$weightDiff = $weight - $lastWeight;

if (abs($weightDiff) >= $tolerance) {
    // Calculate item count change
    $itemCountChange = round(abs($weightDiff) / $itemWeight);
    $operation = ($weightDiff > 0) ? "added" : "removed";

    // INSERT into weight_changes (history)
    $insertHistory = "INSERT INTO weight_changes (weight, status, time, date, item_count, operation)
                      VALUES ('$weight', '$status', '$time', '$date', '$itemCountChange', '$operation')";
    mysqli_query($conn, $insertHistory);

    // INSERT new current weight into weight table (always log latest)
    $insertWeight = "INSERT INTO weight (weight, status, time, date)
                     VALUES ('$weight', '$status', '$time', '$date')";
    mysqli_query($conn, $insertWeight);

    echo "Change recorded: $itemCountChange item(s) $operation";
} else {
    echo "No significant weight change detected.";
}

mysqli_close($conn);
?>
