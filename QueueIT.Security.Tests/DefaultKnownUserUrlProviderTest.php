<?php
require_once('simpletest/autorun.php');
require_once('../QueueIT.Security/DefaultKnownUserUrlProvider.php');

use QueueIT\Security\DefaultKnownUserUrlProvider;

class DefaultKnownUserUrlProviderTest extends UnitTestCase {

	function setUp() {
	}

	function tearDown() {
	}

	function test_getUrl_simple() {
		
		$expectedUrl = "http://www.example.com/somepath/x?prop=value";
		
		$_SERVER["HTTPS"] = "off";
		$_SERVER["SERVER_PORT"] = "80";
		$_SERVER["SERVER_NAME"] = "www.example.com";
		$_SERVER["REQUEST_URI"] = "/somepath/x?prop=value";
		
		
		$urlProvider = new DefaultKnownUserUrlProvider();
			
		$actualUrl = $urlProvider->getUrl();
		
		$this->assertEquals($expectedUrl, $actualUrl);
	}
	
	function test_getUrl_https() {
	
		$expectedUrl = "https://www.example.com/somepath/x?prop=value";
	
		$_SERVER["HTTPS"] = "on";
		$_SERVER["SERVER_PORT"] = "443";
		$_SERVER["SERVER_NAME"] = "www.example.com";
		$_SERVER["REQUEST_URI"] = "/somepath/x?prop=value";
	
		$urlProvider = new DefaultKnownUserUrlProvider();

		$actualUrl = $urlProvider->getUrl();
	
		$this->assertEquals($expectedUrl, $actualUrl);
	}
	
	function test_getUrl_otherport() {
	
		$expectedUrl = "http://www.example.com:8080/somepath/x?prop=value";
	
		$_SERVER["HTTPS"] = "off";
		$_SERVER["SERVER_PORT"] = "8080";
		$_SERVER["SERVER_NAME"] = "www.example.com";
		$_SERVER["REQUEST_URI"] = "/somepath/x?prop=value";
	
		$urlProvider = new DefaultKnownUserUrlProvider();	
	
		$actualUrl = $urlProvider->getUrl();
	
		$this->assertEquals($expectedUrl, $actualUrl);
	}
	
	function test_getUrl_httpswithotherport() {
	
		$expectedUrl = "https://www.example.com:4433/somepath/x?prop=value";
	
		$_SERVER["HTTPS"] = "on";
		$_SERVER["SERVER_PORT"] = "4433";
		$_SERVER["SERVER_NAME"] = "www.example.com";
		$_SERVER["REQUEST_URI"] = "/somepath/x?prop=value";
	
	
		$urlProvider = new DefaultKnownUserUrlProvider();
			
	
		$actualUrl = $urlProvider->getUrl();
	
		$this->assertEquals($expectedUrl, $actualUrl);
	}
	
	function test_getQueueId() {
	
		$expectedQueueId = "48f6687b-7db3-4f95-be30-2fe82d8dcced";
	
		$_GET = array('q' => $expectedQueueId);	
	
		$urlProvider = new DefaultKnownUserUrlProvider();	
	
		$actualQueueId = $urlProvider->getQueueId(null);
	
		$this->assertEquals($expectedQueueId, $actualQueueId);
	}
	
	function test_getQueueId_withprefix() {
	
		$expectedQueueId = "48f6687b-7db3-4f95-be30-2fe82d8dcced";
	
		$_GET = array('preq' => $expectedQueueId);
	
		$urlProvider = new DefaultKnownUserUrlProvider();
	
		$actualQueueId = $urlProvider->getQueueId('pre');
	
		$this->assertEquals($expectedQueueId, $actualQueueId);
	}
	
	function test_getPlaceInQueue() {
	
		$expectedPlaceInQueue = "48f6687b-7db3-4f95-be30-2fe82d8dcced";
	
		$_GET = array('p' => $expectedPlaceInQueue);
	
		$urlProvider = new DefaultKnownUserUrlProvider();
	
		$actualPlaceInQueue = $urlProvider->getPlaceInQueue(null);
	
		$this->assertEquals($expectedPlaceInQueue, $actualPlaceInQueue);
	}
	
