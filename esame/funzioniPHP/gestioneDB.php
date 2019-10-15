<?php
$dbhost  = 'localhost';    
$dbname  = 's262710'; 
$dbuser  = 's262710';     
$dbpass  = 'empallak';     

    function connessioneDB(){
        global $dbhost, $dbname, $dbuser, $dbpass;	
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass,$dbname);
        if(mysqli_connect_error()){ 
            /* redirect alla home con messaggio di errore */
            redirect("index.php", "Errore di collegamento al DB"); 
        }
        return $conn;
    }

    function insertUtente($username, $password){
        $conn = connessioneDB();
        $sale = md5(uniqid(rand(),true));       /* creazione stringa randomica */
        $sale = substr($sale, 0, 10);
        $hash = md5($password. $sale);          /* creazione password + sale */

        /* injection username */
        $username = mysqli_real_escape_string($conn, $username);
        $query = "INSERT INTO utenti VALUES('". $username."','". $hash."','". $sale ."')";
        $err = 0;

        if (!mysqli_query($conn, $query)){
            $err = 1;
        }

        mysqli_close($conn);
        
        if ($err == 1){
            return false;
        }else{
            return true;
        }
    }

    function loginUtente($username, $password){
        $conn = connessioneDB();

        /* injection username */
        $username = mysqli_real_escape_string($conn, $username);
        $query = "SELECT email, password, sale FROM utenti WHERE email='". $username."'";

        $err = 0;
        $ris = mysqli_query($conn, $query);
        if (!$ris){
            $err = 1;
        }else{
            if (mysqli_num_rows($ris) == 1){
                $riga = mysqli_fetch_array($ris);
                /* controllo sulla password */
                $hash = md5($password. $riga['sale']);          /* creazione password + sale */
                if ($riga['password'] != $hash){
                    /* le password non sono uguali */
                    $err = 1;
                }
            }else{
                /* utente non esiste! */
                $err = 1;
            }
        }
        mysqli_close($conn);

        if ($err == 1){
            /* ritorna false -> c'è stato un errore */
            return false;
        }else{
            return true;
        }
    }

    function acquistaPosti(){
        $email = userLoggedIn();
        $query1 = "SELECT posto, email, stato 
                FROM PostiAcquistati_Prenotati
                WHERE email='". $email . "'and stato='prenotato' FOR UPDATE";
    
        $query2Update = "UPDATE PostiAcquistati_Prenotati 
                            SET stato='acquistato'
                            WHERE email='". $email ."' and stato='prenotato'";
        
        $conn = connessioneDB();

        mysqli_autocommit($conn, false);

        try{
            $ris = mysqli_query($conn,$query1);             /* ottengo stato effettivo del db per l'utente corrente */
            if(!$ris){
                throw new Exception("Acquisto fallito");
            }
            
            $count = 0;
            /* conteggio dei posti prenotati */
            foreach ($_POST as $chiave=>$valore){           /* scansione dei posti ottentuti dagli input hidden */
                if ($valore == 'p'){                        /* controllo che siano nello stato prenotato  */          
                    $count++;
                }
            }

            /* verifica del numero di posti prenotati è uguale a quella sul DB */
            if ($count == mysqli_num_rows( $ris)){          /* verifica se il numero di posti prenotati visibili dall'utente siano effttivamente prenotati */
                /* verifica della concidenza dei posti sul DB */
                for ($i = 0; $i < mysqli_num_rows( $ris); $i++){
                    $riga = mysqli_fetch_array($ris);
                    if(!isset($_POST[$riga['posto']]) || $_POST[$riga['posto']] != "p"){        /* verifica effettiva che siano gli stessi posti */
                        throw new Exception("Stato non valido");
                    }
                }
            }else{
                throw new Exception("Stato non valido");
            }
            
    
            if (mysqli_num_rows( $ris)>0){
                /* update dello stato */
                if(!mysqli_query($conn, $query2Update)){
                    throw new Exception("Acquisto fallito");  
                }
            }else{
                /* nessun posto prenotato */
                mysqli_autocommit($conn, true);
                mysqli_close($conn);
                return "Nessun posto prenotato!";
            }
            mysqli_commit($conn);           /* commit */
            $returnValue = 'Acquisto avvenuto!';
        }catch(Exception $e){
            mysqli_rollback($conn);           /* rollback */
            /* liberare i posti prenotati */
            $returnValue = $e->getMessage();
            $returnValue .= " ". liberaPosti($conn);
        }

        mysqli_autocommit($conn, true);
    
        mysqli_close($conn);
        return $returnValue;
    }

    function liberaPosti($conn){
        /* liberazione dei posti prenotati dall'utente corrente */
        $email = userLoggedIn();
        mysqli_autocommit($conn, false);

        $query = "SELECT posto, email, stato 
                FROM PostiAcquistati_Prenotati
                WHERE email='". $email . "'and stato='prenotato' FOR UPDATE";
        $returnValue = '';
        try{
            $ris = mysqli_query($conn,$query);
            if(!$ris){
                throw new Exception();
            }

            for ($i = 0; $i < mysqli_num_rows($ris); $i++){
                $riga = mysqli_fetch_array($ris);
                liberaPosto($conn, $riga['posto']);
                $returnValue .= $riga['posto']. " ";                /* concatenazione dei posti eliminati */
            }
            $returnValue .= "Cancellazione dei posti prenotati!";
            mysqli_commit($conn);           /* commit */
        }catch(Exception $e){
            mysqli_rollback($conn);           /* rollback */
            $returnValue = "Problema nella eliminazione dei posti!";
        }
        mysqli_autocommit($conn, true);
        return $returnValue;
    }

    function liberaPosto($conn, $posto){
        /* liberazione del posto */
        $queryDelete = "DELETE FROM PostiAcquistati_Prenotati 
                            WHERE posto='". $posto ."'";

        if(!mysqli_query($conn,$queryDelete)){
            throw new Exception("Errore nella liberazione");
        }else{
            return "Cancellazione del posto prenotato!";
        }
    }

?>