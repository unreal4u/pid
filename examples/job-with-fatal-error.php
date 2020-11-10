<?php

// Load the class
include('../src/notthrilled/Pid.php');

// Enter here for how many seconds this example script should be running
$maxSeconds = 30;

try {
    $options = array('timeout' => 10);
    $pid = new notthrilled\Pid($options);
} catch (\Exception $e) {
    // Ok, you should never call die or exit within your script, but this is just an example file
    die($e->getMessage().PHP_EOL);
}

if ($pid->alreadyRunning) {
	die(sprintf('Already running an instance of this script (pid #%s)'.PHP_EOL, $pid->pid));
}

printf('Not running any instance of %s, this is PID %d'.PHP_EOL, basename(__FILE__), $pid->pid);
sleep(5);

// Produce an intentional fatal error, this will NOT delete the PID
require('i-should-not-exist');
