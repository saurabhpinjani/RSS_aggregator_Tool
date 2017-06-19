<?php
     session_start();
?>

<!DOCTYPE html>
<html>
<head>
</head>
<body style="background-color: #E8F0F5"> 
<center>

<form action="form.php" method="post">
<?php 
    $box_names = array('0' => "Magnetic" ,
                       '1' => "2D" ,
                       '2' => "Solar" ,
                       '3' => "Computational" ,
                       '4' => "Electron Transport" ,
                       '5' => "Others" );
    session_start();
    require 'vendor/autoload.php';
    $clientBuilder = Elasticsearch\ClientBuilder::create();   // Instantiate a new ClientBuilder
    $clientBuilder->setHosts(['http://localhost:9200']);           // Set the hosts
    $client = $clientBuilder->build();          // Build the client object
    $params = array();      
    $params['index'] = "arxiv_feed";
    $params['type'] = "info";
    $params['id'] = 0;
    $res = $client->get($params);

    // print_r($res);    
    $params = array();      
    $params['index'] = "arxiv_feed";
    $params['type'] = "feed";
    $params['id'] = $res['_source']['marked_articles']+1;
    $choice_old = $client->get($params);

    if ($_POST['submit_name']=="NEXT") 
    {
        echo "<h3> PLEASE DO NOT RELOAD </h3>";
        echo "<h3> ONLY USE THE NEXT BUTTON </h3>";
        //storing the choices
        $params = array();
        $params['index'] = "arxiv_feed";
        $params['type'] = "feed";
        $params['id'] = $res['_source']['marked_articles']+1;
        $params['body'] = $choice_old['source'];
        $params['body']['category_choice']['category'] = $_POST['category'];
        $result_choice = $client->index($params);

        //updating the marked count
        $params = array();      
        $params['index'] = "arxiv_feed";
        $params['type'] = "info";
        $params['id'] = 0;
        $params['body'] = array('count' => $res['_source']['count'],
                                'marked_articles' => $res['_source']['marked_articles']+1);
        $result = $client->index($params);
    }

    $params = array();      
    $params['index'] = "arxiv_feed";
    $params['type'] = "info";
    $params['id'] = 0;
    $res = $client->get($params);
    $info = $res;
    // print_r($res);

    $params = array();      
    $params['index'] = "arxiv_feed";
    $params['type'] = "feed";
    $params['id'] = $res['_source']['marked_articles']+1;
    $res = $client->get($params);
    
    echo "<h2>ID is:</h2>";
    echo $res['_id'];
    echo '<br>';

    // print_r($res);
    echo "<h2>Title is:</h2>";
    echo $res['_source']['title'];

    echo "<h2>Summary is:</h2>";
    echo $res['_source']['summary'];

    foreach ($box_names as $key => $title) 
    {
        echo "<input type=".'"radio"'." name=".'"category"'." value=".$key.">".$title."<br>";
    }
    echo '<br>';
    echo "<h3> DO NOT FORGET TO SELECT </h3>";
    echo '<br>';
    echo "<input type=".'"submit"'." name=".'"submit_name"'." value=".'"NEXT"'." style=".'"height:40px; width:60px; font-size: 60%"'." >";

    $params = array();      
    $params['index'] = "arxiv_feed";
    $params['type'] = "feed";
    $params['size'] = 2000;
    $res = $client->search($params);

    echo '<br>';

    foreach ($res['hits']['hits'] as $key => $hit) {
        echo $hit['_id'];
        echo " => ";
        foreach ($box_names as $key => $value) {
            if(($hit['_source']['category_choice']['category']==$key) && sizeof($hit['_source']['category_choice'])>0)
            {
                echo " $ ";
            }
            else
            {
                echo " * ";
            }
        }
        // print_r($hit['_source']['category_choice']);
        echo '<br>';
    }


?>
</form>
</center>
</body>
</html>
