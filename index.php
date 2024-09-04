<html lang="en">

<head>
  <title>Document</title>
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
  <?php

  echo "<div class='header'>Schema</div>";

  echo "<div class='year-container'>";
  echo "<div class='class-schema-container'>
        <div class='ee22'><h1 class='klass-namn'>EE22</h1></div>
        <div class='schema'>placeholder</div>
      </div>";
  echo "<div class='class-schema-container'>
        <div class='es22'><h1 class='klass-namn'>ES22</h1></div>
        <div class='schema'>placeholder</div>
      </div>";
  echo "<div class='class-schema-container'>
        <div class='te22'><h1 class='klass-namn'>TE22</h1></div>
        <div class='schema'>";

  // Open the CSV file
  if (($handle = fopen("class_schedules/TE22.csv", "r")) !== FALSE) {
    // Loop through each row of the CSV file
    $data = fgetcsv($handle, 1000, ",") !== FALSE;
    // Echo the third index (index 2)
    if (is_array($data) && isset($data[2])) {
      echo $data[0] . "<br>";
    }
    // Close the CSV file
    fclose($handle);
  } else {
    echo "Unable to open the CSV file.";
  }

  echo "</div>
      </div>";
  echo "</div>";

  ?>

</body>

</html>