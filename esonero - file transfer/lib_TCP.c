#include "lib_TCP.h"
#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <string.h>
#include <time.h>
#include <unistd.h>
#include "errlib.h"
#include "sockwrap.h"
#include <errno.h>
#include <ctype.h>

#define BUFFER_LENGTH 1024

extern char *prog_name;

/* ############################################################################ */
/* #                                 CLIENT TCP                               # */
/* ############################################################################ */

int myGetAddrInfo(char *host, char *serv){
  /*
    -permette di effettuare il controllo se l'indirizzo ip4 o ipv6 sia valido
    -permette di effettuare una risoluzione mediante nome
    -effettua la creazione del socket
    -effettua la connect con il server
  */

  /* controllo sulla porta */ 
  if (!isNumber(serv)){
    err_msg("(%s) error - getaddrinfo() failed %s %s : (code -8) Servname not supported for ai_socktype\n",prog_name,host,serv);
    exit(-1);
  }

  struct addrinfo hints, *res, *res0;
  int error, s; 
  char *cause;
  struct sockaddr_storage serveraddr;     /* per la compatibilità ad IPv4 e IPv6 */
  int rc;

  /* assegnazione degli hints */
  memset(&hints, 0, sizeof(hints));
  hints.ai_flags = AI_NUMERICSERV;
  #if IPV4
  hints.ai_family = AF_INET; 
  #else
  hints.ai_family = AF_UNSPEC;        /* non specificato sia IPv4 e sia IPv6*/  
  #endif  
  hints.ai_socktype = SOCK_STREAM;      /* si vuole socket di tipo STREAM */

  /* controllo se l'indirizzo IPv4 è valido */
  rc = inet_pton(AF_INET, host, &serveraddr);
  if (rc == 1){
    hints.ai_family = AF_INET;      /* per IPv4 */
  }else if (IPV6){
    /* controllo se l'indirizzo IPv6 è valido */
    rc = inet_pton(AF_INET6, host, &serveraddr);
    if (rc == 1){
      hints.ai_family = AF_INET6;   /* per IPv6 */
    }
  }

  /* chiamta getaddrinfo */
  if ((error = getaddrinfo(host, serv, &hints, &res0))) {
    err_sys("%s", error); 
  }

  s = -1;
  for (res = res0; res!=NULL; res = res->ai_next) {     /* riecerca indirizzo da utilizzare*/
    s = socket(res->ai_family, res->ai_socktype, res->ai_protocol);
    if (s < 0) {
      cause = "socket";
      continue;
    }
    if (connect(s, res->ai_addr, res->ai_addrlen) < 0) {
      cause = "connect failed";
      close(s);
      s = -1;
      continue;
    }
    break;  
  }

  freeaddrinfo(res0);       /* free list of structures */
  
  if (s < 0) {              /* controllo valore di ritorno della connect -> non si è torvato nessun indirizzo */
    err_sys("%s", cause); /*NOTREACHED*/
  }

  return s;
}

int isNumber(char *str){
  int i;
  for(i = 0; i < strlen(str); i++){
    if(str[i] < '0' || str[i] > '9')
      return 0;
  }
  return 1;
}

void sendGetMessage(int s,char *nameFile){
  char str[6 + strlen(nameFile)];
  sprintf(str, "GET %s\r\n", nameFile);
  /* invio del messaggio  di "GET" */
  SendnSelect(s,(void*)str, strlen(str), MSG_NOSIGNAL);
}

ssize_t readProtocol(int s, char *nameFile){
  char result[6+1];
  uint32_t length, timestamp;
  ssize_t returnValue = readlineCR_LFClient(s, result, 6);    /* lettura della inizio del messaggio fino a "\r\n"*/

  if (returnValue <= 0){
    /* caso di errore di ricezione */
    return returnValue;
  }

  if (strcmp(result, mERR) == 0){
    /* caso d'errore -> uscire dal while */
    return -1;
  }else if (strcmp(result, mOK) == 0){
    /* lettura dimensione del file */
    if ((returnValue = recvnSelect(s, (void *) &length, 4, 0)) <= 0){
      return returnValue;
    }
    length = ntohl(length);          /* conversione da network by order */

    /* leggi file */
    if ((returnValue = recvFile(s, length, nameFile)) <= 0){
      /* chiusura del socket o errore nella ricezione del file*/
      return returnValue;
    }

    /* leggi timestamp */
    if ((returnValue = recvnSelect(s, (void *) &timestamp, 4, 0)) <=0){
      return returnValue;
    }
    timestamp = ntohl(timestamp);   /* conversione da network by order */
    
    /* Stampa Nome, Grandezza file e timestamp */
    printf("Received file %s\n", nameFile);
    printf("Received file size %d\n", length);
    printf("Received file timestamp %d\n",timestamp);
  }else{
    /* messaggio non riconosciuto */
    return -1;
  }
  
  return 1;
}

