<?php
// Start the session
    session_start();
    $user_data = $_SESSION["User_details"];
    if(sizeof($user_data)==0){header('Location:sign_in.php');}
    $user_name = $user_data['_source']['username'];
?>
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
<ul>
  <li><a href="material_table.php">New table</a></li>
  <li><a class="active"  href="load_material_table.php">Load previous tables</a></li>
  <li><a href="search_server.php">Search Page</a></li>
  <li style="float:right"><a href="sign_out.php">
      <?php echo $user_name." (sign out)" ?>
  </a></li>
  <li style="float:right"><a href="userdata_properties.php">Submit</a></li>
  <li style="float:right"><a href="userdata_compounds.php">Compounds</a></li>
  <li style="float:right"><a href="userdata_journals.php">Journals</a></li>
</ul>

<?php

    $user_data = $_SESSION["User_details"];
    if(sizeof($user_data)==0){header('Location:sign_in.php');}
    $user_name = $user_data['_source']['username'];
    $table_list=array();
    if(array_key_exists('saved_tables',$user_data['_source']))
    {
        $table_list=$user_data['_source']['saved_tables'];
    }
    // print_r($table_list);  
    require 'vendor/autoload.php';
    $clientBuilder = Elasticsearch\ClientBuilder::create();   // Instantiate a new ClientBuilder
    $clientBuilder->setHosts(['http://localhost:9200']);           // Set the hosts
    $client = $clientBuilder->build(); 
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
    $property_array =array();
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
     array_push ($property_array ,'Others');
     array_push ($property_array ,'Total');
    
     

?>  



<br>
<form action="load_material_table.php" method="post">
    <select name="table_name">
    <option value="">Select Table</option>
    

<?php

    foreach ( array_keys($table_list) as $key)
    {
        
        echo "<option value=".$key.">".$key."</option>";
    }

?>
    </select>
<br>
<input type ="hidden" name= "action" value="load"><br>
<input class="button" type="submit" value="Load Table" name="Load_button">

</form>

<?php

if(isset($_POST['action']))
{
    $table_name = $_POST['table_name'];
    if($_POST['action']=="load")
    {
        if($table_name!="")
        {    


             $selected_journals=array(); // pullin out user information and preferences from the session
              $user_details=$_SESSION['User_details'];
              $selected_journals=$user_details['_source']['saved_tables'][$table_name]['journals'];
              $selected_materials =$user_details['_source']['saved_tables'][$table_name]['compounds'];
             
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

            
             $count_array=$net_count_array;
             $results_array=$net_results_array;
             $journals= $table_list[$table_name]['journals'];
             $compounds=$table_list[$table_name]['compounds'];
             echo "<br>";
            echo '<table border="7" cellpadding="10">' ;
            echo '<tr>' ;
            foreach ($property_array as $key)
            {
                echo '<td>';
                echo $key;
                echo '</td>';
            }
            echo '</tr>';
            $j = 0;
            // print the table
      
            for ($i = 0; $i < count($count_array); $i++)
            {
                echo '<tr>';
                $j=0;
                foreach ($property_array as $property) 
                {
                  echo '<td>' ; 
                  
                  echo "<a href='material_table_query_res.php?i=".$i."&j=".$j."'>".$count_array[$i][$j][0]."</a>";
                  echo '</td>';
                  $j++ ; 
                } 
                echo '<td>' ; 
                echo $count_array[$i][$row_len-1]; 
                echo '</td>';
                echo '</tr>';

            }
            $_SESSION['query_result'] = $results_array; // store the results in the session variable
            $_SESSION['net_count']= $count_array;

            echo "</table>";

            echo "<h2> Journals Selected in this Table</h2>";
            echo "<br>";
            foreach ($journals as $journal) 
            {
                echo $journal."<br>";
                
            }

            echo "<form action='load_material_table.php' method='post'>";
            echo "<input type ='hidden'name= 'action' value='delete_table'><br>";
            echo "<input type ='hidden'name= 'table_name' value=".$table_name."><br>";
            echo "<input type='submit' value='delete_table' name='Delete Table'>";
            echo "</form>";
        }

        
    }
    else if($_POST['action']=="delete_table")
    {
        
       
        unset($table_list[$table_name]);
        
        $user_data['_source']['saved_tables'] =$table_list;
        $_SESSION["User_details"]= $user_data;

        $params['index'] = $user_data['_index'];
        $params['type'] = $user_data['_type'];
        $params['id'] = $user_data['_id'];
        $params['body'] = $user_data['_source'];
        
        $res = $client->index($params);
        if($res['result']=="updated")
        {
            echo "<h2> Table deleted</h2>";
        }

    }

}
  
?>

</center>
</body>

</html>