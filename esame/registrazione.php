<?php 
    include 'funzioniPHP/common.php';
    httpsRedirect();

    session_start();
    if(userLoggedIn()){
        /* se l'utente è loggato, viene ridiretto alla home */
		redirect("index.php");
	}
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <title>  
            Aircraft.co: Registrati
        </title> <!-- titolo nella scheda -->
        <meta charset="UTF-8"> <!-- codifica -->
        <link rel="stylesheet" href="css/home.css">
        <script src="js/registrazione.js">
        </script>
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
        <nav id="nav">  
            <!-- 
                -home
                -login
            -->
            <ul>
                <li>
                    <a class="active" href="index.php"><img id="user" src="img/home.png" class="icon" alt="">Home</a>
                </li>
                <li>
                    <a href="login.php"><img id="login" src="img/user.png" class="icon" alt="">Login</a>
                </li>
            </ul>   
        </nav>
        <!-- contenuto principale -->
        <section id="sec" class="section">
            <h2 style="text-align: center">Registrazione</h2>
            <form action="registrazionePost.php" method="post" onsubmit="return verificaP_E()">
                <?php
                    /* ricezione di messaggi di errore */
                    if (isset($_GET['msg']) && $_GET['msg'] != ''){
                        $msg = $_GET['msg'];
                        /* injection */
                        $msg = htmlentities($msg);
                        echo '<p id="errorMsg" class="riprova">',$msg,'</p>';
                    }
                ?>
                <label>
                    <!-- sezione email -->
                    Email:<br><br>
                    <input type="email" name="email" size="20"  oninput="verificaEmail()" placeholder="Inserisci l'email" class="inputTextPass" required>
                    <br><b id="errorMsgEmail" class="riprova"></b></label><br>
                
                <label>
                    <!-- sezione password -->
                    Password:<br><br>
                    <input type="password" name="password" oninput="verificaPassword()" placeholder="inserisci una password" class="inputTextPass" required>
                    <br>inserire almeno un carattere minuscolo e almeno un carattere minusco o almeno un numero
                    <br><b id="errorMsgPassword" class="riprova"></b><br></label>
                
                <!-- pulsante per il submit -->
                <input type="submit" id="invio" class="registerbtn" >
            </form>
        </section>
        <script>
            /* gestione dei cookie: nel caso in cui sono disbilitati */
            var ris = checkCookie();
            if (!ris){
                document.getElementById("msgCookie").innerHTML = "Attiva i cookie per utilizzare il sito!";
                /* diabilitazione delle visualizzazione del contentuto della pagina */
                document.getElementById("nav").style.display = "none";
                document.getElementById("sec").style.display = "none";
            }
        </script> 
    </body>
</html>