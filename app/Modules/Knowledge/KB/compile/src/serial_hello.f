C  Fortran 77

      PROGRAM HELLO
      CHARACTER*30 NAME
*     Get run-host name.
      CALL GETENV("HOST",NAME)
      PRINT *, 'Runhost:', NAME, '   hello, world'
      END