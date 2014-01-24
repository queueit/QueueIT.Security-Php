<?php
require_once('simpletest/autorun.php');
require_once('../QueueIT.Security/QueueFactory.php');

use QueueIT\Security\QueueFactory;

class QueueFactoryTest extends UnitTestCase {
	
	function setUp() {
		
		$_SERVER["SERVER_PORT"] = null;
		$_SERVER["SERVER_NAME"] = null;
		$_SERVER["REQUEST_URI"] = null;
		
		QueueFactory::reset(false);
		
		
		QueueFactory::configure(null);
	}

	function tearDown() {
	}

	function test_createQueue()
	{
		$expectedCustomerId = "customerid";
		$expectedEventId = "eventid";
	
		$queue = QueueFactory::CreateQueue($expectedCustomerId, $expectedEventId);
	
		$this->assertEqual($expectedCustomerId, $queue->getCustomerId());
		$this->assertEqual($expectedEventId, $queue->getEventId());

	}
	
	function test_createQueueFromConfiguration() 
	{
		$expectedCustomerId = "defaultcustomerid";
		$expectedEventId = "defaulteventid";
		
		$queue = QueueFactory::CreateQueueFromConfiguration();
		
		$this->assertEqual($expectedCustomerId, $queue->getCustomerId());
		$this->assertEqual($expectedEventId, $queue->getEventId());
	}
	
	function test_createQueueFromConfigurationNamed()
	{
		$expectedCustomerId = "queue1customerid";
		$expectedEventId = "queue1eventid";
	
		$queue = QueueFactory::CreateQueueFromConfiguration('queue1');
	
		$this->assertEqual($expectedCustomerId, $queue->getCustomerId());
		$this->assertEqual($expectedEventId, $queue->getEventId());
	}
	
	function test_getQueueUrl()
	{
		$expectedCustomerId = "customerid";
		$expectedEventId = "eventid";
		
		$expectedQueueUrl =
			"http://" . $expectedEventId . "-" . $expectedCustomerId . ".queue-it.net/?c=" . $expectedCustomerId . "&e=" . $expectedEventId;
		
		$queue = QueueFactory::createQueue($expectedCustomerId, $expectedEventId);
		
		$actualQueueUrl = $queue->GetQueueUrl();
		
		$this->assertEqual($expectedQueueUrl, $actualQueueUrl);		
	}
	
	function test_getQueueUrlLanguage()
	{
		$expectedCustomerId = "customerid";
		$expectedEventId = "eventid";
		$expectedLanguage = "en-US";
			
		$expectedQueueUrl =
		"http://" . $expectedEventId . "-" . $expectedCustomerId . ".queue-it.net/?c=" . $expectedCustomerId . "&e=" . $expectedEventId . "&cid=" . $expectedLanguage;
	
		$queue = QueueFactory::createQueue($expectedCustomerId, $expectedEventId);
	
		$actualQueueUrl = $queue->GetQueueUrl(null, null, null, $expectedLanguage);
	
		$this->assertEqual($expectedQueueUrl, $actualQueueUrl);
	}
	
	function test_getQueueUrlLayoutName()
	{
		$expectedCustomerId = "customerid";
		$expectedEventId = "eventid";
		$expectedLayoutName = "some layout";
			
		$expectedQueueUrl =
		"http://" . $expectedEventId . "-" . $expectedCustomerId . ".queue-it.net/?c=" . $expectedCustomerId . "&e=" . $expectedEventId . "&l=" . urlencode($expectedLayoutName);
	
		$queue = QueueFactory::createQueue($expectedCustomerId, $expectedEventId);
	
		$actualQueueUrl = $queue->GetQueueUrl(null, null, null, null, $expectedLayoutName);
	
		$this->assertEqual($expectedQueueUrl, $actualQueueUrl);
	}
	
	function test_getQueueUrlDomainAlias()
	{
		$expectedCustomerId = "customerid";
		$expectedEventId = "eventid";
		$expectedDomainAlias = "my.queue.url";
		
		$expectedQueueUrl =
                "http://" . $expectedDomainAlias . "/?c=" . $expectedCustomerId . "&e=" . $expectedEventId;
	
		$queue = QueueFactory::createQueue($expectedCustomerId, $expectedEventId);
	
		$actualQueueUrl = $queue->GetQueueUrl(null, null, $expectedDomainAlias);
	
		$this->assertEqual($expectedQueueUrl, $actualQueueUrl);
	}

