<?php
// $Id: browser_test.php 1964 2009-10-13 15:27:31Z maetl_ $
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../browser.php');
require_once(dirname(__FILE__) . '/../user_agent.php');
require_once(dirname(__FILE__) . '/../http.php');
require_once(dirname(__FILE__) . '/../page.php');
require_once(dirname(__FILE__) . '/../encoding.php');

Mock::generate('SimpleHttpResponse');
Mock::generate('SimplePage');
Mock::generate('SimpleForm');
Mock::generate('SimpleUserAgent');
Mock::generatePartial(
        'SimpleBrowser',
        'MockParseSimpleBrowser',
        array('createUserAgent', 'parse'));
Mock::generatePartial(
        'SimpleBrowser',
        'MockUserAgentSimpleBrowser',
        array('createUserAgent'));

class TestOfHistory extends UnitTestCase {

    function testEmptyHistoryHasFalseContents() {
        $history = new SimpleBrowserHistory();
        $this->assertIdentical($history->getUrl(), false);
        $this->assertIdentical($history->getParameters(), false);
    }

    function testCannotMoveInEmptyHistory() {
        $history = new SimpleBrowserHistory();
        $this->assertFalse($history->back());
        $this->assertFalse($history->forward());
    }

    function testCurrentTargetAccessors() {
        $history = new SimpleBrowserHistory();
        $history->recordEntry(
                new SimpleUrl('http://www.here.com/'),
                new SimpleGetEncoding());
        $this->assertIdentical($history->getUrl(), new SimpleUrl('http://www.here.com/'));
        $this->assertIdentical($history->getParameters(), new SimpleGetEncoding());
    }

    function testSecondEntryAccessors() {
        $history = new SimpleBrowserHistory();
        $history->recordEntry(
                new SimpleUrl('http://www.first.com/'),
                new SimpleGetEncoding());
        $history->recordEntry(
                new SimpleUrl('http://www.second.com/'),
                new SimplePostEncoding(array('a' => 1)));
        $this->assertIdentical($history->getUrl(), new SimpleUrl('http://www.second.com/'));
        $this->assertIdentical(
                $history->getParameters(),
                new SimplePostEncoding(array('a' => 1)));
    }

    function testGoingBackwards() {
        $history = new SimpleBrowserHistory();
        $history->recordEntry(
                new SimpleUrl('http://www.first.com/'),
                new SimpleGetEncoding());
        $history->recordEntry(
                new SimpleUrl('http://www.second.com/'),
                new SimplePostEncoding(array('a' => 1)));
        $this->assertTrue($history->back());
        $this->assertIdentical($history->getUrl(), new SimpleUrl('http://www.first.com/'));
        $this->assertIdentical($history->getParameters(), new SimpleGetEncoding());
    }

    function testGoingBackwardsOffBeginning() {
        $history = new SimpleBrowserHistory();
        $history->recordEntry(
                new SimpleUrl('http://www.first.com/'),
                new SimpleGetEncoding());
        $this->assertFalse($history->back());
        $this->assertIdentical($history->getUrl(), new SimpleUrl('http://www.first.com/'));
        $this->assertIdentical($history->getParameters(), new SimpleGetEncoding());
    }

    function testGoingForwardsOffEnd() {
        $history = new SimpleBrowserHistory();
        $history->recordEntry(
                new SimpleUrl('http://www.first.com/'),
                new SimpleGetEncoding());
        $this->assertFalse($history->forward());
        $this->assertIdentical($history->getUrl(), new SimpleUrl('http://www.first.com/'));
        $this->assertIdentical($history->getParameters(), new SimpleGetEncoding());
    }

    function testGoingBackwardsAndForwards() {
        $history = new SimpleBrowserHistory();
        $history->recordEntry(
                new SimpleUrl('http://www.first.com/'),
                new SimpleGetEncoding());
        $history->recordEntry(
                new SimpleUrl('http://www.second.com/'),
                new SimplePostEncoding(array('a' => 1)));
        $this->assertTrue($history->back());
        $this->assertTrue($history->forward());
        $this->assertIdentical($history->getUrl(), new SimpleUrl('http://www.second.com/'));
        $this->assertIdentical(
                $history->getParameters(),
                new SimplePostEncoding(array('a' => 1)));
    }

