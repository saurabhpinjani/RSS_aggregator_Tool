<?php
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
	require 'vendor/autoload.php';
	$search_result= array();
	$clientBuilder = Elasticsearch\ClientBuilder::create();   // Instantiate a new ClientBuilder
	$clientBuilder->setHosts(['http://localhost:9200']);           // Set the hosts
	$client = $clientBuilder->build();
	$query_result_array= $_SESSION['query_result'];
	$journal_list= $_SESSION['journal_list'];

	$i =$_GET['i'];
	$j= $_GET['j'];
	function event_link_click($id,$journal)
	{	
		$result = $GLOBALS['query_result_array'];

		$params['index']="rss_feed";
	    $params['type']=$journal;
		$params['id'] = $id;
		$response = $GLOBALS['client']->get($params);

		
		$params['body'] = $response['_source'];
		$params['body']['read_yet'] = "vishnushankar";  
		$res = $GLOBALS['client']->index($params);
		// print_r($res);
		$clicked_link = $response['_source']['id'];
		header('Location:'.$clicked_link);
	}
	if (isset($_GET['id']))
	{
	   $click_id=$_GET['id'];
	   $click_journal=$_GET['journal'];
	   event_link_click($click_id,$click_journal);


	}
	echo "<h1> Results </h1>";
	echo '<br>';
	$aggr_results=array();
	$l=0;
	foreach($journal_list as $journal)
	{
		$result=$query_result_array[$i][$j][$journal];
		$hits = sizeof($result);
		echo $hits;
		

		if($hits>0)
		    {
		    
		    	$k = 0;
		    	$params['index']="rss_feed";
			    $params['type']=$journal;
			    foreach ($result as $x) 
			    {
			    	
			    	$params['id']= $x;
			    	$response = $client->get($params);
				    $link = $response['_source']['id'];
				    $impact_factor =$response['_source']['impact_factor'];
				    $read_yet=$response['_source']['read_yet'];
				    $search_result[$k]=array('link'=>$link,'impact_factor'=>$impact_factor,'read_yet'=>$read_yet,'title'=>$response['_source']['title'],'journal'=>$journal,'id'=>$x);
				    
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
	for($l=0;$l<count($aggr_results);$l++)
	{	
		echo "<h2>".$$aggr_results[$l]['journal']."</h2>";
		echo '<br>';
		for($k=0;$k<count($aggr_results[$l]['results']);$k++)
		{
			

			if($aggr_results[$l]['results'][$k]['read_yet']=='brahmavishnu') //brahmavishnu means false
		    {
		    	
		    	echo "<a href='material_table_query_res.php?id=".$aggr_results[$l]['results'][$k]['id']."&journal=".$aggr_results[$l]['results'][$k]['journal']."'>";
		    }
		    else {
		    		$href = $search_result[$k]['link'];
		    		echo "<a href= '$href' style='color: rgb(255,0,0)'>";#######
		    	}	
		    echo $search_result[$k]['title'];
		    echo '</a>';
		    echo '<br>';

		}
		
	}

?>
</center>
</body>

</html>