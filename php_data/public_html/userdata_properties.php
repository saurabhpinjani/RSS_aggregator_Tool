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
    $user_data = $_SESSION["user_details"];
    $user_name = $user_data['_source']['username'];
?>
<ul>
  <li><a href="userdata_journals.php">Journals</a></li>
  <li><a href="userdata_compounds.php">Compounds</a></li>
  <li><a class="active" href="userdata_properties.php">Properties</a></li>
  <li style="float:right"><a href="">
      <?php echo $user_name ?>
  </a></li>
</ul>

<form action="userdata_properties.php" method="post">
<?php 

    session_start();
    require 'vendor/autoload.php';
    $clientBuilder = Elasticsearch\ClientBuilder::create();   // Instantiate a new ClientBuilder
    $clientBuilder->setHosts(['http://localhost:9200']);           // Set the hosts
    $client = $clientBuilder->build();          // Build the client object

    $user_data = $_SESSION["user_details"];
    $journal_choice = $_SESSION["journal_choice"];
    $compounds_choice = $_SESSION["compounds_choice"];
    $property_choice  = $_SESSION["property_choice"];
    if (sizeof($property_choice)==0) {$property_choice = array();}

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
    $dir = "properties/";
    $files = scandir($dir);
    $files = array_slice($files,2); // 2 random files come in the begining so sliced them off
    $properties = array();
    if (!$submitted) 
    {
    ///////////////////////////////////////////////////////////////////////////
        echo "<h2> Pick the properties </h2>";
        $table_size = (string)7;
        echo '<table border="7" cellpadding="10">';
        echo "<tr> <th colspan=".$table_size."> Properties </th> </tr>";
        echo '<tr>';
        foreach ($files as $file) 
        {
            $prop_name = strrev($file);
            $prop_name = substr($prop_name,4);
            $prop_name = strrev($prop_name);
            echo '<td>';
            echo "<input type=checkbox name=".$prop_name.">".$prop_name."<br>";
            echo '</td>';
        }
        echo '</tr>';
        echo '</table>';
        echo '<br>';
        echo "<input type=".'"submit"'." name=".'"submit_name"'." value=".'"SUBMIT"'." style=".'"height:80px; width:160px; font-size: 200%"'." >";
        echo '</form>';
        echo "<h3>Your choices till now are:</h3>";
        foreach ($property_choice as $key => $value) {
            echo $value;
            echo '<br>';
        }
    }
    else
    {
        $x = 0;
        foreach ($files as $file) 
        {
            $prop_name = strrev($file);
            $prop_name = substr($prop_name,4);
            $prop_name = strrev($prop_name);
            $properties[(string)$x] = array($prop_name,$_POST[$prop_name]);
            if ($_POST[$prop_name]=="on") {
                if (!in_array($prop_name, $propert_choice))
                    {array_push($property_choice, $prop_name);}
            }
            $x++ ;
        }
        $_SESSION["property_choice"] = $property_choice;
        echo "<h3>Journals:</h3>";
        foreach ($journal_choice as $key => $value) {
            echo $value;
            echo '<br>';
        }
        echo '<br>';
        echo "<h3>Compounds:</h3>";
        foreach ($compounds_choice as $key => $value) {
            echo $value;
            echo '<br>';
        }
        echo '<br>';
        echo "<h3>Properties:</h3>";
        foreach ($property_choice as $key => $value) {
            echo $value;
            echo '<br>';
        }
        

        $params = array();      
        $params['index'] = $user_data['_index'];
        $params['type'] = $user_data['_type'];
        $params['id'] = $user_data['_id'];
        $params['body'] = array('username' => $user_data['_source']['username'],
                                'password' => $user_data['_source']['password'],
                                'journals' => $journal_choice,
                                'compounds' => $compounds_choice,
                                'properties' => $property_choice );

        // print_r($params);
        $result = $client->index($params);
        $_SESSION['user_details']=$params['body'];

        if ($result['_shards']['successful']) 
        {
            echo "<h2>Everything pushed to server</h2>";
            echo '<br>';
            echo '<br>';
        }
        else
        {
            echo "<h2>Cant be pushed to server</hw_Array2Objrec(object_array)>";
            echo '<br>';
            echo '<br>';
        }
        
        
        echo "<a href=".'"search_server.php"'.">Go to search page</a>";   
    }
?>

</center>
</body>
</html>
