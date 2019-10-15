<?php
    include 'funzioniPHP/gestioneDB.php';
    include 'funzioniPHP/common.php';
    httpsRedirect();

    session_start();

    if(userLoggedIn()){
        /* se l'utente è loggato, viene ridiretto alla home  */
		redirect("index.php");
    }
    
    if (!isset($_POST['email']) || !isset($_POST['password']) || $_POST['email'] == '' || $_POST['password'] == ''){
        /* controllo introduzione effettiva della email e password */
        redirect("login.php", "Introdurre email e password.");
    }

    /* injection */   
   

    if (!verificaEmail($_POST['email'])){
        redirect("login.php", "Email non valida. Riprova.");
    }

    $username = sanitizeString($_POST['email']);

    if (loginUtente($username, $_POST['password'])){
        /* settaggio dell'email e del tempo nella sessione */
        $_SESSION[$matricola.'utente'] = $username;	
        $_SESSION[$matricola.'time'] = time();
        redirect("index.php"); 
    }else{
        redirect("login.php", "Login non riuscito. Riprova.");
    }
?>