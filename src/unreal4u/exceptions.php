<?php

namespace unreal4u;

/**
 * Main throwed exception
 *
 * @package pid
 * @subpackage exceptions
 */
class pidException extends \Exception {}

/**
 * If a process is already running, this kind of exception will be thrown
 *
 * @package pid
 * @subpackage exceptions
 */
class alreadyRunningException extends \ErrorException {}

/**
 * When an error occurs while writing the PID this exception will be thrown
 *
 * @package pid
 * @subpackage exceptions
 */
class pidWriteException extends \Exception {}
