<?php
date_default_timezone_set('Europe/Stockholm'); // Set the correct timezone

$dayOfWeek = isset($_GET['dayOfWeek']) ? intval($_GET['dayOfWeek']) : date("w") - 1;
$currentTime = isset($_GET['currentTime']) ? $_GET['currentTime'] : date("H:i");

$baseClasses = array("TE", "EE", "ES");

function getClasses($baseClasses)
{
    $currentYear = date("y");
    $currentMonth = date("m");

    if ($currentMonth < 8) {
        $currentYear -= 1; // Adjust year for academic calendar
    }

    $classes = array();
    // Generate class names for the last 2 years and the current year
    for ($i = $currentYear - 2; $i <= $currentYear; $i++) {
        foreach ($baseClasses as $class) {
            $classes[] = $class . sprintf("%02d", $i); // Format year as two digits
        }
    }

    return $classes; // Return the generated class names
}

function displayDaySchedule($className, $day, $currentTime)
{
    $output = "<div class='class-schema-container'>
        <div class='$className'><h1 class='klassNamn'>$className</h1></div>
        <div class='schema'>";

    // Open the CSV file for the current class
    if (($csvHandle = fopen(getcwd() . "/admin/class_schedules/$className.csv", "r")) !== FALSE) {
        $currentDay = array();

        // Read the CSV file
        while (($scheduleDays = fgetcsv($csvHandle, 1000, ",")) !== FALSE) {
            $currentDay[] = $scheduleDays[$day]; // Get the schedule for the specific day
        }

        $counter = 0;
        foreach ($currentDay as $day) {
            if ($counter == 0) {
                $counter++;
                continue; // Skip the header row
            }
            if ($day == "") {
                continue; // Skip empty cells
            } else {
                $parts = explode(": ", $day);
                if (isset($parts[0])) {
                    $timeRange = $parts[0]; // e.g., "08:10-09:30"
                    $lessonDetails = isset($parts[1]) ? $parts[1] : ''; // e.g., "Subject"

                    // Extract start and end times
                    list($startTime, $endTime) = explode("-", $timeRange);

                    // Compare current time with lesson end time
                    if ($currentTime > $endTime) {
                        $output .= "<div class='greyed-out'>$timeRange: $lessonDetails</div><br>";
                    } elseif ($currentTime >= $startTime && $currentTime <= $endTime) {
                        $output .= "<div class='current-lesson'>$timeRange: $lessonDetails</div><br>";
                    } else {
                        $output .= "<div>$timeRange: $lessonDetails</div><br>";
                    }
                }
            }
            $counter++;
        }

        fclose($csvHandle); // Close the CSV file
    } else {
        $output .= "Unable to open the CSV file."; // Error message if file can't be opened
    }

    $output .= "</div></div>"; // Close the schema container

    return $output; // Return the output string
}

// Get the 'className' parameter from the GET request
$classNameParam = isset($_GET['className']) ? $_GET['className'] : '';

// Determine if the 'className' is a base class or a specific class
if (in_array($classNameParam, $baseClasses)) {
    // It's a base class, generate class names for the current base class
    $classes = getClasses([$classNameParam]);
} else {
    // It's a specific class
    $classes = [$classNameParam];
}

$output = "<div class='year-container'>";

// Display the schedule for each class
foreach ($classes as $className) {
    $output .= displayDaySchedule($className, $dayOfWeek, $currentTime);
}

$output .= "</div>"; // Close the year container

echo $output; // Output the entire HTML for the schedule
?>