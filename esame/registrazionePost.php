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
        redirect("registrazione.php", "Inserire email e password.");
    }

    /* check email e check password*/
    /* NOTA: verifica injection (tag html) fatto nella funzione verifcaEmail */
    if (verificaEmail($_POST['email']) && verificaPassword($_POST['password'])){
        /* inserimento dell'utente */
        $username = sanitizeString($_POST['email']);
        if (!insertUtente($username,$_POST['password'])){
            redirect("registrazione.php", "Qualcosa è andato storto riprova!");
        }
        $_SESSION[$matricola.'utente'] = $username; 
        $_SESSION[$matricola.'time'] = time();
        redirect("index.php"); 
    }else{
        redirect("registrazione.php", "Email o Password non valide!");
    }
?>