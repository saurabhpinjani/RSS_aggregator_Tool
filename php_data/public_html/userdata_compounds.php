<!DOCTYPE html>
<html>
<head>
<style>
ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
    overflow: hidden;
    border: 1px solid #e7e7e7;
    background-color: #f3f3f3;
}

li {
    float: left;
}

li a {
    display: block;
    color: #666;
    text-align: center;
    padding: 14px 16px;
    text-decoration: none;
}

li a:hover:not(.active) {
    background-color: #ddd;
}

li a.active {
    color: white;
    background-color: #4CAF50;
}
</style>
</head>
<body>
<center>
<?php
    session_start();
    $user_data = $_SESSION["User_details"];
    if(sizeof($user_data)==0){header('Location:sign_in.php');}
    $user_name = $user_data['_source']['username'];
?>
<ul>
  <li><a href="material_table.php">Materials Table</a></li>
  <li><a href="search_server.php">Search Page</a></li>
  <li style="float:right"><a href="sign_out.php">
      <?php echo $user_name." (sign out)" ?>
  </a></li>
  <li style="float:right"><a href="userdata_properties.php">Properties</a></li>
  <li style="float:right"><a class="active" href="userdata_compounds.php">Compounds</a></li>
  <li style="float:right"><a href="userdata_journals.php">Journals</a></li>
</ul>


<form action="userdata_compounds.php" method="post">
<?php 
    session_start();
    // if ($_POST["submit_name"] == "SUBMIT") {
    //     $submitted = 1;
    // }
    // else {
    //     $submitted = 0;
    // }
    function fileToDictionary_1($name)
    {
        $myfile = fopen($name, "r") or die("Unable to open file: ".$name);
        $file_data = array();
        $x = 0;
        while(!feof($myfile)) 
        {     
            $data = (string)fgets($myfile);
            $data = str_replace("\n", "", $data);
            // echo "'";
            // echo $data;
            // echo "'";
            // echo '<br>'; 
            // array_push($file_data, $data);
            $file_data[(string)$x] = $data;
            $x = $x + 1;
        }
        return $file_data;
    }

    //////////////////////////////////////////////////////////////////////////
    //form for compounds choice
    $compounds = fileToDictionary_1("materials/compounds_found.txt");
    // print_r($compounds);
    $compounds_choice = $_SESSION["compounds_choice"]; 
    if (sizeof($compounds_choice)==0) {
        if(sizeof($user_data['_source']['compounds']))
        {
            $compounds_choice = $user_data['_source']['compounds'];
            // print_r($user_data['_source']['compounds']);
        }
        else
            {$compounds_choice = array();}
    }
    
    echo '<br>';
    echo "Search for: <input type=".'"text"'."name=".'"search_text"'.">";
    echo '<br>';
    echo "<input type=".'"submit"'." name=".'"submit_name"'." value=".'"SEARCH"'." style=".'"height:40px; width:80px;"'." >";
    
    echo '</form>';

    $search_text = $_POST["search_text"];
    if (in_array($search_text, $compounds))
    {
        if (!in_array($search_text, $compounds_choice))
            {array_push($compounds_choice, $search_text);}
        echo "FOUND";
        echo '<br>';
    }
    elseif ($_POST["submit_name"] == "SEARCH") {
        echo "NOT FOUND";
        echo '<br>';
    }
    $_SESSION["compounds_choice"] = $compounds_choice;
    echo "<h3>Your choices till now are:</h3>";
    foreach ($compounds_choice as $key => $value) {
        echo "'";
        echo $value;
        echo "'";
        echo '<br>';
    }
    echo "<a href=".'"userdata_properties.php"'.">NEXT </a>";
?>

</center>
</body>
</html>
