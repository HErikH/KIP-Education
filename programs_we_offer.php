<div class="section">
    <h3 class="section-title">Programs We Offer</h3>
    <div class="row">
        <?php
      $programs = [
        "Children Ages 3-6 (K1)" => "Playful English curriculum to spark early curiosity.",
        "Children Ages 7-11 (K2)" => "Fun & dynamic English to build fluency and creativity.",
        "Teenagers Ages 12-17 (TA1)" => "Comprehensive English for academic success and confidence.",
        "Adults (TA2)" => "Practical English for personal and career growth.",
        "Teacher Training" => "Training specialists in line with labor market and MI theory."
      ];

      foreach ($programs as $title => $desc) {
        echo "
          <div class='col-md-6 mb-3'>
            <div class='card h-100 border-0 shadow-sm'>
              <div class='card-body'>
                <h5 class='card-title text-primary'>$title</h5>
                <p class='card-text'>$desc</p>
              </div>
            </div>
          </div>
        ";
      }
    ?>
    </div>
</div>