<?php
include "config.php";

// Connect to DB
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the latest weight changes (limit to recent 10 entries, optional)
$sql = "SELECT id, weight, time, date, item_count, operation FROM weight_changes ORDER BY id DESC LIMIT 10";
$result = mysqli_query($conn, $sql);

// Output HTML rows
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['weight']}</td>
                <td>{$row['time']}</td>
                <td>{$row['date']}</td>
                <td>{$row['item_count']}</td>
                <td>{$row['operation']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No data available</td></tr>";
}

mysqli_close($conn);
?>