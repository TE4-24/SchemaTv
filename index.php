<html lang="en">

<head>
  <title>Dynamic Schedule</title>
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
  <?php
   
   function getClasses($baseClasses)
   {
     $currentYear = date("y"); // Get last two digits of the current year
     $currentMonth = date("m");
 
     if ($currentMonth < 8) { // Before August, use the previous academic year
       $currentYear -= 1;
     }
 
     $classes = array();
     // Generate class names for the last 2 years and the current year
     for ($i = $currentYear - 2; $i <= $currentYear; $i++) {
       foreach ($baseClasses as $class) {
         $classes[] = $class . sprintf("%02d", $i); // Format year as two digits
       }
     }
 
     return $classes;
   }

  $baseClasses = array("TE", "EE", "ES");

  if (isset($_GET['currentBaseClass'])) { //GET is used to
    $currentBaseClass = $_GET['currentBaseClass'];
  } else {
    $currentBaseClass = $baseClasses[0]; // Default to the first base class
  }

  $currentIndex = array_search($currentBaseClass, $baseClasses);

  $nextBaseClass = $baseClasses[($currentIndex + 1) % count($baseClasses)];

  header("Refresh: 5; url=?currentBaseClass=$nextBaseClass");

  function displayMondaySchedule($className, $day)
  {
    echo "<div class='class-schema-container'>
        <div class='$className'><h1 class='klassNamn'>$className</h1></div>
        <div class='schema'>";

    // Open the CSV file
    // if (($csvHandle = fopen("admin/class_schedules/$className.csv", "r")) !== FALSE) {
    if (($csvHandle = fopen(getcwd() . "/admin/class_schedules/$className.csv", "r")) !== FALSE) {
      $currentDay = array();
      while (($scheduleDays = fgetcsv($csvHandle, 1000, ",")) !== FALSE) {
        $currentDay[] = $scheduleDays[$day];
      }

      foreach ($currentDay as $day) {
        if ($day == "") {
          continue;
        } else {
          echo "<div>$day</div> <br>";
        }
      }

      fclose($csvHandle);
    } else {
      echo "Unable to open the CSV file.";
    }

    echo "</div>
      </div>";
  }

  // Display the header
  echo "<div class='header'>Schema for $currentBaseClass</div>";

  // Get dynamically generated class names for the current base class
  $classes = getClasses([$currentBaseClass]);

  // Start the container for the class schedules
  echo "<div class='year-container'>";

  // Display the schedule for each generated class
  foreach ($classes as $className) {
    displayMondaySchedule($className, 2);
  }

  // End the container
  echo "</div>";
  ?>

</body>

</html>
