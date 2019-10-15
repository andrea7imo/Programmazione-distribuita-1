<?php
    include 'funzioniPHP/common.php';
    include 'funzioniPHP/gestioneDB.php';
    riautenticazione();
    
    if (userLoggedIn()){
        /* se l'utente è loggato, viene ridiretto su https */
        httpsRedirect();
    }else{
        redirect('login.php','Effettua il login');
    }

    $returnValue = acquistaPosti();

    redirect('index.php',$returnValue);
?>