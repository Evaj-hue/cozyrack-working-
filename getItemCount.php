<?php
include "config.php";

// Connect to database
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Calculate net item count: added - removed
$sql = "SELECT 
            SUM(CASE WHEN operation = 'added' THEN item_count ELSE 0 END) -
            SUM(CASE WHEN operation = 'removed' THEN item_count ELSE 0 END) 
            AS total_items 
        FROM weight_changes";

$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Total Item Count: " . max(0, $row['total_items']); // avoid negative count
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
