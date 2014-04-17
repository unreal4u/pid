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
        $mapValues[] = array('', null, true, getmypid());
        $mapValues[] = array('', 45, true, getmypid());

        return $mapValues;
    }

    /**
     * Tests the __constructor method
     *
     * @dataProvider provider_constructor
     */
    public function test_constructor($filename='', $timeout=null, $checkOnConstructor=true, $expected=null) {
        $this->pid = new unreal4u\pid(vfsStream::url('exampleDir'), $filename, $timeout, $checkOnConstructor);
        $completeFilename = $this->pid->setFilename(vfsStream::url('exampleDir'), $filename);
        $this->assertEquals($expected, $this->pid->pid);
        $this->assertFileExists($completeFilename);

        try {
            $this->pid = new unreal4u\pid(vfsStream::url('exampleDir'), $filename, $timeout, true);
        } catch (\Exception $e) {
            $this->assertInstanceOf('unreal4u\\alreadyRunningException', $e);
        }

        unset($this->pid);
        $this->assertFileNotExists($completeFilename);
    }

    public function provider_setFilename() {
        $mapValues[] = array('/tmp/', 'pid', '/tmp/pid.pid');
        $mapValues[] = array('/tmp', 'pid', '/tmp/pid.pid');

        return $mapValues;
    }

    /**
     * Tests whether setting filename goes well
     *
     * @dataProvider provider_setFilename
     */
    public function test_setFilename($directory, $filename, $expected) {
        $this->pid = new unreal4u\pid('', '', null, false);
        $actual = $this->pid->setFilename($directory, $filename);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the exception throwing
     *
     * @dataProvider provider_constructor
     * @expectedException unreal4u\pidWriteException
     */
    public function test_notWritable($filename='', $timeout=null, $checkOnConstructor=true, $expected=null) {
        // Test not writable filesystem
        $this->_filesystem->chmod(0000);
        $this->pid = new unreal4u\pid(vfsStream::url('exampleDir'), $filename, $timeout, $checkOnConstructor);
    }

    /**
     * Tests more exception throwing
     *
     * @expectedException unreal4u\pidException
     */
    public function test_getTSpidFile() {
        $this->pid = new unreal4u\pid(vfsStream::url('exampleDir'), 'test.pid', null, false);
        $this->pid->getTSpidFile();
    }

    /**
     * Tests whether the version printing goes well
     */
    public function test___toString() {
        $this->pid = new unreal4u\pid('', '', 1, false);
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
        $this->pid = new unreal4u\pid(vfsStream::url('exampleDir'), '', 1);
        $this->assertEquals(getmypid(), $this->pid->pid);

        sleep(2);

        $this->pid = new unreal4u\pid(vfsStream::url('exampleDir'), '', 1);
        $this->assertEquals(getmypid(), $this->pid->pid);
    }

    /**
     * Tests whether we throw an exception on non writable folder
     *
     * @expectedException unreal4u\pidWriteException
     */
    public function test_pidFolderNotWritable() {
        vfsStream::setup('notWritable', 0000);

        $this->pid = new unreal4u\pid(vfsStream::url('notWritable'));
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
        $this->pid = new unreal4u\pid('', '', null, false);
        $timeout = $this->pid->setTimeout($ttl);
        $this->assertEquals($expected, $timeout);
        // Also verify that the maximum execution time is set correctly
        $this->assertEquals($expected, ini_get('max_execution_time'));
        ini_set('max_execution_time', 0);
    }
}
