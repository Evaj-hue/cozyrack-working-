<?php
include "config.php";

// Get the weight from the query parameter
$weight = $_GET['weight'];

// Default status to 'unknown' if not set
$status = isset($_GET['status']) ? $_GET['status'] : 'unknown';

// Current date and time
$date = date("d/m/Y");
$datename = date("l");
date_default_timezone_set("Asia/manila");
$time = date("h:i A");

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

// Get the last recorded weight
$sql = "SELECT weight FROM weight WHERE id = 1 ORDER BY timestamp DESC LIMIT 1";
$result = mysqli_query($conn, $sql);
$lastWeight = mysqli_fetch_assoc($result)['weight'];

// Ensure both weights are treated as numeric values
$weight = (float) $_GET['weight'];  // Cast the input weight to float
$lastWeight = (float) $lastWeight;  // Cast the database weight to float

// Calculate the weight difference
$weightDifference = $lastWeight - $weight;

if ($weightDifference != 0) {
    // Calculate the number of items added or removed (assume 300 grams per item)
    $itemsChanged = ceil(abs($weightDifference) / 300);

    // Determine if the weight decreased or increased
    $operation = ($weightDifference > 0) ? 'removed' : 'added';

    // Insert the new record for each item changed
    for ($i = 0; $i < $itemsChanged; $i++) {
        $insertSql = "INSERT INTO weight_changes (weight, status, time, date, item_count, operation) 
                      VALUES ('$weight', '$status', '$time', '$date', 1, '$operation')";

        if (mysqli_query($conn, $insertSql)) {
            echo "Record inserted successfully for item " . ($i + 1) . "\n";
        } else {
            echo "Error: " . $insertSql . "<br>" . mysqli_error($conn);
        }
    }

    // Update the main weight record
    $updateSql = "UPDATE weight SET weight = '$weight', status = '$status', time = '$time', date = '$date' WHERE id = 1";

    if (mysqli_query($conn, $updateSql)) {
        echo "Weight updated successfully";
    } else {
        echo "Error: " . $updateSql . "<br>" . mysqli_error($conn);
    }
} else {
    echo "No weight change detected. No update or insert performed.";
}

// Close connection
mysqli_close($conn);
?>