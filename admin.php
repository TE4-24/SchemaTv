<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="adminStyle.css">
    <title>File Upload</title>
</head>
<body>
    <video autoplay muted loop id="myVideo">
        <source src="rain.mp4" type="video/mp4">
    </video>
    <div class="mainContainer">
    <div class='uploadContainer'>
        <?php
            echo "<form action='upload.php' method='post' enctype='multipart/form-data'>";
            echo "<input type='file' name='fileToUpload' id='fileToUpload' class='chooseFolder'>";
            echo "<input type='submit' value='Upload' name='submit'>";
            echo "</form>";
        ?>
    </div>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $target_dir = "../uploads/";
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
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_dir . "schema.txt")) {
            echo "The file has been uploaded.";
            
            include 'csv_converter.php';
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>
    </div>
</body>
</html>