    function testNewEntryReplacesNextOne() {
        $history = new SimpleBrowserHistory();
        $history->recordEntry(
                new SimpleUrl('http://www.first.com/'),
                new SimpleGetEncoding());
        $history->recordEntry(
                new SimpleUrl('http://www.second.com/'),
                new SimplePostEncoding(array('a' => 1)));
        $history->back();
        $history->recordEntry(
                new SimpleUrl('http://www.third.com/'),
                new SimpleGetEncoding());
        $this->assertIdentical($history->getUrl(), new SimpleUrl('http://www.third.com/'));
        $this->assertIdentical($history->getParameters(), new SimpleGetEncoding());
    }

    function testNewEntryDropsFutureEntries() {
        $history = new SimpleBrowserHistory();
        $history->recordEntry(
                new SimpleUrl('http://www.first.com/'),
                new SimpleGetEncoding());
        $history->recordEntry(
                new SimpleUrl('http://www.second.com/'),
                new SimpleGetEncoding());
        $history->recordEntry(
                new SimpleUrl('http://www.third.com/'),
                new SimpleGetEncoding());
        $history->back();
        $history->back();
        $history->recordEntry(
                new SimpleUrl('http://www.fourth.com/'),
                new SimpleGetEncoding());
        $this->assertIdentical($history->getUrl(), new SimpleUrl('http://www.fourth.com/'));
        $this->assertFalse($history->forward());
        $history->back();
        $this->assertIdentical($history->getUrl(), new SimpleUrl('http://www.first.com/'));
        $this->assertFalse($history->back());
    }
}

class TestOfParsedPageAccess extends UnitTestCase {

    function loadPage(&$page) {
        $response = new MockSimpleHttpResponse($this);
        $agent = new MockSimpleUserAgent($this);
        $agent->returns('fetchResponse', $response);

        $browser = new MockParseSimpleBrowser($this);
        $browser->returns('createUserAgent', $agent);
        $browser->returns('parse', $page);
        $browser->__construct();

        $browser->get('http://this.com/page.html');
        return $browser;
    }

    function testAccessorsWhenNoPage() {
        $agent = new MockSimpleUserAgent($this);
        $browser = new MockParseSimpleBrowser($this);
        $browser->returns('createUserAgent', $agent);
        $browser->__construct();
        $this->assertEquals('', $browser->getContent());
    }

    function testParse() {
        $page = new MockSimplePage();
        $page->setReturnValue('getRequest', "GET here.html\r\n\r\n");
        $page->setReturnValue('getRaw', 'Raw HTML');
        $page->setReturnValue('getTitle', 'Here');
        $page->setReturnValue('getFrameFocus', 'Frame');
        $page->setReturnValue('getMimeType', 'text/html');
        $page->setReturnValue('getResponseCode', 200);
        $page->setReturnValue('getAuthentication', 'Basic');
        $page->setReturnValue('getRealm', 'Somewhere');
        $page->setReturnValue('getTransportError', 'Ouch!');

        $browser = $this->loadPage($page);
        $this->assertEquals("GET here.html\r\n\r\n", $browser->getRequest());
        $this->assertEquals('Raw HTML', $browser->getContent());
        $this->assertEquals('Here', $browser->getTitle());
        $this->assertEquals('Frame', $browser->getFrameFocus());
        $this->assertIdentical($browser->getResponseCode(), 200);
        $this->assertEquals('text/html', $browser->getMimeType());
        $this->assertEquals('Basic', $browser->getAuthentication());
        $this->assertEquals('Somewhere', $browser->getRealm());
        $this->assertEquals('Ouch!', $browser->getTransportError());
    }

