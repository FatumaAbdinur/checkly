<?php
//connect to groupbmar database
$server = "localhost";
$serveraccount = "root";
$serverpassword = "";
$db = 'checkly';

$connect = mysqli_connect($server, $serveraccount, $serverpassword, $db);

//check if not connected
if(!$connect) {
    die(mysqli_connect_error($connect));
}
?>