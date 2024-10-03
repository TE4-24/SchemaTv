<?php

class Schedule {
    public $text_file;
    public $classes;
    public $data;
    public $schema;
    public $all_lessons;
    public $names;
    public $df;
    public $lessons;
    public $schedule;
    public $df_schedule; // Declare this property to avoid the deprecation warning

    public function __construct($data, $classes) {
        $this->text_file = $data;
        $this->classes = $classes;
    }
    

    public function get_classes() {
        $current_year = date("Y");
        $current_month = date("n"); // numeric representation of a month, without leading zeros (1 to 12)

        if ($current_month < 8) { // Before August, use the previous academic year
            $current_year -= 1;
        }

        $current_year = substr($current_year, -2); // Get last two digits of the current year

        $classes = array();

        for ($i = $current_year - 2; $i <= $current_year; $i++) {
            foreach ($this->classes as $cls) {
                $classes[] = $cls . sprintf("%02d", $i); // Format year as two digits
            }
        }

        // print_r($classes);
        return $classes;
    }

    public function get_pupils_ssn() {
        $pupils = array();
        $schema_lines = array();

        // Regular expression to match 12-digit SSNs
        $ssn_pattern = '/\b\d{12}\b/';

        //open /uploads/schema.txt
        $file = fopen($this->text_file, "r");

        if ($file) {
            while (($line = fgets($file)) !== false) {
                $schema_lines[] = trim($line);
                foreach ($this->classes as $cls) {
                    if (strpos($line, $cls) === 0) {
                        preg_match_all($ssn_pattern, $line, $matches);
                        foreach ($matches[0] as $ssn) {
                            $pupils[] = array($cls => $ssn);
                        }
                    }
                }
            }
            fclose($file);
        }

        return array($pupils, $schema_lines);
    }

    public function get_all_lessons() {
        $lessons = array();

        function is_ssn($s) {
            $s = trim($s);
            return ctype_digit($s) && strlen($s) == 12;
        }

        // Exclude SSNs from schema_dict and strip whitespace
        $schema_dict = array();
        foreach ($this->schema as $line) {
            $parts = explode("\t", $line);
            $key = trim($parts[0]);
            if (!is_ssn($key)) {
                $schema_dict[$key] = trim($line);
            }
        }

        foreach ($this->data as $pupil) {
            foreach ($pupil as $cls => $ssn) {
                $ssn = trim($ssn);
                $pupil_lessons = array();
                foreach ($schema_dict as $lesson_id => $lesson_line) {
                    if (strpos($lesson_line, $ssn) !== false) {
                        $lesson_name = trim(explode("\t", $lesson_line)[0]);
                        $pupil_lessons[] = array($ssn => $lesson_name);
                    }
                }
                $lessons[] = array($cls => $pupil_lessons);
            }
        }

        return $lessons;
    }

    public function get_pupil_names() {
        $names = array();
        $track_row = 0;
        $i = 0;

        foreach ($this->schema as $line) {
            if (strpos($line, "Student") !== false) {
                $track_row = $i;
                break;
            }
            $i++;
        }

        $schema_dict = array();
        for ($j = $track_row - 1; $j < count($this->schema); $j++) {
            $line = $this->schema[$j];
            $parts = explode("\t", $line);
            $key = trim($parts[0]);
            $schema_dict[$key] = trim($line);
        }

        foreach ($this->data as $pupil) {
            foreach ($pupil as $cls => $ssn) {
                $ssn = trim($ssn);
                if (isset($schema_dict[$ssn])) {
                    $x = array();
                    foreach (explode("\t", $schema_dict[$ssn]) as $item) {
                        $item = trim($item);
                        if ($item && strpos($item, "{") === false) {
                            $x[] = $item;
                        }
                    }
                    if (count($x) > 2) {
                        $name = $x[1] . " " . $x[2];
                        $names[] = array($ssn => $name);
                    }
                }
            }
        }

        return $names;
    }

