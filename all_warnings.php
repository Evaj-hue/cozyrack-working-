<?php
// config.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_management";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>📋 All Unrecognized Weight Warnings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
      padding: 30px;
      margin-left: 200px; /* Adjust if you have a sidebar */
    }
    .container {
      max-width: 1100px;
      margin: auto;
    }
    h2 {
      margin-bottom: 30px;
      text-align: center;
    }
    .table thead {
      background-color: #dc3545;
      color: white;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <a href="index.php" class="btn btn-outline-secondary">
      🔙 Return to Racks
    </a>
    <h2 class="m-0">⚠️ All Unrecognized Weight Warnings</h2>
    <div></div> <!-- Spacer to keep title centered -->
  </div>
<div class="container">

  <table id="warningsTable" class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Weight (kg)</th>
        <th>Message</th>
        <th>Time</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
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
    </tbody>
  </table>
</div>

<script>
  $(document).ready(function () {
    $('#warningsTable').DataTable({
      pageLength: 10,
      lengthMenu: [5, 10, 25, 50],
      order: [[0, 'desc']],
    });
  });
</script>

</body>
</html>
