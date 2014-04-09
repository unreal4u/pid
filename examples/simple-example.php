<?php

include('../src/unreal4u/pid.php');
include('longRunningFunction.php');

// Enter here for how many seconds this example script should be running
$maxSeconds = 30;
$pid = new unreal4u\pid();

if (!$pid->alreadyRunning) {
    longRunningFunction($maxSeconds);
} else {
    // Ok, you should never call die or exit within your script, but this is a simple example file
    die('Already running!' . PHP_EOL);
}
