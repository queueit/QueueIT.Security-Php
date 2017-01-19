<?php
// $Id: url_test.php 1998 2010-07-27 09:55:55Z pp11 $
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../url.php');

class TestOfUrl extends UnitTestCase {
    
    function testDefaultUrl() {
        $url = new SimpleUrl('');
        $this->assertEquals('', $url->getScheme());
        $this->assertEquals('', $url->getHost());
        $this->assertEquals('http', $url->getScheme('http'));
        $this->assertEquals('localhost', $url->getHost('localhost'));
        $this->assertEquals('', $url->getPath());
    }
    
    function testBasicParsing() {
        $url = new SimpleUrl('https://www.lastcraft.com/test/');
        $this->assertEquals('https', $url->getScheme());
        $this->assertEquals('www.lastcraft.com', $url->getHost());
        $this->assertEquals('/test/', $url->getPath());
    }
    
    function testRelativeUrls() {
        $url = new SimpleUrl('../somewhere.php');
        $this->assertFalse($url->getScheme());
        $this->assertFalse($url->getHost());
        $this->assertEquals('../somewhere.php', $url->getPath());
    }
    
    function testParseBareParameter() {
        $url = new SimpleUrl('?a');
        $this->assertEquals('', $url->getPath());
        $this->assertEquals('?a', $url->getEncodedRequest());
        $url->addRequestParameter('x', 'X');
        $this->assertEquals('?a=&x=X', $url->getEncodedRequest());
    }
    
    function testParseEmptyParameter() {
        $url = new SimpleUrl('?a=');
        $this->assertEquals('', $url->getPath());
        $this->assertEquals('?a=', $url->getEncodedRequest());
        $url->addRequestParameter('x', 'X');
        $this->assertEquals('?a=&x=X', $url->getEncodedRequest());
    }
    
    function testParseParameterPair() {
        $url = new SimpleUrl('?a=A');
        $this->assertEquals('', $url->getPath());
        $this->assertEquals('?a=A', $url->getEncodedRequest());
        $url->addRequestParameter('x', 'X');
        $this->assertEquals('?a=A&x=X', $url->getEncodedRequest());
    }
    
    function testParseMultipleParameters() {
        $url = new SimpleUrl('?a=A&b=B');
        $this->assertEquals('?a=A&b=B', $url->getEncodedRequest());
        $url->addRequestParameter('x', 'X');
        $this->assertEquals('?a=A&b=B&x=X', $url->getEncodedRequest());
    }
    
    function testParsingParameterMixture() {
        $url = new SimpleUrl('?a=A&b=&c');
        $this->assertEquals('?a=A&b=&c', $url->getEncodedRequest());
        $url->addRequestParameter('x', 'X');
        $this->assertEquals('?a=A&b=&c=&x=X', $url->getEncodedRequest());
    }
    
    function testAddParametersFromScratch() {
        $url = new SimpleUrl('');
        $url->addRequestParameter('a', 'A');
        $this->assertEquals('?a=A', $url->getEncodedRequest());
        $url->addRequestParameter('b', 'B');
        $this->assertEquals('?a=A&b=B', $url->getEncodedRequest());
        $url->addRequestParameter('a', 'aaa');
        $this->assertEquals('?a=A&b=B&a=aaa', $url->getEncodedRequest());
    }
    
    function testClearingParameters() {
        $url = new SimpleUrl('');
        $url->addRequestParameter('a', 'A');
        $url->clearRequest();
        $this->assertIdentical($url->getEncodedRequest(), '');
    }
    
    function testEncodingParameters() {
        $url = new SimpleUrl('');
        $url->addRequestParameter('a', '?!"\'#~@[]{}:;<>,./|$%^&*()_+-=');
        $this->assertIdentical(
                $request = $url->getEncodedRequest(),
                '?a=%3F%21%22%27%23%7E%40%5B%5D%7B%7D%3A%3B%3C%3E%2C.%2F%7C%24%25%5E%26%2A%28%29_%2B-%3D');
    }
    
    function testDecodingParameters() {            
        $url = new SimpleUrl('?a=%3F%21%22%27%23%7E%40%5B%5D%7B%7D%3A%3B%3C%3E%2C.%2F%7C%24%25%5E%26%2A%28%29_%2B-%3D');
        $this->assertEqual(
                $url->getEncodedRequest(),
                '?a=' . urlencode('?!"\'#~@[]{}:;<>,./|$%^&*()_+-='));
    }
    
