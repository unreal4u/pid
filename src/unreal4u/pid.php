<?php
namespace unreal4u;

include (dirname(__FILE__) . '/exceptions.php');

/**
 * Determines in any OS whether the script is already running or not
 *
 * @package pid
 * @author Camilo Sperberg - http://unreal4u.com/
 * @author http://www.electrictoolbox.com/check-php-script-already-running/
 * @version 2.0.3
 * @license BSD License. Feel free to modify
 */
class pid
{

    /**
     * The version of this class
     *
     * @var string
     */
    private $_version = '2.0.3';

    /**
     * The filename of the PID
     *
     * @var string
     */
    protected $_filename = '';

    /**
     * After how many time we can consider the pid file to be stalled
     *
     * @var int
     */
    protected $_timeout = 30;

    /**
     * The passed on parameters, defaults are in it as well
     *
     * @var array
     */
    private $_parameters = array(
        'directory' => '',
        'filename' => '',
        'timeout' => null,
        'checkOnConstructor' => true
    );

    /**
     * Value of script already running or not
     *
     * @var boolean
     */
    public $alreadyRunning = false;

    /**
     * Contains the PID of the script
     *
     * @var integer $pid
     */
    public $pid = 0;

    /**
     * The main function that does it all
     *
     * Due to mayor BC break, this function receives two types of data. The old way, with 4 arguments and the new way,
     * passing along an array. This array will need the following convention, all arguments are optional:
     * array (
     *   'directory' => '',
     *   'filename' => '',
     *   'timeout' => 30,
     *   'checkOnConstructor' => true,
     * );
     *
     * The old way, AKA, with 4 arguments, will receive the
     *
     * @param mixed $directory
     *            Either an array with data or a string with location of PID directory, without trailing slash
     * @param string $filename
     *            The filename of the PID file
     * @param int $timeout
     *            The timeout for this script
     * @param boolean $checkOnConstructor
     *            Whether to check immediatly or save the effort for later
     */
    public function __construct($directory = '', $filename = '', $timeout = null, $checkOnConstructor = true)
    {
        // First argument can be an array
        $allowedValues = array(
            'directory',
            'filename',
            'timeout',
            'checkOnConstructor'
        );
        $this->_setParameters($allowedValues, func_get_args());

        if ($this->_parameters['checkOnConstructor'] === true) {
            $this->checkPid($this->_parameters);
        }
    }

    /**
     * Destroys the PID file
     */
    public function __destruct()
    {
        // Destruct PID only if we can and we are the current running script
        if (empty($this->alreadyRunning) && is_writable($this->_filename) && (int) file_get_contents($this->_filename) === $this->pid) {
            unlink($this->_filename);
        }
    }

    /**
     * Magic toString method.
     * Will return current version of this class
     *
     * @return string
     */
    public function __toString()
    {
        return basename(__FILE__) . ' v' . $this->_version . ' by Camilo Sperberg - http://unreal4u.com/';
    }

    /**
     * Checks the parameters and saves them into our internal array
     *
     * @param array $validParameters
     * @param array $parameters
     * @return array
     */
    private function _setParameters(array $validParameters, array $parameters)
    {
        $j = count($validParameters);
        for ($i = 0; $i < $j; $i ++) {
            $validParameter = $validParameters[$i];
            if (isset($parameters[$i]) && !is_array($parameters[$i])) {
                $this->_parameters[$validParameter] = $parameters[$i];
            } elseif (isset($parameters[0]) && is_array($parameters[0]) && isset($parameters[0][$validParameter])) {
                /*
                 * @TODO A little note about the condition above:
                 * The above condition must comply with both conditions for PHP5.3. If you use PHP5.4+ it is sufficient
                 * to simply check with isset(), but in 5.3 this WILL fail some tests and even worse: it will kind of
                 * corrupt our $this->_parameters array.
                 * So, when we drop support for PHP5.3, change back this condition into a simple isset() instead of
                 * checking that we have a valid array first.
                 */
                $this->_parameters[$validParameter] = $parameters[0][$validParameter];
            }
        }

        return $this->_parameters;
    }

