<?php

namespace u4u;

/**
 * This class will throw this type of exceptions
 *
 * @package pid
 * @author Camilo Sperberg - http://unreal4u.com/
 */
class pidException extends \ErrorException {
    public function __construct($errstr, $errline=0, $errfile='') {
        parent::__construct($errstr, 0, 0, $errfile, $errline);
    }
}
