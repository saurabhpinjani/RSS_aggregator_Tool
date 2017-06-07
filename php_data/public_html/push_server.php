<html>
<body>

<center>
<h2>Welcome to the cerebro. Find the article of your choice!</h2>
<a href="index.php"><img src="cerebro.jpg" style="width:80%; height:80%;"></a> 
<a href="material_table.php"><h2>Click here for materials table</h2></a>
<!-- <a href="magnetic_table.php"><h2>Click here for magnetic materials table</h2></a> -->
<form action="push_server.php" method="post">
 Index: <input type="text" name="index" ><br>
Type: <input type="text" name="type"><br>
ID: <input type="number" name="id"><br>
Body: <input type="text" name="body"><br>
<input type="submit" value="PUSH !!">
</form>

<form action="search_server.php" method="post">
Search for: <input type="text" name="search_text">
<br>
<input type="submit" value="Search" name="">
</form>

<?php
require 'vendor/autoload.php';

$clientBuilder = Elasticsearch\ClientBuilder::create();   // Instantiate a new ClientBuilder
$clientBuilder->setHosts(['http://localhost:9200']);           // Set the hosts
$client = $clientBuilder->build();          // Build the client object
$params = array();
$params['index'] = $_POST['index'];
$params['type'] = $_POST['type'];
$params['id'] = $_POST['id'];
$params['body'] = array('message' => $_POST['body']);
$result = $client->index($params);
echo "Pushed";
print_r($params);
?>

</center>

</body>
</html>