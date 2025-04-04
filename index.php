<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CozyRack - Load Cell Monitor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }

    .monitor {
      display: flex;
      justify-content: center;
      gap: 40px;
      margin-top: 50px;
    }

    .card-box {
      width: 300px;
      height: 250px;
      border-radius: 20px;
      padding: 20px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
      text-align: center;
    }

    .weight-box { background-color: #ffecb3; }
    .item-box { background-color: #c8e6c9; }

    .label {
      font-size: 1.3rem;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .value {
      font-size: 2.8rem;
      font-weight: bold;
    }

    .table-container, .graph-container {
      margin: 60px auto;
      width: 90%;
    }

    .table th {
      background-color: #007bff;
      color: white;
    }

    canvas {
      background: #ffffff;
      border-radius: 10px;
      padding: 10px;
    }
  </style>
</head>
<body>

  <h2 class="text-center mt-4">📦 CozyRack - Load Cell Monitor</h2>

  <!-- Real-time boxes -->
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
  <div class="graph-container row">
    <div class="col-md-6">
      <h5 class="text-center">📈 Weight Trend</h5>
      <canvas id="weightChart" height="200"></canvas>
    </div>
    <div class="col-md-6">
      <h5 class="text-center">📦 Item Count Trend</h5>
      <canvas id="itemChart" height="200"></canvas>
    </div>
  </div>

  <!-- Weight changes table -->
  <div class="table-container">
    <h3 class="text-center mb-4">📊 Weight Changes History</h3>
    <table class="table table-bordered text-center">
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
      <tbody id="weightChangesTable">
        <!-- AJAX content -->
      </tbody>
    </table>
  </div>

  <script>
    let lastWeight = 0;
    let lastItemCount = 0;
    const tolerance = 0.05;

    const weightData = {
      labels: [],
      datasets: [{
        label: 'Weight (kg)',
        data: [],
        fill: false,
        borderColor: 'orange',
        tension: 0.1
      }]
    };

    const itemData = {
      labels: [],
      datasets: [{
        label: 'Item Count',
        data: [],
        fill: false,
        borderColor: 'green',
        tension: 0.1
      }]
    };

    const weightChart = new Chart(document.getElementById('weightChart'), {
      type: 'line',
      data: weightData,
    });

    const itemChart = new Chart(document.getElementById('itemChart'), {
      type: 'line',
      data: itemData,
    });

    function refreshData() {
      $.get('getvalue.php', function (res) {
        const data = JSON.parse(res);
        const newWeight = parseFloat(data.weight);
        const newItemCount = parseInt(data.item_count);
        const timestamp = new Date().toLocaleTimeString();

        $('#weight').text(newWeight.toFixed(2) + ' kg');
        $('#items').text(newItemCount);

        // Only update graphs if significant change
        if (Math.abs(newWeight - lastWeight) >= tolerance || newItemCount !== lastItemCount) {
          weightData.labels.push(timestamp);
          weightData.datasets[0].data.push(newWeight);

          itemData.labels.push(timestamp);
          itemData.datasets[0].data.push(newItemCount);

          weightChart.update();
          itemChart.update();

          lastWeight = newWeight;
          lastItemCount = newItemCount;
        }
      });

      $.get('fetch_weight_changes.php', function (html) {
        $('#weightChangesTable').html(html);
      });
    }

    setInterval(refreshData, 2000); // Check every 2s, but only update if significant
    refreshData();
  </script>

</body>
</html>
