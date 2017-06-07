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
  <li><a href="userdata_compounds.php">Compounds</a></li>
  <li><a class="active" href="userdata_properties.php">Properties</a></li>
</ul>

<form action="userdata_properties.php" method="post">
<?php 
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
    $x = 0;
    foreach ($files as $file) 
    {
        $prop_name = strrev($file);
        $prop_name = substr($prop_name,4);
        $prop_name = strrev($prop_name);
        echo '<td>';
        echo "<input type=checkbox name=".$prop_name.">".$prop_name."<br>";
        echo '</td>';
        $property_choice[(string)$x] = array($prop_name,$_POST[$prop_name]);
        $x++ ;
    }
    echo '</tr>';
    echo '</table>';
    echo '<br>';
    echo "<input type=".'"submit"'." name=".'"submit_name"'." value=".'"SUBMIT"'." style=".'"height:80px; width:160px; font-size: 200%"'." >";
    echo '</form>';
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
            echo $prop_name;
            echo '<br>';
            
        }
        $x++ ;
    }
    echo "submitted!!";
}
?>

</center>
</body>
</html>
