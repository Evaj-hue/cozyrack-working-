<?php
include "config.php";

// Constants
$itemWeight = 0.5; // Assuming each item weighs 0.5kg

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the latest weight from the 'weight' table
$sql = "SELECT weight FROM weight ORDER BY created_at DESC LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $currentWeight = floatval($row['weight']);

    // Calculate the item count
    $itemCount = round($currentWeight / $itemWeight);

    // Send back the weight and item count as JSON
    echo json_encode([
        'weight' => $currentWeight . 'g',
        'item_count' => $itemCount
    ]);
} else {
    echo json_encode([
        'weight' => '0g',
        'item_count' => '0'
    ]);
}

// Close connection
mysqli_close($conn);
?>
