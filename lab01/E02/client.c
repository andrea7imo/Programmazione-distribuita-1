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
        printf("Errore negli argomenti!\n");
        return -1;
    }
    uint16_t port = htons(atoi(argv[2]));
    struct in_addr addr;

    Inet_aton(argv[1], &addr);
    prog_name = argv[0];
    int s = Socket(PF_INET, SOCK_STREAM, IPPROTO_TCP);

    struct sockaddr_in saddr;
    saddr.sin_family = AF_INET;
    saddr.sin_port = port;
    saddr.sin_addr = addr;

    /* connesione al socket del server */
    Connect(s, (struct sockaddr *)&saddr, sizeof(saddr));

    printf("Ti sei connesso bravo!\n");

    close(s);

    return 0;
}
