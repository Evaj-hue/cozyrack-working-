<?php
include "config.php";

$itemWeight = 0.5; // Set item weight
$tolerance = 0.02;
$maxItems = 20; // You can adjust this depending on your rack's real capacity

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get latest weight
$sql = "SELECT weight FROM weight ORDER BY created_at DESC LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $currentWeight = floatval($row['weight']);

    $recognized = false;
    $matchedItems = 0;

    for ($i = 1; $i <= $maxItems; $i++) {
        $expected = $i * $itemWeight;
        if ($currentWeight >= ($expected - $tolerance) && $currentWeight <= ($expected + $tolerance)) {
            $recognized = true;
            $matchedItems = $i;
            break;
        }
    }

    if (!$recognized && $currentWeight >= 0.1) {
        $msg = '';

        if ($currentWeight < ($itemWeight - $tolerance)) {
            $msg = "Too light - item not recognized";
        } else {
            $msg = "Too heavy - item not recognized";
        }

        // Insert warning
        $stmt = $conn->prepare("INSERT INTO weight_warnings (weight, message) VALUES (?, ?)");
        $stmt->bind_param("ds", $currentWeight, $msg);
        $stmt->execute();
    }

    echo json_encode([
        'weight' => $currentWeight,
        'item_count' => $matchedItems
    ]);
} else {
    echo json_encode([
        'weight' => 0,
        'item_count' => 0
    ]);
}

mysqli_close($conn);
?>
