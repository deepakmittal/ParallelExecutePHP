# ParallelExecutePHP
Executes multiple shell commands parallelly. Has option to wait for all commands to finish executing and retrive std outputs.

##Example
```
//sample.php
include './ParallelExecutor.php';

$executor = new ParallelExecutor();
$executor->addCommand("curl http://www.vdopia.com/");
$executor->addCommand("who am i");
$executor->addCommand("curl http://wgetip.com");

$executor->setVerbose();
$executor->run();
$executor->wait();
```
###Output
```
$ php sample.php

 beginning execution of commands. jobs group id: Byi9l.....
 command: who am i
    job id: 3DCLP, process id: 2566

 command: curl http://wgetip.com
    job id: OxBFl, process id: 2568

 all commands submitted for execution.

 ---------------------------------------------------------------------------------
 waiting for job 3DCLP.......
 SUCCESS
 output:
deepakmi tty??    Feb 17 17:16

 ---------------------------------------------------------------------------------
 ---------------------------------------------------------------------------------
 waiting for job OxBFl.......
 SUCCESS
 output:
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100    13  100    13    0     0     23      0 --:--:-- --:--:-- --:--:--    23
182.73.244.82
 ---------------------------------------------------------------------------------


all jobs executed successfully.
```

