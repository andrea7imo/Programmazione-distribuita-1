<?php
    function creazioneTable($postiPrenotato,$postiAcquistati){
        global $alfabeto;
        global $numRighe;
        global $numColonne;
        $email = userLoggedIn();

        echo "<table>";
            for ($i = 1; $i<= $numRighe; $i++){
                /* ciclo sulle righe */
                echo "<tr>";
                for ($j = 0; $j <= $numColonne; $j++){
                    /* ciclo sulle colonne */
                    if ($j == $numColonne/2){
                        /* creazione della colonna vuota per il corridoio */
                        echo '<td style="padding:10px"></td>';
                        continue;
                    }else if ($j > $numColonne/2){
                        /* adattamento della dell'indice di colonna per via della presenza della colonna vuota */
                        $j_mod = $j - 1;
                    }else{
                        $j_mod = $j;
                    }

                    $indice = $i. "_" . $alfabeto[$j_mod];          /* creazionde dell'indice */
                    $value = $alfabeto[$j_mod]. $i;                 /* creazione del valore da mostrare sul pulsante */
                    $inputHidden = '';
                    $rigaTabella = '<td><input type="button" id="'. $indice.'" value="'. $value. '" class="button ';
                    if (isset($postiAcquistati[$indice]) && $postiAcquistati[$indice] == $indice){
                        /* posto occupato */
                        $rigaTabella .= 'buttonRed" disabled="disabled"></td>';
                        echo $rigaTabella;
                        continue;
                    }else if (isset($postiPrenotato[$indice])){
                        /* posto prenotato */
                        if ($email && $email ==  $postiPrenotato[$indice]){
                            /* posto prenotato dall'utente corrente */
                            $rigaTabella .= 'buttonYellow" ';
                            $inputHidden = '<input type="hidden" name="'. $indice. '" value="p"></td>';
                        }else{
                            /* posto prenotato da un altro utente */
                            $rigaTabella .= 'buttonOrange" ';
                            $inputHidden = '<input type="hidden" name="'. $indice. '" value="" disabled="disabled"></td>';
                        }
                    }else{
                        /* posto libero */
                        $rigaTabella .= 'buttonGreen" ';
                        $inputHidden = '<input type="hidden" name="'. $indice. '" value="" disabled="disabled"></td>';                   
                    }

                    if ($email){
                        /* aggiunta della funzione che viene chiamata da onclick */
                        $rigaTabella .= 'onclick="prenota('.$i.",'". $alfabeto[$j_mod]. "'".')">';
                    }else{
                        $rigaTabella .= 'disabled="disabled">';
                    }
                    $rigaTabella .= $inputHidden;
                    echo $rigaTabella;
                }
                echo "</tr>";
            }
        echo "</table>";
    }
?>