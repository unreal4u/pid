<?php

include('../src/unreal4u/pid.php');
include('longRunningFunction.php');

// Enter here for how many seconds this example script should be running
$maxSeconds = 30;

try {
    $pid = new unreal4u\pid(true, '', 'staleProcess', 5);
} catch (unreal4u\pidWriteException $e) {
    die('I could most probably not write the PID file'.PHP_EOL);
} catch (unreal4u\pidException $e) {
    die('Error detected: '.$e->getMessage().PHP_EOL);
} catch (\Exception $e) {
    die('Another exception: '.$e->getMessage().PHP_EOL);
}

if ($pid->alreadyRunning) {
	die('Process already running with PID #'.$pid->pid.PHP_EOL);
}

longRunningFunction($maxSeconds, $pid->pid);
