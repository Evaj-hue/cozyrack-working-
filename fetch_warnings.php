<?php
include "config.php";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT id, weight, message, created_at FROM weight_warnings ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $datetime = new DateTime($row['created_at']);
        $time = $datetime->format('h:i:s A');
        $date = $datetime->format('Y-m-d');

        // Add row class based on the warning type
        $rowClass = (strpos($row['message'], 'too light') !== false) ? 'table-warning' : 'table-danger';

        echo "<tr class='{$rowClass} warning-row'>
                <td>{$row['id']}</td>
                <td>{$row['weight']} kg</td>
                <td class='fw-bold'>{$row['message']}</td>
                <td>{$time}</td>
                <td>{$date}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center'>No warnings yet 🎉</td></tr>";
}

mysqli_close($conn);
?>
