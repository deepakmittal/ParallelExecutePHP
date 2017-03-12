<?php

include './ParallelExecutor.php';

$executor = new ParallelExecutor();
$executor->setMaxParallelJobs(2);
$executor->addCommand("who am i");
$executor->addCommand("curl http://wgetip.com");
$executor->addCommand("sleep 30");
$executor->addCommand("sleep 10");
$executor->addCommand("sleep 20");
$executor->setVerbose();
$executor->run();
$executor->wait();

