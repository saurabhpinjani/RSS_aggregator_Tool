<?php
// Start the session
session_start();
?>
<!DOCTYPE HTML>  

<html>
<head>
<style>
.error {color: #FF0000;}
</style>
</head>
<body>  
<center>
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
  $file = fopen("materials/2D-List.txt","r");
  $data_array = array();
  $x = 0; 
  while(!feof($file))
  {
    $data_array[$x] = fgets($file);
    $x++;
  }
  $material_array = $data_array;

$file = fopen("../../RSS_urls/journals.txt","r");
  $journal_list= array();
  $x = 0; 
  while(!feof($file))
  {
    $journal_list[$x] = fgets($file);
    $x++;
  }

  $selected_journals=array();
  $user_details=$_SESSION['user_details'];
  $selected_journals=$user_details['journals'];
  $selected_materials =$user_details['compounds'];
  $count_array=array();
  $results_array=array();

  foreach($selected_journals as $journal)
  {
    $string = file_get_contents("materials/"+$journal+"material_table_counts.json");
    $count_array[$journal] = json_decode($string, true);

  
    $string = file_get_contents("materials/"+$journal+"material_table_results.json");
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
  foreach ($selected_materials as $material)
  {
    $sum_row=$empty_row;
    
    
    foreach($selected_journals as $journal)
    {
      $count_array_journal=$count_array[$journal];
      $i=0;
      foreach($count_array_journal as $row)
      { 
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
 
  echo '<table border="1" cellpadding="10">' ;
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