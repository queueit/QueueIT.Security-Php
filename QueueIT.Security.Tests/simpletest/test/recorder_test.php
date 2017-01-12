<?php
// $Id: test.php 1500 2007-04-29 14:33:31Z pp11 $
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../recorder.php');

class TestOfRecorder extends UnitTestCase {
    
    function testContentOfRecorderWithOnePassAndOneFailure() {
        $test = new TestSuite();
        $test->addFile(dirname(__FILE__) . '/support/recorder_sample.php');
        $recorder = new Recorder(new SimpleReporter());
        $test->run($recorder);
        $this->assertEquals(2, count($recorder->results));
        $this->assertIsA($recorder->results[0], 'SimpleResultOfPass');
        $this->assertEquals(array_pop($recorder->results[0]->breadcrumb), 'testTrueIsTrue');
        $this->assertPattern('/ at \[.*\Wrecorder_sample\.php line 7\]/', $recorder->results[0]->message);
        $this->assertIsA($recorder->results[1], 'SimpleResultOfFail');
        $this->assertEquals(array_pop($recorder->results[1]->breadcrumb), 'testFalseIsTrue');
        $this->assertPattern("/Expected false, got \[Boolean: true\] at \[.*\Wrecorder_sample\.php line 11\]/",
                             $recorder->results[1]->message);
    }
}
?>