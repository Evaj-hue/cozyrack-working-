<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CozyRack - Load Cell Monitor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

  <style>
    body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .monitor { display: flex; justify-content: center; gap: 40px; margin-top: 40px; flex-wrap: wrap; }
    .card-box { width: 280px; height: 200px; border-radius: 20px; padding: 20px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); text-align: center; }
    .weight-box { background-color: #ffecb3; }
    .item-box { background-color: #c8e6c9; }
    .label { font-size: 1.3rem; font-weight: bold; margin-bottom: 10px; }
    .value { font-size: 2.5rem; font-weight: bold; }
    .table-container { margin: 60px auto; width: 95%; }
    .table th { background-color: #007bff; color: white; }
    .graph-row { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; margin-top: 40px; }
    .graph-box { width: 45%; min-width: 300px; background: #fff; padding: 20px; border-radius: 20px; box-shadow: 0 6px 12px rgba(0,0,0,0.1); text-align: center; }
  </style>
</head>
<body>

  <h2 class="text-center mt-4">📦 CozyRack - Load Cell Monitor</h2>

  <!-- Real-time Display -->
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
  <div class="chart-container">
    <div class="graph-row">
      <div class="graph-box">
        <canvas id="weightChart"></canvas>
        <button class="btn btn-sm btn-outline-secondary mt-2" onclick="downloadChart(weightChart, 'weight-chart')">Download PNG</button>
      </div>
      <div class="graph-box">
        <canvas id="itemChart"></canvas>
        <button class="btn btn-sm btn-outline-secondary mt-2" onclick="downloadChart(itemChart, 'item-chart')">Download PNG</button>
      </div>
    </div>

    <div class="text-center mt-3">
      <button class="btn btn-success me-2" onclick="exportGraphData()">⬇️ Export Graph Data to CSV</button>
      <button class="btn btn-danger" onclick="resetGraphs()">🔄 Reset Graph</button>
    </div>
  </div>

  <!-- Table -->
  <div class="table-container">
    <h3 class="text-center mb-4">📊 Weight Changes History</h3>
    <table id="changesTable" class="display table table-bordered text-center">
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
        <!-- Filled via fetch_weight_changes.php -->
      </tbody>
    </table>
  </div>

  <script>
    let lastWeight = 0, lastItems = 0;
    let weightChart, itemChart;
    const weightData = [], itemData = [], labels = [];

    function initCharts() {
      const ctx1 = document.getElementById('weightChart').getContext('2d');
      const ctx2 = document.getElementById('itemChart').getContext('2d');

      weightChart = new Chart(ctx1, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Weight (kg)',
            data: weightData,
            borderColor: '#f57c00',
            tension: 0.3,
            fill: false
          }]
        }
      });

      itemChart = new Chart(ctx2, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Item Count',
            data: itemData,
            borderColor: '#388e3c',
            tension: 0.3,
            fill: false
          }]
        }
      });
    }

    function resetGraphs() {
      labels.length = 0;
      weightData.length = 0;
      itemData.length = 0;
      weightChart.update();
      itemChart.update();
    }

    function downloadChart(chart, filename) {
      const link = document.createElement('a');
      link.href = chart.toBase64Image();
      const today = new Date().toISOString().slice(0, 10);
      link.download = `rack_chart_data_${today}_${filename}.png`;

      link.click();
    }

    function exportGraphData() {
      let csv = 'Time,Weight (kg),Item Count\n';
      for (let i = 0; i < labels.length; i++) {
        csv += `${labels[i]},${weightData[i]},${itemData[i]}\n`;
      }

      const blob = new Blob([csv], { type: 'text/csv' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      const today = new Date().toISOString().slice(0, 10);
      a.download = `rack_data_${today}.csv`;

      a.click();
      URL.revokeObjectURL(url);
    }

    function refreshDisplay() {
      $.get('getvalue.php', function (data) {
        const parsed = JSON.parse(data);
        const weightValue = parseFloat(parsed.weight);
        const itemCount = parseInt(parsed.item_count);
        const now = new Date();
        const timeStr = now.toLocaleTimeString();

        $('#weight').text(parsed.weight);
        $('#items').text(itemCount);

        if (Math.abs(weightValue - lastWeight) >= 0.05 || itemCount !== lastItems) {
          labels.push(timeStr);
          weightData.push(weightValue);
          itemData.push(itemCount);

          if (labels.length > 10) {
            labels.shift();
            weightData.shift();
            itemData.shift();
          }

          weightChart.update();
          itemChart.update();

          lastWeight = weightValue;
          lastItems = itemCount;
        }
      });

      $('#weightChangesTable').load('fetch_weight_changes.php', function () {
        if (!$.fn.DataTable.isDataTable('#changesTable')) {
          $('#changesTable').DataTable({
            order: [[3, 'desc']], // Default sort by Date descending
            destroy: true
          });
        }
      });
    }

    $(document).ready(function () {
      initCharts();
      refreshDisplay();
      setInterval(refreshDisplay, 5000);
    });
  </script>
</body>
</html>
