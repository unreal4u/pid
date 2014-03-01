<?php

include ('../src/unreal4u/pid.php');

// Enter here for how many seconds this example script should be running
$maxSeconds = 30;
$pid = new unreal4u\pid();

if (!$pid->alreadyRunning) {
    for ($i = 1; $i != $maxSeconds; $i++) {
        echo 'Pausing execution: ' . $i . "/" . $maxSeconds . ". Execute this script again within the time limit to test PID presence." . PHP_EOL;
        sleep(1);
    }
} else {
    // Ok, you should never call die or exit within your script, but this is a simple example file
    die('Already running!' . PHP_EOL);
}
