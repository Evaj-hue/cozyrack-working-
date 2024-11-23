<?php
include "config.php";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

// Fetch the last 10 records from the weight_changes table
$sql = "SELECT * FROM weight_changes ORDER BY id DESC LIMIT 10";
$result = mysqli_query($conn, $sql);

if ($result) {
  // Display the records in a table
  $counter = 1;
  while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $counter . "</td>";
    echo "<td>" . $row['weight'] . "g</td>";
    echo "<td>" . $row['time'] . "</td>";
    echo "<td>" . $row['date'] . "</td>";
    echo "<td>" . $row['item_count'] . "</td>";
    echo "<td>" . $row['operation'] . "</td>";
    echo "</tr>";
    $counter++;
  }
} else {
  echo "<tr><td colspan='7'>No data found.</td></tr>";
}

// Close connection
mysqli_close($conn);
?>
