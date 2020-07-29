//  C++

#include <iostream>
#include <mpi.h>
#include <omp.h>

using namespace std;

int main (int argc, char *argv[]) {

    // Serial Region  (master thread of an MPI rank)  
    // MPI Parameters
    int rank, size, len;
    char name[MPI_MAX_PROCESSOR_NAME];

    // OpenMP Parameters
    int id, nthreads;

    // All ranks initiate the message-passing environment.
    // Each rank obtains information about itself and its environment.
    MPI_Init(&argc, &argv);                 // start MPI
    MPI_Comm_size(MPI_COMM_WORLD, &size);   // get number of ranks
    MPI_Comm_rank(MPI_COMM_WORLD, &rank);   // get rank
    MPI_Get_processor_name(name, &len);     // get run-host name

    // Master thread obtains information about itself and its environment.
    nthreads = omp_get_num_threads();       // get number of threads
    id = omp_get_thread_num();              // get thread
    cout << "SERIAL REGION:     Runhost:" << name << "   Rank:" << rank << " of " << size << " ranks, Thread:" << id << " of " << nthread  <
< " threads   hello, world" << endl;

    // Open parallel region.
    // Each thread obtains information about itself and its environment.
    #pragma omp parallel private(name,id,nthreads)
    {MPI_Comm_size(MPI_COMM_WORLD, &size);  // get number of ranks
     MPI_Comm_rank(MPI_COMM_WORLD, &rank);  // get rank
     MPI_Get_processor_name(name, &len);    // get run-host name
     nthreads = omp_get_num_threads();      // get number of threads
     id = omp_get_thread_num();             // get thread 
     #pragma omp critical
     {
     cout << "PARALLEL REGION:   Runhost:" << name << "   Rank:" << rank << " of " << size << " ranks, Thread:" << id << " of " << nthreads
<< " threads   hello, world" << endl;
     }
    }
    // Close parallel region.

    // Serial Region  (master thread)
    cout << "SERIAL REGION:     Runhost:" << name << "   Rank:" << rank << " of " << size << " ranks, Thread:" << id << " of " << nthread  <
< " threads   hello, world" << endl;

    // Exit master thread.                                         
    MPI_Finalize();                         // terminate MPI
    return 0;
}