    function testLinkAffirmationWhenPresent() {
        $page = new MockSimplePage();
        $page->setReturnValue('getUrlsByLabel', array('http://www.nowhere.com'));
        $page->expectOnce('getUrlsByLabel', array('a link label'));
        $browser = $this->loadPage($page);
        $this->assertIdentical($browser->getLink('a link label'), 'http://www.nowhere.com');
    }

    function testLinkAffirmationByIdWhenPresent() {
        $page = new MockSimplePage();
        $page->setReturnValue('getUrlById', 'a_page.com', array(99));
        $page->setReturnValue('getUrlById', false, array('*'));
        $browser = $this->loadPage($page);
        $this->assertIdentical($browser->getLinkById(99), 'a_page.com');
        $this->assertFalse($browser->getLinkById(98));
    }

    function testSettingFieldIsPassedToPage() {
        $page = new MockSimplePage();
        $page->expectOnce('setField', array(new SimpleByLabelOrName('key'), 'Value', false));
        $page->setReturnValue('getField', 'Value');
        $browser = $this->loadPage($page);
        $this->assertEquals('Value', $browser->getField('key'));
        $browser->setField('key', 'Value');
    }
}

class TestOfBrowserNavigation extends UnitTestCase {
    function createBrowser($agent, $page) {
        $browser = new MockParseSimpleBrowser();
        $browser->returns('createUserAgent', $agent);
        $browser->returns('parse', $page);
        $browser->__construct();
        return $browser;
    }