    function testUrlInQueryDoesNotConfuseParsing() {
        $url = new SimpleUrl('wibble/login.php?url=http://www.google.com/moo/');
        $this->assertFalse($url->getScheme());
        $this->assertFalse($url->getHost());
        $this->assertEquals('wibble/login.php', $url->getPath());
        $this->assertEquals('?url=http://www.google.com/moo/', $url->getEncodedRequest());
    }
    
    function testSettingCordinates() {
        $url = new SimpleUrl('');
        $url->setCoordinates('32', '45');
        $this->assertIdentical($url->getX(), 32);
        $this->assertIdentical($url->getY(), 45);
        $this->assertEquals('', $url->getEncodedRequest());
    }
    
    function testParseCordinates() {
        $url = new SimpleUrl('?32,45');
        $this->assertIdentical($url->getX(), 32);
        $this->assertIdentical($url->getY(), 45);
    }
    
    function testClearingCordinates() {
        $url = new SimpleUrl('?32,45');
        $url->setCoordinates();
        $this->assertIdentical($url->getX(), false);
        $this->assertIdentical($url->getY(), false);
    }
    
    function testParsingParameterCordinateMixture() {
        $url = new SimpleUrl('?a=A&b=&c?32,45');
        $this->assertIdentical($url->getX(), 32);
        $this->assertIdentical($url->getY(), 45);
        $this->assertEquals('?a=A&b=&c', $url->getEncodedRequest());
    }
    
    function testParsingParameterWithBadCordinates() {
        $url = new SimpleUrl('?a=A&b=&c?32');
        $this->assertIdentical($url->getX(), false);
        $this->assertIdentical($url->getY(), false);
        $this->assertEquals('?a=A&b=&c?32', $url->getEncodedRequest());
    }
    
    function testPageSplitting() {
        $url = new SimpleUrl('./here/../there/somewhere.php');
        $this->assertEquals('./here/../there/somewhere.php', $url->getPath());
        $this->assertEquals('somewhere.php', $url->getPage());
        $this->assertEquals('./here/../there/', $url->getBasePath());
    }
    
    function testAbsolutePathPageSplitting() {
        $url = new SimpleUrl("http://host.com/here/there/somewhere.php");
        $this->assertEquals("/here/there/somewhere.php", $url->getPath());
        $this->assertEquals("somewhere.php", $url->getPage());
        $this->assertEquals("/here/there/", $url->getBasePath());
    }
    
    function testSplittingUrlWithNoPageGivesEmptyPage() {
        $url = new SimpleUrl('/here/there/');
        $this->assertEquals('/here/there/', $url->getPath());
        $this->assertEquals('', $url->getPage());
        $this->assertEquals('/here/there/', $url->getBasePath());
    }
    
    function testPathNormalisation() {
        $url = new SimpleUrl();
        $this->assertEqual(
                $url->normalisePath('https://host.com/I/am/here/../there/somewhere.php'),
                'https://host.com/I/am/there/somewhere.php');
    }

    // regression test for #1535407
    function testPathNormalisationWithSinglePeriod() {
        $url = new SimpleUrl();
        $this->assertEqual(
            $url->normalisePath('https://host.com/I/am/here/./../there/somewhere.php'),
            'https://host.com/I/am/there/somewhere.php');
    }
    
    // regression test for #1852413
    function testHostnameExtractedFromUContainingAtSign() {
        $url = new SimpleUrl("http://localhost/name@example.com");
        $this->assertEquals("http", $url->getScheme());
        $this->assertEquals("", $url->getUsername());
        $this->assertEquals("", $url->getPassword());
        $this->assertEquals("localhost", $url->getHost());
        $this->assertEquals("/name@example.com", $url->getPath());
    }

    function testHostnameInLocalhost() {
        $url = new SimpleUrl("http://localhost/name/example.com");
        $this->assertEquals("http", $url->getScheme());
        $this->assertEquals("", $url->getUsername());
        $this->assertEquals("", $url->getPassword());
        $this->assertEquals("localhost", $url->getHost());
        $this->assertEquals("/name/example.com", $url->getPath());
    }

    function testUsernameAndPasswordAreUrlDecoded() {
        $url = new SimpleUrl('http://' . urlencode('test@test') .
                ':' . urlencode('$!�@*&%') . '@www.lastcraft.com');
        $this->assertEquals('test@test', $url->getUsername());
        $this->assertEquals('$!�@*&%', $url->getPassword());
    }
    
