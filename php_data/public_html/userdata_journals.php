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
<?php
    session_start();
    $user_data = $_SESSION["user_details"];
    $user_name = $user_data['_source']['username'];
?>
<center>
<ul>
  <li><a class="active" href="userdata_journals.php">Journals</a></li>
  <li><a href="userdata_compounds.php">Compounds</a></li>
  <li><a href="userdata_properties.php">Properties</a></li>
  <li style="float:right"><a href="">
      <?php echo $user_name ?>
  </a></li>
</ul>

<form action="userdata_journals.php" method="post">
<?php 
    session_start();
    if ($_POST["submit_name"] == "SUBMIT") {
        $submitted = 1;
    }
    else {
        $submitted = 0;
    }
    function fileToDictionary($name)
    {
        $myfile = fopen($name, "r") or die("Unable to open file: ".$name);
        $file_data = array();
        $x = 0;
        while(!feof($myfile)) 
        {     
            $data = (string)fgets($myfile); 
            $file_data[(string)$x] = $data;
            $x = $x + 1;
        }
        return $file_data;
    }

    $journals = fileToDictionary("journals.txt");
    $journal_choice = $_SESSION["journal_choice"]; 
    if (sizeof($journal_choice)==0) {$journal_choice = array();}
    ////////////////////////////////////////////////////////////////////////
    //form for journal choice input
    if(!$submitted)
    {
        echo "<h2> Pick the journals </h2>";
        echo '<table border="7" cellpadding="10">';
        echo '<tr>';
        echo "<th colspan=".'8'.">Journals</th>";
        echo "</tr>";

        $i = 0;
        echo '<tr>';
        foreach ($journals as $key => $title)     
        {
            echo '<td>';
            echo "<input type=checkbox name=".$key.">".$title."<br>";
            echo '</td>';
            $i++;
            if($i==8) { echo "</tr> <tr>"; $i=0;};
        }
        echo '</tr>';
        echo '</table>';
        echo '<br>';
        echo "<input type=".'"submit"'." name=".'"submit_name"'." value=".'"SUBMIT"'." style=".'"height:80px; width:160px; font-size: 200%"'." >";
        echo '</form>';
    }
    else
    {
        foreach ($journals as $key => $value) {
            $journals[$key] = array($value,$_POST[$key]);
            if ($_POST[$key]=="on") {
                // echo $value;
                if (!in_array($value, $journal_choice))
                {array_push($journal_choice, $value);}
                
            }

        }
        $_SESSION["journal_choice"] = $journal_choice;
        echo "submitted!!";
        echo '<br>';
        echo '<br>';
    }

    echo "<h3>Your choices till now are:</h3>";
    foreach ($journal_choice as $key => $value) {
        echo $value;
        echo '<br>';
    }
    echo '<br>';
    echo "<a href=".'"userdata_compounds.php"'.">NEXT</a>";
    echo '<br>';
        
?>

<a href=""></a>
</center>
</body>
</html>