    function testBrowserRequestMethods() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());
        $agent->expectAt(
                0,
                'fetchResponse',
                array(new SimpleUrl('http://this.com/get.req'), new SimpleGetEncoding()));
        $agent->expectAt(
                1,
                'fetchResponse',
                array(new SimpleUrl('http://this.com/post.req'), new SimplePostEncoding()));
        $agent->expectAt(
                2,
                'fetchResponse',
                array(new SimpleUrl('http://this.com/put.req'), new SimplePutEncoding()));
        $agent->expectAt(
                3,
                'fetchResponse',
                array(new SimpleUrl('http://this.com/delete.req'), new SimpleDeleteEncoding()));
        $agent->expectAt(
                4,
                'fetchResponse',
                array(new SimpleUrl('http://this.com/head.req'), new SimpleHeadEncoding()));                               
        $agent->expectCallCount('fetchResponse', 5);

        $page = new MockSimplePage();

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/get.req');
        $browser->post('http://this.com/post.req');
        $browser->put('http://this.com/put.req');
        $browser->delete('http://this.com/delete.req');
        $browser->head('http://this.com/head.req');
    }  
    
    function testClickLinkRequestsPage() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());
        $agent->expectAt(
                0,
                'fetchResponse',
                array(new SimpleUrl('http://this.com/page.html'), new SimpleGetEncoding()));
        $agent->expectAt(
                1,
                'fetchResponse',
                array(new SimpleUrl('http://this.com/new.html'), new SimpleGetEncoding()));
        $agent->expectCallCount('fetchResponse', 2);

        $page = new MockSimplePage();
        $page->setReturnValue('getUrlsByLabel', array(new SimpleUrl('http://this.com/new.html')));
        $page->expectOnce('getUrlsByLabel', array('New'));
        $page->setReturnValue('getRaw', 'A page');

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/page.html');
        $this->assertTrue($browser->clickLink('New'));
    }

    function testClickLinkWithUnknownFrameStillRequestsWholePage() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());
        $agent->expectAt(
                0,
                'fetchResponse',
                array(new SimpleUrl('http://this.com/page.html'), new SimpleGetEncoding()));
        $target = new SimpleUrl('http://this.com/new.html');
        $target->setTarget('missing');
        $agent->expectAt(
                1,
                'fetchResponse',
                array($target, new SimpleGetEncoding()));
        $agent->expectCallCount('fetchResponse', 2);

        $parsed_url = new SimpleUrl('http://this.com/new.html');
        $parsed_url->setTarget('missing');

        $page = new MockSimplePage();
        $page->setReturnValue('getUrlsByLabel', array($parsed_url));
        $page->setReturnValue('hasFrames', false);
        $page->expectOnce('getUrlsByLabel', array('New'));
        $page->setReturnValue('getRaw', 'A page');

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/page.html');
        $this->assertTrue($browser->clickLink('New'));
    }

    function testClickingMissingLinkFails() {
        $agent = new MockSimpleUserAgent($this);
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());

        $page = new MockSimplePage();
        $page->setReturnValue('getUrlsByLabel', array());
        $page->setReturnValue('getRaw', 'stuff');

        $browser = $this->createBrowser($agent, $page);
        $this->assertTrue($browser->get('http://this.com/page.html'));
        $this->assertFalse($browser->clickLink('New'));
    }

    function testClickIndexedLink() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());
        $agent->expectAt(
                1,
                'fetchResponse',
                array(new SimpleUrl('1.html'), new SimpleGetEncoding()));
        $agent->expectCallCount('fetchResponse', 2);

        $page = new MockSimplePage();
        $page->setReturnValue(
                'getUrlsByLabel',
                array(new SimpleUrl('0.html'), new SimpleUrl('1.html')));
        $page->setReturnValue('getRaw', 'A page');

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/page.html');
        $this->assertTrue($browser->clickLink('New', 1));
    }

    function testClinkLinkById() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());
        $agent->expectAt(1, 'fetchResponse', array(
                new SimpleUrl('http://this.com/link.html'),
                new SimpleGetEncoding()));
        $agent->expectCallCount('fetchResponse', 2);

        $page = new MockSimplePage();
        $page->setReturnValue('getUrlById', new SimpleUrl('http://this.com/link.html'));
        $page->expectOnce('getUrlById', array(2));
        $page->setReturnValue('getRaw', 'A page');

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/page.html');
        $this->assertTrue($browser->clickLinkById(2));
    }

    function testClickingMissingLinkIdFails() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());

        $page = new MockSimplePage();
        $page->setReturnValue('getUrlById', false);

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/page.html');
        $this->assertFalse($browser->clickLink(0));
    }

    function testSubmitFormByLabel() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());
        $agent->expectAt(1, 'fetchResponse', array(
                new SimpleUrl('http://this.com/handler.html'),
                new SimplePostEncoding(array('a' => 'A'))));
        $agent->expectCallCount('fetchResponse', 2);

        $form = new MockSimpleForm();
        $form->setReturnValue('getAction', new SimpleUrl('http://this.com/handler.html'));
        $form->setReturnValue('getMethod', 'post');
        $form->setReturnValue('submitButton', new SimplePostEncoding(array('a' => 'A')));
        $form->expectOnce('submitButton', array(new SimpleByLabel('Go'), false));

        $page = new MockSimplePage();
        $page->returns('getFormBySubmit', $form);
        $page->expectOnce('getFormBySubmit', array(new SimpleByLabel('Go')));
        $page->setReturnValue('getRaw', 'stuff');

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/page.html');
        $this->assertTrue($browser->clickSubmit('Go'));
    }

    function testDefaultSubmitFormByLabel() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());
        $agent->expectAt(1,  'fetchResponse', array(
                new SimpleUrl('http://this.com/page.html'),
                new SimpleGetEncoding(array('a' => 'A'))));
        $agent->expectCallCount('fetchResponse', 2);

        $form = new MockSimpleForm();
        $form->setReturnValue('getAction', new SimpleUrl('http://this.com/page.html'));
        $form->setReturnValue('getMethod', 'get');
        $form->setReturnValue('submitButton', new SimpleGetEncoding(array('a' => 'A')));

        $page = new MockSimplePage();
        $page->returns('getFormBySubmit', $form);
        $page->expectOnce('getFormBySubmit', array(new SimpleByLabel('Submit')));
        $page->setReturnValue('getRaw', 'stuff');
        $page->setReturnValue('getUrl', new SimpleUrl('http://this.com/page.html'));

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/page.html');
        $this->assertTrue($browser->clickSubmit());
    }

    function testSubmitFormByName() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());

        $form = new MockSimpleForm();
        $form->setReturnValue('getAction', new SimpleUrl('http://this.com/handler.html'));
        $form->setReturnValue('getMethod', 'post');
        $form->setReturnValue('submitButton', new SimplePostEncoding(array('a' => 'A')));

        $page = new MockSimplePage();
        $page->returns('getFormBySubmit', $form);
        $page->expectOnce('getFormBySubmit', array(new SimpleByName('me')));
        $page->setReturnValue('getRaw', 'stuff');

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/page.html');
        $this->assertTrue($browser->clickSubmitByName('me'));
    }

    function testSubmitFormById() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());

        $form = new MockSimpleForm();
        $form->setReturnValue('getAction', new SimpleUrl('http://this.com/handler.html'));
        $form->setReturnValue('getMethod', 'post');
        $form->setReturnValue('submitButton', new SimplePostEncoding(array('a' => 'A')));
        $form->expectOnce('submitButton', array(new SimpleById(99), false));

        $page = new MockSimplePage();
        $page->returns('getFormBySubmit', $form);
        $page->expectOnce('getFormBySubmit', array(new SimpleById(99)));
        $page->setReturnValue('getRaw', 'stuff');

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/page.html');
        $this->assertTrue($browser->clickSubmitById(99));
    }

    function testSubmitFormByImageLabel() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());

        $form = new MockSimpleForm();
        $form->setReturnValue('getAction', new SimpleUrl('http://this.com/handler.html'));
        $form->setReturnValue('getMethod', 'post');
        $form->setReturnValue('submitImage', new SimplePostEncoding(array('a' => 'A')));
        $form->expectOnce('submitImage', array(new SimpleByLabel('Go!'), 10, 11, false));

        $page = new MockSimplePage();
        $page->returns('getFormByImage', $form);
        $page->expectOnce('getFormByImage', array(new SimpleByLabel('Go!')));
        $page->setReturnValue('getRaw', 'stuff');

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/page.html');
        $this->assertTrue($browser->clickImage('Go!', 10, 11));
    }

    function testSubmitFormByImageName() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());

        $form = new MockSimpleForm();
        $form->setReturnValue('getAction', new SimpleUrl('http://this.com/handler.html'));
        $form->setReturnValue('getMethod', 'post');
        $form->setReturnValue('submitImage', new SimplePostEncoding(array('a' => 'A')));
        $form->expectOnce('submitImage', array(new SimpleByName('a'), 10, 11, false));

        $page = new MockSimplePage();
        $page->returns('getFormByImage', $form);
        $page->expectOnce('getFormByImage', array(new SimpleByName('a')));
        $page->setReturnValue('getRaw', 'stuff');

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/page.html');
        $this->assertTrue($browser->clickImageByName('a', 10, 11));
    }

    function testSubmitFormByImageId() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());

        $form = new MockSimpleForm();
        $form->setReturnValue('getAction', new SimpleUrl('http://this.com/handler.html'));
        $form->setReturnValue('getMethod', 'post');
        $form->setReturnValue('submitImage', new SimplePostEncoding(array('a' => 'A')));
        $form->expectOnce('submitImage', array(new SimpleById(99), 10, 11, false));

        $page = new MockSimplePage();
        $page->returns('getFormByImage', $form);
        $page->expectOnce('getFormByImage', array(new SimpleById(99)));
        $page->setReturnValue('getRaw', 'stuff');

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/page.html');
        $this->assertTrue($browser->clickImageById(99, 10, 11));
    }

    function testSubmitFormByFormId() {
        $agent = new MockSimpleUserAgent();
        $agent->returns('fetchResponse', new MockSimpleHttpResponse());
        $agent->expectAt(1, 'fetchResponse', array(
                new SimpleUrl('http://this.com/handler.html'),
                new SimplePostEncoding(array('a' => 'A'))));
        $agent->expectCallCount('fetchResponse', 2);

        $form = new MockSimpleForm();
        $form->setReturnValue('getAction', new SimpleUrl('http://this.com/handler.html'));
        $form->setReturnValue('getMethod', 'post');
        $form->setReturnValue('submit', new SimplePostEncoding(array('a' => 'A')));

        $page = new MockSimplePage();
        $page->returns('getFormById', $form);
        $page->expectOnce('getFormById', array(33));
        $page->setReturnValue('getRaw', 'stuff');

        $browser = $this->createBrowser($agent, $page);
        $browser->get('http://this.com/page.html');
        $this->assertTrue($browser->submitFormById(33));
    }
}

