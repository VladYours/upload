<?php

require_once("config.php");
$mysqli = new mysqli(dbhost, dbuser, dbpsw, dbname);
$mysqli->set_charset("utf8");
$query = "SELECT category FROM user_models GROUP BY category ";
$result = $mysqli->query($query);
$category = $result->fetch_all(MYSQLI_ASSOC);
$pag = 1;
$offset = "";
$download = false;
if (isset($_POST["export"])){
    //comes from form POST
    //parse input
    
        $expt = intval($_POST["export"]);
        $cat = $_POST["cat"];
        $gender = $_POST["gender"];
        $bd = $_POST["bd"];
        $age = $_POST["age"];
        $fromy = $_POST["from"];
        $toy = $_POST["to"];
        
        //where maker
        $where = "";
        //add category
        if ($cat != 'all') {
            $where .= "category = '". $cat. "' AND ";
        }
        //add gender
        if ($gender != 'all') {
            $where .= "gender = '". $gender."' AND ";
        }
        //add date
        if ($bd != "") {
            $where .= "birthdate = '". $bd."'";
        } else if ($age != "") {
            $where .= "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) = ".$age;
        } else if ($fromy != "")  {
            $where .= "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) > ". $fromy;
            $where .= "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) < ".$toy;
        }
        $where_sel = "";
        if (strlen($where) > 0){
            $where = rtrim($where, "AND ");
            $where_sel = "WHERE ".$where;
        }
        //end where maker
        
        //select what to do on "export" 
        if ($expt == 0) {
            $query = "SELECT * FROM user_models ".$where_sel." LIMIT 15";
        } else {
            $query = "SELECT * FROM user_models ".$where_sel." LIMIT 15";
            $result = $mysqli->query($query);
            $results = $result->fetch_all(MYSQLI_ASSOC);
            //for download by user
            // header('Content-Type: text/csv; charset=utf-8');  
            // header('Content-Disposition: attachment; filename=export.csv'); 
            //make csv
            // $out = fopen("php://output", "w"); 
            $out = fopen("export.csv", "w"); 
            foreach($results as $res){
                fputcsv($out, [$res["category"], $res["fname"], $res["lname"], $res["email"], $res["gender"], $res["birthdate"]]);
            }
            fclose($out);
            $download = true;
        }
    
} else if (isset($_GET["pag"])) {
    //comes from pagination
    $query = base64_decode($_GET["q"]);
    $pag = intval($_GET["pag"]);
    $offset = " OFFSET ".(15*($pag-1));
} else {
    //just open Page
    $query = "SELECT * FROM user_models LIMIT 15";
}
$result = $mysqli->query($query.$offset);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>



<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Review</title>
  </head>
  <body>
    
    <form class="row text-center" method="POST">
        <div class="col">
            <a href="/upload.php" class="btn btn-secondary">TO UPLOAD</a>
            <button class="btn btn-primary mr-3" id="filter">FILTER</button>
            <button class="btn btn-warning" id="export">EXPORT</button>
            <?php
            if ($download) {
               echo '<a href="export.csv" class="btn btn-success">DOWNLOAD EXPORT</a>'; 
            } ?>
            <input type="hidden" value="0" name="export" id="checkExp"/>
        </div>
        
    
    <table class="table">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">
              Category
              <select class="form-select"cat name="cat" aria-label="Category select">
                  <option value="all">All</option>
                  <?php
                  foreach ($category as $cat){
                      echo "<option value='".$cat["category"]."'>".$cat["category"]."</option>";
                  }
                  ?>
                </select>
          </th>
          <th scope="col">First Name</th>
          <th scope="col">Last Name</th>
          <th scope="col">Email</th>
          <th scope="col">
              Gender
              <select class="form-select" name="gender" aria-label="Gender select">
                  <option value="all">All</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
          </th>
          <th scope="col">
              Birth Date
              <div class="input-group">
                  <input type="date" class="form-control" id="bd" name="bd" placeholder="Date of Birth"><br/>
                  <span class="input-group-text">or</span>
                  <input type="number" class="form-control" id="age" name="age" placeholder="Age"><br/>
              </div>
              or <br/>
              <div class="input-group">
                  <span class="input-group-text">From</span> <input type="number" class="form-control" id="from" name="from" placeholder="From Age"> 
                  <span class="input-group-text">To</span> <input type="number" class="form-control" id="to" name="to" placeholder="To Age">
                </div>
          </th>
        </tr>
      </thead>
      <tbody>
          <?php foreach ($users as $user) {?>
            <tr>
              <th scope="row"><?php echo $user["id"]; ?></th>
              <td>
                  <?php echo $user["category"]; ?>
              </td>
              <td>
                  <?php echo $use["fname"]; ?>
              </td>
              <td>
                  <?php echo $user["lname"]; ?>
              </td>
              <td>
                  <?php echo $user["email"]; ?>
              </td>
              <td>
                  <?php echo $user["gender"] ; ?>
              </td>
              <td>
                  <?php echo $user["birthdate"]; ?>
              </td>
            </tr>
          <?php } ?>
      </tbody>
    </table>
    
    <nav aria-label="Page navigation example">
      <ul class="pagination justify-content-center">
        <?php if ($pag > 1) { ?>
        <li class="page-item"><a class="page-link" href="?pag=<?php echo ($pag-1); ?>&q=<?php echo base64_encode($query); ?>">Previous</a></li>
        <?php } ?>
        <li class="page-item"><a class="page-link" href="#">Page <?php echo $pag; ?> from</a></li>
        <li class="page-item"><a class="page-link" href="?pag=<?php echo ($pag+1); ?>&q=<?php echo base64_encode($query); ?>">Next</a></li>
      </ul>
    </nav>
    
    
    
    </form>

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    -->
    <script>
        let form = document.querySelector('form');
        let filterb = document.querySelector('#filter');
        let exportb = document.querySelector('#export');
        let exportc = document.querySelector('#checkExp');
        exportb.onclick = function () {
            exportc.value = '1';
            form.submit();
        }
        filterb.onclick = function () {
            exportc.value = '0';
            form.submit();
        }
        
    </script>
  </body>
</html>