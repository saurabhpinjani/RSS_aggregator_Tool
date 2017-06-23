<!DOCTYPE html>
<html>
<head>
</head>
<body style="background-color: #E8F0F5"> 
<center>

<?php 
//allot the first journal which is not alloted from journals.txt
//if all are alloted, check for incomplete journals and then allo them
    session_start();
    require 'vendor/autoload.php';
    $clientBuilder = Elasticsearch\ClientBuilder::create();   // Instantiate a new ClientBuilder
    $clientBuilder->setHosts(['http://localhost:9200']);           // Set the hosts
    $client = $clientBuilder->build();          // Build the client object
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

    $journals = fileToDictionary("journals.txt");
    $params = array();
    $params = [
    'index' => 'classified_feed',
    'id' => -1
    ];
    // $allot_data = $client->get($params);
    // print_r($allot_data);

    foreach ($journals as $key => $journal) {
        $params['type']= strtolower($journal);
        $allot_data = $client->get($params);
        // print_r($allot_data);
        if($allot_data['_source']['alloted']=="no")
        {
            $allot_data['_source']['alloted']="yes";
            $params['body'] = $allot_data['_source'];
            $alloted_data = $client->index($params);
            break;
        }
    }
    $params = array();
    $params = [
    'index' => 'classified_feed',
    'type' => strtolower($journal)
    ];
    $params['size'] = 5000;
    $journal_data = $client->search($params);

    $_SESSION["journal_name"] =  strtolower($journal);
    $_SESSION["alloted_journal_data"] = $journal_data['hits']['hits'];
    header('Location:classified_feed_2.php');
?>
</form>
</center>
</body>
</html>