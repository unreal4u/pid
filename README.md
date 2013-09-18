pid.class.php
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

<pre>include('pid.class.php');
$pid = new pid();
if ($pid->already_running) {
    echo 'Process is already running. Dying now would be a good option';
}
</pre>
* `$pid->already_running` will show you if process is already running or not.
* `$pid->pid` will show you the pid number.
* **Please see examples for more options and advanced usage**

Pending
---------
* None

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

Contact the author
-------

* Twitter: [@unreal4u](http://twitter.com/unreal4u)
* Website: [http://unreal4u.com/](http://unreal4u.com/)
* Github:  [http://www.github.com/unreal4u](http://www.github.com/unreal4u)
