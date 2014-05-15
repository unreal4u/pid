[![Latest Stable Version](https://poser.pugx.org/unreal4u/pid/v/stable.png)](https://packagist.org/packages/unreal4u/pid)
[![Build Status](https://travis-ci.org/unreal4u/pid.png?branch=master)](https://travis-ci.org/unreal4u/pid)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/unreal4u/pid/badges/quality-score.png?s=250617550b830844374c830e955dfbdd31df3c11)](https://scrutinizer-ci.com/g/unreal4u/pid/)
[![Code Coverage](https://scrutinizer-ci.com/g/unreal4u/pid/badges/coverage.png?s=69f58ff3d306565bcde70c045878420f7bbdbd29)](https://scrutinizer-ci.com/g/unreal4u/pid/)
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
If for whatever reason, the OS still thinks the process is still running and too much time has passed, the class can overwrite the previous PID file (Thus initiating a new instance).
When the object is destroyed, the corresponding PID file will be deleted as well.

Basic usage
----------

<pre>include('src/unreal4u/pid.php');
try {
    $pid = new unreal4u\pid();
} catch (\Exception $e) {
    echo $e->getMessage();
}

if ($pid->isAlreadyRunning) {
	echo 'Your process is already running';
}
</pre>
* `$pid->pid` will show you the pid number.
* **Please see examples for more options and advanced usage**
* There is only one caveat: if you are going to use this class inside a method within a class, ensure that the destructor gets executed when it should: variables are immediatly destroyed after the method finishes executing, so the PID will be destroyed as well. To ensure this, assign the PID class to an object inside the class, that way, whenever that object gets destroyed, this class will be as well.

Composer
----------

This class has support for (preferably) Composer install. Just add the following section to your composer.json with:

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

try {
    $pid = new unreal4u\pid();
} catch (\Exception $e) {
    // Do something
}
</pre>

Pending
---------
* Better (more thorough) code coverage on PHPUnit tests.
* Test this class thoroughly on a windows machine, many UNIX improvements has been made in the meantime.

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
    * Began deprecating old coding standard
* 2.0.0:
    * Mayor rewrite of basic functioning of the class
    * Variables can now be passed on as an array instead of per variable
    * Class now throws (more) exceptions when something went wrong
    * More tests regarding new functionality
    * Backwards compatibility changes:
        * Class will now throw exceptions when it fails at some part instead of silently failing
        * Function <code>getTSpidFile()</code> renamed to <code>getTimestampPidFile()</code>

Contact the author
-------

* Twitter:   [@unreal4u](http://twitter.com/unreal4u)
* Website:   [http://unreal4u.com/](http://unreal4u.com/)
* Github:    [http://www.github.com/unreal4u](http://www.github.com/unreal4u)
* Packagist: [https://packagist.org/users/unreal4u/]
