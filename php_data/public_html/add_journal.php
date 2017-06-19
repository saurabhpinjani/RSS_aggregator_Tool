<?php
session_start();
?>
!DOCTYPE html>
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
    background-color: #DFEAF1;
}

li a.active {
    color: white;
    background-color: #8796CD;
}
</style>
</head>
<body style="background-color: #E8F0F5"> 
<center>
<html>
<body style="background-color: #E8F0F5" > 

<center>
<h2>Add new journal</h2>



<form action="add_journal.php" method="post">
Journal Name <input type="text" name="journal_name"><br>
RSS Feed Link: <input type ="text" name="rss_feed_link"><br>
Impact Factor: <input type ="text" name="impact_factor"><br>
<input type="submit" value="Log in" name="sign_in_click">
</form>

<?php

	if(isset($_POST['action']))
	{
		 $journal_name = $_POST['journal_name'];
		 $rss_feed_link= $_POST['rss_feed_link'];
		 $impact_factor =$_POST['impact_factor'];
	
		 if($impact_factor="")
		 {
		 	$impact_factor=0;
		 }
		 $filename = "/../../RSS_urls/journals.txt";
         $file = fopen( $filename, "a" );
         
         if( $file == false ) {
            echo ( "Cannot save this to database" );
            exit();
         }
         
      
         fwrite( $file,$journal_name."\n" );
         fclose( $file);
         
      

		 $filename = "/../../RSS_urls/rss_feeds.txt";
         $file = fopen( $filename, "a" );
         
         if( $file == false ) {
            echo ( "Cannot save this to database" );
            exit();
         }
         
      
         fwrite( $file,$rss_feed_link."|".$impact_factor."|".$journal_name."\n" );
         fclose( $file);




	}

?>