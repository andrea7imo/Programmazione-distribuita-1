function checkCookie(){
    var cookieEn = navigator.cookieEnabled;
    if (!cookieEn){ 
        document.cookie = "tstcookie";
        cookieEn = document.cookie.indexOf("tstcookie")!=-1;
    }
    return cookieEn;
}

function prenota(i, j){
	/* funzione che viene chiamata quando si preme un posto */
	var className = document.getElementById(i + "_" + j).className;
	document.getElementById(i + "_" + j).className = "button buttonGray"; 		/* colorazione del posto in grigio -> per l'attesa di conferma per la prenotazione/liberazione */
    if (className == "button buttonGreen" || className == "button buttonOrange"){
		/* prenotazione del posto */
		var flag = className == "button buttonOrange";
        prenotaAjAx(i, j, flag);
    }else if (className == "button buttonYellow"){
		/* liberazione del posto */
        liberaAjAx(i, j);
    }
}

var req;

function ajaxRequest() {
	try { // Non IE Browser? 
    	var request = new XMLHttpRequest()
	} catch(e1){ // No
    	try { // IE 6+?
        	request = new ActiveXObject("Msxml2.XMLHTTP")
    	} catch(e2){ // No
	   		try { // IE 5?
	       		request = new ActiveXObject("Microsoft.XMLHTTP")
	   		} catch(e3){ // No AJAX Support
				request = false
	   		}
  		}
		}
	return request;
}

function postoPrenotato(i,j){
	document.getElementById(i + "_" + j).className = "button buttonYellow";		/* colorazione del posto */
	document.getElementsByName(i + "_" + j)[0].value = "p";
	document.getElementsByName(i + "_" + j)[0].disabled = false;
}

function postoLibero(i,j){
	document.getElementById(i + "_" + j).className = "button buttonGreen";		/* colorazione del posto */
	document.getElementsByName(i + "_" + j)[0].value = "";
	document.getElementsByName(i + "_" + j)[0].disabled = true;
	/* aggiornamento delle statistiche */
	document.getElementById("numTotP").innerHTML = parseInt(document.getElementById("numTotP").innerHTML) - 1;
	document.getElementById("numTotL").innerHTML = parseInt(document.getElementById("numTotL").innerHTML) + 1;
}

function postoAcquistato(i,j){
	document.getElementById(i + "_" + j).className = "button buttonRed";		/* colorazione del posto */
	document.getElementById(i + "_" + j).disabled = true;    		/* disabilitazione del bottone */
	document.getElementsByName(i + "_" + j)[0].value = "";
	document.getElementsByName(i + "_" + j)[0].disabled = true;
	
}

function prenotaAjAx(i,j,orange) {
    req = ajaxRequest();
    
 	req.onreadystatechange=
 		function() {
 			if (req.readyState==4 && (req.status==200 || req.status==0)){
				switch(req.responseText) {
					case "acquistato":
						/* caso posto acquistato */
						window.alert("Posto: " + j + "" + i + " già acquistato!");
						postoAcquistato(i,j);
						/* gestione delle statistiche */
						if (orange){ 
							document.getElementById("numTotA").innerHTML = parseInt(document.getElementById("numTotA").innerHTML) + 1;
							document.getElementById("numTotP").innerHTML = parseInt(document.getElementById("numTotP").innerHTML) - 1;
						}else{
							document.getElementById("numTotA").innerHTML = parseInt(document.getElementById("numTotA").innerHTML) + 1;
							document.getElementById("numTotL").innerHTML = parseInt(document.getElementById("numTotL").innerHTML) - 1;
						}
						break;
				 	case "Errore nella prenotazione":
						window.alert("Errore nella prenotazione del posto: " + j + "" + i);
						/* rintroduzione del colore corretto dei posti */
						if (orange){
							document.getElementById(i + "_" + j).className = "button buttonOrange";
						}else{
							document.getElementById(i + "_" + j).className = "button buttonGreen";
						}
						break;
				 	case "yes":
						/* posto prenotato */
						window.alert("Posto: " + j + "" + i + " prenotato");
						/* gestione delle statistiche */
						if (!orange){
							document.getElementById("numTotP").innerHTML = parseInt(document.getElementById("numTotP").innerHTML) + 1;
							document.getElementById("numTotL").innerHTML = parseInt(document.getElementById("numTotL").innerHTML) - 1;
						}
						postoPrenotato(i,j);
						break;
				 	case "no":
						/* rintroduzione del colore corretto dei posti */
						window.alert("Si è verificato un problema nella prenotazione del posto: " + j + "" + i);
						if (orange){
							document.getElementById(i + "_" + j).className = "button buttonOrange";
						}else{
							document.getElementById(i + "_" + j).className = "button buttonGreen";
						}
						break;
				 	case "login":
						/* reload della pagina */
						document.location.reload();
						break;
				 	case "https":
						/* reload della pagina */
						document.location.reload();
						break;
					default:
						break;
				 }
        	}
    	}
    req.open("POST","prenota.php",true);
    req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send("x=" + i + "&y="+ j);			/* invio */
}

function liberaAjAx(i,j) {
    req = ajaxRequest();
    
 	req.onreadystatechange=
 		function() {
 			if (req.readyState==4 && (req.status==200 || req.status==0)){
				switch(req.responseText) {
					case "acquistato":
						/* caso posto acquistato */
						window.alert("Posto: " + j + "" + i + " già acquistato!");
						postoAcquistato(i,j);
						/* getsione delle statistiche */
						document.getElementById("numTotA").innerHTML = parseInt(document.getElementById("numTotA").innerHTML) + 1;
						document.getElementById("numTotP").innerHTML = parseInt(document.getElementById("numTotP").innerHTML) - 1;
						break;
					case "Errore nella liberazione":
						/* rintroduzione del colore corretto dei posti */
						window.alert("Problema della liberazione del posto: " + j + "" + i);
						document.getElementById(i + "_" + j).className = "button buttonYellow";
						break;
					case "Errore nella liberazione, il posto non è il tuo!":
						/* rintroduzione del colore corretto dei posti */
						window.alert("Errore nella liberazione del posto: " + j + "" + i + " non è il tuo!");
						document.getElementById(i + "_" + j).className = "button buttonOrange";
						document.getElementsByName(i + "_" + j)[0].value = "";
						document.getElementsByName(i + "_" + j)[0].disabled = true;
						break;
					case "Errore il posto è già libero":
						/* rintroduzione del colore corretto dei posti */
						window.alert("Posto: " + j + "" + i + " è già libero");
						postoLibero(i,j);
						break;
					case "yes":
						/* posto prenotato */
						window.alert("Posto: " + j + "" + i + " liberato");
						postoLibero(i,j);
						break;
					case "no":
						/* rintroduzione del colore corretto dei posti */
						window.alert("Si è verificato un problema nella liberazione del posto: " + j + "" + i);
						document.getElementById(i + "_" + j).className = "button buttonYellow";
						break;
					case "login":
						/* reload della pagina */
						document.location.reload();
						break;
					case "https":
						/* reload della pagina */
						document.location.reload();
						break;
					default:
						break; 
        		}
			}
		}
    req.open("POST","libera.php",true);
    req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send("x=" + i + "&y="+ j);			/* invio */
}
