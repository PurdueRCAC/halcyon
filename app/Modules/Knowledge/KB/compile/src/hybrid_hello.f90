!  Fortran 90

PROGRAM hello
    use omp_lib          ! Fortran 90; omp_get_thread_num, omp_get_num_threads

    ! Serial Region  (master thread of an MPI rank)
    ! MPI Parameters
    INCLUDE 'mpif.h'
    INTEGER rank, size, ierror, len
    CHARACTER(30) name                               ! Fortran 90

    ! OpenMP Parameters
    INTEGER id, nthreads

    ! All ranks initiate the message-passing environment.
    ! Each rank obtains information about itself and its environment.
    CALL mpi_init(ierror)                            ! start MPI
    CALL mpi_comm_size(mpi_comm_world, size, ierror) ! get number of ranks
    CALL mpi_comm_rank(mpi_comm_world, rank, ierror) ! get rank
    CALL mpi_get_processor_name(name,len,ierror)     ! get run-host name

    ! Master thread obtains information about itself and its environment.
    nthreads = omp_get_num_threads()                 ! get number of threads
    id = omp_get_thread_num()                        ! get thread
    PRINT *, 'SERIAL REGION:     Runhost:', name, '   Rank:', rank, ' of ', size, 'ranks, Thread:', id, ' of ', nthreads, &
             ' threads   hello, world'

    ! Open parallel region.
    ! Each thread obtains information about itself and its environment.
    !$OMP PARALLEL PRIVATE(name,id,nthreads) 
        CALL mpi_comm_size(mpi_comm_world, size, ierror) ! get number of ranks
        CALL mpi_comm_rank(mpi_comm_world, rank, ierror) ! get rank
        CALL mpi_get_processor_name(name,len,ierror)     ! get run-host name
        nthreads = omp_get_num_threads()                 ! get number of threads
        id = omp_get_thread_num()                        ! get thread
        !$OMP CRITICAL
        PRINT *, 'PARALLEL REGION:   Runhost:', name, '   Rank:', rank, ' of ', size, 'ranks, Thread:', id, ' of ', nthreads, &
             ' threads   hello, world'
        !$OMP END CRITICAL
    !$OMP END PARALLEL
    ! Close parallel region.

    ! Serial Region  (master thread)
    PRINT *, 'SERIAL REGION:     Runhost:', name, '   Rank:', rank, ' of ', size, 'ranks, Thread:', id, ' of ', nthreads, &
             ' threads   hello, world'

    ! Exit master thread.
    CALL mpi_finalize(ierror)                        ! terminate MPI
END PROGRAM hello                                    ! Fortran 90