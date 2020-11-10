<?php

/**
 * pid test case.
 */
class PidTest extends \PHPUnit\Framework\TestCase {
    /**
     * @var pid
     */
    private $pid;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void {
        parent::setUp();

        if (!function_exists('posix_kill')) {
            $this->markTestSkipped('posix extension not installed');
        }
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void {
        $this->pid = null;
        ini_set('max_execution_time', 0);
        parent::tearDown();
    }

    /**
     * Tests whether setting filename goes well
     */
    public function test_setFilename() {
        $this->pid = new notthrilled\Pid('', '', null, false);
        $actual = $this->pid->setFilename("/tmp", "pid");
        $this->assertEquals("/tmp/pid.pid", $actual);
    }

    /**
     * Provider for the __constructor
     *
     * @return array
     */
    public function provider_constructor() {
        $mapValues[] = array(true, '', null, getmypid());
        $mapValues[] = array(true, '', 45, getmypid());

        return $mapValues;
    }

    /**
     * Tests the __constructor method
     *
     * @dataProvider provider_constructor
     * @depends test_setFilename
     */
    public function test_constructor($checkOnConstructor=true, $filename='', $timeout=null, $expected=null) {
        $this->pid = new notthrilled\Pid(sys_get_temp_dir(), $filename, $timeout, $checkOnConstructor);
        $completeFilename = $this->pid->setFilename(sys_get_temp_dir(), $filename);
        $this->assertEquals($expected, $this->pid->pid);
        $this->assertFalse($this->pid->alreadyRunning);
        $this->assertFileExists($completeFilename);

        $this->pid = new notthrilled\Pid(sys_get_temp_dir(), $filename, $timeout, true);
        $this->assertTrue($this->pid->alreadyRunning);

        unset($this->pid);
        $this->assertFileDoesNotExist($completeFilename);
    }

    /**
     * Tests whether the timestamp of the file is correct
     */
    public function test_getTimestampPidFileEverythingOk() {
        $this->pid = new notthrilled\Pid(sys_get_temp_dir(), 'test');

        // Prevent the edge-case where it the time between these two calls passes the second
        $expected = ceil(time() / 2);
        $actual = ceil($this->pid->getTimestampPidFile() / 2);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests whether the version printing goes well
     */
    public function test___toString() {
        $this->pid = new notthrilled\Pid('', '', 1, false);
        $output = sprintf($this->pid);

        $reflector = new \ReflectionProperty('notthrilled\\Pid', 'version');
        $reflector->setAccessible(true);
        $version = $reflector->getValue($this->pid);

        $this->assertStringStartsWith('Pid.php v'.$version, $output);
    }

    /**
     * Tests whether we can delete a pid file and reconstruct it
     */
    public function test_fileModificationTime() {
        $this->pid = new notthrilled\Pid(sys_get_temp_dir(), '', 1, true);
        $this->assertEquals(getmypid(), $this->pid->pid);

        sleep(2);

        $this->pid = new notthrilled\Pid(sys_get_temp_dir(), '', 1, true);
        $this->assertEquals(getmypid(), $this->pid->pid);
    }

    /**
     * Provider for test_setTimeout
     */
    public function provider_setTimeout() {
        $mapValues[] = array(30, 30);
        $mapValues[] = array(45, 45);
        $mapValues[] = array(0, 0);
        $mapValues[] = array(1, 1);
        $mapValues[] = array(-2, 30);
        $mapValues[] = array(array(), 30);
        $mapValues[] = array('', 30);
        $mapValues[] = array(false, 30);

        return $mapValues;
    }

    /**
     * Tests whether the timeout is setted correctly
     *
     * @dataProvider provider_setTimeout
     */
    public function test_setTimeout($ttl, $expected) {
        $this->pid = new notthrilled\Pid('', '', null, false);
        $timeout = $this->pid->setTimeout($ttl);
        $this->assertEquals($expected, $timeout);
        // Also verify that the maximum execution time is set correctly
        $this->assertEquals($expected, ini_get('max_execution_time'));
        ini_set('max_execution_time', 0);
    }
}
