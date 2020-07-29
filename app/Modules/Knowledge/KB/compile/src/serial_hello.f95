!  Fortran 95

PROGRAM hello
    CHARACTER(30) name
    INTEGER, PARAMETER :: high=8
    REAL (KIND=high) x           ! Fortran 95
    CALL getenv("HOST",name)     ! get run-host name
    PRINT *, 'Runhost:', name, '   hello, world'
END PROGRAM hello