	function test_getQueueUrlSsl()
	{
		$expectedCustomerId = "customerid";
		$expectedEventId = "eventid";
	
		$expectedQueueUrl =
			"https://" . $expectedEventId . "-" . $expectedCustomerId . ".queue-it.net/?c=" . $expectedCustomerId . "&e=" . $expectedEventId;
			
		$queue = QueueFactory::createQueue($expectedCustomerId, $expectedEventId);
	
		$actualQueueUrl = $queue->GetQueueUrl(null, true, null);
	
		$this->assertEqual($expectedQueueUrl, $actualQueueUrl);
	}
	
	function test_getQueueUrlIncludeTarget()
	{
		$expectedCustomerId = "customerid";
		$expectedEventId = "eventid";
		$expectedTarget = "http://target.url/?someprop=somevalue&another=value";
	
		$_SERVER["SERVER_PORT"] = '80';
		$_SERVER["SERVER_NAME"] = 'target.url';
		$_SERVER["REQUEST_URI"] = '/?someprop=somevalue&another=value';
		
		$expectedQueueUrl =
			"http://" . $expectedEventId . "-" . $expectedCustomerId . ".queue-it.net/?c=" . $expectedCustomerId . "&e=" . $expectedEventId . '&t=' . urlencode($expectedTarget);
			
		$queue = QueueFactory::createQueue($expectedCustomerId, $expectedEventId);
	
		$actualQueueUrl = $queue->GetQueueUrl(true, null, null);
	
		$this->assertEqual($expectedQueueUrl, $actualQueueUrl);
	}
	
	function test_getQueueUrlTargetUrl()
	{
		$expectedCustomerId = "customerid";
		$expectedEventId = "eventid";
		$expectedTarget = "http://target.url/?someprop=somevalue&another=value";
	
		$expectedQueueUrl =
		"http://" . $expectedEventId . "-" . $expectedCustomerId . ".queue-it.net/?c=" . $expectedCustomerId . "&e=" . $expectedEventId . '&t=' . urlencode($expectedTarget);
			
		$queue = QueueFactory::createQueue($expectedCustomerId, $expectedEventId);
	
		$actualQueueUrl = $queue->GetQueueUrl($expectedTarget, null, null);
	
		$this->assertEqual($expectedQueueUrl, $actualQueueUrl);
	}
	
	function test_getCancelUrl()
	{
		$expectedCustomerId = "customerid";
		$expectedEventId = "eventid";
	
        $expectedCancelUrl =
        	"http://" . $expectedEventId . "-" . $expectedCustomerId . ".queue-it.net/cancel.aspx?c=" . $expectedCustomerId . "&e=" . $expectedEventId;
			
		$queue = QueueFactory::createQueue($expectedCustomerId, $expectedEventId);
	
		$actualCancelUrl = $queue->GetCancelUrl();
	
		$this->assertEqual($expectedCancelUrl, $actualCancelUrl);
	}
	
	function test_getCancelUrlSsl()
	{
		$expectedCustomerId = "customerid";
		$expectedEventId = "eventid";
	
		$expectedCancelUrl =
		"https://" . $expectedEventId . "-" . $expectedCustomerId . ".queue-it.net/cancel.aspx?c=" . $expectedCustomerId . "&e=" . $expectedEventId;
			
		$queue = QueueFactory::createQueue($expectedCustomerId, $expectedEventId);
	
		$actualCancelUrl = $queue->GetCancelUrl(null, null, true);
	
		$this->assertEqual($expectedCancelUrl, $actualCancelUrl);
	}
	
