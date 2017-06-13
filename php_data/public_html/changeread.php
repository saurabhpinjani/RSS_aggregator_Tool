<?php 
	session_start();
	function ChangeReadYet($x)
    {
        $params['index'] = $x['_index'];
        $params['type'] = $x['_type'];
        $params['id'] = $x['_id'];
        $params['body'] = $x['_source'];
        $params['body']['read_yet'] = "vishnushankar";  
        $response = $GLOBALS['client']->index($params);
        print_r($response);
        $clicked_link = $params['body']['id'];
		header('Location:'.$clicked_link);
    }

	require 'vendor/autoload.php';
	$clientBuilder = Elasticsearch\ClientBuilder::create();   // Instantiate a new ClientBuilder
	$clientBuilder->setHosts(['http://localhost:9200']);           // Set the hosts
	$client = $clientBuilder->build();          // Build the client object
	$i = $_GET['i'];
	$data = $_SESSION['search_result'];
	ChangeReadYet($data[$i]);

?>