<?php

// Load the class
include('../src/notthrilled/Pid.php');
// Load common file which will execute a long running function
include('longRunningFunction.php');

// Enter here for how many seconds this example script should be running
$maxSeconds = 30;

try {
    $options = array('filename' => 'staleProcess', 'timeout' => 5);
    $pid = new notthrilled\Pid($options);
} catch (notthrilled\PidWriteException $e) {
    die('I could most probably not write the PID file'.PHP_EOL);
} catch (notthrilled\PidException $e) {
    die('Error detected: '.$e->getMessage().PHP_EOL);
} catch (\Exception $e) {
    die('Another exception: '.$e->getMessage().PHP_EOL);
}

if ($pid->alreadyRunning) {
	die('Process already running with PID #'.$pid->pid.PHP_EOL);
}

longRunningFunction($maxSeconds, $pid->pid);
