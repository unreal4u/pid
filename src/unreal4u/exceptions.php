<?php

namespace unreal4u;

// @TODO Each exception in its own file?

/**
 * Main throwed exception
 *
 * @package pid
 * @subpackage exceptions
 */
class pidException extends \Exception {}

/**
 * When an error occurs while writing the PID this exception will be thrown
 *
 * @package pid
 * @subpackage exceptions
 */
class pidWriteException extends \Exception {}
