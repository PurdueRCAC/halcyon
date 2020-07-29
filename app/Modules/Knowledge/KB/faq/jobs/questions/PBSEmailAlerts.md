---                                                                              
title: "How can I get email alerts about my PBS job status?"                                                           
tags:                                                                               
 - wholenode
 - sharednode
---                                                                               

### Question                                                                           
How can I be notified when my PBS job was executed and if it completed successfully?

### Answer
Submit your job with the following command line arguments                           
<pre><code>qsub -M email_address -m bea myjobsubmissionfile</code></pre>          
Or, include the following in your job submission file.                              

<pre><code>#PBS -M email_address                                                  
#PBS -m bae                                                                         
</code></pre>          

The -m option can have the following letters; "a", "b", and "e":   

a - mail is sent when the job is aborted by the batch system.                       
b - mail is sent when the job begins execution.                                     
e - mail is sent when the job terminates.                          
