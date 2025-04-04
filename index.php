<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Real-Time Load Cell Monitor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

  <!-- DataTables + Buttons -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

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
    .weight-box {
      background-color: #ffecb3;
    }
    .item-box {
      background-color: #c8e6c9;
    }
    .label {
      font-size: 1.3rem;
      font-weight: bold;
      margin-bottom: 10px;
    }
    .value {
      font-size: 2.8rem;
      font-weight: bold;
    }
    .table-container {
      margin: 60px auto;
      width: 90%;
    }
    .table th {
      background-color: #007bff;
      color: white;
    }
    #exportCSV {
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

  <h2 class="text-center mt-4">📦 CozyRack - Load Cell Monitor</h2>

  <!-- Real-time display boxes -->
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

  <!-- Table for weight changes -->
  <div class="table-container">
    <h3 class="text-center mb-4">📊 Weight Changes History</h3>
    <div class="text-end mb-2">
      <button id="exportCSV" class="btn btn-sm btn-outline-primary">⬇️ Export CSV</button>
    </div>
    <table id="changesTable" class="table table-bordered text-center">
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
        <!-- AJAX-loaded rows -->
      </tbody>
    </table>
  </div>

  <!-- JS Script to load and update real-time data -->
  <script>
    let dataTable;

    function refreshDisplay() {
      // Fetch real-time weight and item count
      $.ajax({
        url: 'getvalue.php',
        method: 'GET',
        success: function (data) {
          const parsed = JSON.parse(data);
          $('#weight').text(parsed.weight + ' kg');
          $('#items').text(parsed.item_count);
        }
      });

      // Fetch table data
      $.ajax({
        url: 'fetch_weight_changes.php',
        method: 'GET',
        success: function (response) {
          $('#weightChangesTable').html(response);

          if ($.fn.DataTable.isDataTable('#changesTable')) {
            dataTable.destroy(); // Destroy existing DataTable instance
          }

          dataTable = $('#changesTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
              {
                extend: 'csvHtml5',
                text: 'Export CSV',
                title: 'Weight_Changes_History',
                className: 'd-none', // Hide native button
                exportOptions: {
                  columns: ':visible'
                }
              }
            ]
          });
        }
      });
    }

    // Trigger CSV export on button click
    $('#exportCSV').on('click', function () {
      if (dataTable) {
        dataTable.button('.buttons-csv').trigger();
      }
    });

    // Refresh every 2 seconds
    setInterval(refreshDisplay, 2000);
    refreshDisplay();
  </script>

</body>
</html>
