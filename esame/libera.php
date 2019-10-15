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
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass,$dbname);
    if(mysqli_connect_error()){ 
        echo "no";
        exit();    
    }

    $posto = $x. "_". $y;
    $posto = mysqli_real_escape_string($conn, $posto);

    $query1 = "SELECT posto, email, stato 
                FROM PostiAcquistati_Prenotati
                WHERE posto='". $posto. "' FOR UPDATE";
    
    $query2Delete = "DELETE FROM PostiAcquistati_Prenotati 
                        WHERE posto='". $posto."'";

    
    mysqli_autocommit($conn, false);
    $err = false;

    try{
        $ris = mysqli_query($conn,$query1);
        if(!$ris){
            throw new Exception("Errore nella liberazione");
        }

        if (mysqli_num_rows( $ris) == 1){
            $riga = mysqli_fetch_array($ris);

            if ($riga['stato'] == 'acquistato'){                /* verifica che il posto sia acquistato */
                throw new Exception("acquistato");
            }

            if ($riga['email'] == userLoggedIn()){              /* verifica che il posto sia prenotato dall'utente corrente */
                liberaPosto($conn, $posto);
            }else{
                throw new Exception("Errore nella liberazione, il posto non è il tuo!");
            }
        }else{
            /* posto è già libero */
            throw new Exception("Errore il posto è già libero");
        }
        mysqli_commit($conn);               /* commit */
    }catch(Exception $e){
        mysqli_rollback($conn);             /* rollback */
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