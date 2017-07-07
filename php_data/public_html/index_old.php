<html>
<link rel="stylesheet" type="text/css" href="style.css"/>

<body>
<div id="topLine">
  <div id="header">
    <ul class="nav">
      <li><a href="http://material.ie">Home</a></li>
      <li><a href="http://material.ie/material_table.php">Property table</a></li>
    </ul>
    <h1><span>   M</span>aterials - <span>S</span>earch</h1>
  </div>
</div>


 
<center>
<a href="https://pixabay.com/en/brain-human-anatomy-anatomy-human-1787622/"><img src="brain.jpg"></a>

<!--
<a href="material_table.php"><h3>Click here for materials table</h3></a>
--> 

<form action="search_server.php" method="post">
<h1>Search for material: <input type="text" name="search_text_1"></h1>
<br>
<input class="button" type="submit" value="Search" name="submit" id="submit">
</form>
 
<?php


   
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
     
    if($hits>0)
    {
        echo "Total number of hits: $hits";
        echo '<br>';
        foreach ($result['hits']['hits'] as $x) 
        {
            $link = $x['_source']['id'];
            echo "<a href= '$link'>";
            echo $x['_source']['title'];
            echo '</a>';
            echo '<br>';
        }
 
    }
?>
 


</center>
  
</body>
</html>