    public function convert_to_csv($filename="pupils_lessons.csv") {
        $flattened_data = array();

        foreach ($this->all_lessons as $lesson_dict) {
            foreach ($lesson_dict as $cls => $pupil_lessons) {
                foreach ($pupil_lessons as $lesson) {
                    foreach ($lesson as $ssn => $lesson_name) {
                        if ($cls == $lesson_name) {
                            continue;
                        }
                        $name_to_append = "";
                        foreach ($this->names as $name) {
                            if (isset($name[$ssn])) {
                                $name_to_append = $name[$ssn];
                                break;
                            }
                        }
                        $flattened_data[] = array(
                            'kurs' => $cls,
                            'personnummer' => $ssn,
                            'lektion' => $lesson_name,
                            'namn' => $name_to_append
                        );
                    }
                }
            }
        }

        // Write to CSV file
        $file = fopen($filename, 'w');
        fputcsv($file, array('kurs', 'personnummer', 'lektion', 'namn'));
        foreach ($flattened_data as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        return $flattened_data;
    }

    public function add_minutes_to_time($old_time, $minutes_to_add) {
        $old_time_split = explode(":", $old_time);
        $old_time_minutes = intval($old_time_split[1]);
        $old_time_hours = intval($old_time_split[0]);
        $new_time_minutes = $old_time_minutes + intval($minutes_to_add);
        $new_time_hours = $old_time_hours + intdiv($new_time_minutes, 60);
        $new_time_minutes = $new_time_minutes % 60;
        return sprintf("%02d:%02d", $new_time_hours, $new_time_minutes);
    }

    public function format_days_to_lessons($current_period) {
        $days = array("ndag", "Tisdag", "Onsdag", "Torsdag", "Fredag");
        $lessons = array(
            "Måndag" => array(),
            "Tisdag" => array(),
            "Onsdag" => array(),
            "Torsdag" => array(),
            "Fredag" => array()
        );

        foreach ($this->schema as $line) {
            $line = trim($line);
            $line_data = explode("\t", $line);
            foreach ($days as $day) {
                if (strpos($line, $day) !== false) {
                    foreach ($this->df as $row) {
                        $lektion = $row['lektion'];
                        if (preg_match('/\b' . preg_quote($lektion, '/') . '\b/', $line)) {
                            if (in_array($current_period, $line_data) || (!in_array("P1", $line_data) && !in_array("P2", $line_data) && !in_array("P3", $line_data))) {
                                $step = false;
                                $old_time = "";
                                for ($i = 0; $i < count($line_data); $i++) {
                                    $x = $line_data[$i];
                                    if ($step) {
                                        $step = false;
                                        foreach ($lessons as $key => &$value) {
                                            if ($key == $day || ($key == "Måndag" && $day == "ndag")) {
                                                if (strpos($old_time, ":") !== false) {
                                                    $new_time = $this->add_minutes_to_time($old_time, $x);
                                                    $value[count($value)-1][$lektion][] = $new_time;
                                                }
                                                $value[count($value)-1][$lektion][] = $x;
                                                $room = isset($line_data[$i+5]) ? $line_data[$i+5] : '';
                                                $value[count($value)-1][$lektion][] = $room;
                                                continue 2;
                                            }
                                        }
                                    }
                                    if (strpos($x, ":") !== false) {
                                        $step = true;
                                        foreach ($lessons as $key => &$value) {
                                            if ($key == $day || ($key == "Måndag" && $day == "ndag")) {
                                                $value[] = array($lektion => array($x));
                                                $old_time = $x;
                                                continue 2;
                                            }
                                        }
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
            }
        }

        return $lessons;
    }

    public function convert_time_lessons_to_csv($output_filename) {
        $flattened_data = array();
        foreach ($this->lessons as $day => $day_lessons) {
            foreach ($day_lessons as $lesson) {
                foreach ($lesson as $lesson_name => $time_and_room) {
                    $start_time = isset($time_and_room[0]) ? $time_and_room[0] : '';
                    $end_time = isset($time_and_room[1]) ? $time_and_room[1] : '';
                    $total_minutes = isset($time_and_room[2]) ? $time_and_room[2] : '';
                    $room = isset($time_and_room[3]) ? $time_and_room[3] : '';

                    $flattened_data[] = array(
                        'lektion' => $lesson_name,
                        'tid' => json_encode(array($start_time, $end_time, $total_minutes)),
                        'dag' => $day,
                        'rum' => $room
                    );
                }
            }
        }

        // Write to CSV file
        $file = fopen($output_filename, 'w');
        fputcsv($file, array('lektion', 'tid', 'dag', 'rum'));
        foreach ($flattened_data as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        return $flattened_data;
    }

    public function create_combined_schedule() {
        $days = array("Måndag", "Tisdag", "Onsdag", "Torsdag", "Fredag");
        $df = array();
        if (($handle = fopen("lessons.csv", "r")) !== FALSE) {
            $header = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== FALSE) {
                $df[] = array_combine($header, $data);
            }
            fclose($handle);
        }

        $schedule = array();
        foreach ($days as $day) {
            $schedule[$day] = array();
        }

        foreach ($df as $row) {
            $lesson_name = $row['lektion'];
            $time_info_str = $row['tid'];
            $day = $row['dag'];
            $room = isset($row['rum']) ? $row['rum'] : '';

            $time_info = json_decode($time_info_str, true);
            if (count($time_info) == 3) {
                $start_time = strtotime($time_info[0]);
                $end_time = $time_info[1];
                $schedule[$day][] = array($start_time, $end_time, $lesson_name, $room);
            } else {
                echo "Unexpected time format in line: " . json_encode($row) . "\n";
            }
        }

        // Sort lessons by start time for each day
        foreach ($schedule as $day => &$lessons) {
            usort($lessons, function($a, $b) {
                return $a[0] - $b[0];
            });
        }

        // Determine maximum number of lessons in any day
        $max_rows = 0;
        foreach ($schedule as $day => $lessons) {
            $max_rows = max($max_rows, count($lessons));
        }

        // Create dataframe schedule
        $df_schedule = array();
        for ($i = 0; $i < $max_rows; $i++) {
            $row = array();
            foreach ($days as $day) {
                if (isset($schedule[$day][$i])) {
                    $start_time_str = date('H:i', $schedule[$day][$i][0]);
                    $lesson_str = $schedule[$day][$i][2] . " (" . $start_time_str . "-" . $schedule[$day][$i][1];
                    if ($schedule[$day][$i][3] != '') {
                        $lesson_str .= "," . $schedule[$day][$i][3];
                    }
                    $lesson_str .= ")";
                    $row[$day] = $lesson_str;
                } else {
                    $row[$day] = '';
                }
            }
            $df_schedule[] = $row;
        }

        // Write to CSV
        $output_folder = "combined_schedule";
        if (!file_exists($output_folder)) {
            mkdir($output_folder, 0777, true);
        }
        $file = fopen($output_folder . "/schedule.csv", 'w');
        fputcsv($file, $days);
        foreach ($df_schedule as $row) {
            $line = array();
            foreach ($days as $day) {
                $line[] = $row[$day];
            }
            fputcsv($file, $line);
        }
        fclose($file);

        return $df_schedule;
    }

    public function create_class_schedule_from_combined_schedule($df_schedule) {
        $schedule = array();
        $pupil_lessons_df = array();
        if (($handle = fopen("pupils_lessons.csv", "r")) !== FALSE) {
            $header = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== FALSE) {
                $pupil_lessons_df[] = array_combine($header, $data);
            }
            fclose($handle);
        }

        // Adjusted regular expression to handle optional room
        $lesson_pattern = '/^(.*?) \(([^-]+)-([^\),]+)(?:,([^)]+))?\)$/';

        foreach ($df_schedule as $row) {
            foreach ($row as $day => $lesson) {
                if ($lesson != '') {
                    if (preg_match($lesson_pattern, $lesson, $matches)) {
                        $lesson_name = trim($matches[1]);
                        $start_time = trim($matches[2]);
                        $end_time = trim($matches[3]);
                        $room = isset($matches[4]) ? trim($matches[4]) : null;

                        // Get the list of courses (kurs) associated with the lesson
                        $kurs_list = array();
                        foreach ($pupil_lessons_df as $pl_row) {
                            if ($pl_row['lektion'] == $lesson_name) {
                                $kurs_list[] = $pl_row['kurs'];
                            }
                        }
                        $kurs_list = array_unique($kurs_list);

                        foreach ($kurs_list as $kurs) {
                            if (!isset($schedule[$day])) {
                                $schedule[$day] = array();
                            }
                            // Find existing class_dict or create a new one
                            $class_dict = null;
                            foreach ($schedule[$day] as &$item) {
                                if (isset($item[$kurs])) {
                                    $class_dict = &$item;
                                    break;
                                }
                            }
                            if ($class_dict === null) {
                                $class_dict = array($kurs => array());
                                $schedule[$day][] = &$class_dict;
                            }
                            // Build lesson info
                            $lesson_info = array(
                                'start_time' => $start_time,
                                'end_time' => $end_time,
                                'lesson_name' => $lesson_name
                            );
                            if ($room) {
                                $lesson_info['room'] = $room;
                            }
                            $class_dict[$kurs][] = $lesson_info;
                            unset($class_dict); // unset reference
                        }
                    } else {
                        continue; // Skip if the lesson string doesn't match the expected format
                    }
                }
            }
        }

        return $schedule;
    }

    public function create_csv_for_each_class($schedule) {
        $class_data = array();
        $days_of_week = array("Måndag", "Tisdag", "Onsdag", "Torsdag", "Fredag");

        function convert_time_to_minutes($time_str) {
            list($hour, $minute) = explode(':', $time_str);
            return intval($hour) * 60 + intval($minute);
        }

        foreach ($days_of_week as $day) {
            if (isset($schedule[$day])) {
                $classes = $schedule[$day];
                foreach ($classes as $class_dict) {
                    foreach ($class_dict as $kurs => $lessons) {
                        if (!isset($class_data[$kurs])) {
                            $class_data[$kurs] = array();
                            foreach ($days_of_week as $d) {
                                $class_data[$kurs][$d] = array();
                            }
                        }
                        foreach ($lessons as $lesson) {
                            $start_time = $lesson['start_time'];
                            $end_time = $lesson['end_time'];
                            $lesson_name = $lesson['lesson_name'];
                            $room = isset($lesson['room']) ? $lesson['room'] : null;
                            $time_range = $start_time . "-" . $end_time;
                            // Format the lesson string including room if available
                            if ($room) {
                                $lesson_str = $time_range . ": " . $lesson_name . " (" . $room . ")";
                            } else {
                                $lesson_str = $time_range . ": " . $lesson_name;
                            }
                            $class_data[$kurs][$day][] = array(
                                'start_minutes' => convert_time_to_minutes($start_time),
                                'lesson_str' => $lesson_str
                            );
                        }
                    }
                }
            }
        }

        // Sort lessons by start time for each day and class
        foreach ($class_data as $kurs => &$days) {
            foreach ($days_of_week as $day) {
                usort($days[$day], function($a, $b) {
                    return $a['start_minutes'] - $b['start_minutes'];
                });
                // Replace the list of dicts with list of lesson strings
                $lessons = array();
                foreach ($days[$day] as $item) {
                    $lessons[] = $item['lesson_str'];
                }
                $days[$day] = $lessons;
            }
        }

        // Write CSV files for each class
        $output_folder = "class_schedules";
        if (!file_exists($output_folder)) {
            mkdir($output_folder, 0777, true);
        }

        foreach ($class_data as $kurs => $data) {
            $max_lessons = 0;
            foreach ($data as $lessons) {
                $max_lessons = max($max_lessons, count($lessons));
            }

            $csv_data = array();
            for ($i = 0; $i < $max_lessons; $i++) {
                $row = array();
                foreach ($days_of_week as $day) {
                    if (isset($data[$day][$i])) {
                        $row[$day] = $data[$day][$i];
                    } else {
                        $row[$day] = '';
                    }
                }
                $csv_data[] = $row;
            }

            // Write to CSV
            $file = fopen($output_folder . '/' . $kurs . '.csv', 'w');
            fputcsv($file, $days_of_week);
            foreach ($csv_data as $row) {
                $line = array();
                foreach ($days_of_week as $day) {
                    $line[] = $row[$day];
                }
                fputcsv($file, $line);
            }
            fclose($file);
            echo "Class schedule saved for: " . $kurs . "\n";
        }
    }

}

function main() {
    $schedule = new Schedule("uploads/schema.txt", array("TE", "EE", "ES"));
    $schedule->classes = $schedule->get_classes();
    list($schedule->data, $schedule->schema) = $schedule->get_pupils_ssn();
    $schedule->all_lessons = $schedule->get_all_lessons();
    $schedule->names = $schedule->get_pupil_names();
    $schedule->df = $schedule->convert_to_csv("pupils_lessons.csv");
    $schedule->lessons = $schedule->format_days_to_lessons("P1");
    $schedule->convert_time_lessons_to_csv("lessons.csv");
    $schedule->df_schedule = $schedule->create_combined_schedule();
    $schedule->schedule = $schedule->create_class_schedule_from_combined_schedule($schedule->df_schedule);
    $schedule->create_csv_for_each_class($schedule->schedule);
}

main();

?>