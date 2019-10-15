<?php
$matricola = "262710_";
$numRighe = 10;
$numColonne = 6;
$alfabeto = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

    function httpsRedirect(){
        /* serve per redirigire (forzare) la pagina principale su https, quando si è loggati */
        if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off'){
            $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $redirect_url");
            exit();
        }	
    }

    function verificaEmail($username){
        /* controllo dell'email inserita dall'utente:
                -che abbia il formato di un email
                -che rispetti la lunghezza massima disponibile
                -che non contega tag html/php */
        $emailS = sanitizeString($username);
        return filter_var($username, FILTER_VALIDATE_EMAIL) && strlen($username)<=256 && $emailS == $username;
    }

    function sanitizeString($var){
        $var = strip_tags($var);    
        $var = htmlentities($var);
        return $var;
    }

    function verificaPassword($password){
        /* controllo della password che segua le specifiche date e non superi la lunghezza massima disponibile */
        $pattern='/.*[a-z].*[A-Z0-9].*|.*[A-Z0-9].*[a-z].*/';
        return preg_match($pattern, $password) && strlen($password)<=256;
    }

    function userLoggedIn() {
        /* controllo che l'utente sia autenticato */
        global $matricola;
        if (isset($_SESSION[$matricola.'utente'])) {
            return ($_SESSION[$matricola.'utente']);
        } else {
            return false;
        }
    }

    function getUserTime(){
        global $matricola;
        if (isset($_SESSION[$matricola.'time'])) {
            return ($_SESSION[$matricola.'time']);
        } else {
            return false;
        }
    }

    function riautenticazione(){
        session_start(); 
        $t = time(); 
        $diff = 0; 
        $new = false; 
        global $matricola;

        if (userLoggedIn()){
            $t0=$_SESSION[$matricola.'time']; 
            $diff=($t-$t0); // inactivity 
        } else {
            $new=true; 
        }

        if ($diff > 120) {
            $_SESSION=array();
            if (ini_get("session.use_cookies")) { // PHP using cookies to handle session
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 3600*24, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }
            session_destroy(); 
            header('HTTP/1.1 307 temporary redirect'); 
            if ($new){
                $location = 'index.php';
                $msg = '';
            }else{
                $location = 'login.php';
                $msg = 'Rieffettua il login!';
            }
            redirect($location,$msg);
            exit(); 
        } else if (!$new) {
            $_SESSION[$matricola.'time']=time(); /* update time */
        }
    }

    function redirect($location,$msg=''){
        if ($msg == ''){
            header('Location: '.$location);
        }else{
            header('Location: '.$location."?msg=".urlencode($msg));
        }
        exit();	
    }

    function logout(){
        $_SESSION = array();
        if (ini_get("session.use_cookies")) { // PHP using cookies to handle session
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600*24, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }

    function testReloadAuthenticationAjAx(){
        global $matricola;

        /* verifica che l'utente sia loggato */
        if (!userLoggedIn()){
            echo "login";
            exit();
        }
        
        /* verifica che la richiesta provenga da https */
        if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off'){
            $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            echo "https";
            exit();
        }

        /* verifica del timeout */
        if(time() - $_SESSION[$matricola."time"] > 120){
            echo "login";
            exit();
        }else{
            $_SESSION[$matricola."time"] = time();
        }
    }
?>