<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/admin/adminStyle.css" type="text/css">
    <title>File Upload</title>
</head>


<body>
    <div class="mainContainer">
        <?php
        ini_set('session.gc_maxlifetime', 60); // Set session max lifetime to 20 minutes (1200 seconds)
        session_start();

        // Set the session timeout duration (in seconds)
        $session_timeout = 60; // 20 minutes

        // Check if the session is set and if it has expired
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_timeout)) {
            // Last request was more than $session_timeout seconds ago
            session_unset();     // Unset $_SESSION variable for the run-time
            session_destroy();   // Destroy session data in storage
        }
        $_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time stamp

        $env = parse_ini_file('.env');
        $password = $env['ADMIN_PASSWORD'];

        if (isset($_POST['password'])) {
            if ($_POST['password'] === $password) {
                $_SESSION['loggedin'] = true;
            } else {
                echo "<p class='error'>Incorrect password.</p>";
            }
        }

        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            echo "<form action='/admin/index.php' method='post' class='login-form'>";
            echo "<input type='password' name='password' placeholder='Enter Password' class='input-field'>";
            echo "<input type='submit' value='Login' class='submit-button'>";
            echo "</form>";
        } else {
            echo "<div class='uploadContainer'>";
            echo "<form action='/admin/index.php' method='post' enctype='multipart/form-data' class='upload-form'>";
            echo "<input type='file' name='fileToUpload' id='fileToUpload' class='chooseFolder'>";
            echo "<input type='submit' value='Upload' name='submit' class='submit-button'>";
            echo "</form>";
            echo "</div>";

            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileToUpload"])) {
                if (!file_exists("uploads")) {
                    mkdir("uploads");
                }
                $target_dir = "uploads/";
                $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
                $uploadOk = 1;
                $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                if ($fileType != "txt") {
                    echo "<p class='error'>Sorry, only TXT files are allowed.</p>";
                    $uploadOk = 0;
                }

                if ($uploadOk == 0) {
                    echo "<p class='error'>Sorry, your file was not uploaded.</p>";
                } else {
                    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_dir . "schema.txt")) {
                        echo "<p class='success'>The file has been uploaded.";

                        // include php converter in ../csv_converter.php
                        include '../csv_converter.php';

                    } else {
                        echo "<p class='error'>Sorry, there was an error uploading your file.</p>";
                    }
                }
            }
        }
        ?>
    </div>
</body>

</html>