    function testBlitz() {
        $this->assertUrl(
                "https://username:password@www.somewhere.com:243/this/that/here.php?a=1&b=2#anchor",
                array("https", "username", "password", "www.somewhere.com", 243, "/this/that/here.php", "com", "?a=1&b=2", "anchor"),
                array("a" => "1", "b" => "2"));
        $this->assertUrl(
                "username:password@www.somewhere.com/this/that/here.php?a=1",
                array(false, "username", "password", "www.somewhere.com", false, "/this/that/here.php", "com", "?a=1", false),
                array("a" => "1"));
        $this->assertUrl(
                "username:password@somewhere.com:243?1,2",
                array(false, "username", "password", "somewhere.com", 243, "/", "com", "", false),
                array(),
                array(1, 2));
        $this->assertUrl(
                "https://www.somewhere.com",
                array("https", false, false, "www.somewhere.com", false, "/", "com", "", false));
        $this->assertUrl(
                "username@www.somewhere.com:243#anchor",
                array(false, "username", false, "www.somewhere.com", 243, "/", "com", "", "anchor"));
        $this->assertUrl(
                "/this/that/here.php?a=1&b=2?3,4",
                array(false, false, false, false, false, "/this/that/here.php", false, "?a=1&b=2", false),
                array("a" => "1", "b" => "2"),
                array(3, 4));
        $this->assertUrl(
                "username@/here.php?a=1&b=2",
                array(false, "username", false, false, false, "/here.php", false, "?a=1&b=2", false),
                array("a" => "1", "b" => "2"));
    }
    
    function testAmbiguousHosts() {
        $this->assertUrl(
                "tigger",
                array(false, false, false, false, false, "tigger", false, "", false));
        $this->assertUrl(
                "/tigger",
                array(false, false, false, false, false, "/tigger", false, "", false));
        $this->assertUrl(
                "//tigger",
                array(false, false, false, "tigger", false, "/", false, "", false));
        $this->assertUrl(
                "//tigger/",
                array(false, false, false, "tigger", false, "/", false, "", false));
        $this->assertUrl(
                "tigger.com",
                array(false, false, false, "tigger.com", false, "/", "com", "", false));
        $this->assertUrl(
                "me.net/tigger",
                array(false, false, false, "me.net", false, "/tigger", "net", "", false));
    }
    
    function testAsString() {
        $this->assertPreserved('https://www.here.com');
        $this->assertPreserved('http://me:secret@www.here.com');
        $this->assertPreserved('http://here/there');
        $this->assertPreserved('http://here/there?a=A&b=B');
        $this->assertPreserved('http://here/there?a=1&a=2');
        $this->assertPreserved('http://here/there?a=1&a=2?9,8');
        $this->assertPreserved('http://host?a=1&a=2');
        $this->assertPreserved('http://host#stuff');
        $this->assertPreserved('http://me:secret@www.here.com/a/b/c/here.html?a=A?7,6');
        $this->assertPreserved('http://www.here.com/?a=A__b=B');
        $this->assertPreserved('http://www.example.com:8080/');
    }
    
    function testUrlWithTwoSlashesInPath() {
        $url = new SimpleUrl('/article/categoryedit/insert//');
        $this->assertEquals('/article/categoryedit/insert//', $url->getPath());
    }
    
    function testUrlWithRequestKeyEncoded() {
        $url = new SimpleUrl('/?foo%5B1%5D=bar');
        $this->assertEquals('?foo%5B1%5D=bar', $url->getEncodedRequest());
        $url->addRequestParameter('a[1]', 'b[]');
        $this->assertEquals('?foo%5B1%5D=bar&a%5B1%5D=b%5B%5D', $url->getEncodedRequest());

        $url = new SimpleUrl('/');
        $url->addRequestParameter('a[1]', 'b[]');
        $this->assertEquals('?a%5B1%5D=b%5B%5D', $url->getEncodedRequest());
    }

    function testUrlWithRequestKeyEncodedAndParamNamLookingLikePair() {
        $url = new SimpleUrl('/');
        $url->addRequestParameter('foo[]=bar', '');
        $this->assertEquals('?foo%5B%5D%3Dbar=', $url->getEncodedRequest());
        $url = new SimpleUrl('/?foo%5B%5D%3Dbar=');
        $this->assertEquals('?foo%5B%5D%3Dbar=', $url->getEncodedRequest());
    }

