<?php

require_once 'vendor/autoload.php';
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
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        vfsStream::setup('exampleDir');
        if (!function_exists('posix_kill')) {
            $this->markTestSkipped('posix extension not installed');
        }
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        $this->pid = null;
        parent::tearDown();
    }

    /**
     * Provider for the __constructor
     *
     * @return array
     */
    public function provider_constructor() {
        $mapValues[] = array('', '', null, true, getmypid());
        $mapValues[] = array('', '', 45, true, getmypid());

        return $mapValues;
    }

    /**
     * Tests the __constructor method
     *
     * @dataProvider provider_constructor
     */
    public function test_constructor($directory='', $filename='', $timeout=null, $checkOnConstructor=true, $expected=null) {
        $this->pid = new unreal4u\pid(vfsStream::url('exampleDir'), $filename, $timeout, $checkOnConstructor);
        $this->assertEquals($expected, $this->pid->pid);
        $this->assertFalse($this->pid->already_running);

        $this->pid = new unreal4u\pid(vfsStream::url('exampleDir'), $filename, $timeout, $checkOnConstructor);
        $this->assertEquals($expected, $this->pid->pid);
        $this->assertTrue($this->pid->already_running);
    }

    /**
     * Tests whether we can delete a pid file and reconstruct it
     */
    public function test_fileModificationTime() {
        $this->pid = new unreal4u\pid(vfsStream::url('exampleDir'), '', 1);
        $this->assertEquals(getmypid(), $this->pid->pid);
        $this->assertFalse($this->pid->already_running);

        sleep(1);

        $this->pid = new unreal4u\pid(vfsStream::url('exampleDir'), '', 1);
        $this->assertEquals(getmypid(), $this->pid->pid);
        $this->assertTrue($this->pid->already_running);
    }

    /**
     * Tests magic toString method
     */
    public function test___toString() {
        $this->pid = new unreal4u\pid();
        $output = sprintf($this->pid);
        $this->assertStringStartsWith('pid', $output);
    }

    /**
     * Tests the supression of the error output
     */
    public function test_getTSpidFileSupressErrors() {
        $this->pid = new unreal4u\pid('', '', null, false);
        $this->pid->supressErrors = true;
        $returnValue = $this->pid->getTSpidFile();
        $this->assertFalse($returnValue);
    }

    /**
     * Tests getTSpidFile and exception throwing
     *
     * @expectedException unreal4u\pidException
     */
    public function test_getTSpidFile() {
        $this->pid = new unreal4u\pid('', '', null, false);
        $this->pid->getTSpidFile();
    }

    /**
     * Tests whether we throw an exception on non writable folder
     *
     * @expectedException unreal4u\pidException
     */
    public function test_pidFolderNotWritable() {
        vfsStream::setup('notWritable', 0000);

        $this->pid = new unreal4u\pid(vfsStream::url('notWritable'));
    }

    /**
     * Tests whether we get an errorcode without throwing exception
     */
    public function test_pidFolderNotWritableSupressErrors() {
        vfsStream::setup('notWritable', 0000);

        $this->pid = new unreal4u\pid('', '', null, false);
        $this->pid->supressErrors = true;
        $returnValue = $this->pid->checkPid(vfsStream::url('notWritable'));
        $this->assertEquals(1, $returnValue);
    }
}
