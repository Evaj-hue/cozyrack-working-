<?php 
include "config.php";

// Check connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the weight for the record with id = 1
$sql = "SELECT weight FROM weight WHERE id = 1";
$result = mysqli_query($conn, $sql);

// Check if the query was successful
if ($result) {
    // Fetch the weight
    $row = mysqli_fetch_assoc($result);
    echo $row['weight'];
} else {
    // Handle the error if the query fails
    echo "Error: " . mysqli_error($conn);
}

// Close the connection
mysqli_close($conn);
?>
