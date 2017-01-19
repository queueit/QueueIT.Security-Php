<?php
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/support/test1.php');

class TestOfAutorun extends UnitTestCase {
    function testLoadIfIncluded() {
        $tests = new TestSuite();
        $tests->addFile(dirname(__FILE__) . '/support/test1.php');
        $this->assertEquals(1, $tests->getSize());
    }

    function testExitStatusOneIfTestsFail() {
        exec('php ' . dirname(__FILE__) . '/support/failing_test.php', $output, $exit_status);
        $this->assertEquals(1, $exit_status);
    }

    function testExitStatusZeroIfTestsPass() {
        exec('php ' . dirname(__FILE__) . '/support/passing_test.php', $output, $exit_status);
        $this->assertEquals(0, $exit_status);
    }
}

?>