#include <sys/socket.h>
#include <arpa/inet.h>
#include <netdb.h>
#include <stdio.h>
#include <signal.h>
#include <unistd.h>

/* per abilitare un relativo supporto introdurre 1 o 0 */
#define IPV4 1
#define IPV6 !IPV4 

/* definizione dei messaggi */
#define mERR "-ERR\r\n"
#define mOK  "+OK\r\n"  

int isNumber(char *str);

/* client */
int myGetAddrInfo(char *host, char *serv);
void sendGetMessage(int s,char *nameFile);
ssize_t readProtocol(int s, char* nameFile);
ssize_t recvFile(int s, uint32_t size, char *nameFile);

/* funzioni generiche*/
ssize_t readlineCR_LFClient(int s, char* result, size_t n);
ssize_t sendSelect(int fd, void *buffptr, size_t nbytes, int flags);
ssize_t recvSelect(int fd, void *bufptr, size_t nbytes, int flags);

/* funzioni generiche verisone n bytes */
ssize_t sendnSelect(int fd, void *buffptr, size_t nbytes, int flags);
void SendnSelect(int fd, void *buffptr, size_t nbytes, int flags);
ssize_t recvnSelect(int s, void *ptr, size_t len, int flags);

/* server */
int socketBindAndListen(char *port, int bklog);
ssize_t readlineCR_LFServer(int s, char* result, size_t n);
ssize_t sendOkDimMessage(int s, uint32_t dim);
ssize_t sendFile(int s, FILE *fp, uint32_t size);
ssize_t sendErrorMessage(int s);