class TestOfBrowserFrames extends UnitTestCase {

    function createBrowser($agent) {
        $browser = new MockUserAgentSimpleBrowser();
        $browser->returns('createUserAgent', $agent);
        $browser->__construct();
        return $browser;
    }

    function createUserAgent($pages) {
        $agent = new MockSimpleUserAgent();
        foreach ($pages as $url => $raw) {
            $url = new SimpleUrl($url);
            $response = new MockSimpleHttpResponse();
            $response->setReturnValue('getUrl', $url);
            $response->setReturnValue('getContent', $raw);
            $agent->returns('fetchResponse', $response, array($url, '*'));
        }
        return $agent;
    }

    function testSimplePageHasNoFrames() {
        $browser = $this->createBrowser($this->createUserAgent(
                array('http://site.with.no.frames/' => 'A non-framed page')));
        $this->assertEqual(
                $browser->get('http://site.with.no.frames/'),
                'A non-framed page');
        $this->assertIdentical($browser->getFrames(), 'http://site.with.no.frames/');
    }

    function testFramesetWithSingleFrame() {
        $frameset = '<frameset><frame name="a" src="frame.html"></frameset>';
        $browser = $this->createBrowser($this->createUserAgent(array(
                'http://site.with.one.frame/' => $frameset,
                'http://site.with.one.frame/frame.html' => 'A frame')));
        $this->assertEquals('A frame', $browser->get('http://site.with.one.frame/'));
        $this->assertIdentical(
                $browser->getFrames(),
                array('a' => 'http://site.with.one.frame/frame.html'));
    }

