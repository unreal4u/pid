<?php

/**
 * This function will pause the execution of the script, simulating a long running process
 */
function longRunningFunction($timeout=1, $pid) {
    for ($i = 1; $i != $timeout; $i++) {
        printf(
            'PID %d: %d/%d. Execute this script again within the time limit to test PID presence.'.PHP_EOL,
            $pid,
            $i,
            $timeout
        );
        sleep(1);
    }

    return true;
}