	function test_getCancelUrlDomainAlias()
	{
		$expectedCustomerId = "customerid";
		$expectedEventId = "eventid";
		$expectedDomainAlias = 'vent.queue-it.net';
	
		$expectedCancelUrl =
		"http://" . $expectedDomainAlias . "/cancel.aspx?c=" . $expectedCustomerId . "&e=" . $expectedEventId;
			
		$queue = QueueFactory::createQueue($expectedCustomerId, $expectedEventId);
	
		$actualCancelUrl = $queue->GetCancelUrl(null, null, null, $expectedDomainAlias);
	
		$this->assertEqual($expectedCancelUrl, $actualCancelUrl);
	}
	
	function test_getCancelUrlLandingPage()
	{
		$expectedCustomerId = "customerid";
		$expectedEventId = "eventid";
		$expectedTarget = 'http://target.url/?someprop=somevalue&another=value';
	
		$expectedCancelUrl =
			"http://" . $expectedEventId . "-" . $expectedCustomerId . ".queue-it.net/cancel.aspx?c=" . $expectedCustomerId . "&e=" . $expectedEventId . "&r=" . urlEncode($expectedTarget);
					
		$queue = QueueFactory::createQueue($expectedCustomerId, $expectedEventId);
	
		$actualCancelUrl = $queue->GetCancelUrl($expectedTarget);
	
		$this->assertEqual($expectedCancelUrl, $actualCancelUrl);
	}
	
	function test_getCancelUrlLandingPageFromConfiguration()
	{
		$expectedCustomerId = "queue1customerid";
		$expectedEventId = "queue1eventid";
	
		$expectedCancelUrl =
                "https://queue.mala.dk/cancel.aspx?c=" . $expectedCustomerId . "&e=" . $expectedEventId . "&r=http%3A%2F%2Fwww.mysplitpage.com%2F";
				
		$queue = QueueFactory::createQueueFromConfiguration('queue1');
	
		$actualCancelUrl = $queue->GetCancelUrl();
	
		$this->assertEqual($expectedCancelUrl, $actualCancelUrl);
	}
	
	function test_getLandingPageUrl()
	{
		$queue = QueueFactory::createQueue("customerid", "eventid");
		
		$actualLandingPageUrl = $queue->getLandingPageUrl();
		
		$this->assertNull($actualLandingPageUrl);
	}
	
	function test_getLandingPageUrlFromConfiguration()
	{
		$_SERVER["SERVER_PORT"] = '80';
		$_SERVER["SERVER_NAME"] = 'target.url';
		$_SERVER["REQUEST_URI"] = '/?someprop=somevalue&another=value';
		
		$expectedLandingPageUrl = "http://www.mysplitpage.com/?t=http%3A%2F%2Ftarget.url%2F%3Fsomeprop%3Dsomevalue%26another%3Dvalue";
				
		$queue = QueueFactory::createQueueFromConfiguration("queue1");
	
		$actualLandingPageUrl = $queue->getLandingPageUrl();
	
		$this->assertEqual($expectedLandingPageUrl, $actualLandingPageUrl);
	}
	
	function test_getLandingPageUrlIncludeTarget()
	{
		$_SERVER["SERVER_PORT"] = '80';
		$_SERVER["SERVER_NAME"] = 'target.url';
		$_SERVER["REQUEST_URI"] = '/?someprop=somevalue&another=value';
		
		$expectedLandingPageUrl = "http://www.mysplitpage.com/?t=http%3A%2F%2Ftarget.url%2F%3Fsomeprop%3Dsomevalue%26another%3Dvalue";
	
		$queue = QueueFactory::createQueueFromConfiguration("queue1");
	
		$actualLandingPageUrl = $queue->getLandingPageUrl(true);
	
		$this->assertEqual($expectedLandingPageUrl, $actualLandingPageUrl);
	}
	
	function test_getLandingPageUrlTargetUrl()
	{
		$expectedTarget = "http://target.url/?someprop=somevalue&another=value";
		$expectedLandingPageUrl = "http://www.mysplitpage.com/?t=http%3A%2F%2Ftarget.url%2F%3Fsomeprop%3Dsomevalue%26another%3Dvalue";
	
		$queue = QueueFactory::createQueueFromConfiguration("queue1");
	
		$actualLandingPageUrl = $queue->getLandingPageUrl($expectedTarget);
	
		$this->assertEqual($expectedLandingPageUrl, $actualLandingPageUrl);
	}
}