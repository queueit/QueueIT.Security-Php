<?php
// $Id: tag_test.php 1748 2008-04-14 01:50:41Z lastcraft $
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../tag.php');
require_once(dirname(__FILE__) . '/../encoding.php');
Mock::generate('SimpleMultipartEncoding');

class TestOfTag extends UnitTestCase {
    
    function testStartValuesWithoutAdditionalContent() {
        $tag = new SimpleTitleTag(array('a' => '1', 'b' => ''));
        $this->assertEquals('title', $tag->getTagName());
        $this->assertIdentical($tag->getAttribute('a'), '1');
        $this->assertIdentical($tag->getAttribute('b'), '');
        $this->assertIdentical($tag->getAttribute('c'), false);
        $this->assertIdentical($tag->getContent(), '');
    }
    
    function testTitleContent() {
        $tag = new SimpleTitleTag(array());
        $this->assertTrue($tag->expectEndTag());
        $tag->addContent('Hello');
        $tag->addContent('World');
        $this->assertEquals('HelloWorld', $tag->getText());
    }
    
    function testMessyTitleContent() {
        $tag = new SimpleTitleTag(array());
        $this->assertTrue($tag->expectEndTag());
        $tag->addContent('<b>Hello</b>');
        $tag->addContent('<em>World</em>');
        $this->assertEquals('HelloWorld', $tag->getText());
    }
    
    function testTagWithNoEnd() {
        $tag = new SimpleTextTag(array());
        $this->assertFalse($tag->expectEndTag());
    }
    
    function testAnchorHref() {
        $tag = new SimpleAnchorTag(array('href' => 'http://here/'));
        $this->assertEquals('http://here/', $tag->getHref());
        
        $tag = new SimpleAnchorTag(array('href' => ''));
        $this->assertIdentical($tag->getAttribute('href'), '');
        $this->assertIdentical($tag->getHref(), '');
        
        $tag = new SimpleAnchorTag(array());
        $this->assertIdentical($tag->getAttribute('href'), false);
        $this->assertIdentical($tag->getHref(), '');
    }
    
    function testIsIdMatchesIdAttribute() {
        $tag = new SimpleAnchorTag(array('href' => 'http://here/', 'id' => 7));
        $this->assertIdentical($tag->getAttribute('id'), '7');
        $this->assertTrue($tag->isId(7));
    }
}

class TestOfWidget extends UnitTestCase {
    
    function testTextEmptyDefault() {
        $tag = new SimpleTextTag(array('type' => 'text'));
        $this->assertIdentical($tag->getDefault(), '');
        $this->assertIdentical($tag->getValue(), '');
    }
    
    function testSettingOfExternalLabel() {
        $tag = new SimpleTextTag(array('type' => 'text'));
        $tag->setLabel('it');
        $this->assertTrue($tag->isLabel('it'));
    }
    
    function testTextDefault() {
        $tag = new SimpleTextTag(array('value' => 'aaa'));
        $this->assertEquals('aaa', $tag->getDefault());
        $this->assertEquals('aaa', $tag->getValue());
    }
    
    function testSettingTextValue() {
        $tag = new SimpleTextTag(array('value' => 'aaa'));
        $tag->setValue('bbb');
        $this->assertEquals('bbb', $tag->getValue());
        $tag->resetValue();
        $this->assertEquals('aaa', $tag->getValue());
    }
    
    function testFailToSetHiddenValue() {
        $tag = new SimpleTextTag(array('value' => 'aaa', 'type' => 'hidden'));
        $this->assertFalse($tag->setValue('bbb'));
        $this->assertEquals('aaa', $tag->getValue());
    }
    
    function testSubmitDefaults() {
        $tag = new SimpleSubmitTag(array('type' => 'submit'));
        $this->assertIdentical($tag->getName(), false);
        $this->assertEquals('Submit', $tag->getValue());
        $this->assertFalse($tag->setValue('Cannot set this'));
        $this->assertEquals('Submit', $tag->getValue());
        $this->assertEquals('Submit', $tag->getLabel());
        
        $encoding = new MockSimpleMultipartEncoding();
        $encoding->expectNever('add');
        $tag->write($encoding);
    }
    