    function assertUrl($raw, $parts, $params = false, $coords = false) {
        if (! is_array($params)) {
            $params = array();
        }
        $url = new SimpleUrl($raw);
        $this->assertIdentical($url->getScheme(), $parts[0], "[$raw] scheme -> %s");
        $this->assertIdentical($url->getUsername(), $parts[1], "[$raw] username -> %s");
        $this->assertIdentical($url->getPassword(), $parts[2], "[$raw] password -> %s");
        $this->assertIdentical($url->getHost(), $parts[3], "[$raw] host -> %s");
        $this->assertIdentical($url->getPort(), $parts[4], "[$raw] port -> %s");
        $this->assertIdentical($url->getPath(), $parts[5], "[$raw] path -> %s");
        $this->assertIdentical($url->getTld(), $parts[6], "[$raw] tld -> %s");
        $this->assertIdentical($url->getEncodedRequest(), $parts[7], "[$raw] encoded -> %s");
        $this->assertIdentical($url->getFragment(), $parts[8], "[$raw] fragment -> %s");
        if ($coords) {
            $this->assertIdentical($url->getX(), $coords[0], "[$raw] x -> %s");
            $this->assertIdentical($url->getY(), $coords[1], "[$raw] y -> %s");
        }
    }
    
    function assertPreserved($string) {
        $url = new SimpleUrl($string);
        $this->assertEquals($string, $url->asString());
    }
}

class TestOfAbsoluteUrls extends UnitTestCase {
    
	function testDirectoriesAfterFilename() {
		$string = '../../index.php/foo/bar';
		$url = new SimpleUrl($string);
		$this->assertEquals($string, $url->asString());
		
		$absolute = $url->makeAbsolute('http://www.domain.com/some/path/');
		$this->assertEquals('http://www.domain.com/index.php/foo/bar', $absolute->asString());
	}

    function testMakingAbsolute() {
        $url = new SimpleUrl('../there/somewhere.php');
        $this->assertEquals('../there/somewhere.php', $url->getPath());
        $absolute = $url->makeAbsolute('https://host.com:1234/I/am/here/');
        $this->assertEquals('https', $absolute->getScheme());
        $this->assertEquals('host.com', $absolute->getHost());
        $this->assertEquals(1234, $absolute->getPort());
        $this->assertEquals('/I/am/there/somewhere.php', $absolute->getPath());
    }
    
    function testMakingAnEmptyUrlAbsolute() {
        $url = new SimpleUrl('');
        $this->assertEquals('', $url->getPath());
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/page.html');
        $this->assertEquals('http', $absolute->getScheme());
        $this->assertEquals('host.com', $absolute->getHost());
        $this->assertEquals('/I/am/here/page.html', $absolute->getPath());
    }
    
    function testMakingAnEmptyUrlAbsoluteWithMissingPageName() {
        $url = new SimpleUrl('');
        $this->assertEquals('', $url->getPath());
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/');
        $this->assertEquals('http', $absolute->getScheme());
        $this->assertEquals('host.com', $absolute->getHost());
        $this->assertEquals('/I/am/here/', $absolute->getPath());
    }
    
    function testMakingAShortQueryUrlAbsolute() {
        $url = new SimpleUrl('?a#b');
        $this->assertEquals('', $url->getPath());
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/');
        $this->assertEquals('http', $absolute->getScheme());
        $this->assertEquals('host.com', $absolute->getHost());
        $this->assertEquals('/I/am/here/', $absolute->getPath());
        $this->assertEquals('?a', $absolute->getEncodedRequest());
        $this->assertEquals('b', $absolute->getFragment());
    }
    
    function testMakingADirectoryUrlAbsolute() {
        $url = new SimpleUrl('hello/');
        $this->assertEquals('hello/', $url->getPath());
        $this->assertEquals('hello/', $url->getBasePath());
        $this->assertEquals('', $url->getPage());
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/page.html');
        $this->assertEquals('/I/am/here/hello/', $absolute->getPath());
    }
    
    function testMakingARootUrlAbsolute() {
        $url = new SimpleUrl('/');
        $this->assertEquals('/', $url->getPath());
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/page.html');
        $this->assertEquals('/', $absolute->getPath());
    }
    
    function testMakingARootPageUrlAbsolute() {
        $url = new SimpleUrl('/here.html');
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/page.html');
        $this->assertEquals('/here.html', $absolute->getPath());
    }
    
    function testCarryAuthenticationFromRootPage() {
        $url = new SimpleUrl('here.html');
        $absolute = $url->makeAbsolute('http://test:secret@host.com/');
        $this->assertEquals('/here.html', $absolute->getPath());
        $this->assertEquals('test', $absolute->getUsername());
        $this->assertEquals('secret', $absolute->getPassword());
    }
    
    function testMakingCoordinateUrlAbsolute() {
        $url = new SimpleUrl('?1,2');
        $this->assertEquals('', $url->getPath());
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/');
        $this->assertEquals('http', $absolute->getScheme());
        $this->assertEquals('host.com', $absolute->getHost());
        $this->assertEquals('/I/am/here/', $absolute->getPath());
        $this->assertEquals(1, $absolute->getX());
        $this->assertEquals(2, $absolute->getY());
    }
    
