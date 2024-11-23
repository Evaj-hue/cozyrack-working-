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
    <h1 class="d-flex justify-content-center mb-3" style="font-size: 25px;">M42-TECH WEIGHT MACHINE</h1>
    
    <!-- Podium-like Rack -->
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg" style="border-radius: 10px;">
          <div class="row g-0">
            <div class="col-md-6">
              <div class="card-body d-flex flex-column justify-content-center align-items-center bg-light" style="height: 300px;">
                <h5 class="card-title text-center">WEIGHT DISPLAY</h5>
                <img src="weight.png" class="img-fluid rounded-start mb-3" alt="Scale" style="width: 100px;">
                <div class="digital" style="font-size: 2em;">0g</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card-body d-flex flex-column justify-content-center align-items-center bg-info" style="height: 300px; color: white;">
                <h5 class="card-title text-center">LAST WEIGHT</h5>
                <div id="lastw" class="d-flex flex-column align-items-center"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <script>
    let i = 0;

    function weight() {
      $.ajax({
        url: "getvalue.php",
        method: "POST",
        success: function(response) {
          console.log(response);
          if (response > 1) {
            $('.digital').text(response + 'g');
            let update = $("<p class='bg-dark card-text d-flex justify-content-center text-white border border-2 border-danger rounded p-2 mb-1'>"
              + response + 'g &nbsp; (' + new Date().toLocaleString() + ")</p>");
            
            // Check if there are already 10 elements, remove the oldest if so
            if ($('#lastw').children().length >= 10) {
              $('#lastw p:last-child').remove();
            }
            $('#lastw').prepend(update);

            // Display box animation on first weight detection
            if (i == 0) {
              $('#box').css("display", "block");
              anime({ targets: '#box', translateY: 250 });
            }
            i++;
          } else {
            // Reset when weight is zero
            if (i != 0) {
              anime({ targets: '#box', translateY: -120 });
            }
            $('.digital').text("0g");
            i = 0;
          }
          setTimeout(weight, 500); // Continuously call the function every 500ms
        }
      });
    }

    // Start fetching weight data
    setTimeout(weight, 1000); // Delay the first call to avoid instant reload
  </script>

</body>
</html>