    function testTitleTakenFromFramesetPage() {
        $frameset = '<title>Frameset title</title>' .
                '<frameset><frame name="a" src="frame.html"></frameset>';
        $browser = $this->createBrowser($this->createUserAgent(array(
                'http://site.with.one.frame/' => $frameset,
                'http://site.with.one.frame/frame.html' => '<title>Page title</title>')));
        $browser->get('http://site.with.one.frame/');
        $this->assertEquals('Frameset title', $browser->getTitle());
    }

    function testFramesetWithSingleUnnamedFrame() {
        $frameset = '<frameset><frame src="frame.html"></frameset>';
        $browser = $this->createBrowser($this->createUserAgent(array(
                'http://site.with.one.frame/' => $frameset,
                'http://site.with.one.frame/frame.html' => 'One frame')));
        $this->assertEqual(
                $browser->get('http://site.with.one.frame/'),
                'One frame');
        $this->assertIdentical(
                $browser->getFrames(),
                array(1 => 'http://site.with.one.frame/frame.html'));
    }

    function testFramesetWithMultipleFrames() {
        $frameset = '<frameset>' .
                '<frame name="a" src="frame_a.html">' .
                '<frame name="b" src="frame_b.html">' .
                '<frame name="c" src="frame_c.html">' .
                '</frameset>';
        $browser = $this->createBrowser($this->createUserAgent(array(
                'http://site.with.frames/' => $frameset,
                'http://site.with.frames/frame_a.html' => 'A frame',
                'http://site.with.frames/frame_b.html' => 'B frame',
                'http://site.with.frames/frame_c.html' => 'C frame')));
        $this->assertEqual(
                $browser->get('http://site.with.frames/'),
                'A frameB frameC frame');
        $this->assertIdentical($browser->getFrames(), array(
                'a' => 'http://site.with.frames/frame_a.html',
                'b' => 'http://site.with.frames/frame_b.html',
                'c' => 'http://site.with.frames/frame_c.html'));
    }

