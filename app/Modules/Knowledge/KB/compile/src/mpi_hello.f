C  Fortran 77

      PROGRAM HELLO

C     MPI Parameters
      INCLUDE 'mpif.h'
      INTEGER RANK, SIZE, IERROR, LEN
      CHARACTER*30 NAME

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

      PRINT *, 'Runhost:', name, '   Rank:', RANK, ' of ', SIZE,
     &         'ranks', '   hello, world'

C     Terminate MPI.
      CALL MPI_FINALIZE(IERROR)
      END