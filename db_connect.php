<?php
//conect to the db
	$connect = mysqli_connect('localhost','root', "", 'checkly');
	if(!$connect){
		die(mysqli_connect_error($connect));
	}
?>