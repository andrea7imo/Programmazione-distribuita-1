<?php
    include 'funzioniPHP/common.php';
	session_start();
	logout();
	redirect("index.php");
?>