    function testFrameFocusByName() {
        $frameset = '<frameset>' .
                '<frame name="a" src="frame_a.html">' .
                '<frame name="b" src="frame_b.html">' .
                '<frame name="c" src="frame_c.html">' .
                '</frameset>';
        $browser = $this->createBrowser($this->createUserAgent(array(
                'http://site.with.frames/' => $frameset,
                'http://site.with.frames/frame_a.html' => 'A frame',
                'http://site.with.frames/frame_b.html' => 'B frame',
                'http://site.with.frames/frame_c.html' => 'C frame')));
        $browser->get('http://site.with.frames/');
        $browser->setFrameFocus('a');
        $this->assertEquals('A frame', $browser->getContent());
        $browser->setFrameFocus('b');
        $this->assertEquals('B frame', $browser->getContent());
        $browser->setFrameFocus('c');
        $this->assertEquals('C frame', $browser->getContent());
    }

    function testFramesetWithSomeNamedFrames() {
        $frameset = '<frameset>' .
                '<frame name="a" src="frame_a.html">' .
                '<frame src="frame_b.html">' .
                '<frame name="c" src="frame_c.html">' .
                '<frame src="frame_d.html">' .
                '</frameset>';
        $browser = $this->createBrowser($this->createUserAgent(array(
                'http://site.with.frames/' => $frameset,
                'http://site.with.frames/frame_a.html' => 'A frame',
                'http://site.with.frames/frame_b.html' => 'B frame',
                'http://site.with.frames/frame_c.html' => 'C frame',
                'http://site.with.frames/frame_d.html' => 'D frame')));
        $this->assertEqual(
                $browser->get('http://site.with.frames/'),
                'A frameB frameC frameD frame');
        $this->assertIdentical($browser->getFrames(), array(
                'a' => 'http://site.with.frames/frame_a.html',
                2 => 'http://site.with.frames/frame_b.html',
                'c' => 'http://site.with.frames/frame_c.html',
                4 => 'http://site.with.frames/frame_d.html'));
    }

    function testFrameFocusWithMixedNamesAndIndexes() {
        $frameset = '<frameset>' .
                '<frame name="a" src="frame_a.html">' .
                '<frame src="frame_b.html">' .
                '<frame name="c" src="frame_c.html">' .
                '<frame src="frame_d.html">' .
                '</frameset>';
        $browser = $this->createBrowser($this->createUserAgent(array(
                'http://site.with.frames/' => $frameset,
                'http://site.with.frames/frame_a.html' => 'A frame',
                'http://site.with.frames/frame_b.html' => 'B frame',
                'http://site.with.frames/frame_c.html' => 'C frame',
                'http://site.with.frames/frame_d.html' => 'D frame')));
        $browser->get('http://site.with.frames/');
        $browser->setFrameFocus('a');
        $this->assertEquals('A frame', $browser->getContent());
        $browser->setFrameFocus(2);
        $this->assertEquals('B frame', $browser->getContent());
        $browser->setFrameFocus('c');
        $this->assertEquals('C frame', $browser->getContent());
        $browser->setFrameFocus(4);
        $this->assertEquals('D frame', $browser->getContent());
        $browser->clearFrameFocus();
        $this->assertEquals('A frameB frameC frameD frame', $browser->getContent());
    }