    /**
     * Verifies the PID on whatever system we may have, for now, only Windows and UNIX variants
     */
    private function _verifyPID()
    {
        $this->pid = (int) trim(file_get_contents($this->_filename));
        if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
            $this->_verifyPIDWindows();
        } else {
            $this->_verifyPIDUnix();
        }

        return $this->alreadyRunning;
    }

    /**
     * Windows' way of dealing with PIDs
     */
    private function _verifyPIDWindows()
    {
        $wmi = new \COM('winmgmts://');
        $processes = $wmi->ExecQuery('SELECT ProcessId FROM Win32_Process WHERE ProcessId = \'' . (int) $this->pid . '\'');
        if (count($processes) > 0) {
            $i = 0;
            foreach ($processes as $a) {
                $i ++;
            }

            if ($i > 0) {
                $this->alreadyRunning = true;
            }
        }

        return $this->alreadyRunning;
    }

    /**
     * Unix's way of dealing with PIDs
     */
    private function _verifyPIDUnix()
    {
        if (posix_kill($this->pid, 0)) {
            $this->alreadyRunning = true;
            if (! is_null($this->_timeout)) {
                $fileModificationTime = $this->getTimestampPidFile();
                if ($fileModificationTime + $this->_timeout < time()) {
                    $this->alreadyRunning = false;
                    unlink($this->_filename);
                }
            }
        }

        return $this->alreadyRunning;
    }

    /**
     * Sets the absolute directory name
     *
     * @return string
     */
    private function _setDirectory()
    {
        if (empty($this->_parameters['directory']) || ! is_string($this->_parameters['directory'])) {
            $this->_parameters['directory'] = sys_get_temp_dir();
        }

        $this->_parameters['directory'] = rtrim($this->_parameters['directory'], '/');
        return $this->_parameters['directory'];
    }

    /**
     * Sets the absolute filename
     *
     * @return string
     */
    private function _setFilename()
    {
        if (empty($this->_parameters['filename']) || ! is_string($this->_parameters['filename'])) {
            $this->_parameters['filename'] = basename($_SERVER['PHP_SELF']);
        }

        return $this->_parameters['filename'];
    }

    /**
     * Sets the internal PID name
     *
     * @return string
     */
    public function setFilename($directory = '', $filename = '')
    {
        $validParameters = array(
            'directory',
            'filename'
        );
        $this->_setParameters($validParameters, func_get_args());
        $this->_setDirectory();
        $this->_setFilename();
        $this->_filename = $this->_parameters['directory'] . '/' . $this->_parameters['filename'] . '.pid';

        return $this->_filename;
    }

    /**
     * Does the actual check
     *
     * @param string $directory
     *            The directory where the pid file is stored
     * @param string $filename
     *            Name of the pid file
     * @param int $timeout
     *            The time after which a pid file is considered "stalled"
     * @return int
     *            Returns the PID of the running process (or 1 in case of error)
     */
    public function checkPid($directory = '', $filename = '', $timeout = null)
    {
        // Why check twice when we can check only once?
        if (!empty($this->_parameters['checkOnConstructor'])) {
            $validParameters = array(
                'directory',
                'filename',
                'timeout'
            );
            $this->_setParameters($validParameters, func_get_args());

            $this->setFilename($this->_parameters);
            $this->setTimeout($timeout);
        }

        if (is_writable($this->_filename) || is_writable(dirname($this->_filename))) {
            if (! file_exists($this->_filename) || ! $this->_verifyPID()) {
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
    public function getTimestampPidFile()
    {
        if (empty($this->pid) || !file_exists($this->_filename)) {
            throw new pidException(sprintf('Execute checkPid() function first'), 1);
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
    public function setTimeout($ttl = 30)
    {
        if (is_numeric($ttl) && $ttl >= 0) {
            $this->_timeout = $ttl;
        } else {
            $this->_timeout = 30;
            $maxExecutionTime = ini_get('max_execution_time');
            if (! empty($maxExecutionTime)) {
                $this->_timeout = $maxExecutionTime;
            }
        }

        ini_set('max_execution_time', $this->_timeout);
        return $this->_timeout;
    }
}
