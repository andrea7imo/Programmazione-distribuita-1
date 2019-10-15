#include <stdio.h>
#include <stdlib.h>
#include "../lib_TCP.h"
#include <sys/types.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <string.h>
#include <time.h>
#include <unistd.h>
#include "../errlib.h"
#include "../sockwrap.h"

char *prog_name;

int main (int argc, char *argv[]){
	/* controllo del numero di argomenti passati*/
	if (argc < 4){
		printf("./client <dest_host> <dest_port> <filename> [<filename>...] [-r]\n");
		return -1;
	}

	prog_name = argv[0];

	/* chiamata getaddrinfo */
	int s = myGetAddrInfo(argv[1], argv[2]);

	int i = 0;

	while(i < argc-3){
		sendGetMessage(s,argv[3 + i]);				/* invio del messaggio di "+GET" */
		if(readProtocol(s, argv[3 + i]) <= 0){		/* protocollo di lettura */
			/* chiusura del soket del server o errore nella ricezione	*/
			break;
		}
		i++;
	}

	close(s);

	return 0;
}
