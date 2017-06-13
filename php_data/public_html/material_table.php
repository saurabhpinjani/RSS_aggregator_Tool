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
  require 'vendor/autoload.php';

  function add_arrays($a,$b)
  {
    $sum= array();
    $len=count($a);
    for ($i=0;$i<$len-1;$i++)
    {
      $sum[$i][0]=$a[$i][0]+$b[$i][0];
      $sum[$i][1]=$a[$i][1]+$b[$i][1];
    }
    $sum[$len-1]=$a[$len-1];
    return $sum;
  }
  
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
    
    array_push ($property_array ,$prop_name);
  }
  sort($property_array);
 
 
  $selected_journals=array(); // pullin out user information and preferences from the session
  $user_details=$_SESSION['User_details'];
  $selected_journals=$user_details['_source']['journals'];
  $selected_materials =$user_details['_source']['compounds'];

  $count_array=array();
  $results_array=array();

  foreach($selected_journals as $journal) // reading the material tables of all the  selected journals
  {
    
   
    $string = file_get_contents("materials/".$journal."_material_table_counts.json");
    $count_array[$journal] = json_decode($string, true);
      
    $string = file_get_contents("materials/".$journal."_material_table_results.json");
    $results_array[$journal] = json_decode($string, true);

  } 
  $row_len = count($count_array[$journal][0]);
  $net_results_array=array();
  $net_count_array=array();
  $cnt=0;
  $empty_row=array();
  for($a=0;$a<$row_len-1;$a++)
  {
    $empty_row[$a][0]=0;
    $empty_row[$a][1]=0;
  }
  $empty_row[$row_len-1]=0;
  
  // aggregate the data from all the material tables
  foreach ($selected_materials as $material)
  {
    $sum_row=$empty_row;
    foreach($selected_journals as $journal)
    {
      
      $count_array_journal=$count_array[$journal];
      
      $i=0;
      foreach($count_array_journal as $row)
      { 
        $row[$row_len-1] = str_replace("\n", "", $row[$row_len-1]);
        $row[$row_len-1] = str_replace(" ", "", $row[$row_len-1]);   
        if($row[$row_len-1] == $material)
        { 
          $sum_row=add_arrays($row,$sum_row);
          
          for($j=0;$j<$row_len-1;$j++)
          {
            $net_results_array[$cnt][$j][$journal]=$results_array[$journal][$i][$j];
          }
        break; 
        }
        

        $i=$i+1;
      }

    }
    $net_count_array[$cnt]=$sum_row;
    $cnt=$cnt+1;
  }

  $_SESSION['query_result'] = $net_results_array; // store the results in the session variable
  
  echo '<table border="7" cellpadding="10">' ;
  echo '<tr>' ;
  array_push ($property_array ,'Others');
  array_push ($property_array ,'Total');
 
  // print the table headers
  foreach ($property_array as $key) {
    echo '<td>';
    echo $key;
    echo '</td>';
  }
  echo '</tr>';
  $j = 0;
  // print the table
  for ($i = 0; $i < count($net_count_array); $i++) {
    echo '<tr>';
    $j=0;
    foreach ($property_array as $property) 
    {
      echo '<td>' ; 
      
      echo "<a href='material_table_query_res.php?i=".$i."&j=".$j."'>".$net_count_array[$i][$j][0]."</a>";
      echo '</td>';
      $j++ ; 
    } 
    echo '<td>' ; 
    echo $net_count_array[$i][$row_len-1]; 
    echo '</td>';
    echo '</tr>';
  }
  
?>  
    
</table>
</center>
</body>

</html>
