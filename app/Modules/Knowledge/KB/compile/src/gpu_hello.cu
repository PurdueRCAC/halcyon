/*

FILENAME:  gpu_hello.cu


Copy the string "hello, world" from CPU to GPU and back
using common CUDA methods.

Naming convention of the GPU world:
H_  -  host (CPU)
D_  -  device (GPU)

*/


#include <cuda.h>    /* GPU library                           */
#include <stdio.h>   /* printf()                              */


/* Forward Reference                                          */
__global__ void HelloWorld (char*,char*);

int main(int argc, char** argv) {

    /* 1) The host initializes an array.                      */
    /*    - define source message and target array.           */
    /*    - allocate memory on the host.                      */
    char H_str1[] = "hello, world";
    char H_str2[] = "XXXXXXXXXXXX";

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
        fprintf(stderr, "No GPU specified, using first GPU");
        if (cudaSetDevice(0) != cudaSuccess) {
            int num_devices;
            cudaGetDeviceCount(&num_devices);
            fprintf(stderr, "Error initializing device 0,\
 device value must be 0-%d\n", (num_devices-1));
            return 0;
        }
    }

    /* Allocate memory on the GPU device.                     */
    char *D_str1, *D_str2;
    size_t size = sizeof(H_str1);     /* 13 characters        */
    cudaMalloc((void**)&D_str1, size);
    cudaMalloc((void**)&D_str2, size);

    /* 2) Copy array from host memory to GPU memory.          */
    cudaMemcpy(D_str1, H_str1, size, cudaMemcpyHostToDevice);

    /* Set the grid and block sizes.                          */
    dim3 dimGrid(1);
    dim3 dimBlock(size);     /* one thread per character      */

    /* 3) GPU operates on the array.                          */
    /*    - invoke the kernel.                                */
    HelloWorld<<< dimGrid, dimBlock >>>(D_str1,D_str2);

    /* 4) Copy array from GPU memory to host memory.          */
    cudaMemcpy(H_str2, D_str2, size, cudaMemcpyDeviceToHost);

    /* Free up the allocated memory on the GPU.               */
    cudaFree(D_str1);
    cudaFree(D_str2);

    /* Display result of the copy.                            */
    printf("%s\n", H_str2);

    return 0;
}

/* Device Kernel                                              */
/* On the GPU, perform some computation (copy).               */
__global__ void HelloWorld(char* str1, char* str2) {
    /* Determine thread ID.                                   */
    int idx = blockIdx.x * blockDim.x + threadIdx.x;

    /* Copy one element of the string.                        */
    str2[idx] = str1[idx];
}