    function testMakingAbsoluteAppendedPath() {
        $url = new SimpleUrl('./there/somewhere.php');
        $absolute = $url->makeAbsolute('https://host.com/here/');
        $this->assertEquals('/here/there/somewhere.php', $absolute->getPath());
    }
    
    function testMakingAbsoluteBadlyFormedAppendedPath() {
        $url = new SimpleUrl('there/somewhere.php');
        $absolute = $url->makeAbsolute('https://host.com/here/');
        $this->assertEquals('/here/there/somewhere.php', $absolute->getPath());
    }
    
    function testMakingAbsoluteHasNoEffectWhenAlreadyAbsolute() {
        $url = new SimpleUrl('https://test:secret@www.lastcraft.com:321/stuff/?a=1#f');
        $absolute = $url->makeAbsolute('http://host.com/here/');
        $this->assertEquals('https', $absolute->getScheme());
        $this->assertEquals('test', $absolute->getUsername());
        $this->assertEquals('secret', $absolute->getPassword());
        $this->assertEquals('www.lastcraft.com', $absolute->getHost());
        $this->assertEquals(321, $absolute->getPort());
        $this->assertEquals('/stuff/', $absolute->getPath());
        $this->assertEquals('?a=1', $absolute->getEncodedRequest());
        $this->assertEquals('f', $absolute->getFragment());
    }
    
    function testMakingAbsoluteCarriesAuthenticationWhenAlreadyAbsolute() {
        $url = new SimpleUrl('https://www.lastcraft.com');
        $absolute = $url->makeAbsolute('http://test:secret@host.com/here/');
        $this->assertEquals('www.lastcraft.com', $absolute->getHost());
        $this->assertEquals('test', $absolute->getUsername());
        $this->assertEquals('secret', $absolute->getPassword());
    }
    
    function testMakingHostOnlyAbsoluteDoesNotCarryAnyOtherInformation() {
        $url = new SimpleUrl('http://www.lastcraft.com');
        $absolute = $url->makeAbsolute('https://host.com:81/here/');
        $this->assertEquals('http', $absolute->getScheme());
        $this->assertEquals('www.lastcraft.com', $absolute->getHost());
        $this->assertIdentical($absolute->getPort(), false);
        $this->assertEquals('/', $absolute->getPath());
    }
}

class TestOfFrameUrl extends UnitTestCase {
    
    function testTargetAttachment() {
        $url = new SimpleUrl('http://www.site.com/home.html');
        $this->assertIdentical($url->getTarget(), false);
        $url->setTarget('A frame');
        $this->assertIdentical($url->getTarget(), 'A frame');
    }
}

/**
 * @note Based off of http://www.mozilla.org/quality/networking/testing/filetests.html
 */
class TestOfFileUrl extends UnitTestCase {
    
    function testMinimalUrl() {
        $url = new SimpleUrl('file:///');
        $this->assertEquals('file', $url->getScheme());
        $this->assertIdentical($url->getHost(), false);
        $this->assertEquals('/', $url->getPath());
    }
    
    function testUnixUrl() {
        $url = new SimpleUrl('file:///fileInRoot');
        $this->assertEquals('file', $url->getScheme());
        $this->assertIdentical($url->getHost(), false);
        $this->assertEquals('/fileInRoot', $url->getPath());
    }
    
    function testDOSVolumeUrl() {
        $url = new SimpleUrl('file:///C:/config.sys');
        $this->assertEquals('file', $url->getScheme());
        $this->assertIdentical($url->getHost(), false);
        $this->assertEquals('/C:/config.sys', $url->getPath());
    }
    
    function testDOSVolumePromotion() {
        $url = new SimpleUrl('file://C:/config.sys');
        $this->assertEquals('file', $url->getScheme());
        $this->assertIdentical($url->getHost(), false);
        $this->assertEquals('/C:/config.sys', $url->getPath());
    }
    
    function testDOSBackslashes() {
        $url = new SimpleUrl('file:///C:\config.sys');
        $this->assertEquals('file', $url->getScheme());
        $this->assertIdentical($url->getHost(), false);
        $this->assertEquals('/C:/config.sys', $url->getPath());
    }
    
    function testDOSDirnameAfterFile() {
        $url = new SimpleUrl('file://C:\config.sys');
        $this->assertEquals('file', $url->getScheme());
        $this->assertIdentical($url->getHost(), false);
        $this->assertEquals('/C:/config.sys', $url->getPath());
    }
    
}

?>