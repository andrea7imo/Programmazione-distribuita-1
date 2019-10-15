function verificaEmail(){
    var email = document.getElementsByName("email")[0].value;
    /* pattern */
    var re = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/;
    
    if (email == ''){
        /* email vuota -> messaggio di errore scompare */
        document.getElementById("errorMsgEmail").innerHTML = "";
        return false;
    }

    if (!re.test(String(email).toLowerCase())){
        /* email non valida */
        document.getElementById("errorMsgEmail").innerHTML = "Riprova: Email non valida!";
        return false;
    }else{
        /* email valida */
        document.getElementById("errorMsgEmail").innerHTML = "";
        return true;
    }
}

function verificaPassword(){
    var password = document.getElementsByName("password")[0].value
    var lowerCase = 0;
    var upperCase = 0;
    var number = 0;

    if (password == ''){
        /* password vuota -> messaggio di errore scompare */
        document.getElementById("errorMsgPassword").innerHTML = "";
        return false;
    }

    /* verifica password */
    for (var i = 0; i < password.length; i++) {
        var c = password.charAt(i);
        if (isNaN(c)){
            if (c == c.toLowerCase()){
                lowerCase++;
            }else{
                upperCase++;
            }
        }else{
            number++;
        }
    }

    if (lowerCase > 0 && (upperCase > 0 || number > 0)){
        /* password valida */
        document.getElementById("errorMsgPassword").innerHTML = "";
        return true;
    }else{
        /* password non valida */
        document.getElementById("errorMsgPassword").innerHTML = "Riprova: la password non supporta i requisiti minimi";
        return false;
    }
}

function verificaP_E(){
    /* verifica password ed email */
    var p = verificaPassword();
    var e = verificaEmail();
    return p && e;
}