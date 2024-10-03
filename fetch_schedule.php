<?php
date_default_timezone_set('Europe/Stockholm'); // Set the correct timezone

$dayOfWeek = date("w") - 1; // Get the current day of the week (0 = Sunday)
$currentTime = date("H:i"); // Get the current time in H:i format

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

$baseClasses = array("TE", "EE", "ES");
$currentBaseClass = isset($_GET['currentBaseClass']) ? $_GET['currentBaseClass'] : $baseClasses[0]; // Default to the first base class if not set

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

// Get dynamically generated class names for the current base class
$classes = getClasses([$currentBaseClass]);
$output = "<div class='year-container'>";

// Display the schedule for each generated class
foreach ($classes as $className) {
    $output .= displayDaySchedule($className, $dayOfWeek, $currentTime);
}

$output .= "</div>"; // Close the year container

echo $output; // Output the entire HTML for the schedule
?>