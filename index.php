<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>📦 CozyRack - Real-Time Load Cell Monitor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap + Chart.js -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Segoe UI', sans-serif;
      margin-left: 200px; /* space for sidebar */
    }
    .monitor {
      display: flex;
      gap: 30px;
      justify-content: center;
      margin-top: 40px;
      flex-wrap: wrap;
    }
    .card-box {
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      text-align: center;
      height: 220px;
    }
    .weight-box { background-color: #fff3cd; }
    .item-box { background-color: #d1e7dd; }
    .label { font-size: 1.2rem; font-weight: 600; margin-bottom: 10px; }
    .value { font-size: 2.5rem; font-weight: bold; }
    .graph-section {
      display: flex;
      gap: 30px;
      justify-content: center;
      flex-wrap: wrap;
      margin: 50px auto;
      max-width: 1100px;
    }
    .graph-container {
      display: flex;
      justify-content: center;
      gap: 30px;
      margin: 40px auto;
      flex-wrap: wrap;
    }
    .graph-box {
      width: 400px;
    }
    .table-container {
      width: calc(100% - 220px);
      margin: 30px auto;
    }
    .table th {
      background-color: #007bff;
      color: white;
    }
    .btns {
      text-align: center;
      margin-bottom: 20px;
    }
    
  </style>
</head>
<body>
  <h2 class="text-center mt-4">📦 CozyRack - Real-Time Load Cell Monitor</h2>

  <!-- Info Boxes -->
  <div class="monitor">
    <div class="card-box weight-box">
      <div class="label">Real-Time Weight</div>
      <div id="weight" class="value">0.00 kg</div>
    </div>
    <div class="card-box item-box">
      <div class="label">Total Items</div>
      <div id="items" class="value">0</div>
    </div>
  </div>

  <!-- Graphs -->
  <div class="graph-container">
    <div class="graph-box">
      <canvas id="weightChart"></canvas>
    </div>
    <div class="graph-box">
      <canvas id="itemChart"></canvas>
    </div>
  </div>

  <!-- Buttons -->
  <div class="btns">
    <button class="btn btn-primary" onclick="downloadCSV()">Export Graph Data as CSV</button>
    <button class="btn btn-secondary" onclick="downloadPNG()">Download Graph as PNG</button>
    <button class="btn btn-danger" onclick="resetGraphs()">Reset Graph</button>
  </div>

  <!-- Weight changes table -->
  <div class="table-container">
    <h3 class="text-center mb-4">📊 Weight Changes History</h3>
    <table id="weightChangesTable" class="table table-bordered text-center">
      <thead>
        <tr>
          <th>#</th>
          <th>Weight (kg)</th>
          <th>Time</th>
          <th>Date</th>
          <th>Item Count</th>
          <th>Operation</th>
        </tr>
      </thead>
      <tbody>
        <!-- Data from AJAX will go here -->
      </tbody>
    </table>
  </div>
  <div id="warningBox" class="alert alert-warning text-center" style="display: none;"></div>

  <div class="table-container">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="all_warnings.php" class="btn btn-outline-danger">
          🔍 View All Warnings</a>
        <h4 class="text-center text-danger">⚠️ Unrecognized Weight Warnings</h4>
        <div></div> <!-- Spacer to keep title centered -->
      </div>
    </div>
    <table id="warningsTable" class="table table-bordered table-sm">
      <thead>
        <tr>
          <th>ID</th>
          <th>Weight (kg)</th>
          <th>Message</th>
          <th>Time</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody id="warningTableBody"></tbody>
    </table>
  </div>

<script>
  $(document).ready(function() {
    // Initialize DataTable for both tables
    $('#weightChangesTable').DataTable({
      "searching": true, // Enable search
      "paging": true,    // Enable paging
      "pageLength": 5,   // Default number of rows per page
      "lengthMenu": [5, 10, 20] // Options for rows per page
    });

    $('#warningsTable').DataTable({
      "searching": true, // Enable search
      "paging": true,    // Enable paging
      "pageLength": 5,   // Default number of rows per page
      "lengthMenu": [5, 10, 20] // Options for rows per page
    });
  });

  // Your existing refreshDisplay, filterTable, and other functions remain the same
  let lastWeight = 0;
  let lastItemCount = 0;
  const weightData = [], itemData = [], labels = [];

  const weightChart = new Chart(document.getElementById('weightChart'), {
    type: 'line', data: { labels, datasets: [{ label: 'Weight (kg)', data: weightData, borderColor: 'orange', fill: false }] }, options: { responsive: true }
  });

  const itemChart = new Chart(document.getElementById('itemChart'), {
    type: 'line', data: { labels, datasets: [{ label: 'Item Count', data: itemData, borderColor: 'green', fill: false }] }, options: { responsive: true }
  });

  function refreshDisplay() {
    $.get('getvalue.php', function(res) {
      const parsed = JSON.parse(res);
      const newWeight = parseFloat(parsed.weight);
      const newItem = parseInt(parsed.item_count);

      $('#weight').text(parseFloat(parsed.weight).toFixed(2) + ' kg');
      $('#items').text(parsed.item_count);

      const timestamp = new Date().toLocaleTimeString();

      if (Math.abs(newWeight - lastWeight) > 0.1 || newItem !== lastItemCount) {
        labels.push(timestamp);
        weightData.push(newWeight);
        itemData.push(newItem);

        if (labels.length > 10) {
          labels.shift();
          weightData.shift();
          itemData.shift();
        }

        weightChart.update();
        itemChart.update();

        lastWeight = newWeight;
        lastItemCount = newItem;
      }
    });

    // Update table
    $.get('fetch_weight_changes.php', function(data) {
      $('#weightChangesTable').DataTable().clear().draw();  // Clear existing data
      $('#weightChangesTable').DataTable().rows.add($(data)).draw();  // Add new data to the table
    });
  }

  function refreshWarnings() {
    $.get('fetch_warnings.php', function(data) {
      $('#warningTableBody').html(data);
      $('#warningsTable').DataTable().clear().draw();
      $('#warningsTable').DataTable().rows.add($(data)).draw();
    });
  }

  setInterval(refreshDisplay, 5000);
  setInterval(refreshWarnings, 5000);
  refreshDisplay();
  refreshWarnings();
  
  // Download CSV function
  function downloadCSV() {
    let csv = 'Timestamp,Weight,Item Count\n';
    for (let i = 0; i < labels.length; i++) {
      csv += `${labels[i]},${weightData[i]},${itemData[i]}\n`;
    }
    const blob = new Blob([csv], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    const today = new Date().toISOString().slice(0, 10);
    link.download = `rack_data_${today}.csv`;
    link.click();
  }

  function downloadPNG() {
    const today = new Date().toISOString().slice(0, 10);
    const link1 = document.createElement('a');
    link1.download = `rack_weight_graph_${today}.png`;
    link1.href = weightChart.toBase64Image();
    link1.click();

    const link2 = document.createElement('a');
    link2.download = `rack_item_graph_${today}.png`;
    link2.href = itemChart.toBase64Image();
    link2.click();
  }

  function resetGraphs() {
    labels.length = weightData.length = itemData.length = 0;
    weightChart.update();
    itemChart.update();
  }
</script>

</body>
</html>
