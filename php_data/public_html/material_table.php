<?php
// Start the session
session_start();
?>
<!DOCTYPE HTML>  

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
  <li><a class="active"  href="material_table.php">Materials Table</a></li>
  <li><a href="search_server.php">Search Page</a></li>
  <li style="float:right"><a href="sign_out.php">
      <?php echo $user_name." (sign out)" ?>
  </a></li>
  <li style="float:right"><a href="userdata_properties.php">Properties</a></li>
  <li style="float:right"><a href="userdata_compounds.php">Compounds</a></li>
  <li style="float:right"><a href="userdata_journals.php">Journals</a></li>
</ul>

<?php 

  function add_arrays($a,$b)
  {
    $sum= array();
    for ($i=0;$i<9;$i++)
    {
      $sum[$i][0]=$a[$i][0]+$b[$i][0];
      $sum[$i][1]=$a[$i][1]+$b[$i][1];
    }
    $sum[9]=$a[9];
    return $sum;
  }
  require 'vendor/autoload.php';
  $dir = "properties/";
  $files = scandir($dir);
  $files = array_slice($files,2);
  foreach ($files as $file) {
    $prop_name = strrev($file);
    $prop_name = substr($prop_name,4);
    $prop_name = strrev($prop_name);
    $file_loc = $dir.$file;
    $file = fopen($file_loc,"r");
    $data_array = array();
    $x = 0; 
    while(!feof($file))
    {
      $data_array[$x] = fgets($file);
      $x++;
    }
    $property_array[$prop_name] = $data_array;
  }
 
 
  $selected_journals=array();
  $user_details=$_SESSION['User_details'];
  
  $selected_journals=$user_details['_source']['journals'];
  $selected_materials =$user_details['_source']['compounds'];
  $count_array=array();
  $results_array=array();

  foreach($selected_journals as $journal)
  {
    // $journal=strtolower($journal);
    // echo "'";
    // echo $journal;
    // echo "'";
    // echo '<br>'; 
    // $journal = str_replace("\n", "", $journal);
   
    $string = file_get_contents("materials/".$journal."_material_table_counts.json");
    $count_array[$journal] = json_decode($string, true);
    
    // echo "size of journal json: ".count($count_array[$journal]);
    // print_r($count_array[$journal]);
   
  
    $string = file_get_contents("materials/".$journal."_material_table_results.json");
    $results_array[$journal] = json_decode($string, true);

  } 

  $net_results_array=array();
  $net_count_array=array();
  $cnt=0;
  $empty_row=array();
  for($a=0;$a<9;$a++)
  {
    $empty_row[$a][0]=0;
    $empty_row[$a][1]=0;
  }
  $empty_row[9]=0;
  // print_r(count($selected_materials));
  // echo "<br>";
  // print_r($selected_journals);
  //  echo "<br>";
  foreach ($selected_materials as $material)
  {
    $sum_row=$empty_row;
    foreach($selected_journals as $journal)
    {
      // echo $journal;
      $count_array_journal=$count_array[$journal];
      // echo $count_array_journal;
      $i=0;
      foreach($count_array_journal as $row)
      { 
        $row[9] = str_replace("\n", "", $row[9]);
        $row[9] = str_replace(" ", "", $row[9]);   
        if($row[9] == $material)
        { 
          $sum_row=add_arrays($row,$sum_row);
          break; 
        }
        for($j=0;$j<9;$j++)
        {
          $net_results_array[$cnt][$j][$journal]=$results_array[$journal][$i][$j];
        }

        $i=$i+1;
      }

    }
    $net_count_array[$cnt]=$sum_row;
    $cnt=$cnt+1;
  }

  $_SESSION['query_result'] = $net_results_array;
 
  echo '<table border="7" cellpadding="10">' ;
  echo '<tr>' ;
  $property_array['Others']='';
  $property_array['Total']='';
  
  foreach ($property_array as $key => $value) {
    echo '<td>';
    echo $key;
    echo '</td>';
  }
  echo '</tr>';
  $j = 0;
  for ($i = 0; $i < count($net_count_array); $i++) {
    echo '<tr>';
    $j=0;
    foreach ($property_array as $property) 
    {
      echo '<td>' ; 
      
      echo "<a href='material_table_query_res.php?i=".$i."&j=".$j."'>".$net_count_array[$i][$j][0].",".$net_count_array[$i][$j][1]."</a>";
      echo '</td>';
      $j++ ; 
    } 
    echo '<td>' ; 
    echo $net_count_array[$i][9]; 
    echo '</td>';
    echo '</tr>';
  }
  
?>  
    
</table>
</center>
</body>

</html>