    function testNestedFrameset() {
        $inner = '<frameset>' .
                '<frame name="page" src="page.html">' .
                '</frameset>';
        $outer = '<frameset>' .
                '<frame name="inner" src="inner.html">' .
                '</frameset>';
        $browser = $this->createBrowser($this->createUserAgent(array(
                'http://site.with.nested.frame/' => $outer,
                'http://site.with.nested.frame/inner.html' => $inner,
                'http://site.with.nested.frame/page.html' => 'The page')));
        $this->assertEqual(
                $browser->get('http://site.with.nested.frame/'),
                'The page');
        $this->assertIdentical($browser->getFrames(), array(
                'inner' => array(
                        'page' => 'http://site.with.nested.frame/page.html')));
    }

    function testCanNavigateToNestedFrame() {
        $inner = '<frameset>' .
                '<frame name="one" src="one.html">' .
                '<frame name="two" src="two.html">' .
                '</frameset>';
        $outer = '<frameset>' .
                '<frame name="inner" src="inner.html">' .
                '<frame name="three" src="three.html">' .
                '</frameset>';
        $browser = $this->createBrowser($this->createUserAgent(array(
                'http://site.with.nested.frames/' => $outer,
                'http://site.with.nested.frames/inner.html' => $inner,
                'http://site.with.nested.frames/one.html' => 'Page one',
                'http://site.with.nested.frames/two.html' => 'Page two',
                'http://site.with.nested.frames/three.html' => 'Page three')));

        $browser->get('http://site.with.nested.frames/');
        $this->assertEquals('Page onePage twoPage three', $browser->getContent());

        $this->assertTrue($browser->setFrameFocus('inner'));
        $this->assertEquals(array('inner'), $browser->getFrameFocus());
        $this->assertTrue($browser->setFrameFocus('one'));
        $this->assertEquals(array('inner', 'one'), $browser->getFrameFocus());
        $this->assertEquals('Page one', $browser->getContent());

        $this->assertTrue($browser->setFrameFocus('two'));
        $this->assertEquals(array('inner', 'two'), $browser->getFrameFocus());
        $this->assertEquals('Page two', $browser->getContent());

        $browser->clearFrameFocus();
        $this->assertTrue($browser->setFrameFocus('three'));
        $this->assertEquals(array('three'), $browser->getFrameFocus());
        $this->assertEquals('Page three', $browser->getContent());

        $this->assertTrue($browser->setFrameFocus('inner'));
        $this->assertEquals('Page onePage two', $browser->getContent());
    }

    function testCanNavigateToNestedFrameByIndex() {
        $inner = '<frameset>' .
                '<frame src="one.html">' .
                '<frame src="two.html">' .
                '</frameset>';
        $outer = '<frameset>' .
                '<frame src="inner.html">' .
                '<frame src="three.html">' .
                '</frameset>';
        $browser = $this->createBrowser($this->createUserAgent(array(
                'http://site.with.nested.frames/' => $outer,
                'http://site.with.nested.frames/inner.html' => $inner,
                'http://site.with.nested.frames/one.html' => 'Page one',
                'http://site.with.nested.frames/two.html' => 'Page two',
                'http://site.with.nested.frames/three.html' => 'Page three')));

        $browser->get('http://site.with.nested.frames/');
        $this->assertEquals('Page onePage twoPage three', $browser->getContent());

        $this->assertTrue($browser->setFrameFocusByIndex(1));
        $this->assertEquals(array(1), $browser->getFrameFocus());
        $this->assertTrue($browser->setFrameFocusByIndex(1));
        $this->assertEquals(array(1, 1), $browser->getFrameFocus());
        $this->assertEquals('Page one', $browser->getContent());

        $this->assertTrue($browser->setFrameFocusByIndex(2));
        $this->assertEquals(array(1, 2), $browser->getFrameFocus());
        $this->assertEquals('Page two', $browser->getContent());

        $browser->clearFrameFocus();
        $this->assertTrue($browser->setFrameFocusByIndex(2));
        $this->assertEquals(array(2), $browser->getFrameFocus());
        $this->assertEquals('Page three', $browser->getContent());

        $this->assertTrue($browser->setFrameFocusByIndex(1));
        $this->assertEquals('Page onePage two', $browser->getContent());
    }
}
?>