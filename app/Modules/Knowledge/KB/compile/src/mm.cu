/***********************************************************************
* FILENAME:  MM.cu
*            Matrix Multiplication
*            Matrix operands have row-major order.
*
* C = A * B
* Multiplies two square matrices (NxN * NxN).
* Matrix values have type double.
*
* A simple CUDA program has a basic workflow:
*     1)  Initialize matrix operands as double-precision arrays on host (CPU).
*     2)  Copy operands from host memory to GPU memory.
*     3)  Apply matrix operaton to operands on GPU
*     4)  Copy result from GPU memory to host memory.
*
*
* CUDA C Programming Guide Version 4.2 (3.2.3, p.22):
* http://developer.download.nvidia.com/compute/DevZone/docs/html/C/doc/CUDA_C_Programming_Guide.pdf
*
* MM with linearized matrix operands:
* http://www.hpcwire.com/hpcwire/2008-10-08/compilers_and_more_programming_gpus_today.html
*
*************************************************************************/


#include <stdio.h>
#include "cuda.h" 

#define N 1024               /* size of square matrix                   */
#define TILE_WIDTH 16


/* MM kernel using global (not shared) memory.                          */
__global__
void myMM_global (const double * const A, const double * const B, double *C, int width) {

    /* Get row and column from block and thread IDs                     */
    int row = (blockDim.y*blockIdx.y) + threadIdx.y;
    int col = (blockDim.x*blockIdx.x) + threadIdx.x;

    /* Initialize result of one element which one thread computes.      */
    double result=0.0;

    /* Compute one element of the matrix product.                       */
    for (int i = 0; i < width; ++i)
        result += A[row*width + i] * B[i*width + col];

    /* Store the result of one matrix element in matrix C.              */
    C[row * width + col] = result;
}


/* MM kernel using shared memory.                                       */
__global__
void myMM_shared (const double * const A, const double * const B, double* C, int width) {
    __shared__ double A_shared[TILE_WIDTH][TILE_WIDTH];
    __shared__ double B_shared[TILE_WIDTH][TILE_WIDTH];

    int bx = blockIdx.x;  int by = blockIdx.y;
    int tx = threadIdx.x; int ty = threadIdx.y;

    /* Identify the row and column of the C element to work on.         */
    int row = by * TILE_WIDTH + ty;
    int col = bx * TILE_WIDTH + tx;

    double result = 0.0;

    /* Loop over the A and B tiles required to compute the C element.   */
    for (int phase = 0; phase < width/TILE_WIDTH; ++phase) {
        /* Shared effort: loading of A and B tiles into shared memory.  */
        A_shared[ty][tx] = A[row*width + (phase*TILE_WIDTH + tx)];
        B_shared[ty][tx] = B[col + (phase*TILE_WIDTH + ty)*width]; 
        __syncthreads(); 

        for (int k = 0; k < TILE_WIDTH; ++k)
            result += A_shared[ty][k] * B_shared[k][tx];
        __syncthreads();

    }
    C[row*width+col] = result;
}


/************************************************************************/
/************************************************************************/
/************************************************************************/ 
 

