<!DOCTYPE HTML>  

<html>
<head>
<style>
.button {
  background-color: #bbb;
  padding: .5em;
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  border-radius: 6px;
  color: #fff;
  font-family: 'Oswald';
  font-size: 20px;
  text-decoration: none;
  border: none;
}

.button:hover {
  border: none;
  background: teal;
  box-shadow: 0px 0px 1px #777;
}
ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
    overflow: hidden;
    border: 1px solid #e7e7e7;
    background-color: #bbb;
    font-family: 'Oswald';
    font-size: 17px;
}

li {
    float: left;
}

li a {
    display: block;
    color: #fff;
    text-align: center;
    padding: 14px 16px;
    text-decoration: none;
}

li a:hover:not(.active) {
    background-color: teal;
    color: #fff;
}

li a.active {
    color: white;
    background-color: teal;
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
  <li><a href="material_table.php">New table</a></li>
  <li><a href="load_material_table.php">Load previous tables</a></li>
  <li><a href="search_server.php">Search Page</a></li>
  <li style="float:right"><a href="sign_out.php">
      <?php echo $user_name." (sign out)" ?>
  </a></li>
  <li style="float:right"><a href="userdata_properties.php">Submit</a></li>
  <li style="float:right"><a class="active" href="userdata_compounds.php">Compounds</a></li>
  <li style="float:right"><a href="userdata_journals.php">Journals</a></li>
</ul>

<br>
<br>

<h2>
<a href="/materials/compounds_found.txt" target="_blank">List of compounds on the database as of now</a>
</h2>
<br>    

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
    // $compounds_choice = $_SESSION["compounds_choice"]; 
    // if (sizeof($compounds_choice)==0) {
    //     if(sizeof($user_data['_source']['compounds']))
    //     {
    //         $compounds_choice = $user_data['_source']['compounds'];
    //         // print_r($user_data['_source']['compounds']);
    //     }
    //     else
    //         {$compounds_choice = array();}
    // }

    if(isset($_SESSION["compounds_choice"]))
    {
        $compounds_choice = $_SESSION["compounds_choice"];
    }
    else
    {
        if (sizeof($user_data['_source']['compounds'])>0) 
        {
            $compounds_choice = $user_data['_source']['compounds'];
        }
        else
        {
            $compounds_choice = array();
        }
    }

    $deselected = array();
    if ($_POST["remove"] == "remove selected") {
        foreach ($compounds_choice as $key => $value) {
            if ($_POST[$value]=="on") {
                array_push($deselected, $value);
                // echo "key= ".$key;
                // echo '<br>';
                // echo "value= ".$value;
                // echo '<br>';
            }
        }
        foreach ($deselected as $key => $value) {
            if(($key_1 = array_search($value, $compounds_choice)) !== false)
                    {unset($compounds_choice[$key_1]);}
        }
        sort($compounds_choice);
    }
    echo '<br>';
    echo "Search for: <input type=".'"text"'."name=".'"search_text"'.">";
    echo '<br> <br>';
    echo "<input class=".'"button"'." type=".'"submit"'." name=".'"submit_name"'." value=".'"SEARCH"'." >";
    
    echo '</form>';
    /*if (in_array("", $compounds_choice))
    {
        $key = array_search("", $compounds_choice);
        unset($compounds_choice,$key);
    }*/
    $search_text = $_POST["search_text"];
    if (in_array($search_text, $compounds))
    {
        if ((!in_array($search_text, $compounds_choice)) && ($search_text!= ""))
            {array_push($compounds_choice, $search_text);}
        echo "FOUND";
        echo '<br>';
    }
    elseif ($_POST["submit_name"] == "SEARCH") {
        echo "NOT FOUND";
        echo '<br>';
    }
    $_SESSION["compounds_choice"] = $compounds_choice;
    echo '<form action="userdata_compounds.php" method="post">';
    echo "<h3>Your choices till now are:</h3>";
    foreach ($compounds_choice as $key => $value) {
        echo '<input type="checkbox" name="'.$value.'" value = "on" />';
        // echo "'";
        echo $value;
        // echo "'";
        echo '<br>';
    }
    echo "<input class=".'"button"'." type=".'"submit"'." name=".'"remove"'." value=".'"remove selected"'." >";
    echo "</form>";
    // echo "<a href=".'"userdata_properties.php"'.">NEXT </a>";
?>
<br>
<br>
<br>
<br>
<form action="userdata_properties.php">
    <input class="button" type="submit" name="next" value="NEXT">
</form>
</center>
</body>
</html>
