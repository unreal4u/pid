<?php

use org\bovigo\vfs\vfsStream;

/**
 * pid test case.
 */
class pidTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var pid
     */
    private $pid;

    /**
     * Contains the filesystem
     * @var vfsStream
     */
    private $_filesystem = null;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        if (!function_exists('posix_kill')) {
            $this->markTestSkipped('posix extension not installed');
        }
        $this->_filesystem = vfsStream::setup('exampleDir');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        $this->pid = null;
        ini_set('max_execution_time', 0);
        parent::tearDown();
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
     * Provider for test_setFilename
     *
     * @return array
     */
    public function provider_setFilename() {
        $mapValues[] = array('/tmp/', 'pid', '/tmp/pid.pid');
        $mapValues[] = array('/tmp', 'pid', '/tmp/pid.pid');
        // @TODO 2014-04-18 Expand these tests

        return $mapValues;
    }

    /**
     * Tests whether setting filename goes well
     *
     * @dataProvider provider_setFilename
     */
    public function test_setFilename($directory, $filename, $expected) {
        $this->pid = new unreal4u\pid(false);
        $actual = $this->pid->setFilename($directory, $filename);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the __constructor method
     *
     * @dataProvider provider_constructor
     * @depends test_setFilename
     */
    public function test_constructor($checkOnConstructor=true, $filename='', $timeout=null, $expected=null) {
        $this->pid = new unreal4u\pid($checkOnConstructor, vfsStream::url('exampleDir'), $filename, $timeout);
        $completeFilename = $this->pid->setFilename(vfsStream::url('exampleDir'), $filename);
        $this->assertEquals($expected, $this->pid->pid);
        $this->assertFalse($this->pid->alreadyRunning);
        $this->assertFileExists($completeFilename);

        $this->pid = new unreal4u\pid(true, vfsStream::url('exampleDir'), $filename, $timeout);
        $this->assertTrue($this->pid->alreadyRunning);

        unset($this->pid);
        $this->assertFileNotExists($completeFilename);
    }

    /**
     * Tests the exception throwing
     *
     * @dataProvider provider_constructor
     * @expectedException unreal4u\pidWriteException
     */
    public function test_notWritable($checkOnConstructor=true, $filename='', $timeout=null, $expected=null) {
        // Test not writable filesystem
        $this->_filesystem->chmod(0000);
        $this->pid = new unreal4u\pid($checkOnConstructor, vfsStream::url('exampleDir'), $filename, $timeout);
    }

    /**
     * Tests more exception throwing
     *
     * @expectedException unreal4u\pidException
     */
    public function test_getTimestampPidFile() {
        $this->pid = new unreal4u\pid(false, vfsStream::url('exampleDir'), 'test.pid');

        // Prevent the edge-case where it the time between these two calls passes the second
        $expected = ceil(time() / 2);
        $actual = ceil($this->pid->getTimestampPidFile() / 2);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests whether the version printing goes well
     */
    public function test___toString() {
        $this->pid = new unreal4u\pid(false, '', '', 1);
        $output = sprintf($this->pid);

        $reflector = new \ReflectionProperty('unreal4u\\pid', '_version');
        $reflector->setAccessible(true);
        $version = $reflector->getValue($this->pid);

        $this->assertStringStartsWith('pid.php v'.$version, $output);
    }

    /**
     * Tests whether we can delete a pid file and reconstruct it
     */
    public function test_fileModificationTime() {
        $this->pid = new unreal4u\pid(true, vfsStream::url('exampleDir'), '', 1);
        $this->assertEquals(getmypid(), $this->pid->pid);

        sleep(2);

        $this->pid = new unreal4u\pid(true, vfsStream::url('exampleDir'), '', 1);
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
        $this->pid = new unreal4u\pid(false);
        $timeout = $this->pid->setTimeout($ttl);
        $this->assertEquals($expected, $timeout);
        // Also verify that the maximum execution time is set correctly
        $this->assertEquals($expected, ini_get('max_execution_time'));
        ini_set('max_execution_time', 0);
    }
}