    function testPopulatedSubmit() {
        $tag = new SimpleSubmitTag(
                array('type' => 'submit', 'name' => 's', 'value' => 'Ok!'));
        $this->assertEquals('s', $tag->getName());
        $this->assertEquals('Ok!', $tag->getValue());
        $this->assertEquals('Ok!', $tag->getLabel());
        
        $encoding = new MockSimpleMultipartEncoding();
        $encoding->expectOnce('add', array('s', 'Ok!'));
        $tag->write($encoding);
    }
    
    function testImageSubmit() {
        $tag = new SimpleImageSubmitTag(
                array('type' => 'image', 'name' => 's', 'alt' => 'Label'));
        $this->assertEquals('s', $tag->getName());
        $this->assertEquals('Label', $tag->getLabel());
        
        $encoding = new MockSimpleMultipartEncoding();
        $encoding->expectAt(0, 'add', array('s.x', 20));
        $encoding->expectAt(1, 'add', array('s.y', 30));
        $tag->write($encoding, 20, 30);
    }
    
    function testImageSubmitTitlePreferredOverAltForLabel() {
        $tag = new SimpleImageSubmitTag(
                array('type' => 'image', 'name' => 's', 'alt' => 'Label', 'title' => 'Title'));
        $this->assertEquals('Title', $tag->getLabel());
    }
    
    function testButton() {
        $tag = new SimpleButtonTag(
                array('type' => 'submit', 'name' => 's', 'value' => 'do'));
        $tag->addContent('I am a button');
        $this->assertEquals('s', $tag->getName());
        $this->assertEquals('do', $tag->getValue());
        $this->assertEquals('I am a button', $tag->getLabel());

        $encoding = new MockSimpleMultipartEncoding();
        $encoding->expectOnce('add', array('s', 'do'));
        $tag->write($encoding);
    }
}

class TestOfTextArea extends UnitTestCase {
    
    function testDefault() {
        $tag = new SimpleTextAreaTag(array('name' => 'a'));
        $tag->addContent('Some text');
        $this->assertEquals('a', $tag->getName());
        $this->assertEquals('Some text', $tag->getDefault());
    }
    
    function testWrapping() {
        $tag = new SimpleTextAreaTag(array('cols' => '10', 'wrap' => 'physical'));
        $tag->addContent("Lot's of text that should be wrapped");
        $this->assertEqual(
                $tag->getDefault(),
                "Lot's of\r\ntext that\r\nshould be\r\nwrapped");
        $tag->setValue("New long text\r\nwith two lines");
        $this->assertEqual(
                $tag->getValue(),
                "New long\r\ntext\r\nwith two\r\nlines");
    }
    
    function testWrappingRemovesLeadingcariageReturn() {
        $tag = new SimpleTextAreaTag(array('cols' => '20', 'wrap' => 'physical'));
        $tag->addContent("\rStuff");
        $this->assertEquals('Stuff', $tag->getDefault());
        $tag->setValue("\nNew stuff\n");
        $this->assertEquals("New stuff\r\n", $tag->getValue());
    }
    
    function testBreaksAreNewlineAndCarriageReturn() {
        $tag = new SimpleTextAreaTag(array('cols' => '10'));
        $tag->addContent("Some\nText\rwith\r\nbreaks");
        $this->assertEquals("Some\r\nText\r\nwith\r\nbreaks", $tag->getValue());
    }
}

class TestOfCheckbox extends UnitTestCase {
    
    function testCanSetCheckboxToNamedValueWithBooleanTrue() {
        $tag = new SimpleCheckboxTag(array('name' => 'a', 'value' => 'A'));
        $this->assertFalse($tag->getValue());
        $tag->setValue(true);
        $this->assertIdentical($tag->getValue(), 'A');
    }
}

class TestOfSelection extends UnitTestCase {
    
    function testEmpty() {
        $tag = new SimpleSelectionTag(array('name' => 'a'));
        $this->assertIdentical($tag->getValue(), '');
    }
    
    function testSingle() {
        $tag = new SimpleSelectionTag(array('name' => 'a'));
        $option = new SimpleOptionTag(array());
        $option->addContent('AAA');
        $tag->addTag($option);
        $this->assertEquals('AAA', $tag->getValue());
    }
    
