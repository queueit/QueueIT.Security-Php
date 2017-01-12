<?php
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../default_reporter.php');

class TestOfCommandLineParsing extends UnitTestCase {
    
    function testDefaultsToEmptyStringToMeanNullToTheSelectiveReporter() {
        $parser = new SimpleCommandLineParser(array());
        $this->assertIdentical($parser->getTest(), '');
        $this->assertIdentical($parser->getTestCase(), '');
    }
    
    function testNotXmlByDefault() {
        $parser = new SimpleCommandLineParser(array());
        $this->assertFalse($parser->isXml());
    }
    
    function testCanDetectRequestForXml() {
        $parser = new SimpleCommandLineParser(array('--xml'));
        $this->assertTrue($parser->isXml());
    }
    
    function testCanReadAssignmentSyntax() {
        $parser = new SimpleCommandLineParser(array('--test=myTest'));
        $this->assertEquals('myTest', $parser->getTest());
    }
    
    function testCanReadFollowOnSyntax() {
        $parser = new SimpleCommandLineParser(array('--test', 'myTest'));
        $this->assertEquals('myTest', $parser->getTest());
    }
    
    function testCanReadShortForms() {
        $parser = new SimpleCommandLineParser(array('-t', 'myTest', '-c', 'MyClass', '-x'));
        $this->assertEquals('myTest', $parser->getTest());
        $this->assertEquals('MyClass', $parser->getTestCase());
        $this->assertTrue($parser->isXml());
    }
}
?>