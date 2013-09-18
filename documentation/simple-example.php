<?php
include ('../pid.class.php');
// Enter here for how many seconds this example script should be running
$howmany = 30;
$pid = new \u4u\pid();

if (!$pid->already_running) {
    for ($i = 1; $i != $howmany; $i++) {
        echo 'Pausing execution: ' . $i . "/" . $howmany . ". Execute this script again within the time limit to test PID presence." . PHP_EOL;
        sleep(1);
    }
} else {
    // Ok, you should never call die or exit within your script, but this is a simple example file
    die('Already running!' . PHP_EOL);
}
