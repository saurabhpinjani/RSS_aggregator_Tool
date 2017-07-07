<!DOCTYPE html>
<html>
<head>
</head>
<body style="background-color: #E8F0F5"> 
<center>
<form action="classified_feed_status.php" method="post">
<?php 
//allot the first journal which is not alloted from journals.txt
//if all are alloted, check for incomplete journals and then allo them
    session_start();
    require 'vendor/autoload.php';
    $clientBuilder = Elasticsearch\ClientBuilder::create();   // Instantiate a new ClientBuilder
    $clientBuilder->setHosts(['http://localhost:9200']);           // Set the hosts
    $client = $clientBuilder->build();          // Build the client object

    if(isset($_POST['reset_name']))
    {

        $journal = $_POST['reset_name'];
        // echo $journal;
        $params = array();
        $params = [
                    'index' => 'classified_feed',
                    'type'  => $journal,
                    'id'    => -1
                  ];
        $allotData = $client->get($params);
        $params['body'] = $allotData['_source'];
        $params['body']['alloted'] = "no";
        $params['body']['completed'] = "no";
        $allotPush = $client->index($params);

        $params = array();
        $params = [
                    'index' => 'classified_feed',
                    'type'  => $journal,
                    'id'    => 0
                  ];
        $titleData = $client->get($params);
        $params['body'] = $titleData['_source'];
        $params['body']['classified_titles'] = array();
        $titlePush = $client->index($params);
    }

    if(isset($_POST['submit_name']))
    {
        $journal = $_POST['submit_name'];
        $params = array();
        $params = [
                    'index' => 'classified_feed',
                    'type'  => $journal,
                    'id'    => -1
                  ];
        $allotData = $client->get($params);
        $params['body'] = $allotData['_source'];
        $params['body']['alloted'] = "yes";
        $allotPush = $client->index($params);

        $params = array();
        $params = [
                    'index' => 'classified_feed',
                    'type'  => $journal,
                    'size'  => 10000
                  ];

        $journalData = $client->search($params);
        $_SESSION['journal_name'] = $journal;
        $_SESSION['alloted_journal_data'] = $journalData['hits']['hits'];
        header('Location:classified_feed_2.php');
        // print_r($journalData);
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

    $journals = fileToDictionary("journals.txt");
    $params = array();
    $params = [
    'index' => 'classified_feed',
    ];

    $total = 0;
    echo '<table border="7" cellpadding="10">' ;
    echo '<tr>';
    echo "<td> Journal name </td> <td> Alloted </td> <td> Completed </td> <td> Total articles </td> <td> Classified articles </td> <td> RESET ALL CLASSIFICATIONS </td>";
    echo '</tr>';
    foreach ($journals as $key => $journal) {
        # code...    
        echo '<tr>' ;
        echo '<td>';
        echo "<input type=".'"submit"'." name=".'"submit_name"'." value=".'"';
        echo $journal;
        echo '" >';
        echo '</td>';
        // echo '<td>'.$journal.'</td>';

        $params['type'] = $journal;

        $params['id'] = -1;
        $allotData = $client->get($params);
        
        echo '<td>';

        echo $allotData['_source']['alloted'];
        echo '</td>';

        echo '<td>';
        echo $allotData['_source']['completed'];
        echo '</td>';


        $params['id'] = 0;
        $titleData = $client->get($params);

        echo '<td>';
        echo sizeof($titleData['_source']['all_titles']);
        echo '</td>';

        echo '<td>';
        echo sizeof($titleData['_source']['classified_titles']);
        echo '</td>';

        echo '<td>';
        echo "<input type=".'"submit"'." name=".'"reset_name"'." value=".'"';
        echo $journal;
        echo '" >';
        echo '</td>';

        echo '</tr>';
        $total = $total + sizeof($titleData['_source']['all_titles']);
    }
    echo "<th colspan=6> In total ". $total ." articles are in the database</th>";
    echo '</table>';
?>
</form>
</center>
</body>
</html>