    function testSingleDefault() {
        $tag = new SimpleSelectionTag(array('name' => 'a'));
        $option = new SimpleOptionTag(array('selected' => ''));
        $option->addContent('AAA');
        $tag->addTag($option);
        $this->assertEquals('AAA', $tag->getValue());
    }
    
    function testSingleMappedDefault() {
        $tag = new SimpleSelectionTag(array('name' => 'a'));
        $option = new SimpleOptionTag(array('selected' => '', 'value' => 'aaa'));
        $option->addContent('AAA');
        $tag->addTag($option);
        $this->assertEquals('aaa', $tag->getValue());
    }
    
    function testStartsWithDefault() {
        $tag = new SimpleSelectionTag(array('name' => 'a'));
        $a = new SimpleOptionTag(array());
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag(array('selected' => ''));
        $b->addContent('BBB');
        $tag->addTag($b);
        $c = new SimpleOptionTag(array());
        $c->addContent('CCC');
        $tag->addTag($c);
        $this->assertEquals('BBB', $tag->getValue());
    }
    
    function testSettingOption() {
        $tag = new SimpleSelectionTag(array('name' => 'a'));
        $a = new SimpleOptionTag(array());
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag(array('selected' => ''));
        $b->addContent('BBB');
        $tag->addTag($b);
        $c = new SimpleOptionTag(array());
        $c->addContent('CCC');
        $tag->setValue('AAA');
        $this->assertEquals('AAA', $tag->getValue());
    }
    
    function testSettingMappedOption() {
        $tag = new SimpleSelectionTag(array('name' => 'a'));
        $a = new SimpleOptionTag(array('value' => 'aaa'));
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag(array('value' => 'bbb', 'selected' => ''));
        $b->addContent('BBB');
        $tag->addTag($b);
        $c = new SimpleOptionTag(array('value' => 'ccc'));
        $c->addContent('CCC');
        $tag->addTag($c);
        $tag->setValue('AAA');
        $this->assertEquals('aaa', $tag->getValue());
        $tag->setValue('ccc');
        $this->assertEquals('ccc', $tag->getValue());
    }
    
    function testSelectionDespiteSpuriousWhitespace() {
        $tag = new SimpleSelectionTag(array('name' => 'a'));
        $a = new SimpleOptionTag(array());
        $a->addContent(' AAA ');
        $tag->addTag($a);
        $b = new SimpleOptionTag(array('selected' => ''));
        $b->addContent(' BBB ');
        $tag->addTag($b);
        $c = new SimpleOptionTag(array());
        $c->addContent(' CCC ');
        $tag->addTag($c);
        $this->assertEquals(' BBB ', $tag->getValue());
        $tag->setValue('AAA');
        $this->assertEquals(' AAA ', $tag->getValue());
    }
    
    function testFailToSetIllegalOption() {
        $tag = new SimpleSelectionTag(array('name' => 'a'));
        $a = new SimpleOptionTag(array());
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag(array('selected' => ''));
        $b->addContent('BBB');
        $tag->addTag($b);
        $c = new SimpleOptionTag(array());
        $c->addContent('CCC');
        $tag->addTag($c);
        $this->assertFalse($tag->setValue('Not present'));
        $this->assertEquals('BBB', $tag->getValue());
    }
    
    function testNastyOptionValuesThatLookLikeFalse() {
        $tag = new SimpleSelectionTag(array('name' => 'a'));
        $a = new SimpleOptionTag(array('value' => '1'));
        $a->addContent('One');
        $tag->addTag($a);
        $b = new SimpleOptionTag(array('value' => '0'));
        $b->addContent('Zero');
        $tag->addTag($b);
        $this->assertIdentical($tag->getValue(), '1');
        $tag->setValue('Zero');
        $this->assertIdentical($tag->getValue(), '0');
    }
    
    function testBlankOption() {
        $tag = new SimpleSelectionTag(array('name' => 'A'));
        $a = new SimpleOptionTag(array());
        $tag->addTag($a);
        $b = new SimpleOptionTag(array());
        $b->addContent('b');
        $tag->addTag($b);
        $this->assertIdentical($tag->getValue(), '');
        $tag->setValue('b');
        $this->assertIdentical($tag->getValue(), 'b');
        $tag->setValue('');
        $this->assertIdentical($tag->getValue(), '');
    }
    
