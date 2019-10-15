#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <sys/stat.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <string.h>
#include <time.h>
#include <unistd.h>
#include "../errlib.h"
#include "../sockwrap.h"
#include "../lib_TCP.h"
#include <errno.h>
#include <ctype.h>

#define LISTENQUEUE 15

char *prog_name;
int ricezione(int s);

int main (int argc, char *argv[]){
	/* controllo argomenti */
	if (argc != 2){
		/* numero di argomenti non corretto*/
		printf("./server <port>\n");
		return -1;
	}

	/* assegnazione nome del programma */
	prog_name = argv[0];
	
	int s =	socketBindAndListen(argv[1], LISTENQUEUE /* grandezza della coda delle richieste*/ );
	int connfd;
	socklen_t caddrlen;

	/* dichiarazione condizionata delle variabili */
	#if IPV4
	struct sockaddr_in caddr;
	caddrlen = sizeof(struct sockaddr_in);
	#else
	struct sockaddr_in6 caddr;
	caddrlen = sizeof(struct sockaddr_in6);
	#endif

	while(1){
		connfd = Accept (s, (struct sockaddr *) &caddr, &caddrlen);
		ricezione(connfd);
	}

	close(s);

	return 0;
}

int ricezione(int connfd){
	char msg[256], *msg_p;
	uint32_t bytes;
	FILE *fp;
	ssize_t n;
	struct stat st;
	uint32_t mtime;

	while(1){
		msg_p = msg; /* resetto il puntatore per manipolare le stringhe */

		if ((n = readlineCR_LFServer(connfd, msg, 256)) == 0){
			break;
		}else if (n < 0) {
			sendErrorMessage(connfd);
			break;
		}

		if((n > strlen("GET ") && strncmp(msg,"GET ",strlen("GET "))==0)){
			/* adattamento per il nome del file */
			msg[n - 2] = '\0';				/* scarto \r\n -> aggiunta del \0 */
			msg_p += 4; 	/* avanzo di 4 con il puntatore sacrto "GET " */
			
			/* apertura del file richiesto */
			fp = fopen(msg_p, "rb");
			/* controllo del file */

			if(fp == NULL){
				/* errore di apertura file -> invio messaggio di errore */
				sendErrorMessage(connfd);
				break;
			}

			if(stat(msg_p,&st) < 0){
				/* errore nella stat */
				sendErrorMessage(connfd);
				break;
			}

			bytes = st.st_size;			/* grandezza del file in byte */

			if(sendOkDimMessage(connfd,bytes) <= 0){
				break;
			}

			/* invio  file */
			if (sendFile(connfd, fp, bytes) <= 0 ){
				/* Errore nell'invio del file del file */
				fclose(fp);
				break;
			}
			/* invio corretto del file */
		
			fclose(fp);
			mtime = htonl(st.st_mtime);
			/* invio del timestamp */
			if(sendnSelect(connfd, (void*)&mtime, 4, MSG_NOSIGNAL) <= 0){
				/* errore o chiusura connesione */
				break;
			}
			
		}else{
			/* nessuna corrispondeza trovata -> invio messaggio di errore al client */
			sendErrorMessage(connfd);
			break;
		}
	}
	close(connfd);
	return 0;
}