ssize_t readlineCR_LFClient(int s, char* result, size_t n){  /* lato client */
    ssize_t nread;
    char *result1;
    size_t nleft = n;
    int nr = 0;

    if (result == NULL){
      return -1;
    }

    result1 = result;

    /* ricevo il primo byte per capire qual'è la natura 
    del messaggio -> se il primo byte non è identificato si esce! */
    nread = recvSelect(s,result1, 1, 0);

    if (nread <= 0){
      return -1;
    }

    if(nread == 1 && *result1 == '+'){ 
      /* caso +OK */   
      nleft = 4;
    }else if (nread == 1 && *result1 == '-'){
      /* caso -ERR */
      nleft = 5;
    }else{
      /* messaggio non esistente */
      return -1;
    }
    
    result1 += 1;

    while(1){
        nread = recvSelect(s, result1, nleft, 0);

        if ((int)nread > 0){
            nleft -= nread;
            nr += (int)nread;
            result1 += (int)nread;
            if ((int)nread > 1 && *(result1-2) == '\r' && *(result1-1) == '\n'){    /* verifica della corretta terminazione del messaggio */
		            *(result1) ='\0';                     /* aggiunta del terminatore di stringa per la stampa */
                break;
            }
        }
        else if (nread == 0){
                return 0;
        }else{
          /* error */
          return -1;
        }

    }
    return strlen(result);
}

ssize_t recvFile(int s, uint32_t size, char *nameFile){
  FILE *fp;
  char buff[BUFFER_LENGTH];
  uint32_t nleft;
  ssize_t nread;
  uint32_t sizeLeft = (size_t)size;
  int n;

  /* apertura file in sola scrittura */
  fp = fopen(nameFile, "wb");

  if (fp == NULL){    /* controllo se c'e' un errore nell'apertura del file */
    return 0;
  }

  /* ricezione del file */
  while(1){
    /* 
      -sizeLeft: contine quanto manca da per completare la ricezione del file 
      -nleft: si ha quanto di deve leggere dal buffer in quel determinato ciclo
    */
    if (sizeLeft > BUFFER_LENGTH){
      nleft = BUFFER_LENGTH;
      sizeLeft -= BUFFER_LENGTH;
    }else{
      nleft = sizeLeft;
      sizeLeft = 0;
    }

    nread = recvnSelect(s, (void *)buff, nleft, 0);

    if (nread > 0){
      /* memorizzazione del file */
      n = fwrite(buff, 1, nread, fp);

      if(n != nread){       /* controllo se c'è stato un errore nella scrittura sul disco */
        fclose(fp);
        /* Errore nella scrittura del file su disco! */
        /* eliminazione del file */
        remove(nameFile);
        return -1;
      }
    }else if (nread == 0){
      fclose(fp);
      remove(nameFile);
      return 0;
    }else{
      /* errore */
      fclose(fp);
      remove(nameFile);
      return -1;
    }

    if(sizeLeft == 0)         /* condizione di terminazione */
      break;
  }
  fclose(fp);
  return size-sizeLeft;
}


/* ############################################################################ */
/* #                                 funzioni generiche                       # */
/* ############################################################################ */

ssize_t recvSelect(int fd, void *buffptr, size_t nbytes, int flags){
  int n;
  fd_set cset;
  ssize_t nread;
  struct timeval tval;

  /* inizializzazione */
  FD_ZERO(&cset);
  FD_SET(fd,&cset);
  tval.tv_sec = 15;
  tval.tv_usec = 0;

  /* select */
  if ((n = select(FD_SETSIZE, &cset, NULL, NULL, &tval)) == -1){
    return -1;
  }
  if (n == 1){
    nread = recv(fd, buffptr, nbytes, flags);
  }else{
    /* time expired */
    return -2;
  }
  return nread;
}

ssize_t sendSelect(int fd, void *buffptr, size_t nbytes, int flags){
  int n;
  fd_set cset;
  ssize_t nread;
  struct timeval tval;

  /* inizializzazione */
  FD_ZERO(&cset);
  FD_SET(fd,&cset);
  tval.tv_sec = 15;
  tval.tv_usec = 0;

  /* select */
  if ((n = select(FD_SETSIZE, NULL, &cset, NULL, &tval)) == -1){
    return -1;
  }
  if (n == 1){
    nread = send(fd, buffptr, nbytes, flags);
  }else{
    /* time expired */
    return -2;
  }
  return nread;
}

void SendnSelect(int fd, void *buffptr, size_t nbytes, int flags){
  if (sendnSelect(fd, buffptr, nbytes, flags) != nbytes)
		err_sys ("(%s) error - writen() failed", prog_name);
}

ssize_t sendnSelect (int fd, void *vptr, size_t n, int flags){
	size_t nleft;
	ssize_t nwritten;
	char *ptr;

  /* inizilizzazione */
	ptr = vptr;
	nleft = n;

	while (nleft > 0){
	  if ( (nwritten = sendSelect(fd, ptr, nleft, flags)) <= 0){
			if (INTERRUPTED_BY_SIGNAL){
				nwritten = 0;
				continue; /* and call send() again */
			}else
				return -1;
		  }
    /* aggiornamento dei puntatori */
		nleft -= nwritten;
		ptr += nwritten;
	}
	return n-nleft;
}

