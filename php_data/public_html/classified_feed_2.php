<!DOCTYPE html>
<html>
<head>
</head>
<body style="background-color: #E8F0F5"> 
<center>

<?php 
//allot the first journal which is not alloted from journals.txt
//if all are alloted, check for incomplete journals and then allo them
    function PrintEverythinInADictionary($x)
    {
        foreach ($x as $key => $value) {
            if(sizeof($value)>1)
            {
                PrintEverythinInADictionary($value);
            }
            else
            {
                echo '<tr>';
                echo '<td>'.'<b>'.$key.'</b>'.'</td>'.'<td>'.$value.'</td>';
                echo '</tr>';
            }
        }
    }
    function fileToDictionary($name)
    {
        $myfile = fopen($name, "r") or die("Unable to open file: ".$name);
        $file_data = array();
        $x = 0;
        while(!feof($myfile)) 
        {     
            $data = (string)fgets($myfile); 
            $data = str_replace("\n", "", $data);
            $data = strtolower($data);
            $file_data[(string)$x] = $data;
            $x = $x + 1;
        }
        return $file_data;
    }

    session_start();
    require 'vendor/autoload.php';
    $clientBuilder = Elasticsearch\ClientBuilder::create();   // Instantiate a new ClientBuilder
    $clientBuilder->setHosts(['http://localhost:9200']);           // Set the hosts
    $client = $clientBuilder->build();          // Build the client object
    $properties = fileToDictionary("property_options.txt");
    $journal_name = $_SESSION["journal_name"];
    $journal_data = $_SESSION['alloted_journal_data'];
    if($_POST["submit_name"]=="NEXT")
    {
        $current_article = $_SESSION["current_article"];
        $compounds_list = $_SESSION["compounds_list"];
        
        $params = array();
        $params = 
        [
            'index' => $current_article['_index'],
            'type' => $current_article['_type'],
            'id' => $current_article['_id'],
            'body' => $current_article['_source']
        ];
        
        foreach ($properties as $key => $value) 
        {
            $params['body']['user_choice']['property_choice'][$value] = $_POST[$key];
        }

        foreach ($compounds_list as $key => $compound) 
        {
            $params['body']['user_choice']['compound_choice'][$compound] = $_POST[(string)($key + sizeof($properties))];
        }
        
        $params['body']['user_choice']['compound_choice']['none_of_these'] = $_POST[(string)($key + sizeof($properties) +1)];    
        $res = $client->index($params);

        $params = array();
        $params = 
        [
            'index' => 'classified_feed',
            'type' => $journal_data[0]['_type'],
            'id' => 0
        ];
        $hash_data = $client->get($params);

        $i = sizeof($hash_data['_source']['classified_titles'])+1;
        $hash_data['_source']['classified_titles'][$i] = $current_article['_source']['title'];
        
        $params = 
        [
            'index' => $hash_data['_index'],
            'type' => $hash_data['_type'],
            'id' => $hash_data['_id'],
            'body' => $hash_data['_source']
        ];
        $res = $client->index($params);

        $params = 
        [
            'index' => $hash_data['_index'],
            'type' => $hash_data['_type']
        ];
        $params['size'] = 5000;
        $journal_search = $client->search($params); 
        $_SESSION["journal_data"] = $journal_search['hits']['hits'];
        $journal_data = $_SESSION['journal_data'];

    }
    // print_r($journal_data);
    echo "<h1>".$journal_data[0]['_type']."</h1>";
    echo '<br>';
    $params = array();
    $params = [
    'index' => 'classified_feed',
    'type' => $journal_data[0]['_type'],
    'id' => 0
    ];
    $hash_data = $client->get($params);
    $hash_data_1 = $hash_data;
    $x = array_values($hash_data_1['_source']['classified_titles']);
    $y = array_values($hash_data_1['_source']['all_titles']);
    sort($x);
    sort($y);
    // print_r($x);
    // echo '<br>';
    // print_r($y);
    if($x != $y)
    {   
        $count_unclassified = (string) (sizeof($y)-sizeof($x));
        echo $count_unclassified." article(s) left to be classified in this journal.<br>";
        echo "sit back for 5 min and finish this in one go..<br>";
        // echo "<h3>List of articles filled by you</h3>";
        // print_r($hash_data['_source']['classified_titles']);
        // echo '<br>';
        // print_r($x);
        // echo '<br>';
        // echo '<br>';
        // echo '<br>';
        // print_r($y);

        foreach ($journal_data as $key => $article) 
        {
            if( (!in_array($article['_source']['title'], $hash_data['_source']['classified_titles'])) && ($article['_id']>0) )
            {
                // echo $article['_id']." not in hash <br>";
                // print_r($hash_data['_source']['classified_titles']);
                echo "New article loaded";
                echo "<br> <br>";
                break;
            }
        }
        // var_dump($article['_source']);
        // echo '<pre>';
        // print_r($article['_source']);
        // echo '</pre>';
        // echo "======================================================";
        $_SESSION["current_article"] = $article;
        echo '<br>';
        echo '<table border="7" cellpadding="10">';
        echo '<tr>';
        echo "<th colspan=".'2>'.$article['_source']['title']."</th>";
        echo "</tr>";
        PrintEverythinInADictionary($article['_source']);
        echo '</table>';

        
        // 
        echo '<form action='.'"classified_feed_2.php"'.'method='.'"post"'.'>';

        echo '<h2>'."Choose the right property/properties this article belongs to".'</h2>';
        foreach ($properties as $key => $property) {
            echo "<input type=checkbox name=".$key." value=".'"on"'. " >".$property."<br>";
        }

        $compounds_list = explode("  ",$article['_source']['compounds_list']);
        $compounds_list_1 = $compounds_list;
        $compounds_list = array();
        foreach ($compounds_list_1 as $key => $compound) {
            if(($compound!='')&&($compound!='\n')&&($compound!=' '))
            {
                array_push($compounds_list, $compound);
            }
        }
        $_SESSION["compounds_list"]=$compounds_list;

        echo '<h2>'."Choose the right element(s)/compound(s) this article belongs to".'</h2>';
        foreach ($compounds_list as $compound_key => $compound) {
            $compound_key = (string)($compound_key + sizeof($properties));
            echo "<input type=checkbox name=".'"'.$compound_key.'"'." value=".'"on"'. " >".$compound."<br>";

        }
        $compound_key = (string)($compound_key + sizeof($properties) + 1 );
        $compound = "none_of_these";
        echo "<input type=checkbox name=".'"'.$compound_key.'"'." value=".'"on"'. " >".$compound."<br>";
        

        echo "<input type=".'"submit"'." name=".'"submit_name"'." value=".'"NEXT"'." style=".'"height:80px; width:160px; font-size: 200%"'." >";
        echo "</form>";
    }
    else
    {
        $params = array();
        $params = [
        'index' => 'classified_feed',
        'type' => $journal_name,
        'id' => -1
        ];
        $allot_data = $client->get($params);
        $allot_data['_source']['completed']="yes";
        $params['body'] = $allot_data['_source'];
        $alloted_data = $client->index($params);

        echo "You have filled all the articles in this journal.<br>";
        echo "If your journal was too small or if you want to help us more, click below to fill one more journal";
        echo '<a href='.'"classified_feed_1.php"'. '> <h2> NEXT AVAILABLE JOURNAL </h2> </a>';
    }
?>  

</center>
</body>
</html>