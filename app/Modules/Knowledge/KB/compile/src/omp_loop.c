/*  C  */

#include <stdio.h>
#include <unistd.h>
#include <omp.h>

int main () {

    /* Serial Region  (master thread)                                             */
    /* Parameters of the Application                                              */
    int len=30;
    char name[30];                      /* run-host name                          */
    int i;                              /* loop control variable                  */

    /* OpenMP Parameters                                                          */
    int id, nthreads;

    /* Master thread obtains information about itself and its environment.        */
    nthreads = omp_get_num_threads();   /* get number of threads                  */
    id = omp_get_thread_num();          /* get thread ID                          */
    gethostname(name,len);              /* get run-host name                      */
    printf("SERIAL REGION:   Runhost:%s   Thread:%d of %d thread    hello, world\n", name,id,nthreads);

    /* Open parallel region.                                                      */
    #pragma omp parallel shared(nthreads)
    {nthreads = omp_get_num_threads();   /* get number of threads                 */
    }  /* store value in shared nthreads of serial region                         */

    /* ************************************************************************** */
    /* Loop-level parallelism.                                                    */
    /* Pass nthreads from serial region to parallel loop.                         */
    /* Parallelize a loop with N iterations where N is twice the number of        */
    /* threads.  Each thread will process two iterations of the loop.             */
    /* Each iteration obtains information about its thread and its environment.   */
    #pragma omp parallel for private(name,id) firstprivate(nthreads)
    for (i=0; i<2*nthreads; i++) {
        nthreads = omp_get_num_threads();   /* get number of threads              */
        id = omp_get_thread_num();          /* get thread ID                      */
        gethostname(name,len);              /* get run-host name                  */
        printf("PARALLEL LOOP:   Runhost:%s   Thread:%d of %d threads   Iteration:%2d   hello, world\n", name,id,nthreads,i);
    }   /*  lexical extent of loop-level parallelism                              */
    /* ************************************************************************** */

    /* Serial Region  (master thread)                                             */
    nthreads = omp_get_num_threads();   /* get number of threads                  */
    printf("SERIAL REGION:   Runhost:%s   Thread:%d of %d thread    hello, world\n", name,id,nthreads);
    return 0;
}