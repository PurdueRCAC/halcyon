/*  C  */

#include <stdio.h>
#include <mpi.h>
#include <omp.h>

int main (int argc, char *argv[]) {

    /* Serial Region  (master thread of an MPI rank)                                             */
    /* Parameters of the Application.                                             */
    int i;                                  /* loop control variable              */

    /* MPI Parameters */
    int rank, size, len;
    char name[MPI_MAX_PROCESSOR_NAME];

    /* OpenMP Parameters                                                          */
    int id, nthreads;

    /* All ranks initiate the message-passing environment.  */
    /* Each rank obtains information about its thread and its environment.   */
    /* Master thread obtains information about itself and its environment.        */
    MPI_Init(&argc, &argv);                 /* start MPI                          */
    MPI_Comm_size(MPI_COMM_WORLD, &size);   /* get number of ranks                */
    MPI_Comm_rank(MPI_COMM_WORLD, &rank);   /* get rank                           */
    MPI_Get_processor_name(name, &len);     /* get run-host name                  */
    nthreads = omp_get_num_threads();       /* get number of threads              */
    id = omp_get_thread_num();              /* get thread                         */
    printf("SERIAL REGION:   Runhost:%s   Rank:%d of %d ranks, Thread:%d of %d thread    hello, world\n", name,rank,size,id,nthreads);

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
    #pragma omp parallel for private(name,rank,id) firstprivate(nthreads)
    for (i=0; i<2*nthreads; i++) {
        MPI_Comm_size(MPI_COMM_WORLD, &size);   /* get number of ranks            */
        MPI_Comm_rank(MPI_COMM_WORLD, &rank);   /* get rank                       */
        MPI_Get_processor_name(name, &len);     /* get run-host name              */
        nthreads = omp_get_num_threads();       /* get number of threads          */
        id = omp_get_thread_num();              /* get thread                     */
        printf("PARALLEL LOOP:   Runhost:%s   Rank:%d of %d ranks, Thread:%d of %d threads   Iteration:%2d   hello, world\n", name,rank,size
,id,nthreads,i);
    }   /*  lexical extent of loop-level parallelism                              */
    /* ************************************************************************** */

    /* Serial Region  (master thread)                                             */
    nthreads = omp_get_num_threads();   /* get number of threads                  */
    printf("SERIAL REGION:   Runhost:%s   Rank:%d of %d ranks, Thread:%d of %d thread    hello, world\n", name,rank,size,id,nthreads);

    /* Exit master thread.                                                        */
    MPI_Finalize();                     /* terminate MPI                          */
    return 0;
}