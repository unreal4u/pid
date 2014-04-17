<?php

include('../src/unreal4u/pid.php');
include('longRunningFunction.php');

// Enter here for how many seconds this example script should be running
$maxSeconds = 30;

try {
    $pid = new unreal4u\pid();
} catch (\Exception $e) {
    // Ok, you should never call die or exit within your script, but this is just an example file
    die($e->getMessage().PHP_EOL);
}

longRunningFunction($maxSeconds, $pid->pid);
