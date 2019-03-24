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

#define MAX_C 31

int main(int argc, char **argv){
  /* controllo sugli argomenti */
  if (argc != 4){
    printf("Errore nei paramentri!\n");
    return -1;
  }
  /* controllo sulla lunghezza della stringa */
  if(strlen(argv[3]) > MAX_C){
    printf("Nome troppo lungo!");
    return -1;
  }

  prog_name = argv[0];

  uint16_t port = htons(atoi(argv[2]));                           /* conversione della prota in formato big endian*/
  struct in_addr addr;

  Inet_aton(argv[1], &addr);                                      /* creazione di addr*/

  int s = Socket(PF_INET, SOCK_DGRAM, IPPROTO_UDP);               /* creazione del socket */

  struct sockaddr_in saddr, caddr;
  saddr.sin_family = AF_INET;
  saddr.sin_port = port;
  saddr.sin_addr = addr;

  printf("Sending string: \"%s\"\t\tTo:%s::%s\n", argv[3], argv[1], argv[2]);
  /* invio della stringa */
  Sendto(s, (void *) argv[3], strlen(argv[3]), 0, (struct sockaddr*) &saddr, sizeof(saddr));

  char buff[MAX_C];
  socklen_t size;
  int pid, pid1 = getpid();

  if ((pid = fork()) == 0){
    /* gestione del timer */
    sleep(10);
    printf("Timeout expired!\n");
    kill(pid1, 9);
  }else if (pid > 0){
    /* ricezione della stringa */
    int nread = Recvfrom(s, (void *) buff, MAX_C, 0, (struct sockaddr*) &caddr, &size);
    kill(pid, 9);
    buff[nread] = '\0';
    printf("Receiving string: \"%s\"\tFrom::%s:%d\n", buff, inet_ntoa(caddr.sin_addr), ntohs(caddr.sin_port));
  }else{
    /* caso di errore */
    printf("Error to create a new process!\n");
  }

  Close(s);

  return 0;
}
