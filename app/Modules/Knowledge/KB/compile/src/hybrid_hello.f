C  Fortran 77

      PROGRAM HELLO

C     Serial Region  (master thread of an MPI rank)
C     MPI Parameters
      INCLUDE 'mpif.h'
      INTEGER RANK, SIZE, IERROR, LEN
      CHARACTER*30 NAME

C     OpenMP Parameters
      integer ID, NTHREADS
      INTEGER OMP_GET_THREAD_NUM, OMP_GET_NUM_THREADS

C     All ranks initiate the message-passing environment.
C     Each rank obtains information about itself and its environment.
C     Start MPI.
      CALL MPI_INIT(IERROR)
C     Get number of ranks.
      CALL MPI_COMM_SIZE(MPI_COMM_WORLD, SIZE, IERROR)
C     Get rank.
      CALL MPI_COMM_RANK(MPI_COMM_WORLD, RANK, IERROR)
C     Get run-host name.
      CALL MPI_GET_PROCESSOR_NAME(NAME,LEN,IERROR)

C     Master thread obtains information about itself and its environment.
C     Get number of threads.
      NTHREADS = OMP_GET_NUM_THREADS()
C     Get thread.
      ID = OMP_GET_THREAD_NUM()
      PRINT *, 'SERIAL REGION:     Runhost:', NAME, '   Rank:', RANK,
     &         ' of ', SIZE, 'ranks, Thread:', ID, ' of ', NTHREADS,
     &         ' thread    hello, world'

C     Open parallel region.
C     Each thread obtains information about itself and its environment.
C$OMP PARALLEL PRIVATE(NAME,ID,NTHREADS)
      CALL MPI_COMM_SIZE(MPI_COMM_WORLD, SIZE, IERROR)
      CALL MPI_COMM_RANK(MPI_COMM_WORLD, RANK, IERROR)
      CALL MPI_GET_PROCESSOR_NAME(NAME,LEN,IERROR)
      NTHREADS = OMP_GET_NUM_THREADS()
      ID = OMP_GET_THREAD_NUM()
C$OMP CRITICAL
      PRINT *, 'PARALLEL REGION:   Runhost:', NAME, '   Rank:', RANK,
     &         ' of ', SIZE, 'ranks, Thread:', ID, ' of ', NTHREADS,
     &         ' threads   hello, world'
C$OMP END CRITICAL
C$OMP END PARALLEL
C     Close parallel region.

C     Serial Region  (master thread)
      PRINT *, 'SERIAL REGION:     Runhost:', NAME, '   Rank:', RANK,
     &         ' of ', SIZE, 'ranks, Thread:', ID, ' of ', NTHREADS,
     &         ' thread    hello, world'

C     Exit master thread.
C     Terminate MPI.
      CALL MPI_FINALIZE(IERROR)
      END