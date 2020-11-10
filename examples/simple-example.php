<?php

// Load the class
include('../src/notthrilled/Pid.php');
// Load common file which will execute a long running function
include('longRunningFunction.php');

// Enter here for how many seconds this example script should be running
$maxSeconds = 30;

try {
    $pid = new notthrilled\Pid();
} catch (\Exception $e) {
    // Calling die() is actually a bad practice, but this is just an example file
    die($e->getMessage().PHP_EOL);
}

if (!$pid->alreadyRunning) {
	longRunningFunction($maxSeconds, $pid->pid);
} else {
    die('Script was already running, so we had to terminate the execution of it'.PHP_EOL);
}
