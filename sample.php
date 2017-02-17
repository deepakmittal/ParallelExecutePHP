<?php

include './ParallelExecutor.php';

$executor = new ParallelExecutor();
$executor->addCommand("curl http://www.vdopia.com/");
$executor->addCommand("who am i");
$executor->addCommand("curl http://wgetip.com");

$executor->setVerbose();
$executor->run();
$executor->wait();

