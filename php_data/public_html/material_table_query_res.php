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
	require 'vendor/autoload.php';
	$search_result= array();
	$clientBuilder = Elasticsearch\ClientBuilder::create();   // Instantiate a new ClientBuilder
	$clientBuilder->setHosts(['http://localhost:9200']);           // Set the hosts
	$client = $clientBuilder->build();
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

	$query_result_array= $_SESSION['query_result']; // fetch the results from the session variable
	$user_details=$_SESSION['User_details'];
	$journal_list=$user_details['_source']['journals']; // get list of selected journals
	$i =$_GET['i'];
	$j= $_GET['j'];
	function event_link_click($id,$journal) // function executed when a particular articles link is clicked
	{	
		

		$params['index']="rss_feed";
		$params['type']=$journal;
		$params['id'] = $id;
		$response = $GLOBALS['client']->get($params);
		$clicked_link = $response['_source']['id'];
		header('Location:'.$clicked_link); // takes to the url of article
	} 
	if (isset($_GET['id'])) // if any article is clicked
	{	

	   $click_id=$_GET['id'];
	   $l=$_GET['l'];
	   $aggr_results =$_SESSION['aggr_results'];
	   $click_journal=$aggr_results[$l]['journal'];
	   
	   event_link_click($click_id,$click_journal);


	}
	echo "<h1> Results </h1>";
	echo '<br>';
	$aggr_results=array();
	$l=0;
	foreach($journal_list as $journal) // print the titles of all the articles journal by journal
	{	
		$result=$query_result_array[$i][$j][$journal];
		$hits = sizeof($result);
		
		

		if($hits>0)
		    {
		    
		    	$k = 0;
		    	$search_result=array();
		    	$params['index']="rss_feed";
			    $params['type']=$journal;
			    foreach ($result as $x) 
			    {
			    	
			    	$params['id']= $x;
			    	$response = $client->get($params);
				    $link = $response['_source']['id'];
				    $impact_factor =$response['_source']['impact_factor'];
				    $search_result[$k]=array('link'=>$link,'impact_factor'=>$impact_factor,'title'=>$response['_source']['title'],'journal'=>$journal,'id'=>$x);
				    
				    $k++ ;
				}
				$aggr_results[$l]['results']=$search_result;
				$aggr_results[$l]['impact_factor']=$impact_factor;
				$aggr_results[$l]['journal']=$journal;
				$l=$l+1;
				
			}
	}
	
	usort($aggr_results, function($a, $b)
	{
		if($a['impact_factor'] >$b['impact_factor'])
		{
			return -1;
		}
		else if($a['impact_factor'] < $b['impact_factor']){
			return 1;
		}
	    return 0;
	});
	$_SESSION['aggr_results']=$aggr_results;
	for($l=0;$l<count($aggr_results);$l++)
	{	
		echo "<h2>".$aggr_results[$l]['journal']."</h2>";
		echo '<br>';

		for($k=0;$k<count($aggr_results[$l]['results']);$k++)
		{
			echo "<a href='material_table_query_res.php?id=".$aggr_results[$l]['results'][$k]['id']."&l=".$l."'>";
		   
		    echo $aggr_results[$l]['results'][$k]['title'];
		    echo '</a>';
		    echo '<br>';

		}
		
	}

?>
</center>
</body>

</html>