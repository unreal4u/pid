<?php

// Load the class
include('../src/unreal4u/pid.php');
// Load common file which will execute a long running function
include('longRunningFunction.php');

class complexExample {
    private $_pid = null;

    public function __construct($timeout=30) {
        $options = array('checkOnConstructor' => false);

        $this->_pid = new unreal4u\pid($options);

        try {
            $options = array(
                'filename' => 'myVeryOwnName',
                'timeout' => $timeout
            );

            $this->_pid->checkPid($options);
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
