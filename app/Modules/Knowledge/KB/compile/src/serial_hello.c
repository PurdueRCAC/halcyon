/*  C  */

#include <stdio.h>
#include <unistd.h>

int main () {
    int len=30;
    char name[30];
    gethostname(name,len);     /* get run-host name */
    printf("Runhost:%s   hello, world\n", name);
    return 0;
}