// matrix.c (Rob Farber)
// Source: Programming Intel's Xeon Phi: A Jumpstart Introduction
// <http://www.drdobbs.com/parallel/programming-intels-xeon-phi-a-jumpstart/240144160>

#ifndef MIC_DEV
#define MIC_DEV 0
#endif

#include <stdio.h>
#include <stdlib.h>
#include <omp.h>
#include <mkl.h>
#include <math.h>

// An OpenMP simple matrix multiply
   void doMult(int size, float (* restrict A)[size],
        float (* restrict B)[size], float (* restrict C)[size])
{
#pragma offload target(mic:MIC_DEV) \
                in(A:length(size*size)) in( B:length(size*size))    \
                out(C:length(size*size))
  {
    // Zero the C matrix
       #pragma omp parallel for default(none) shared(C,size)
    for (int i = 0; i < size; ++i)
      for (int j = 0; j < size; ++j)
        C[i][j] =0.f;

    // Compute matrix multiplication.
       #pragma omp parallel for default(none) shared(A,B,C,size)
    for (int i = 0; i < size; ++i)
      for (int k = 0; k < size; ++k)
        for (int j = 0; j < size; ++j)
          C[i][j] += A[i][k] * B[k][j];
  }
}

float nrmsdError(int size, float (* restrict M1)[size],
        float (* restrict M2)[size])
{
    double sum=0.;
    double max,min;
    max=min=(M1[0][0]- M2[0][0]);

#pragma omp parallel for
    for (int i = 0; i < size; ++i)
      for (int j = 0; j < size; ++j) {
    double diff = (M1[i][j]- M2[i][j]);
#pragma omp critical
    {
      max = (max>diff)?max:diff;
      min = (min<diff)?min:diff;
      sum += diff*diff;
    }
      }

    return(sqrt(sum/(size*size))/(max-min));
}

float doCheck(int size, float (* restrict A)[size],
          float (* restrict B)[size],
          float (* restrict C)[size],
          int nIter,
          float *error)
{
  float (*restrict At)[size] = malloc(sizeof(float)*size*size);
  float (*restrict Bt)[size] = malloc(sizeof(float)*size*size);
  float (*restrict Ct)[size] = malloc(sizeof(float)*size*size);
  float (*restrict Cgemm)[size] = malloc(sizeof(float)*size*size);

  // transpose to get best sgemm performance
     #pragma omp parallel for
  for(int i=0; i < size; i++)
    for(int j=0; j < size; j++) {
      At[i][j] = A[j][i];
      Bt[i][j] = B[j][i];
    }

  float alpha = 1.0f, beta = 0.0f; /* Scaling factors */

  // warm up
       sgemm("N", "N", &size, &size, &size, &alpha,
    (float *)At, &size, (float *)Bt, &size, &beta, (float *) Ct, &size);
  double mklStartTime=dsecnd();
  for(int i=0; i < nIter; i++)
    sgemm("N", "N", &size, &size, &size, &alpha,
      (float *)At, &size, (float *)Bt, &size, &beta, (float *) Ct, &size);
  double mklEndTime=dsecnd();

  // transpose in Cgemm to calculate error
       #pragma omp parallel for
  for(int i=0; i < size; i++)
    for(int j=0; j < size; j++)
      Cgemm[i][j] = Ct[j][i];

  *error = nrmsdError(size, C,Cgemm);

  free(At); free(Bt); free(Ct); free(Cgemm);

  return (2e-9*size*size*size/((mklEndTime-mklStartTime)/nIter) );
}

int main(int argc, char *argv[])
{

  if(argc != 4) {
    fprintf(stderr,"Use: %s size nThreads nIter\n",argv[0]);
    return -1;
  }

  int i,j,k;
  int size=atoi(argv[1]);
  int nThreads=atoi(argv[2]);
  int nIter=atoi(argv[3]);

  omp_set_num_threads(nThreads);

  float (*restrict A)[size] = malloc(sizeof(float)*size*size);
  float (*restrict B)[size] = malloc(sizeof(float)*size*size);
  float (*restrict C)[size] = malloc(sizeof(float)*size*size);

  // Fill the A and B arrays
     #pragma omp parallel for default(none) shared(A,B,size) private(i,j,k)
  for (i = 0; i < size; ++i) {
    for (j = 0; j < size; ++j) {
      A[i][j] = (float)i + j;
      B[i][j] = (float)i - j;
    }
  }

  double aveDoMultTime=0.;
  {
    // warm up
           doMult(size, A,B,C);

    double startTime = dsecnd();
    for(int i=0; i < nIter; i++) {
      doMult(size, A,B,C);
    }
    double endTime = dsecnd();
    aveDoMultTime = (endTime-startTime)/nIter;
  }

#pragma omp parallel
#pragma omp master
  printf("%s nThreads %d matrix %d %d runtime %g GFlop/s %g",
     argv[0], omp_get_num_threads(), size, size,
     aveDoMultTime, 2e-9*size*size*size/aveDoMultTime);
#pragma omp barrier

  // do check
       float error=0.f;
  float mklGflop = doCheck(size,A,B,C,nIter,&error);
  printf(" mklGflop %g NRMSD_error %g", mklGflop, error);

  printf("\n");

  free(A); free(B); free(C);
  return 0;
}