	function test_getPlaceInQueue_withprefix() {
	
		$expectedPlaceInQueue = "48f6687b-7db3-4f95-be30-2fe82d8dcced";
	
		$_GET = array('prep' => $expectedPlaceInQueue);
	
		$urlProvider = new DefaultKnownUserUrlProvider();
	
		$actualPlaceInQueue = $urlProvider->getPlaceInQueue('pre');
	
		$this->assertEquals($expectedPlaceInQueue, $actualPlaceInQueue);
	}
	
	function test_getTimestamp() {
	
		$expectedTimestamp = '1360241766';
	
		$_GET = array('ts' => '1360241766');
	
		$urlProvider = new DefaultKnownUserUrlProvider();
	
		$actualTimestamp = $urlProvider->getTimeStamp(null);
	
		$this->assertEquals($expectedTimestamp, $actualTimestamp);
	}
	
	function test_getTimestamp_withprefix() {
	
		$expectedTimestamp = '1360241766';
	
		$_GET = array('prets' => '1360241766');
	
		$urlProvider = new DefaultKnownUserUrlProvider();
	
		$actualTimestamp = $urlProvider->getTimeStamp('pre');
	
		$this->assertEquals($expectedTimestamp, $actualTimestamp);
	}
	
	function test_getEventId() {
	
		$expectedEventId = "testevent";
	
		$_GET = array('e' => $expectedEventId);
	
		$urlProvider = new DefaultKnownUserUrlProvider();
	
		$actualEventId = $urlProvider->getEventId(null);
	
		$this->assertEquals($expectedEventId, $actualEventId);
	}
	
	function test_getEventId_withprefix() {
	
		$expectedEventId = "testevent";
	
		$_GET = array('pree' => $expectedEventId);
	
		$urlProvider = new DefaultKnownUserUrlProvider();
	
		$actualEventId = $urlProvider->getEventId('pre');
	
		$this->assertEquals($expectedEventId, $actualEventId);
	}
	
	function test_getCustomerId() {
	
		$expectedCustomerId = "testevent";
	
		$_GET = array('c' => $expectedCustomerId);
	
		$urlProvider = new DefaultKnownUserUrlProvider();
	
		$actualCustomerId = $urlProvider->getCustomerId(null);
	
		$this->assertEquals($expectedCustomerId, $actualCustomerId);
	}
	
	function test_getCustomerId_withprefix() {
	
		$expectedCustomerId = "testevent";
	
		$_GET = array('prec' => $expectedCustomerId);
	
		$urlProvider = new DefaultKnownUserUrlProvider();
	
		$actualCustomerId = $urlProvider->getCustomerId('pre');
	
		$this->assertEquals($expectedCustomerId, $actualCustomerId);
	}
	
	function test_getOriginalUrl() {
	
		$expectedUrl = "http://www.example.com/somepath/x?prop=value";
		
		$_SERVER["HTTPS"] = "off";
		$_SERVER["SERVER_PORT"] = "80";
		$_SERVER["SERVER_NAME"] = "www.example.com";
		$_SERVER["REQUEST_URI"] = "/somepath/x?prop=value&c=somecust&e=someevent&q=48f6687b-7db3-4f95-be30-2fe82d8dcced&p=48f6687b-7db3-4f95-be30-2fe82d8dcced&ts=1360241766&h=sakdfhkuwekfbkshweufhskdfsdf";
	
		$urlProvider = new DefaultKnownUserUrlProvider();
			
		$actualUrl = $urlProvider->getOriginalUrl(null);
	
		$this->assertEquals($expectedUrl, $actualUrl);
	}
	
	function test_getOriginalUrl_withprefix() {
	
		$expectedUrl = "http://www.example.com/somepath/x?prop=value";
	
		$_SERVER["HTTPS"] = "off";
		$_SERVER["SERVER_PORT"] = "80";
		$_SERVER["SERVER_NAME"] = "www.example.com";
		$_SERVER["REQUEST_URI"] = "/somepath/x?prop=value&prec=somecust&pRee=someevent&pReq=48f6687b-7db3-4f95-be30-2fe82d8dcced&pRep=48f6687b-7db3-4f95-be30-2fe82d8dcced&prets=1360241766&pReh=sakdfhkuwekfbkshweufhskdfsdf";
	
		$urlProvider = new DefaultKnownUserUrlProvider();
			
		$actualUrl = $urlProvider->getOriginalUrl('pre');
	
		$this->assertEquals($expectedUrl, $actualUrl);
	}
}

?>