<html lang="en">

<head>
  <title>Document</title>
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
  <?php
  include 'admin.php';  /*
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
*/

  function displayMondaySchedule($className)
  {
    echo "<div class='class-schema-container'>
        <div class='$className'><h1 class='klass-namn'>$className</h1></div>
        <div class='schema'>";

    // open the CSV file
    if (($csvHandle = fopen("class_schedules/$className.csv", "r")) !== FALSE) {
      $currentDay = array();
      while (($scheduleDays = fgetcsv($csvHandle, 1000, ",")) !== FALSE) {
        $currentDay[] = $scheduleDays[0];
      }

      foreach ($currentDay as $day) {
        echo "$day <br>";
      }

      fclose($csvHandle);
    } else {
      echo "Unable to open the CSV file.";
    }

    echo "</div>
      </div>";
  }
  echo "<div class='year-container'>";
  displayMondaySchedule('EE22');
  displayMondaySchedule('ES22');
  displayMondaySchedule('TE22');
  echo "</div>";

  ?>

</body>

</html>