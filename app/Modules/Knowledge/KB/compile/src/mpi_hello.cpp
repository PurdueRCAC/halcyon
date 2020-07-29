//  C++

#include <iostream>
#include <mpi.h>

using namespace std;

int main (int argc, char *argv[]) {

    // MPI Parameters
    int rank, size, len;
    char name[MPI_MAX_PROCESSOR_NAME];

    // All ranks initiate the message-passing environment.
    // Each rank obtains information about itself and its environment.
    MPI_Init(&argc, &argv);                 // start MPI
    MPI_Comm_size(MPI_COMM_WORLD, &size);   // get number of ranks
    MPI_Comm_rank(MPI_COMM_WORLD, &rank);   // get rank
    MPI_Get_processor_name(name, &len);     // get run-host name

    cout << "Runhost:" << name << "   Rank:" << rank << " of " << size << " ranks   hello, world" << endl;

    MPI_Finalize();                         // terminate MPI
    return 0;
}