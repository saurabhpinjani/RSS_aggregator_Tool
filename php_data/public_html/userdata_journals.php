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
    background-color: #DFEAF1;
}

li a.active {
    color: white;
    background-color: #8796CD;
}
</style>
</head>
<body style="background-color: #E8F0F5"> 
<?php
    session_start();
    $user_data = $_SESSION["User_details"];
    if(sizeof($user_data)==0){header('Location:sign_in.php');}
    $user_name = $user_data['_source']['username'];  
?>
<center>
<ul>
  <li><a href="material_table.php">Materials Table</a></li>
  <li><a href="search_server.php">Search Page</a></li>
  <li style="float:right"><a href="sign_out.php">
      <?php echo $user_name." (sign out)" ?>
  </a></li>
  <li style="float:right"><a href="userdata_properties.php">Properties</a></li>
  <li style="float:right"><a href="userdata_compounds.php">Compounds</a></li>
  <li style="float:right"><a class="active" href="userdata_journals.php">Journals</a></li>
</ul>

<form action="userdata_journals.php" method="post">
<?php 
    session_start();
    if ($_POST["submit_name"] == "NEXT") {
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
            $data = str_replace("\n", "", $data);
            $data = strtolower($data);
            $file_data[(string)$x] = $data;
            $x = $x + 1;
        }
        return $file_data;
    }

    $journals = fileToDictionary("journals.txt");

    //journals
    if(isset($_SESSION["journal_choice"]))
    {
        $journal_choice = $_SESSION["journal_choice"];
    }
    else
    {
        if (sizeof($user_data['_source']['journals'])>0) 
        {
            $journal_choice = $user_data['_source']['journals'];
        }
        else
        {
            $journal_choice = array();
        }
    }

    // $journal_choice = $_SESSION["journal_choice"]; 
    // if (sizeof($journal_choice)==0) {
    //     if(sizeof($user_data['_source']['journals']))
    //     {
    //         // echo "1";
    //         $journal_choice = $user_data['_source']['journals'];
    //     }
    //     else
    //         {$journal_choice = array();}
    // }
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
            // echo "<input type=checkbox name=".$key.">".$title."<br>";
            if(in_array($title, $journal_choice))
                echo "<input type=checkbox name=".$key." value=".'"on"'. " checked>".$title."<br>";
            else
                echo "<input type=checkbox name=".$key." value=".'"on"'. ">".$title."<br>";
            echo '</td>';
            $i++;
            if($i==8) { echo "</tr> <tr>"; $i=0;};
        }
        echo '</tr>';
        echo '</table>';
        echo '<br>';
        echo "<input type=".'"submit"'." name=".'"submit_name"'." value=".'"NEXT"'." style=".'"height:80px; width:160px; font-size: 200%"'." >";
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
            else
            {
                if(($key = array_search($value, $journal_choice)) !== false)
                    {unset($journal_choice[$key]);}
            }

        }
        $_SESSION["journal_choice"] = $journal_choice;
        header('Location:userdata_compounds.php');
    }
        
?>

<a href=""></a>
</center>
</body>
</html>