    function testMultipleDefaultWithNoSelections() {
        $tag = new MultipleSelectionTag(array('name' => 'a', 'multiple' => ''));
        $a = new SimpleOptionTag(array());
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag(array());
        $b->addContent('BBB');
        $tag->addTag($b);
        $this->assertIdentical($tag->getDefault(), array());
        $this->assertIdentical($tag->getValue(), array());
    }
    
    function testMultipleDefaultWithSelections() {
        $tag = new MultipleSelectionTag(array('name' => 'a', 'multiple' => ''));
        $a = new SimpleOptionTag(array('selected' => ''));
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag(array('selected' => ''));
        $b->addContent('BBB');
        $tag->addTag($b);
        $this->assertIdentical($tag->getDefault(), array('AAA', 'BBB'));
        $this->assertIdentical($tag->getValue(), array('AAA', 'BBB'));
    }
    
    function testSettingMultiple() {
        $tag = new MultipleSelectionTag(array('name' => 'a', 'multiple' => ''));
        $a = new SimpleOptionTag(array('selected' => ''));
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag(array());
        $b->addContent('BBB');
        $tag->addTag($b);
        $c = new SimpleOptionTag(array('selected' => '', 'value' => 'ccc'));
        $c->addContent('CCC');
        $tag->addTag($c);
        $this->assertIdentical($tag->getDefault(), array('AAA', 'ccc'));
        $this->assertTrue($tag->setValue(array('BBB', 'ccc')));
        $this->assertIdentical($tag->getValue(), array('BBB', 'ccc'));
        $this->assertTrue($tag->setValue(array()));
        $this->assertIdentical($tag->getValue(), array());
    }
    
    function testFailToSetIllegalOptionsInMultiple() {
        $tag = new MultipleSelectionTag(array('name' => 'a', 'multiple' => ''));
        $a = new SimpleOptionTag(array('selected' => ''));
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag(array());
        $b->addContent('BBB');
        $tag->addTag($b);
        $this->assertFalse($tag->setValue(array('CCC')));
        $this->assertTrue($tag->setValue(array('AAA', 'BBB')));
        $this->assertFalse($tag->setValue(array('AAA', 'CCC')));
    }
}

class TestOfRadioGroup extends UnitTestCase {
    
    function testEmptyGroup() {
        $group = new SimpleRadioGroup();
        $this->assertIdentical($group->getDefault(), false);
        $this->assertIdentical($group->getValue(), false);
        $this->assertFalse($group->setValue('a'));
    }
    
    function testReadingSingleButtonGroup() {
        $group = new SimpleRadioGroup();
        $group->addWidget(new SimpleRadioButtonTag(
                array('value' => 'A', 'checked' => '')));
        $this->assertIdentical($group->getDefault(), 'A');
        $this->assertIdentical($group->getValue(), 'A');
    }
    
    function testReadingMultipleButtonGroup() {
        $group = new SimpleRadioGroup();
        $group->addWidget(new SimpleRadioButtonTag(
                array('value' => 'A')));
        $group->addWidget(new SimpleRadioButtonTag(
                array('value' => 'B', 'checked' => '')));
        $this->assertIdentical($group->getDefault(), 'B');
        $this->assertIdentical($group->getValue(), 'B');
    }
    
    function testFailToSetUnlistedValue() {
        $group = new SimpleRadioGroup();
        $group->addWidget(new SimpleRadioButtonTag(array('value' => 'z')));
        $this->assertFalse($group->setValue('a'));
        $this->assertIdentical($group->getValue(), false);
    }
    
    function testSettingNewValueClearsTheOldOne() {
        $group = new SimpleRadioGroup();
        $group->addWidget(new SimpleRadioButtonTag(
                array('value' => 'A')));
        $group->addWidget(new SimpleRadioButtonTag(
                array('value' => 'B', 'checked' => '')));
        $this->assertTrue($group->setValue('A'));
        $this->assertIdentical($group->getValue(), 'A');
    }
    
    function testIsIdMatchesAnyWidgetInSet() {
        $group = new SimpleRadioGroup();
        $group->addWidget(new SimpleRadioButtonTag(
                array('value' => 'A', 'id' => 'i1')));
        $group->addWidget(new SimpleRadioButtonTag(
                array('value' => 'B', 'id' => 'i2')));
        $this->assertFalse($group->isId('i0'));
        $this->assertTrue($group->isId('i1'));
        $this->assertTrue($group->isId('i2'));
    }
    
