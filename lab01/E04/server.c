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
#define MAX_C 31

char *prog_name;

int main(int argc, char **argv){
  if (argc != 2){
    printf("Errore nei paramentri!");
    return -1;
  }

  prog_name = argv[0];
  uint16_t port = htons(atoi(argv[1]));                           /* conversione della prota in formato big endian*/
  struct in_addr addr;

  Inet_aton(argv[1], &addr);                                     /* creazione di addr*/

  int s = Socket(PF_INET, SOCK_DGRAM, IPPROTO_UDP);              /* creazione del socket */

  struct sockaddr_in saddr, caddr;
  saddr.sin_family = AF_INET;
  saddr.sin_port = port;
  saddr.sin_addr.s_addr = (uint32_t) htonl(INADDR_ANY);

  Bind(s, (struct sockaddr *) &saddr, sizeof(saddr));           /* bind del indirizzo */

  char buff[MAX_C];
  socklen_t size;
  ssize_t nread;

  while(1){
    printf("Waiting...\n");
    size = sizeof(struct sockaddr_storage);                     /* senza di questo il programma crascia!*/
    nread = Recvfrom(s, (void *) buff, MAX_C, 0, (struct sockaddr*) &caddr, &size);
    printf("Receiving string: \"%s\"\tFrom:%s::%d\n", buff, inet_ntoa(caddr.sin_addr), ntohs(caddr.sin_port));
    buff[nread] = '\0';
    sendto(s, (void *) buff, nread, 0, (struct sockaddr*) &caddr, size);
  }

  Close(s);

  return 0;
}
