<html>
<body>

<center>
<h1>Cerebro</h1>
<a href="index.php"><img src="cerebro.jpg" style="width:80%; height:80%;"></a> 
<h2>Sign in</h2>


<form action="sign_in.php" method="post">
Username: <input type="text" name="sign_in_username"><br>
Password: <input type ="password" name="sign_in_password"><br>
<input type ="hidden" name= "action" value="sign_in"><br>
<input type="submit" value="Log in" name="sign_in_click">
</form>

<h2> Sign Up</h2>

<form action="sign_in.php" method="post">
Select Username: <input type="text" name="sign_up_username"><br>
Password: <input type ="password" name="sign_up_password"><br>
Confirm Password: <input type="password" name ="sign_up_password2"><br>
<input type ="hidden" name= "action" value="sign_up"><br>
<input type="submit" value="Sign up" name="sign_up_click">
</form>

<?php
    
    
   session_start();

    require 'vendor/autoload.php';
    $clientBuilder = Elasticsearch\ClientBuilder::create();   // Instantiate a new ClientBuilder
    $clientBuilder->setHosts(['http://localhost:9200']);           // Set the hosts
    $client = $clientBuilder->build();          // Build the client object
    // $search_text = $_POST['search_text_1'];
    // $params['size'] = 1000;
    // $params['body']['query']['match']['_all']['query'] = "the";
    // $params['body']['query']['match']['_all']['operator'] = "and";
    // $result = $client->search($params);
    
    // echo "searched outside";
    // echo '<br>';
    if(isset($_POST['action']))
    {
        if($_POST['action']=="sign_in")
        {
            $search_username = $_POST['sign_in_username'];
            $params['size'] = 1;
            $params['index']="users";
            $params['type']="user_data";
            $params['body']['query']['match']['username']['query'] = $search_username;
            $params['body']['query']['match']['username']['operator'] = "and";
            $result = $client->search($params);
            // echo "searched outside";
            // echo '<br>';
            $hits = $result['hits']['total'];
            $data = $result['hits']['hits'];
            if($hits==0){
                echo "<script type='text/javascript'>alert('Incorrect Username')</script>";
            }
            else
            {
                if($data[0]['_source']['password']==$_POST['sign_in_password'])
                {
                    $_SESSION['User_details']=$data[0];
                    echo "<script type='text/javascript'>alert('Successful Login')</script>";
                    header('Location:search_server.php');

                }
                else
                {
                    echo "<script type='text/javascript'>alert('Incorrect Password')</script>";
                }
            }
        }
        elseif($_POST['action']=="sign_up")
        {
            $search_username = $_POST['sign_up_username'];
            $params = array();
            $params['size'] = 10;
            // $params['index']='users';
            // $params['type']='user_data';
            $params['body']['query']['match']['username'] = $search_username;
            $params['body']['query']['match']['username']['operator'] = "and";
            if($_POST['sign_up_password']==$_POST['sign_up_password2'])
            {
                $result = $client->search($params);
                $hits = $result['hits']['total'];
                $data = $result['hits']['hits'];
                if($hits==0)
                {
                    $params = array();
                    $params['index']="users";
                    $params['type']="metadata";
                    $params['id'] = 0;
                    $response = $client->get($params);
                    $user_count=$response['_source']['count'];
                    
                    $params1['index']="users";
                    $params1['type']="user_data";
                    $params1['id'] = $user_count;
                    $params1['body']['username']=$_POST['sign_up_username'];
                    $params1['body']['password']=$_POST['sign_up_password'];
                    $res = $client->index($params1);

                    $params['index']="users";
                    $params['type']="metadata";
                    $params['id'] = 0;
                    $params['body']['count'] =$user_count+1;
                    $res = $client->index($params);

                    $params2['index']="users";
                    $params2['type']="user_data";
                    $params2['id'] = $user_count;
                    $response = $client->get($params2);
                    $_SESSION['User_details']=$response;

                    echo "<script type='text/javascript'>alert('Successful Sign Up')</script>";
                    header('Location:userdata_journals.php');
                      
                }
                else{
                    echo "<script type='text/javascript'>alert('Username already being used! Try other username')</script>";
                }
            }
            else
            {
                echo "<script type='text/javascript'>alert('Passwords do not match')</script>";
            }
            
        }
    
    
    }
  

   
?>

</center>


</body>
</html>