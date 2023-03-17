<?php
require_once("config.php");
$mysqli = new mysqli(dbhost, dbuser, dbpsw, dbname);
$mysqli->set_charset("utf8");
$uploadOk = 1;
$err = "";
  if (isset($_FILES["csv"])){ 
    $target_dir = "csv/";
    $target_file = $target_dir . basename($_FILES["csv"]["name"]);
    $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
    
    // Check if file already exists
    if (file_exists($target_file)) {
      $err .= "Sorry, file already exists.";
      $uploadOk = 0;
    }
    
    // Check file size
    if ($_FILES["csv"]["size"] >1000000) {
      $err .= "Sorry, your file is too large.";
      $uploadOk = 0;
    }
    
    // Allow certain file formats
    if($fileType != "csv" && $fileType != "txt") {
      $err .= "Sorry, only CSV or TXT files are allowed.";
      $uploadOk = 0;
    }
    
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
      $err .= "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        
      if (move_uploaded_file($_FILES["csv"]["tmp_name"], $target_file)) {
          if (($handle = fopen($target_file, "r")) !== FALSE) {
                    //parse CSV to model
                    $values = "";
                    while (($data = fgetcsv($handle, 700)) !== FALSE) {
                        $category = $mysqli->real_escape_string($data[0]);
                        $fname = $mysqli->real_escape_string($data[1]);
                        $lname = $mysqli->real_escape_string($data[2]);
                        $email = $mysqli->real_escape_string($data[3]);
                        $gender = $mysqli->real_escape_string($data[4]);
                        $date = $mysqli->real_escape_string($data[5]);
                        $values .= "('".$category."','".$fname."','".$lname."','".$email."','".$gender."','".$date."'),";
                    }
                    fclose($handle);
                    $values_for_insert = rtrim($values, ",");
                    //truncate table
                    $query = "TRUNCATE TABLE user_models ";
                    $result = $mysqli->query($query);
                    //insert in DB
                    $query = "INSERT INTO user_models (category, fname, lname, email, gender, birthdate)
VALUES ". $values_for_insert;
                    $result = $mysqli->query($query);
                    if ($result) {
                        header('Location: ./review.php');
                    } else {
                        $err .= "Error when insert to DB";
                        $uploadOk = 2;
                    }
                    
                } else {
                    $err .= "Error when parse data from CSV";
                    $uploadOk = 0;
                } //end else 
                //delete file from server
                unlink($target_file);
      } else {
          $err .= "Sorry, there was an error uploading your file.";
          $uploadOk = 0;
      }
    }
  } 

?>


<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Upload file</title>
  </head>
  <body>
    
    <h1 class="w-100 text-center">Upload File with Users Info</h1>
    <form method="POST" enctype="multipart/form-data" class="row g-3 text-center justify-content-center">
        <?php
        if ($uploadOk != 1) {
            echo '<div class="alert alert-danger" role="alert">'.$err.$uploadOk.'</div>';
        }
        ?>
        <div class="col-auto">
          <label for="formFile" class="form-label">File with Users</label>
          <input class="form-control" type="file" name="csv" id="formFile">
          <button class="btn btn-primary mt-3" type="submit">Upload</button>
        </div>
    </form>

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    -->
    
  </body>
</html>
