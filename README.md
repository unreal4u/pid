[![Latest Stable Version](https://poser.pugx.org/unreal4u/pid/v/stable.png)](https://packagist.org/packages/unreal4u/pid)
[![Build Status](https://travis-ci.org/unreal4u/pid.png?branch=master)](https://travis-ci.org/unreal4u/pid)
[![License](https://poser.pugx.org/unreal4u/pid/license.png)](https://packagist.org/packages/unreal4u/pid)

pid.php
======

Credits
--------

This class is made by unreal4u (Camilo Sperberg). [http://unreal4u.com/](unreal4u.com).

About this class
--------

* Can be used to verify whether a process is already running or not.
* Is platform independant: Can be used in Windows or Linux. In both, they will call OS specific functions to find out whether the process is running or not.
* It does not detect previous fatal errors, but it can omit the previous PID file if a given time has passed since the creation.

Detailed description
---------

This package will check if a certain PID file is present or not, and depending on that will:

Create a PID file.
If it already exists, will ask the OS to check whether it is still a running process.
If for whatever reason, the OS still thinks the process is running and too much time has passed, the class can overwrite the previous PID file.

This package has been extensivily tested with xdebug, APC, PHPUnit testing and Suhosin so that no errors are present.

Basic usage
----------

<pre>include('src/unreal4u/pid.php');
$pid = new unreal4u\pid();
if ($pid->alreadyRunning) {
    echo 'Process is already running. Dying now could perhaps be a good option';
}
</pre>
* `$pid->alreadyRunning` will show you if process is already running or not.
* `$pid->pid` will show you the pid number.
* **Please see examples for more options and advanced usage**

Composer
----------

This class has support for Composer install. Just add the following section to your composer.json with:

<pre>
{
    "require": {
        "unreal4u/pid": "@stable"
    }
}
</pre>

Now you can instantiate a new pid class by executing:

<pre>
require('vendor/autoload.php');

$pid = new unreal4u\pid();
</pre>

Pending
---------
* Better coverage on PHPUnit tests
    * Such as file system not writable
    * Or throwing some exceptions
    * Maybe a run on a windows machine?

Version History
----------

* 1.0 :
    * Initial version

* 1.1:
    * Support for Windows PID check

* 1.3:
    * PHPUnit testing
    * Documentation improved (Created this README actually)
    * More examples

* 1.3.1:
    * Script now set itself a maximum execution time

* 1.4.0:
    * Class is now compatible with composer

* 1.4.2:
    * Better documentation
    * Better code coverage
* 1.4.5:
    * Travis-CI support
    *

Contact the author
-------

* Twitter:   [@unreal4u](http://twitter.com/unreal4u)
* Website:   [http://unreal4u.com/](http://unreal4u.com/)
* Github:    [http://www.github.com/unreal4u](http://www.github.com/unreal4u)
* Packagist: [https://packagist.org/users/unreal4u/]
