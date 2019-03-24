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

char *prog_name;

int main(int argc, char **argv){
    /* controllo argomenti */
    if (argc != 3){
        printf("Errore negli argomenti!");
        return -1;
    }
    uint16_t port = htons(atoi(argv[2]));
    struct in_addr addr;

    if(inet_aton(argv[1], &addr) == 0){
        printf("Errore nell'indirizzo!");
        return -2;
    }
    prog_name = argv[0];
    int s = Socket(PF_INET, SOCK_STREAM, IPPROTO_TCP);

    struct sockaddr_in saddr;
    saddr.sin_family = AF_INET;
    saddr.sin_port = port;
    saddr.sin_addr = addr;

    /* connesione al socket del server */
    Connect(s, (struct sockaddr *)&saddr, sizeof(saddr));

    printf("Ti sei connesso bravo!\n");

    int n1, n2;
    char str[50];
    printf("inserisci due numeri:\n");
    scanf("%d %d", &n1, &n2);
    sprintf(str, "%d %d\r\n", n1, n2);                /* conversione dei numeri in stringhe */


    printf("%s", str);
    Sendn(s, (void *) str, strlen(str), 0);           /* invio striga */

    ssize_t nread;
    size_t nleft = 100;
    char result[100];
    char *result1;
    result1 = result;

    while(1){
        nread = Recv(s, result1, nleft, 0);
        if ((int)nread > 0){
            nleft -= nread;
            result1 += (int)nread;
            if (*(result1-1) == '\n'){
		            result[(int)nread] = '\0';                     /* aggiunta del terminatore di stringa */
                break;
            }
        }
        else if (nread == 0)  /*conn. closed by party */
                break;
    }

    printf("Risultato: %s", result);

    Close(s);

    return 0;
}
