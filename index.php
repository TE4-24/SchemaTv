<!DOCTYPE html>
<html lang="en">

<head>
    <title>Dynamic Schedule</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link rel="shortcut icon" href="ntilogo.svg" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    $allClasses = getClasses($baseClasses);
    ?>

    <!-- Class Picker (visible on small screens) -->
    <div class="picker-container">
        <select id="class-picker">
            <?php
            foreach ($allClasses as $className) {
                echo "<option value='$className'>$className</option>";
            }
            ?>
        </select>
    </div>

    <!-- Schedule Container for Rotating Schedule (visible on large screens) -->
    <div id="rotating-schedule-container"></div>

    <!-- Schedule Container for Class Picker Schedule (visible on small screens) -->
    <div id="class-picker-schedule-container"></div>
    <div id="clock">
    </div>

    <script>
    const dayOfWeek = <?php echo $dayOfWeek; ?>;
    const currentTime = "<?php echo $currentTime; ?>";
    let time;
    let day;
    let screenWidth;

    document.addEventListener('DOMContentLoaded', () => {
        function setupSchedule() {
            screenWidth = window.innerWidth;

            if (screenWidth >= 768) {
                // Hide class picker and show rotating schedule
                document.querySelector('.picker-container').style.display = 'none';
                document.getElementById('class-picker-schedule-container').style.display = 'none';
                document.getElementById('rotating-schedule-container').style.display = 'block';

                initRotatingSchedule();
            } else {
                // Show class picker and hide rotating schedule
                document.querySelector('.picker-container').style.display = 'block';
                document.getElementById('class-picker-schedule-container').style.display = 'block';
                document.getElementById('rotating-schedule-container').style.display = 'none';

                initClassPickerSchedule();
            }
        }

        function initRotatingSchedule() {
            let currentBaseClassIndex = 0; // Start with the first base class (TE)
            const baseClasses = ["TE", "EE", "ES"]; // Base classes to cycle through

            function fetchSchedule() {
                const currentBaseClass = baseClasses[currentBaseClassIndex];
                const scheduleContainer = document.getElementById('rotating-schedule-container');

                // Apply fade-out animation to the current schedule
                scheduleContainer.classList.add('fade-out');

                // Wait for the fade-out animation to complete before fetching the new schedule
                setTimeout(() => {
                    fetch(
                            `fetch_schedule.php?className=${currentBaseClass}&dayOfWeek=${day}&currentTime=${time}`
                        )
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
                currentBaseClassIndex = (currentBaseClassIndex + 1) % baseClasses
                    .length; // Cycle through classes
                fetchSchedule(); // Fetch the new schedule for the current class
            }

            // Initial fetch when the page loads
            fetchSchedule(); // Load the initial schedule

            // Rotate classes every 10 seconds
            setInterval(rotateClasses, 1000 * 10);
        }

        function initClassPickerSchedule() {
            const classPicker = document.getElementById('class-picker');
            const scheduleContainer = document.getElementById('class-picker-schedule-container');

            // Function to fetch and display the schedule for the selected class
            function fetchSchedule(className) {
                // Save the selected class in localStorage
                localStorage.setItem('selectedClass', className);

                // Fetch the schedule via AJAX
                fetch(
                        `fetch_schedule.php?className=${className}&dayOfWeek=${dayOfWeek}&currentTime=${currentTime}`
                    )
                    .then(response => response.text())
                    .then(data => {
                        scheduleContainer.innerHTML = data;
                    })
                    .catch(error => console.error('Error fetching schedule:', error));
            }

            // Load the selected class from localStorage or default to the first option
            const savedClass = localStorage.getItem('selectedClass');
            if (savedClass) {
                classPicker.value = savedClass;
                fetchSchedule(savedClass);
            } else {
                fetchSchedule(classPicker.value);
            }

            // Add event listener to the class picker
            classPicker.addEventListener('change', () => {
                fetchSchedule(classPicker.value);
            });
        }

        // create a fuction to update the day of the week
        function updateDay() {
            day = new Date().getDay() - 1;
        }

        //hours and minutes in european format
        function updateTime() {
            time = new Date().toLocaleTimeString('en-GB', {
                hour: '2-digit',
                minute: '2-digit'
            });
            document.getElementById('clock').innerHTML = time;
        }

        setupSchedule();
        updateTime();
        updateDay();

        setInterval(updateTime, 1000 * 5);

        setInterval(updateDay, 1000 * 60 * 60);

        window.addEventListener('resize', () => {
            screenwidth = window.innerWidth;
            if (screenwidth >= 768 && document.querySelector('.picker-container').style.display ===
                'block') {
                location.reload();
            } else if (screenwidth < 768 && document.querySelector('.picker-container').style
                .display === 'none') {
                location.reload();
            }
        });
    });
    </script>
</body>

</html>