<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="adminStyle.css">
    <title>File Upload</title>
</head>

<body>
    <div class="mainContainer">
        <?php
        session_start();

        $password = "your_password"; // Set your password here

        if (isset($_POST['password'])) {
            if ($_POST['password'] === $password) {
                $_SESSION['loggedin'] = true;
            } else {
                echo "<p style='color:red;'>Incorrect password.</p>";
            }
        }

        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            echo "<form action='/admin' method='post'>";
            echo "<input type='password' name='password' placeholder='Enter Password'>";
            echo "<input type='submit' value='Login'>";
            echo "</form>";
        } else {
            echo "<div class='uploadContainer'>";
            echo "<form action='/admin' method='post' enctype='multipart/form-data'>";
            echo "<input type='file' name='fileToUpload' id='fileToUpload' class='chooseFolder'>";
            echo "<input type='submit' value='Upload' name='submit'>";
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
                    echo "Sorry, only TXT files are allowed.";
                    $uploadOk = 0;
                }

                if ($uploadOk == 0) {
                    echo "Sorry, your file was not uploaded.";
                } else {
                    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], to: $target_dir . "schema.txt")) {
                        echo "The file has been uploaded.";

                        // include php converter in ../csv_converter.php
                        include '../csv_converter.php';
                    } else {
                        echo "Sorry, there was an error uploading your file.";
                    }
                }
            }
        }
        ?>
    </div>
</body>

</html>"