ssize_t recvnSelect(int s, void *ptr, size_t len, int flags){
    ssize_t nread;
    size_t nleft;
    for (nleft=len; nleft > 0; ){
      nread = recvSelect(s, ptr, nleft, flags);
      if (nread > 0){
        /* aggiornaemnto dei puntatori */
        nleft -= nread;
        ptr += nread;
      }else if (nread == 0){ /* conn. closed by party */
        return 0;
      }else{
        /* Errore */
        return -1;
      }
    }
    return (len - nleft);
}


/* ############################################################################ */
/* #                                 SERVER TCP                               # */
/* ############################################################################ */

int socketBindAndListen(char* port, int bklog){
  /*
    -effettua il controllo sulla porta se essa e' un numero
    -supporto IPv4 e IPv6
    -Socket
    -Bind
    -Accept
  */
 
  if(!isNumber(port)){
    printf("non è un numero di porta!\n");
    exit(-1);
  }
  int s;

  #if IPV4
  struct sockaddr_in saddr;
  saddr.sin_family = AF_INET;
  saddr.sin_port = htons(atoi(port));
  saddr.sin_addr.s_addr = htonl(INADDR_ANY);
  s = Socket(PF_INET, SOCK_STREAM, IPPROTO_TCP);
  #else
  struct sockaddr_in6 saddr;
  memset(&saddr, 0, sizeof(saddr));
  saddr.sin6_family = AF_INET6;
  saddr.sin6_port   = htons(atoi(port));
  saddr.sin6_addr   = in6addr_any;
  s = Socket(PF_INET6, SOCK_STREAM, IPPROTO_TCP);
  #endif

  /* bind e listen */
  Bind(s, (struct sockaddr *) &saddr, (socklen_t)sizeof(saddr));
  Listen(s, bklog);
  return s;
}

ssize_t readlineCR_LFServer(int s, char* result, size_t n){  /* lato server*/
    ssize_t nread;
    char *result1;
    size_t nleft = n;

    if(result == NULL){
      return -1;
    }

    result1 = result;

    /* ricevo il primo byte per capire qual'è la natura 
    del messaggio -> se il primo byte non è identificato si esce! */
    nread = recvSelect(s,result1, 1, 0);

    if (nread == 0){
      return 0;
    }else if(nread < 0){
      return -1;
    }

    if(!(nread == 1 && *result1 == 'G')){  /* tutti i messaggi che iniziano con una lettera che non è 'G' viene scartato */
      /* messaggio non esistente */
      return -1;
    }
    
    result1 += 1;

    while(1){
        nread = recvSelect(s, result1, nleft, 0);

        if ((int)nread > 0){
            nleft -= nread;
            result1 += (int)nread;
            if ((int)nread > 1 && *(result1-2) == '\r' && *(result1-1) == '\n'){      /* verifica della corretta terminazione del messaggio */
		            *(result1) ='\0';                     /* aggiunta del terminatore di stringa */
                break;
            }
        }
        else if (nread == 0){  /*conn. closed by party */
                return 0;
        }else{
          /* error */
          return -1;
        }

    }
    return strlen(result);
}

ssize_t sendFile(int s, FILE *fp, uint32_t size){
  char buff[BUFFER_LENGTH];
  int nread = 0, n;
  uint32_t sizeLeft = size, nleft;

  while(1){
    /*
      -sizeLeft numero di byte rimaneti da inviare
      -nleft numero di byte da inviare nel i-esimo ciclo
    */
    if (sizeLeft > BUFFER_LENGTH){
      nleft = BUFFER_LENGTH;
      sizeLeft -= BUFFER_LENGTH;
    }else{
      nleft = sizeLeft;
      sizeLeft = 0;
    }
    /* lettura del file */
    nread = fread(buff, 1, nleft, fp);

    if (nread != nleft){      /* controllo se si è verificato un errore nella lettura del disco */
      return -1;
    }

    if (nread > 0){
      /* invio del pezzo i-esimo del file */
      if((n = sendnSelect(s, (void *)buff, nread, MSG_NOSIGNAL)) <= 0){
        return n;
      }
    }

    if (sizeLeft == 0){
      /* fine del trasferiemento */
      break;
    }
  }
  return size - sizeLeft;
}

ssize_t sendOkDimMessage(int s, uint32_t dim){
  /*
    -invio del messaggio di "+OK\r\n"
    -invio della dimensione
  */
  ssize_t n;

  dim = htonl(dim);       /* trasformazione della dimensione in network by order */

  /* invio messaggio: "+OK\r\n" */
  if((n = sendnSelect(s, mOK, strlen(mOK), MSG_NOSIGNAL)) <=0)
    return n;
  
  /* invio della dimensione */
  if((n = sendnSelect(s, (void*)&dim, 4, MSG_NOSIGNAL)) <= 0)
    return n;
  return n;
}

ssize_t sendErrorMessage(int s){
  /* invio del messaggio di errore */
  return sendnSelect(s, mERR, strlen(mERR), MSG_NOSIGNAL);
}


