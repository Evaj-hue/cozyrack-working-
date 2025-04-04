<?php
include "config.php";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT id, weight, time, date, item_count, operation FROM weight_changes ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

$output = "";
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $output .= "<tr>
                      <td>{$row['id']}</td>
                      <td>{$row['weight']}</td>
                      <td>{$row['time']}</td>
                      <td>{$row['date']}</td>
                      <td>{$row['item_count']}</td>
                      <td>{$row['operation']}</td>
                    </tr>";
    }
} else {
    $output = "<tr><td colspan='6'>No weight changes found.</td></tr>";
}

echo $output;
mysqli_close($conn);
?>
