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
<ul>
  <li><a href="userdata_journals.php">Journals</a></li>
  <li><a class="active" href="userdata_compounds.php">Compounds</a></li>
  <li><a href="userdata_properties.php">Properties</a></li>
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
        $compounds_choice = array();
    }
    // asort($compounds);

    // if(!$submitted)
    // {
    // echo "<h2> Pick the compounds </h2>";
    // echo '<table border="7" cellpadding="10">';
    // echo '<tr>';
    // echo "<th colspan=".'8'.">Compounds</th>";
    // echo "</tr>";
    // $i = 0;
    // echo '<tr>';
    // foreach ($compounds as $key => $value) {
    //     echo '<td>';
    //     echo "<input type=checkbox name=".$key.">".$value."<br>";
    //     echo '</td>';
    //     $i++;
    //     if($i==8) { echo "</tr> <tr>"; $i=0;};
    // }
    // echo '</tr>';
    // echo '</table>';
    // echo '<br>';
    // echo "<input type=".'"submit"'." name=".'"submit_name"'." value=".'"SUBMIT"'." style=".'"height:80px; width:160px; font-size: 200%"'." >";
    // echo '</form>';
    // }
    // else
    // {
    //     foreach ($compounds as $key => $value) {
    //         $compounds[$key] = array($value, $_POST[$key]);
    //         if ($_POST[$key]=="on") {
    //             echo $value;
    //             echo '<br>';
    //         }
    //     }
    //     echo "Submitted";
        
    // }
    ////////////////////////////////////////////////////////////////////////////
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
    echo "your choices are:";
    echo '<br>';
    foreach ($compounds_choice as $key => $value) {
        echo $value;
        echo '<br>';
    }
?>
<form action="userdata_compounds.php" method="post">

<?php
    echo '<br>';
    echo '<br>';
    if ($_POST["submit_name"] == "SUBMIT") {
        echo "Pushed to server!!";
        //submit here

    }
    else 
    {
        echo "Submit into the server";
        echo '<br>';
        echo "<input type=".'"submit"'." name=".'"submit_name"'." value=".'"SUBMIT"'." style=".'"height:40px; width:80px;"'." >";
    }
    
    echo '</form>';
?>
</center>
</body>
</html>
