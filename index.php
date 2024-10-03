<!DOCTYPE html>
<html lang="en">

<head>
    <title>Dynamic Schedule</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
    <?php
    date_default_timezone_set('Europe/Stockholm');
    $dayOfWeek = date("w") - 1;
    $currentTime = date("H:i");

    function getClasses($baseClasses)
    {
        $currentYear = date("y");
        $currentMonth = date("m");

        if ($currentMonth < 8) {
            $currentYear -= 1;
        }

        $classes = array();
        for ($i = $currentYear - 2; $i <= $currentYear; $i++) {
            foreach ($baseClasses as $class) {
                $classes[] = $class . sprintf("%02d", $i);
            }
        }

        return $classes;
    }

    $baseClasses = array("TE", "EE", "ES");
    $currentBaseClass = isset($_GET['currentBaseClass']) ? $_GET['currentBaseClass'] : $baseClasses[0];

    function displayDaySchedule($className, $day, $currentTime)
    {
        echo "<div class='class-schema-container'>
            <div class='$className'><h1 class='klassNamn'>$className</h1></div>
            <div class='schema'>";

        if (($csvHandle = fopen(getcwd() . "/admin/class_schedules/$className.csv", "r")) !== FALSE) {
            $currentDay = array();
            while (($scheduleDays = fgetcsv($csvHandle, 1000, ",")) !== FALSE) {
                $currentDay[] = $scheduleDays[$day];
            }
            
            $counter = 0;
            foreach ($currentDay as $day) {
                if ($counter == 0) {
                    $counter++;
                    continue;
                }
                if ($day == "") {
                    continue;
                } else {
                    $parts = explode(": ", $day);
                    if (isset($parts[0])) {
                        $timeRange = $parts[0];
                        $lessonDetails = isset($parts[1]) ? $parts[1] : '';
                        list($startTime, $endTime) = explode("-", $timeRange);

                        if ($currentTime > $endTime) {
                            echo "<div class=''>$timeRange: $lessonDetails</div> <br>";
                        } elseif ($currentTime >= $startTime && $currentTime <= $endTime) {
                            echo "<div class='current-lesson'>$timeRange: $lessonDetails</div> <br>";
                        } else {
                            echo "<div>$timeRange: $lessonDetails</div> <br>";
                        }
                    }
                }
                $counter++;
            }

            fclose($csvHandle);
        } else {
            echo "Unable to open the CSV file.";
        }

        echo "</div>
          </div>";
    }

    $classes = getClasses([$currentBaseClass]);
    echo "<div id='schedule-container'>"; // Correct ID here
    echo "<div class='year-container'>";

    foreach ($classes as $className) {
        displayDaySchedule($className, $dayOfWeek, $currentTime);
    }

    echo "</div>";
    echo "</div>";
    ?>
    <script>
    let currentBaseClassIndex = 0; // Start with the first base class (TE)
    const baseClasses = ["TE", "EE", "ES"]; // Base classes to cycle through

    function fetchSchedule() {
        const currentBaseClass = baseClasses[currentBaseClassIndex];
        fetch(`fetch_schedule.php?currentBaseClass=${currentBaseClass}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('schedule-container').innerHTML = data; // Correct ID usage
            })
            .catch(error => console.error('Error fetching schedule:', error));
    }

    function rotateClasses() {
        currentBaseClassIndex = (currentBaseClassIndex + 1) % baseClasses.length; // Cycle through classes
        fetchSchedule(); // Fetch the new schedule for the current class
    }

    // Initial fetch when the page loads
    document.addEventListener('DOMContentLoaded', () => {
        fetchSchedule(); // Load the initial schedule

        // Set an interval to rotate classes every 10 seconds (adjust as necessary)
        setInterval(rotateClasses, 7000); // Change class every 10 seconds
    });
    </script>

</body>

</html>