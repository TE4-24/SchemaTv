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

?>