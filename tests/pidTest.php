<?php

require_once '../vendor/autoload.php';
require_once 'PHPUnit/Framework/TestCase.php';
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
}
