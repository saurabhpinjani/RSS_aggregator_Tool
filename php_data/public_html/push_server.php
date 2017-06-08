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
<?php
    session_start();
    $user_data = $_SESSION["User_details"];
    $user_name = $user_data['_source']['username'];  
?>
<center>
<ul>
  <li><a href="material_table.php">Materials Table</a></li>
  <li><a href="search_server.php">Search Page</a></li>
  <li style="float:right"><a href="sign_out.php">
      <?php echo "ROOT USER"." (sign out)" ?>
  </a></li>
  <li style="float:right"><a href="userdata_properties.php">Properties</a></li>
  <li style="float:right"><a href="userdata_compounds.php">Compounds</a></li>
  <li style="float:right"><a href="userdata_journals.php">Journals</a></li>
</ul>

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