    function testIsLabelMatchesAnyWidgetInSet() {
        $group = new SimpleRadioGroup();
        $button1 = new SimpleRadioButtonTag(array('value' => 'A'));
        $button1->setLabel('one');
        $group->addWidget($button1);
        $button2 = new SimpleRadioButtonTag(array('value' => 'B'));
        $button2->setLabel('two');
        $group->addWidget($button2);
        $this->assertFalse($group->isLabel('three'));
        $this->assertTrue($group->isLabel('one'));
        $this->assertTrue($group->isLabel('two'));
    }
}

class TestOfTagGroup extends UnitTestCase {
    
    function testReadingMultipleCheckboxGroup() {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag(array('value' => 'A')));
        $group->addWidget(new SimpleCheckboxTag(
                array('value' => 'B', 'checked' => '')));
        $this->assertIdentical($group->getDefault(), 'B');
        $this->assertIdentical($group->getValue(), 'B');
    }
    
    function testReadingMultipleUncheckedItems() {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag(array('value' => 'A')));
        $group->addWidget(new SimpleCheckboxTag(array('value' => 'B')));            
        $this->assertIdentical($group->getDefault(), false);
        $this->assertIdentical($group->getValue(), false);
    }
    
    function testReadingMultipleCheckedItems() {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag(
                array('value' => 'A', 'checked' => '')));
        $group->addWidget(new SimpleCheckboxTag(
                array('value' => 'B', 'checked' => '')));
        $this->assertIdentical($group->getDefault(), array('A', 'B'));
        $this->assertIdentical($group->getValue(), array('A', 'B'));
    }
    
    function testSettingSingleValue() {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag(array('value' => 'A')));
        $group->addWidget(new SimpleCheckboxTag(array('value' => 'B')));
        $this->assertTrue($group->setValue('A'));
        $this->assertIdentical($group->getValue(), 'A');
        $this->assertTrue($group->setValue('B'));
        $this->assertIdentical($group->getValue(), 'B');
    }
    
    function testSettingMultipleValues() {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag(array('value' => 'A')));
        $group->addWidget(new SimpleCheckboxTag(array('value' => 'B')));
        $this->assertTrue($group->setValue(array('A', 'B')));
        $this->assertIdentical($group->getValue(), array('A', 'B'));
    }
    
    function testSettingNoValue() {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag(array('value' => 'A')));
        $group->addWidget(new SimpleCheckboxTag(array('value' => 'B')));
        $this->assertTrue($group->setValue(false));
        $this->assertIdentical($group->getValue(), false);
    }
    
    function testIsIdMatchesAnyIdInSet() {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag(array('id' => 1, 'value' => 'A')));
        $group->addWidget(new SimpleCheckboxTag(array('id' => 2, 'value' => 'B')));
        $this->assertFalse($group->isId(0));
        $this->assertTrue($group->isId(1));
        $this->assertTrue($group->isId(2));
    }
}

class TestOfUploadWidget extends UnitTestCase {
    
    function testValueIsFilePath() {
        $upload = new SimpleUploadTag(array('name' => 'a'));
        $upload->setValue(dirname(__FILE__) . '/support/upload_sample.txt');
        $this->assertEquals(dirname(__FILE__) . '/support/upload_sample.txt', $upload->getValue());
    }
    
    function testSubmitsFileContents() {
        $encoding = new MockSimpleMultipartEncoding();
        $encoding->expectOnce('attach', array(
                'a',
                'Sample for testing file upload',
                'upload_sample.txt'));
        $upload = new SimpleUploadTag(array('name' => 'a'));
        $upload->setValue(dirname(__FILE__) . '/support/upload_sample.txt');
        $upload->write($encoding);
    }
}

class TestOfLabelTag extends UnitTestCase {
    
    function testLabelShouldHaveAnEndTag() {
        $label = new SimpleLabelTag(array());
        $this->assertTrue($label->expectEndTag());
    }
    
    function testContentIsTextOnly() {
        $label = new SimpleLabelTag(array());
        $label->addContent('Here <tag>are</tag> words');
        $this->assertEquals('Here are words', $label->getText());
    }
}
?>