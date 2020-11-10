<?php

namespace notthrilled;

/**
 * Determines in any OS whether the script is already running or not
 *
 * @package Pid
 * @author Mike Jackson
 * @version 2.0.5
 * @license BSD License. Feel free to modify
 */
class Pid
{

    /**
     * The version of this class
     *
     * @var string
     */
    private $version = '2.0.5';

    /**
     * The filename of the PID
     *
     * @var string
     */
    protected $filename = '';

    /**
     * After how many time we can consider the pid file to be stalled
     *
     * @var int
     */
    protected $timeout = 30;

    /**
     * The passed on parameters, defaults are in it as well
     *
     * @var array
     */
    private $parameters = [
        'directory' => '',
        'filename' => '',
        'timeout' => null,
        'checkOnConstructor' => true
    ];

    /**
     * The original timeout, gets restored on the destructor
     *
     * @var int
     */
    private $originalTimeout = 0;

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
        $allowedValues = [
            'directory',
            'filename',
            'timeout',
            'checkOnConstructor'
        ];
        $this->setParameters($allowedValues, func_get_args());

        if ($this->parameters['checkOnConstructor'] === true) {
            $this->checkPid($this->parameters);
        }
    }

    /**
     * Destroys the PID file
     */
    public function __destruct()
    {
        // Destruct PID only if we can and we are the current running script
        if (
            empty($this->alreadyRunning)
            && is_writable($this->filename)
            && (int) file_get_contents($this->filename) === $this->pid
        ) {
            unlink($this->filename);
        }
        ini_set('max_execution_time', $this->originalTimeout);
    }

    /**
     * Magic toString method.
     * Will return current version of this class
     *
     * @return string
     */
    public function __toString()
    {
        return basename(__FILE__) . ' v' . $this->version;
    }

    /**
     * Checks the parameters and saves them into our internal array
     *
     * @param array $validParameters
     * @param array $parameters
     * @return array
     */
    private function setParameters(array $validParameters, array $parameters)
    {
        $j = count($validParameters);
        for ($i = 0; $i < $j; $i++) {
            $validParameter = $validParameters[$i];
            if (isset($parameters[$i]) && !is_array($parameters[$i])) {
                $this->parameters[$validParameter] = $parameters[$i];
            } elseif (isset($parameters[0]) && is_array($parameters[0]) && isset($parameters[0][$validParameter])) {
                /*
                 * @TODO A little note about the condition above:
                 * The above condition must comply with both conditions for PHP5.3. If you use PHP5.4+ it is sufficient
                 * to simply check with isset(), but in 5.3 this WILL fail some tests and even worse: it will kind of
                 * corrupt our $this->parameters array.
                 * So, when we drop support for PHP5.3, change back this condition into a simple isset() instead of
                 * checking that we have a valid array first.
                 */
                $this->parameters[$validParameter] = $parameters[0][$validParameter];
            }
        }

        return $this->parameters;
    }

    /**
     * Verifies the PID on whatever system we may have, for now, only Windows and UNIX variants
     */
    private function verifyPID()
    {
        $this->pid = (int) trim(file_get_contents($this->filename));
        if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
            $this->verifyPIDWindows();
        } else {
            $this->verifyPIDUnix();
        }

        return $this->alreadyRunning;
    }

    /**
     * Windows' way of dealing with PIDs
     */
    private function verifyPIDWindows()
    {
        $wmi = new \COM('winmgmts://');
        $processes = $wmi->ExecQuery(
            'SELECT ProcessId FROM Win32_Process WHERE ProcessId = \''
                . (int) $this->pid
                . '\''
        );
        if (count($processes) > 0) {
            $i = 0;
            foreach ($processes as $a) {
                $i++;
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
    private function verifyPIDUnix()
    {
        if (posix_kill($this->pid, 0)) {
            $this->alreadyRunning = true;
            if (!is_null($this->timeout)) {
                $fileModificationTime = $this->getTimestampPidFile();
                if ($fileModificationTime + $this->timeout < time() && $this->timeout !== 0) {
                    $this->alreadyRunning = false;
                    unlink($this->filename);
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
    private function setDirectory()
    {
        if (empty($this->parameters['directory']) || !is_string($this->parameters['directory'])) {
            $this->parameters['directory'] = sys_get_temp_dir();
        }

        $this->parameters['directory'] = rtrim($this->parameters['directory'], '/');
        return $this->parameters['directory'];
    }

    /**
     * Sets the absolute filename
     *
     * @return string
     */
    private function privateSetFilename()
    {
        if (empty($this->parameters['filename']) || !is_string($this->parameters['filename'])) {
            $this->parameters['filename'] = basename($_SERVER['PHP_SELF']);
        }

        return $this->parameters['filename'];
    }

    /**
     * Sets the internal PID name
     *
     * @return string
     */
    public function setFilename($directory = '', $filename = '')
    {
        $validParameters = [
            'directory',
            'filename'
        ];
        $this->setParameters($validParameters, func_get_args());
        $this->setDirectory();
        $this->privateSetFilename();
        $this->filename = $this->parameters['directory'] . '/' . $this->parameters['filename'] . '.pid';

        return $this->filename;
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
        if (!empty($this->parameters['checkOnConstructor'])) {
            $validParameters = [
                'directory',
                'filename',
                'timeout'
            ];
            $this->setParameters($validParameters, func_get_args());

            $this->setFilename($this->parameters);
            
            // if we give the timeout as CLI arg we should actually use it instead of ignoring it
            if ($timeout === null && isset($this->parameters['timeout'])) {
                $timeout = $this->parameters['timeout'];
            }
            $this->setTimeout($timeout);
        }

        if (is_writable($this->filename) || is_writable(dirname($this->filename))) {
            if (!file_exists($this->filename) || !$this->verifyPID()) {
                $this->pid = getmypid();
                file_put_contents($this->filename, $this->pid);
            }
        } else {
            throw new PidWriteException(sprintf('Cannot write to pid file "%s".', $this->filename), 3);
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
        if (empty($this->pid) || !file_exists($this->filename)) {
            throw new PidException(sprintf('Execute checkPid() function first'), 1);
        }

        return filemtime($this->filename);
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
        $this->originalTimeout = ini_get('max_execution_time');
        if (is_numeric($ttl) && $ttl >= 0) {
            $this->timeout = $ttl;
        } else {
            $this->timeout = 30;
            if (!empty($this->originalTimeout)) {
                $this->timeout = $this->originalTimeout;
            }
        }

        ini_set('max_execution_time', $this->timeout);
        return $this->timeout;
    }
}
