#include <stdio.h>
#include <unistd.h>
int main() {
  char host[100],mic0[100],mic1[100];
  #pragma offload target(mic:0)
  {
    gethostname(mic0,100);
  }
  #pragma offload target(mic:1)
  {
    gethostname(mic1,100);
  }
  gethostname(host,100);
  printf("Hello from %s\n", host);
  printf("Hello from %s\n", mic0);
  printf("Hello from %s\n", mic1);
}
