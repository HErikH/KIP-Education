<div class="section intelligences-section">
    <h3 class="section-title d-flex justify-content-between align-items-center" data-toggle="collapse"
        data-target="#intelligences" aria-expanded="false" aria-controls="intelligences">
        Multiple Intelligences
        <span class="toggle-icon">&#9654;</span>
    </h3>

    <div class="collapse" id="intelligences">
        <div class="row">
            <?php
        $intelligences = [
          "Linguistic" => "Language use (reading, writing, speaking).",
          "Logical-Mathematical" => "Reasoning, problem-solving.",
          "Spatial" => "Visual and spatial awareness.",
          "Musical" => "Sensitivity to sound and rhythm.",
          "Bodily-Kinesthetic" => "Physical coordination.",
          "Interpersonal" => "Understanding others.",
          "Intrapersonal" => "Self-awareness.",
          "Naturalistic" => "Understanding nature and patterns."
        ];

        foreach ($intelligences as $title => $desc) {
          echo "
            <div class='col-md-6 mb-3'>
              <div class='card h-100 shadow-sm'>
                <div class='card-body'>
                  <h5 class='card-title text-primary'>$title Intelligence</h5>
                  <p class='card-text'>$desc</p>
                </div>
              </div>
            </div>
          ";
        }
      ?>
        </div>
    </div>
</div>