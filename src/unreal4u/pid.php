<?php

namespace unreal4u;

include(dirname(__FILE__).'/exceptions.php');

/**
 * Determines in any OS whether the script is already running or not
 *
 * @package pid
 * @author Camilo Sperberg - http://unreal4u.com/
 * @author http://www.electrictoolbox.com/check-php-script-already-running/
 * @version 2.0.0
 * @license BSD License. Feel free to modify
 */
class pid {

    /**
     * The version of this class
     * @var string
     */
    private $_version = '2.0.0';

    /**
     * The filename of the PID
     * @var string
     */
    protected $_filename = '';

    /**
     * After how many time we can consider the pid file to be stalled
     * @var int
     */
    protected $_timeout = 30;

    /**
     * Value of script already running or not
     * @var boolean
     */
    protected $_alreadyRunning = false;

    /**
     * Contains the PID of the script
     * @var integer $pid
     */
    public $pid = 0;

    /**
     * The main function that does it all
     *
     * @param $directory string The directory where the PID file goes to, without trailing slash
     * @param $filename string The filename of the PID file
     * @param $timeout int If we want to add a timeout
     */
    public function __construct($checkOnConstructor=true, $directory='', $filename='', $timeout=null) {
        $this->setFilename($directory, $filename);
        $this->setTimeout($timeout);

        if ($checkOnConstructor === true) {
            $this->checkPid($directory, $filename, $timeout);
        }
    }

    /**
     * Destroys the PID file
     */
    public function __destruct() {
        // Destruct PID only if we can and we are the current running script
        if (!empty($this->pid) && empty($this->_alreadyRunning) && is_writable($this->_filename) && (int)file_get_contents($this->_filename) === $this->pid) {
            unlink($this->_filename);
        }
    }

    /**
     * Magic toString method. Will return current version of this class
     *
     * @return string
     */
    public function __toString() {
        return basename(__FILE__).' v'.$this->_version.' by Camilo Sperberg - http://unreal4u.com/';
    }

    /**
     * Verifies the PID on whatever system we may have, for now, only Windows and UNIX variants
     *
     * @throws alreadyRunningException
     */
    private function _verifyPID() {
        $this->pid = (int)trim(file_get_contents($this->_filename));
        if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
            $this->_verifyPIDWindows();
        } else {
            $this->_verifyPIDUnix();
        }

        if ($this->_alreadyRunning === true) {
            throw new alreadyRunningException(sprintf('A script with pid %d is already running', $this->pid), 2);
        }

        return false;
    }

    /**
     * Windows' way of dealing with PIDs
     */
    private function _verifyPIDWindows() {
        $wmi = new \COM('winmgmts://');
        $processes = $wmi->ExecQuery('SELECT ProcessId FROM Win32_Process WHERE ProcessId = \'' . (int)$this->pid . '\'');
        if (count($processes) > 0) {
            $i = 0;
            foreach ($processes as $a) {
                $i++;
            }

            if ($i > 0) {
                $this->_alreadyRunning = true;
            }
        }

        return $this->_alreadyRunning;
    }

    /**
     * Unix's way of dealing with PIDs
     */
    private function _verifyPIDUnix() {
        if (posix_kill($this->pid, 0)) {
            $this->_alreadyRunning = true;
            if (!is_null($this->_timeout)) {
                $fileModificationTime = $this->getTSpidFile();
                if ($fileModificationTime + $this->_timeout < time()) {
                    $this->_alreadyRunning = false;
                    unlink($this->_filename);
                }
            }
        }

        return $this->_alreadyRunning;
    }

    /**
     * Sets the internal PID name
     */
    public function setFilename($directory='', $filename='') {
        if (empty($directory) || !is_string($directory)) {
            $directory = sys_get_temp_dir();
        }

        if (empty($filename) || !is_string($filename)) {
            $filename = basename($_SERVER['PHP_SELF']);
        }

        $this->_filename = rtrim($directory, '/').'/'.$filename.'.pid';

        return $this->_filename;
    }

    /**
     * Does the actual check
     *
     * @param string $directory The directory where the pid file is stored
     * @param string $filename Name of the pid file
     * @param int $timeout The time after which a pid file is considered "stalled"
     * @return int Returns the PID of the running process (or 1 in case of error)
     */
    public function checkPid($directory='', $filename='', $timeout=null) {
        $this->setFilename($directory, $filename);
        $this->setTimeout($timeout);

        if (is_writable($this->_filename) || is_writable(dirname($this->_filename))) {
            if (!file_exists($this->_filename) || !$this->_verifyPID()) {
                $this->pid = getmypid();
                file_put_contents($this->_filename, $this->pid);
            }
        } else {
            throw new pidWriteException(sprintf('Cannot write to pid file "%s".', $this->_filename), 3);
        }

        return $this->pid;
    }

    /**
     * Gets the last modified date of the pid file
     *
     * @return int Returns the timestamp
     */
    public function getTSpidFile() {
        if (empty($this->pid)) {
            throw new pidException(sprintf('You must execute checkPid() function first'), 1);
        }
        return filemtime($this->_filename);
    }

    /**
     * Sets a timeout
     *
     * If a numeric value is provided, it will set it to that timeout. In other case, it will set it to current time
     * limit. This will however be 0 in CLI mode, so that value will defeat the purpose of this class entirely. In that
     * case, the script will set a default timeout time of 30 seconds.
     *
     * This method will also set a max_execution_time, why other reason should be set a timeout time otherwise?
     *
     * @param $ttl int
     * @return int Returns the timeout to what is was set
     */
    public function setTimeout($ttl=30) {
        if (is_numeric($ttl) && $ttl >= 0) {
            $this->_timeout = $ttl;
        } else {
            $this->_timeout = 30;
            $maxExecutionTime = ini_get('max_execution_time');
            if (!empty($maxExecutionTime)) {
                $this->_timeout = $maxExecutionTime;
            }
        }

        ini_set('max_execution_time', $this->_timeout);
        return $this->_timeout;
    }
}
