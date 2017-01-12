<?php
// $Id: test.php 1748 2008-04-14 01:50:41Z lastcraft $
require_once dirname(__FILE__) . '/../../autorun.php';
require_once dirname(__FILE__) . '/../testdox.php';

// uncomment to see test dox in action
//SimpleTest::prefer(new TestDoxReporter());

class TestOfTestDoxReporter extends UnitTestCase
{
    function testIsAnInstanceOfSimpleScorerAndReporter() {
        $dox = new TestDoxReporter();
        $this->assertIsA($dox, 'SimpleScorer');
        $this->assertIsA($dox, 'SimpleReporter');
    }

    function testOutputsNameOfTestCase() {
        $dox = new TestDoxReporter();
        ob_start();
        $dox->paintCaseStart('TestOfTestDoxReporter');
        $buffer = ob_get_clean();
        $this->assertPattern('/^TestDoxReporter/', $buffer);
    }

    function testOutputOfTestCaseNameFilteredByConstructParameter() {
        $dox = new TestDoxReporter('/^(.*)Test$/');
        ob_start();
        $dox->paintCaseStart('SomeGreatWidgetTest');
        $buffer = ob_get_clean();
        $this->assertPattern('/^SomeGreatWidget/', $buffer);
    }

    function testIfTest_case_patternIsEmptyAssumeEverythingMatches() {
        $dox = new TestDoxReporter('');
        ob_start();
        $dox->paintCaseStart('TestOfTestDoxReporter');
        $buffer = ob_get_clean();
        $this->assertPattern('/^TestOfTestDoxReporter/', $buffer);
    }

    function testEmptyLineInsertedWhenCaseEnds() {
        $dox = new TestDoxReporter();
        ob_start();
        $dox->paintCaseEnd('TestOfTestDoxReporter');
        $buffer = ob_get_clean();
        $this->assertEquals($buffer, "\n");
    }

    function testPaintsTestMethodInTestDoxFormat() {
        $dox = new TestDoxReporter();
        ob_start();
        $dox->paintMethodStart('testSomeGreatTestCase');
        $buffer = ob_get_clean();
        $this->assertEquals($buffer, "- some great test case");
        unset($buffer);

        $random = rand(100, 200);
        ob_start();
        $dox->paintMethodStart("testRandomNumberIs{$random}");
        $buffer = ob_get_clean();
        $this->assertEquals($buffer, "- random number is {$random}");
    }

    function testDoesNotOutputAnythingOnNoneTestMethods() {
        $dox = new TestDoxReporter();
        ob_start();
        $dox->paintMethodStart('nonMatchingMethod');
        $buffer = ob_get_clean();
        $this->assertEquals($buffer, '');
    }

    function testPaintMethodAddLineBreak() {
        $dox = new TestDoxReporter();
        ob_start();
        $dox->paintMethodEnd('someMethod');
        $buffer = ob_get_clean();
        $this->assertEquals($buffer, "\n");
    }

    function testProperlySpacesSingleLettersInMethodName() {
        $dox = new TestDoxReporter();
        ob_start();
        $dox->paintMethodStart('testAVerySimpleAgainAVerySimpleMethod');
        $buffer = ob_get_clean();
        $this->assertEquals($buffer, '- a very simple again a very simple method');
    }

    function testOnFailureThisPrintsFailureNotice() {
        $dox = new TestDoxReporter();
        ob_start();
        $dox->paintFail('');
        $buffer = ob_get_clean();
        $this->assertEquals($buffer, ' [FAILED]');
    }

    function testWhenMatchingMethodNamesTestPrefixIsCaseInsensitive() {
        $dox = new TestDoxReporter();
        ob_start();
        $dox->paintMethodStart('TESTSupportsAllUppercaseTestPrefixEvenThoughIDoNotKnowWhyYouWouldDoThat');
        $buffer = ob_get_clean();
        $this->assertEqual(
            '- supports all uppercase test prefix even though i do not know why you would do that',
            $buffer
        );
    }
}
?>