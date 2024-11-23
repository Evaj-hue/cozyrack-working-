<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Weight Machine</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.js"></script>
</head>
<body>

  <div class="container mt-5">
    <h1 class="d-flex justify-content-center mb-3" style="font-size: 25px;">CozyRack Monitor</h1>
    
    <!-- Podium-like Rack -->
    <div class="row justify-content-center">
      <div class="col-md-4">
        <div class="card shadow-lg" style="border-radius: 10px;">
          <div class="row g-0">
            <div class="col-md-12">
              <div class="card-body d-flex flex-column justify-content-center align-items-center bg-light" style="height: 300px;">
                <h5 class="card-title text-center">WEIGHT DISPLAY</h5>
                <img src="weight.png" class="img-fluid rounded-start mb-3" alt="Scale" style="width: 100px;">
                <div class="digital" style="font-size: 2em;">0g</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-lg" style="border-radius: 10px;">
          <div class="row g-0">
            <div class="col-md-12">
              <div class="card-body d-flex flex-column justify-content-center align-items-center bg-info" style="height: 300px; color: white;">
                <h5 class="card-title text-center">LAST WEIGHT</h5>
                <div id="lastw" class="d-flex flex-column align-items-center"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-lg" style="border-radius: 10px;">
          <div class="row g-0">
            <div class="col-md-12">
              <div class="card-body d-flex flex-column justify-content-center align-items-center bg-success" style="height: 300px; color: white;">
                <h5 class="card-title text-center">LAST ITEM COUNT</h5>
                <div id="last_count" class="d-flex flex-column align-items-center" style="font-size: 1.5em;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table to display weight_changes -->
    <div class="mt-5">
      <h2 class="text-center">Weight Changes</h2>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th>Weight</th>
            
            <th>Time</th>
            <th>Date</th>
            <th>Item Count</th>
            <th>Operation</th>
          </tr>
        </thead>
        <tbody id="weightChangesTable">
          <!-- Data will be inserted here via AJAX -->
        </tbody>
      </table>
    </div>

  </div>

  <script>
    // Function to fetch and update the weight, item count, and weight changes
    function refreshData() {
      $.ajax({
        url: "getvalue.php", // Fetches the latest data for weight and item count
        method: "GET",
        success: function(response) {
          let data = JSON.parse(response); // Parse the returned JSON
          
          // Update the weight and item count on the page
          $('.digital').text(data.weight + 'g');
          $('#lastw').text(data.weight + 'g');
          $('#last_count').text(data.item_count);
        },
        error: function() {
          console.log("Error fetching data");
        }
      });
      
      // Fetch the weight_changes data and populate the table
      $.ajax({
        url: "fetch_weight_changes.php", // File to fetch data from the weight_changes table
        method: "GET",
        success: function(response) {
          $('#weightChangesTable').html(response); // Insert the data into the table
        },
        error: function() {
          console.log("Error fetching weight changes");
        }
      });
    }

    // Continuously refresh data every 500ms
    setInterval(refreshData, 500); // Refresh every 500 milliseconds
  </script>

</body>
</html>