int main (int argc, char** argv) {

    /* Set device based on input from command line            */
    if (argc > 1) {
        if (cudaSetDevice(atoi(argv[1])) != cudaSuccess) {
            int num_devices;
            cudaGetDeviceCount(&num_devices);
            fprintf(stderr, "Error initializing device %s,\
 device value must be 0-%d\n", argv[1], (num_devices-1));
            return 0;
        }
    } else {
        fprintf(stderr, "Usage: %s gpu_device\n", argv[0]);
        return 0;
    }

    /* Declare CPU arrays.                                              */
    double A[N*N],B[N*N],C[N*N];       /* linearized CPU double arrays  */ 
    int r,c;

    /* Declare GPU arrays.                                              */
    double *G_A,*G_B,*G_C;             /* linearized GPU double arrays  */
	size_t size_a,size_b,size_c;       /* size of linearized array in bytes */
    size_a = size_b = size_c = N*N;

    /* Setup a clock.                                                   */
    cudaEvent_t start, stop;
    float CPU_elapsedtime, GPU_global_elapsedtime, GPU_shared_elapsedtime;
    cudaEventCreate(&start);
    cudaEventCreate(&stop);




    /* 1)  Initialize matrix operands as double-precision arrays on host (CPU). */
    for (r=0;r<N;++r)
    for (c=0;c<N;++c) {
        A[r*N+c] = 1.0;
        B[r*N+c] = 1.0;
    }


/*-----------------------------------------------------------------------*/

    /* MM on a CPU.                                                      */
    cudaEventRecord(start,0);
    for (int r = 0; r < N; ++r )
    for (int c = 0; c < N; ++c )
    for (int k = 0; k < N; ++k )
        C[r*N+c] += A[r*N+c] * B[k*N+c];
    cudaEventRecord(stop,0);
    cudaEventSynchronize(stop);
    cudaEventElapsedTime(&CPU_elapsedtime,start,stop);
    printf("                                                            speedup\n");
    printf("                                                            -------\n");
    printf("Elapsed time in CPU:                   %7.1f milliseconds\n", CPU_elapsedtime); 
/*-----------------------------------------------------------------------*/

    /* MM on Global Memory of GPGPU.                                     */
    cudaEventRecord(start,0);

    /* 2)  Copy operands from CPU memory to GPGPU memory.                */
    cudaMalloc((void**)&G_A,size_a*sizeof(double));  /* alloc A in GPGPU */
    cudaMalloc((void**)&G_B,size_b*sizeof(double));  /* alloc B in GPGPU */
    cudaMalloc((void**)&G_C,size_c*sizeof(double));  /* alloc C in GPGPU */
    cudaMemcpy(G_A,A,size_a*sizeof(double),cudaMemcpyHostToDevice);
    cudaMemcpy(G_B,B,size_b*sizeof(double),cudaMemcpyHostToDevice);

    /* 3)  Apply matrix operation to operands on GPGPU                   */
    /*     There is no partial final block in this example.              */
    dim3 block(TILE_WIDTH,TILE_WIDTH);      /* using a 2D block: 16,16,1 */
    dim3 grid(N/TILE_WIDTH,N/TILE_WIDTH);   /* as many 16x16-thread blocks as needed: */
    myMM_global<<< grid,block >>>(G_A,G_B,G_C,N);  /* grid(16,16,1)  */ 

    /* 4)  Copy result from GPGPU memory to CPU memory.                  */
    cudaMemcpy(C,G_C,size_c*sizeof(double),cudaMemcpyDeviceToHost);

    /* Deallocate memory on GPGPU.                                       */
    cudaFree(G_A);
    cudaFree(G_B);
    cudaFree(G_C);

    cudaEventRecord(stop,0);
    cudaEventSynchronize(stop);
    cudaEventElapsedTime(&GPU_global_elapsedtime,start,stop);
    printf("Elapsed time in GPU (global memory):   %7.1f milliseconds  %5.1f\n",
           GPU_global_elapsedtime,CPU_elapsedtime/GPU_global_elapsedtime);
/*
    printf("\nGLOBAL MEMORY:\n");
    for (r=0;r<10;++r)
    for (c=0;c<10;++c) {
        printf("%2d,%2d   %g\n", r,c,C[r*N+c]);
	}
*/
/*-----------------------------------------------------------------------*/

    /* MM on Shared Memory of GPGPU.                                     */
    cudaEventRecord(start,0);

    /* 2)  Copy operands from CPU memory to GPGPU memory.                */
    cudaMalloc((void**)&G_A,size_a*sizeof(double));  /* alloc A in GPGPU */
    cudaMalloc((void**)&G_B,size_b*sizeof(double));  /* alloc B in GPGPU */
    cudaMalloc((void**)&G_C,size_c*sizeof(double));  /* alloc C in GPGPU */
    cudaMemcpy(G_A,A,size_a*sizeof(double),cudaMemcpyHostToDevice);
    cudaMemcpy(G_B,B,size_b*sizeof(double),cudaMemcpyHostToDevice);

    /* 3)  Apply matrix operation to operands on GPGPU                   */
    /*     There is not partial final block in this example.             */
    /*     Use the same grid and block from the previous case.           */
    myMM_shared<<< grid,block >>>(G_A,G_B,G_C,N);

    /* 4)  Copy result from GPGPU memory to CPU memory.                  */
    cudaMemcpy(C,G_C,size_c*sizeof(double),cudaMemcpyDeviceToHost);

    /* Deallocate memory on GPGPU.                                       */
    cudaFree(G_A);
    cudaFree(G_B);
    cudaFree(G_C);

    cudaEventRecord(stop,0);
    cudaEventSynchronize(stop);
    cudaEventElapsedTime(&GPU_shared_elapsedtime,start,stop);
    printf("Elapsed time in GPU (shared memory):   %7.1f milliseconds  %5.1f\n",
           GPU_shared_elapsedtime,CPU_elapsedtime/GPU_shared_elapsedtime);
/*
    printf("\nSHARED MEMORY:\n");
    for (r=0;r<10;++r)
    for (c=0;c<10;++c) {
        printf("%2d,%2d   %g\n", r,c,C[r*N+c]);
	}
*/
/*-----------------------------------------------------------------------*/ 

    /* Deallocate the clock.                                             */
    cudaEventDestroy(start);
    cudaEventDestroy(stop); 

	return 0; 
}
