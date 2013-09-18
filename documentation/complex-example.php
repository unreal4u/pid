<?php

include('../pid.class.php');

// for how many time this script should be running
$timeout = 15;

// Calling the pid class without it checking on load if we are running
$pid = new \u4u\pid(null, null, null, false);

try {
    // Manual call to a PID check, assume default directory and filename.
    $pid->checkPid('','',($timeout * 2));
} catch (\u4u\pidException $e) {
    die('Captured exception: '.$e->getMessage().PHP_EOL);
}

if (!$pid->already_running) {
    for ($i = 1; $i != $timeout; $i++) {
        echo 'Pausing execution: '.$i.'/'.$timeout.PHP_EOL;
        sleep(1);
    }
} else {
    // Process is already running, that means we must terminate this one
    die('Already running!'.PHP_EOL);
}
