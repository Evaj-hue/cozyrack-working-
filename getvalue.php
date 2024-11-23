<?php
include "config.php";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the latest weight and last item count from the 'weight' table
$sql = "SELECT weight, last_item_count FROM weight ORDER BY timestamp DESC LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    // Send back the weight and item count as JSON
    echo json_encode([
        'weight' => $row['weight'],
        'item_count' => $row['last_item_count']
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
