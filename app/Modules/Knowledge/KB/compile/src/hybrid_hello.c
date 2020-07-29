/*  C  */

#include <stdio.h>
#include <mpi.h>
#include <omp.h>

int main (int argc, char *argv[]) {

    /* Serial Region  (master thread of an MPI rank) */
    /* MPI Parameters                 */
    int rank, size, len;
    char name[MPI_MAX_PROCESSOR_NAME];

    /* OpenMP Parameters */
    int id, nthreads;

    /* All ranks initiate the message-passing environment.             */
    /* Each rank obtains information about itself and its environment. */

    MPI_Init(&argc, &argv);                 /* start MPI           */
    MPI_Comm_size(MPI_COMM_WORLD, &size);   /* get number of ranks */
    MPI_Comm_rank(MPI_COMM_WORLD, &rank);   /* get rank            */
    MPI_Get_processor_name(name, &len);     /* get run-host name   */

    /* Master thread obtains information about itself and its environment. */
    nthreads = omp_get_num_threads();       /* get number of threads */
    id = omp_get_thread_num();              /* get thread            */
    printf("SERIAL REGION:     Runhost:%s   Rank:%d of %d ranks, Thread:%d of %d thread    hello, world\n", name,rank,size,id,nthreads);

    /* Open parallel region.  */
    /* Each thread obtains information about itself and its environment. */
    #pragma omp parallel private(name,id,nthreads)
    {MPI_Comm_size(MPI_COMM_WORLD, &size);  /* get number of ranks   */
     MPI_Comm_rank(MPI_COMM_WORLD, &rank);  /* get rank              */
     MPI_Get_processor_name(name, &len);    /* get run-host name     */
     nthreads = omp_get_num_threads();      /* get number of threads */
     id = omp_get_thread_num();             /* get thread            */
     printf("PARALLEL REGION:   Runhost:%s   Rank:%d of %d ranks, Thread:%d of %d threads   hello, world\n", name,rank,size,id,nthreads);
    }
    /* Close parallel region. */

    /* Serial Region  (master thread) */
    printf("SERIAL REGION:     Runhost:%s   Rank:%d of %d ranks, Thread:%d of %d thread    hello, world\n", name,rank,size,id,nthreads);

    /* Exit master thread.                                         */
    MPI_Finalize();                         /* terminate MPI       */
    return 0;
}