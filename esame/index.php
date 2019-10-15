<?php
    include 'funzioniPHP/common.php';
    include 'funzioniPHP/gestioneDB.php';
    include 'funzioniPHP/homeFun.php';
    /* riautenticazione se solo se è scaduto il timeout */
    riautenticazione();
    $email = userLoggedIn();
    
    if ($email){
        /* se l'utente è loggato, viene ridiretto su https */
        httpsRedirect();
    }
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <title>  
            Aircraft.co
        </title> <!-- titolo nella scheda -->
        <meta charset="UTF-8"> <!-- codifica -->
        <link rel="stylesheet" href="css/home.css">
        <script src="js/home.js">
        </script>
    </head>
    <body>
        <!-- intestazione -->
        <header class="header">
            <!-- logo -->
            <img id="logo" src="img/logo1.png" alt="">
            <hgroup> 
                <!-- titolo -->
                <h1>Aircraft.co</h1> 
            </hgroup>

            <!-- messaggio di avvertenza quando i cookie non sono attivi -->
            <p id="msgCookie" style="color:white;float:right;padding-top:30px; padding-right:15px;"></p>

            <noscript>
                <!-- messaggio quando js non è in funzione -->
                <p style="color:white;float:right;padding-top:30px; padding-right:15px;">JavaScript non è abilitato, il sito potrebbe non funzionare correttamente!</p>
            </noscript> 
        </header>
        <!-- barra di navigazione -->
        <form id="acquistaForm" action="acquista.php" method="post">
        <nav id="nav">
            <?php
                /* controllo se l'utente loggato per determinare quale menu laterale può vedere */
                if (!$email){
                    /* 
                        -loggin
                        -registrazione
                     */
            ?>
                <ul>
                    <li>
                        <a class="active" href="login.php"><img id="user" src="img/user.png" class="icon" alt="">Login</a>
                    </li>
                    <li>
                        <a href="registrazione.php"><img id="reg" src="img/registration.png" class="icon" alt="">Registrati</a>
                    </li>
                </ul>
            <?php
                }else{
                    /* 
                        -aggiorna
                        -loggout
                        -acquista
                     */
            ?>      
                <ul>
                    <li>
                        <a class="active" href="index.php"><img id="Aggiorna" src="img/update.png" class="icon" alt="">Aggiorna</a>
                    </li>
                    <li>
                        <a href="logout.php"><img id="logout" src="img/logout.png" class="icon" alt="">Logout</a>
                    </li>
                    <li>
                        <!-- tasto acquista modelizzato come un link -->
                        <a href="#" onclick="document.getElementById('acquistaForm').submit()"><img id="Acquista" src="img/acquista.png" class="icon" alt="">Acquista</a>
                    </li>
                </ul>
            <?php
                }
            ?>
        </nav>
        <!-- contenuto principale -->
        <section id="sec" class="section">
            <?php
                if (userLoggedin()){
                    /* Descrizione di come effettuare la prenotazione/liberazione/acquisto del posto */
                    echo '<h2>Prenota i tuoi posti!</h2>';
                    echo '<p>Premi su un posto verde per prenotarlo.</p>';
                    echo '<p>Premi su un posto giallo per liberarlo.</p>';
                    echo '<p>Una volta che i posti sono prenotati puoi acquistarli con il tasto acquista sulla sinistra!';
                }else{
                    /* messaggio principale quando non si è loggati */
                    echo '<h2>Effettua il login o registrati per prenotare i posti!</h2>';
                }

                /* ricezione di messaggi di errore */
                if (isset($_GET['msg']) && $_GET['msg'] != ""){
                    $msg = $_GET['msg'];
                    /* injection */
                    $msg = htmlentities($msg);
                    echo '<p id="errorMsgPassword" class="riprova" style="text-align:left">',$msg,'</p>';
                }

                /* preparazione tabella di bottoni e preparazione delle statistiche */
                $conn = connessioneDB();
                $query = "SELECT posto, email, stato FROM PostiAcquistati_Prenotati";
                $numTotA = 0;
                $numTotP = 0;
                $postiPrenotato = array();
                $postiAcquistati = array();

                $ris = mysqli_query($conn, $query);
                if ($ris){
                    for ($i = 0; $i < mysqli_num_rows($ris); $i++){
                        $riga = mysqli_fetch_array($ris);
                        if ($riga['stato'] == 'acquistato'){
                            $postiAcquistati[$riga['posto']] = $riga['posto'];
                            $numTotA++;
                        }else if ($riga['stato'] == 'prenotato'){
                            $postiPrenotato[$riga['posto']] = $riga['email'];
                            $numTotP++;
                        }                        
                    }
                }

                /* chiusura connessione db */
                mysqli_close($conn);

                /* statistiche */
                $numTot = $numRighe*$numColonne;
                $numTotL = $numTot - $numTotA -$numTotP;                
                
                /* creazione tabella */
                creazioneTable($postiPrenotato,$postiAcquistati);        
            ?> 
        </section>
        <?php 
            /* stampa delle statistiche */
            echo '<label id="stat" class="stat">';
            if ($email){
                /* split dell'email per ottentere solo la prima parte dell'email */
                $username = explode("@", $email)[0];
                echo '<b>Ciao ', $username, ", ecco a te le statistiche:</b><br><br>";
            }else{
                echo '<b>Statistiche:</b><br><br>';
            }
            echo "Posti totali: <b>", $numTot, "</b><br>";
            echo 'Posti Acquistati: <b id="numTotA">', $numTotA, "</b><br>";
            echo 'Posti Prenotati: <b id="numTotP">', $numTotP, "</b><br>";
            echo 'Posti Liberi: <b id="numTotL">', $numTotL, "</b></label>"; 
        ?>
    </form>
    <script>
            /* gestione dei cookie: nel caso in cui sono disbilitati */
            var ris = checkCookie();
            if (!ris){
                document.getElementById("msgCookie").innerHTML = "Attiva i cookie per utilizzare il sito!";
                /* diabilitazione delle visualizzazione del contentuto della pagina */
                document.getElementById("nav").style.display = "none";
                document.getElementById("sec").style.display = "none";
                document.getElementById("stat").style.display = "none";
            }
    </script> 
    </body>
</html>