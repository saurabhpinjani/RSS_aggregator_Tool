<html>
<body>

<center>
<h2>Welcome to the cerebro. Find the article of your choice!</h2>
<a href="index.php"><img src="cerebro.jpg" style="width:80%; height:80%;"></a> 
<a href="material_table.php"><h2>Click here for materials table</h2></a>
<!--<a href="magnetic_table.php"><h2>Click here for magnetic materials table</h2></a>

<form action="push_server.php" method="post">
 Index: <input type="text" name="index" ><br>
Type: <input type="text" name="type"><br>
ID: <input type="number" name="id"><br>
Body: <input type="text" name="body"><br>
<input type="submit" value="PUSH !!">
</form>
-->
<form action="search_server.php" method="post">
Search for: <input type="text" name="search_text_1">
<br>
<input type="submit" value="Search" name="">
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