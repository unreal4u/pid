<?php

include('../src/unreal4u/pid.php');
include('longRunningFunction.php');

class complexExample {
    private $_pid = null;

    public function __construct($timeout=30) {
        $this->_pid = new unreal4u\pid(false);

        try {
            $this->_pid->checkPid('', 'myVeryOwnName', $timeout);
        } catch (unreal4u\alreadyRunningException $e) {
            // Ok, you should never call die or exit within your script, but this is just an example file
            die($e->getMessage().PHP_EOL);
        } catch (unreal4u\pidWriteException $e) {
            die('I could most probably not write the PID file'.PHP_EOL);
        } catch (unreal4u\pidException $e) {
            die('Error detected: '.$e->getMessage().PHP_EOL);
        } catch (\Exception $e) {
            // A normal Exception should not happen too often, but MAY occur anyway in the future
            die('Any other exception: '.$e->getMessage().PHP_EOL);
        }

        if (!$this->_pid->alreadyRunning) {
            $this->runForLong($timeout);
        } else {
            throw new Exception(sprintf('Process already running with pid %s', $this->_pid->pid).PHP_EOL);
        }
    }

    public function runForLong($maxSeconds) {
        longRunningFunction($maxSeconds, $this->_pid->pid);
    }
}

try {
    $complexExample = new complexExample(30);
} catch (\Exception $e) {
    die($e->getMessage());
}
