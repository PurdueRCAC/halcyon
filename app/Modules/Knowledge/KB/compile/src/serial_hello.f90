!  Fortran 90

PROGRAM hello
    CHARACTER(30) name           ! Fortran 90
    CALL getenv("HOST",name)     ! get run-host name
    PRINT *, 'Runhost:', name, '   hello, world'
END PROGRAM hello                ! Fortran 90