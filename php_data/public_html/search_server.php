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
<?php
    session_start();
    $user_data = $_SESSION["User_details"];
    if(sizeof($user_data)==0){header('Location:sign_in.php');}
    $user_name = $user_data['_source']['username'];  
?>
<center>
<ul>
  <li><a href="material_table.php">New table</a></li>
  <li><a href="load_material_table.php">Load previous tables</a></li>
  <li><a class="active" href="search_server.php">Search Page</a></li>
  <li style="float:right"><a href="sign_out.php">
      <?php echo $user_name." (sign out)" ?>
  </a></li>
  <li style="float:right"><a href="userdata_properties.php">Submit</a></li>
  <li style="float:right"><a href="userdata_compounds.php">Compounds</a></li>
  <li style="float:right"><a href="userdata_journals.php">Journals</a></li>
</ul>
<br>
<a href="index.php"><img src="cerebro.png"></a> 
<!--<a href="magnetic_table.php"><h2>Click here for magnetic materials table</h2></a>

<form action="push_server.php" method="post">
 Index: <input type="text" name="index" ><br>
Type: <input type="text" name="type"><br>
ID: <input type="number" name="id"><br>
Body: <input type="text" name="body"><br>
<input type="submit" value="PUSH !!">
</form>
-->
<br>
<form action="search_server.php" method="post">
Search for: <input type="text" name="search_text_1">
<br>
<input type="radio" name="search_type" value="words_match">Match all the words<br>
<input type="radio" name="search_type" value="phrase_match">Match the phrase<br>
<input class="button" type="submit" value="Search" name="">
</form>

<?php
    
    session_start();

	require 'vendor/autoload.php';
	$clientBuilder = Elasticsearch\ClientBuilder::create();   // Instantiate a new ClientBuilder
	$clientBuilder->setHosts(['http://localhost:9200']);           // Set the hosts
	$client = $clientBuilder->build();          // Build the client object
    $search_text = $_POST['search_text_1'];
    $params['size'] = 1000;
    $params['body']['query']['match']['_all']['query'] = $search_text;
    if($_POST['search_type']=="phrase_match")
        $params['body']['query']['match']['_all']['type'] = "phrase";
    else if($_POST['search_type']=="words_match")
        $params['body']['query']['match']['_all']['operator'] = "and";
    $result = $client->search($params);
    $hits = $result['hits']['total'];
    $data = $result['hits']['hits'];
    
    $_SESSION['search_result'] = $data;

    if($hits>0)
    {
    	echo "Total number of hits: $hits";
    	echo '<br>';
        $i = 0;
	    foreach ($result['hits']['hits'] as $x) 
	    {
		    $link = $x['_source']['id'];
            if($x['_source']['read_yet']=='brahmavishnu') //brahmavishnu means false
            {
                //ChangeReadYet($x);
                echo $x['_id'];
                echo "<a href= 'changeread.php?i=".$i."' style='color: rgb(0,0,255)'>";
            }
            else 
            {
                echo $x['_id'];
                echo "<a href= '$link' style='color: rgb(255,0,0)'>";
            }   
            echo $x['_source']['title'];
            echo '</a>';
            echo '<br>';

            /*
		    echo "<a href= '$link'>";
		    echo $x['_source']['title'];
		    echo '</a>';
		    echo '<br>';*/
            $i++ ;
		}

	}
    else
    {
    	echo "Sorry! Error 404";
    	echo '<br>';
    	echo "#PEACE";
    }
?>

</center>


</body>
</html>