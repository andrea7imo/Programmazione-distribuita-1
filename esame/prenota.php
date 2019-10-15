<?php
include 'funzioniPHP/gestioneDB.php';
include 'funzioniPHP/common.php';

session_start();
testReloadAuthenticationAjAx();

if (!isset($_POST["x"]) || !isset($_POST["y"])){
    echo "no";
    exit();
}

$x = $_POST["x"];
$y = $_POST["y"];

if ($x > 0 && $x <= $numRighe && strlen($y) == 1 && (ord(strtolower($y))-96) >=1 && (ord(strtolower($y))-96) <= $numColonne){
    /* controllo della validata degli input */
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass,$dbname);
    if(mysqli_connect_error()){ 
        echo "no";
        exit();    
    }

    $email = userLoggedIn();
    $posto = $x. "_". $y;                       /* creazione dell'indice */
    $posto = mysqli_real_escape_string($conn, $posto);   /* injection */

    $query1 = "SELECT posto, email, stato 
                FROM PostiAcquistati_Prenotati
                WHERE posto='". $posto. "' FOR UPDATE";
    
    $query2Update = "UPDATE PostiAcquistati_Prenotati 
                        SET email='". $email ."', stato='prenotato'
                        WHERE posto='". $posto."'";

    $query2Insert = "INSERT INTO PostiAcquistati_Prenotati VALUES('". $posto ."','". $email ."','prenotato')";
    
    
    mysqli_autocommit($conn, false);
    $err = false;

    try{
        $ris = mysqli_query($conn,$query1);
        if(!$ris){
            throw new Exception("Errore nella prenotazione");
        }

        if (mysqli_num_rows( $ris) == 1){               /* verifica se c'è gia una prenotazione */
            $riga = mysqli_fetch_array($ris);

            if ($riga['stato'] == 'acquistato'){        /* verifica se il posto se è stato già acquistato */
                throw new Exception("acquistato");
            }else{
                /* update */
                if(!mysqli_query($conn, $query2Update)){
                    throw new Exception("Errore nella prenotazione");          
                }
            }
        }else{
            /* insert */
            if(!mysqli_query($conn, $query2Insert)){
                throw new Exception("Errore nella prenotazione");
            }
        }
        mysqli_commit($conn);           /* commit */
    }catch(Exception $e){
        mysqli_rollback($conn);         /* rollback */
        echo $e->getMessage();
        $err = true;
    }
    mysqli_autocommit($conn, true);
    
    mysqli_close($conn);

    if(!$err){
        echo "yes";
    }
}else{
    echo "no";
}

?>