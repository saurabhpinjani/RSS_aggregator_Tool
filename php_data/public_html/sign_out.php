<?php
session_start();
header('Location:sign_in.php');
session_destroy();
?>