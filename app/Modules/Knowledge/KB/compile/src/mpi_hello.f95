!  Fortran 95

PROGRAM hello

    INTEGER, PARAMETER :: high=8
    REAL (KIND=high) x           ! Fortran 95

    ! MPI Parameters
    INCLUDE 'mpif.h'
    INTEGER rank, size, ierror, len
    CHARACTER(30) name

    ! All ranks initiate the message-passing environment.
    ! Each rank obtains information about itself and its environment.
    CALL mpi_init(ierror)                          ! start MPI
    CALL mpi_comm_size(MPI_COMM_WORLD,size,ierror) ! get number of ranks
    CALL mpi_comm_rank(MPI_COMM_WORLD,rank,ierror) ! get rank
    CALL mpi_get_processor_name(name,len,ierror)   ! get run-host name

    PRINT *, 'Runhost:', name, '   Rank:', rank, ' of ', size, 'ranks', '   hello, world'

    CALL mpi_finalize(ierror)                      ! terminate MPI
END PROGRAM hello