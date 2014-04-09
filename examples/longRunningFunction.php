<?php

/**
 * This function will pause the execution of the script, simulating a long running process
 */
function longRunningFunction($timeout=1) {
    for ($i = 1; $i != $timeout; $i++) {
        echo 'Pausing execution: '.$i.'/'.$timeout.'. Execute this script again within the time limit to test PID presence.'.PHP_EOL;
        sleep(1);
    }

    return true;
}
