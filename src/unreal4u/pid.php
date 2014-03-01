<?php

namespace unreal4u;

include(dirname(__FILE__).'/auxiliar_classes.php');

/**
 * Determines in Windows or other OS whether the script is already running or not
 *
 * @package u4u-classes
 * @subpackage PIDVerifier
 * @author Camilo Sperberg - http://unreal4u.com/
 * @author http://www.electrictoolbox.com/check-php-script-already-running/
 * @version 1.4.5
 * @license BSD License. Feel free to modify
 * @throws pidException
 */
class pid {

    /**
     * The version of this class
     * @var string
     */
    private $_version = '1.4.5';

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
    public $alreadyRunning = false;

    /**
     * Alias of above. To be deprecated in next version
     * @var boolean
     */
    public $already_running = false;

    /**
     * Contains the PID of the script
     * @var integer $pid
     */
    public $pid = 0;

    /**
     * Whether we want to supress exception throwing or not. Defaults to false
     * @var boolean
     */
    public $supressErrors = false;

    /**
     * The main function that does it all
     *
     * @param $directory string The directory where the PID file goes to, without trailing slash
     * @param $filename string The filename of the PID file
     * @param $timeout int If we want to add a timeout
     */
    public function __construct($directory='', $filename='', $timeout=null, $checkOnConstructor=true) {
        if ($checkOnConstructor === true) {
            $this->checkPid($directory, $filename, $timeout);
        }
    }

    /**
     * Destroys the PID file
     */
    public function __destruct() {
        if (!empty($this->_filename)) {
            if (is_writable($this->_filename) and !$this->alreadyRunning) {
                unlink($this->_filename);
            }
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
     * Does the actual check
     *
     * @param string $directory The directory where the pid file is stored
     * @param string $filename Name of the pid file
     * @param int $timeout The time after which a pid file is considered "stalled"
     * @return int Returns the PID of the running process (or 1 in case of error)
     */
    public function checkPid($directory='', $filename='', $timeout=null) {
        if (empty($directory)) {
            $directory = sys_get_temp_dir();
        }

        if (empty($filename)) {
            $filename = basename($_SERVER['PHP_SELF']);
        }

        $this->setTimeout($timeout);
        $this->_filename = rtrim($directory, '/').'/'.$filename.'.pid';

        if (is_writable($this->_filename) || is_writable($directory)) {
            if (file_exists($this->_filename)) {
                $this->pid = (int)trim(file_get_contents($this->_filename));
                // If we are in Windows, do this check
                if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
                    $wmi = new \COM('winmgmts://');
                    $processes = $wmi->ExecQuery('SELECT ProcessId FROM Win32_Process WHERE ProcessId = \'' . $this->pid . '\'');
                    if (count($processes) > 0) {
                        $i = 0;
                        foreach ($processes as $a) {
                            $i++;
                        }

                        if ($i > 0) {
                            $this->alreadyRunning = true;
                            // @TODO deprecate in next mayor version
                            $this->already_running = true;
                        }
                    }
                } else {
                    // If we are in Linux, do these checks instead
                    if (posix_kill($this->pid, 0)) {
                        $this->alreadyRunning = true;
                        // @TODO deprecate in next mayor version
                        $this->already_running = true;
                        if (!is_null($this->_timeout)) {
                            $fileModificationTime = $this->getTSpidFile();
                            if ($fileModificationTime + $this->_timeout < time()) {
                                $this->alreadyRunning = false;
                                // @TODO deprecate in next mayor version
                                $this->already_running = false;
                                unlink($this->_filename);
                            }
                        }
                    }
                }
            }
        } else {
            $this->throwException('Cannot write to pid file "'.$this->_filename.'".', __LINE__);
            return 1;
        }

        if (!$this->alreadyRunning) {
            $this->pid = getmypid();
            file_put_contents($this->_filename, $this->pid);
        }

        return $this->pid;
    }

    /**
     * Gets the last modified date of the pid file
     *
     * @return int Returns the timestamp
     */
    public function getTSpidFile() {
        if (empty($this->_filename)) {
            $this->throwException('You must execute checkPid() function first', __LINE__);
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
        if (is_numeric($ttl)) {
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

    /**
     * Can throw exceptions for us
     *
     * @param string $msg The message we want to throw
     * @param int $line The line in which the error ocurred
     * @return boolean Will return false if exception isn't thrown
     * @throws pidException
     */
    protected function throwException($msg='', $line=0) {
        if (empty($this->supressErrors)) {
            throw new pidException($msg, $line, __FILE__);
        }

        return false;
    }
}
