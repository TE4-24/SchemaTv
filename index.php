<!DOCTYPE html>
<html lang="en">

<head>
    <title>Dynamic Schedule</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link rel="shortcut icon" href="ntilogo.svg" type="image/x-icon">
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
        const scheduleContainer = document.getElementById('schedule-container');

        // Apply fade-out animation to the current schedule
        scheduleContainer.classList.add('fade-out');

        // Wait for the fade-out animation to complete before fetching the new schedule
        setTimeout(() => {
            fetch(`fetch_schedule.php?currentBaseClass=${currentBaseClass}`)
                .then(response => response.text())
                .then(data => {
                    // Replace the old content with the new schedule
                    scheduleContainer.innerHTML = data;

                    // Remove fade-out and apply fade-in
                    scheduleContainer.classList.remove('fade-out');
                    scheduleContainer.classList.add('fade-in');

                    // Remove the fade-in class after the animation is complete
                    setTimeout(() => {
                        scheduleContainer.classList.remove('fade-in');
                    }, 500); // Duration of the fade-in animation
                })
                .catch(error => console.error('Error fetching schedule:', error));
        }, 500); // Delay for the fade-out animation to complete
    }

    function rotateClasses() {
        currentBaseClassIndex = (currentBaseClassIndex + 1) % baseClasses.length; // Cycle through classes
        fetchSchedule(); // Fetch the new schedule for the current class
    }

    // Initial fetch when the page loads
    document.addEventListener('DOMContentLoaded', () => {
        fetchSchedule(); // Load the initial schedule

        // Set an interval to rotate classes every 7 seconds
        setInterval(rotateClasses, 7000); // Change class every 7 seconds
    });
    </script>

</body>

</html>