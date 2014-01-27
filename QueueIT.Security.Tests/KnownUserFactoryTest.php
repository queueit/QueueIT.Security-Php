<?php
require_once('simpletest/autorun.php');
require_once('../QueueIT.Security/KnownUserFactory.php');

use QueueIT\Security\KnownUserFactory, QueueIT\Security\RedirectType;

class KnownUserFactoryTest extends UnitTestCase {

	function setUp() {
		KnownUserFactory::reset(false);
	}

	function tearDown() {
	}

	function test_verifyMd5Hash() {
		//Arrange
		$prefix = null;
		$sharedKey = "zaqxswcdevfrbgtnhymjukiloZAQCDEFRBGTNHYMJUKILOPlkjhgfdsapoiuytrewqmnbvcx";
		
		$expectedPlaceInqueue = 7810;
		$expectedQueueId = "fe070f51-5548-403c-9f0a-2626c15cb81b";
		$placeInQueueEncrypted = "3d20e598-0304-474f-87e8-371a34073d3b";
		$unixTimestamp = 1360241766;
		$expectedTimeStamp = new DateTime("2013-02-07 12:56:06", new DateTimeZone("UTC"));
		$expectedCustomerId = "somecust";
		$expectedEventId = "someevent";
		$expectedOriginalUrl = "http://www.example.com/test.aspx?prop=value";
		$expectedRedirectType = RedirectType::Queue;
		
		$urlNoHash = $expectedOriginalUrl . "?".$prefix."c=somecust&".$prefix."e=someevent&".$prefix."q=".$expectedQueueId."&".$prefix."p=".$placeInQueueEncrypted."&".$prefix."ts=".$unixTimestamp."&".$prefix."rt=queue&".$prefix."h=";
		
		$expectedHash = md5(utf8_encode($urlNoHash . $sharedKey));
		
		$url = $urlNoHash.$expectedHash;
		
		$urlProvider = new MockUrlProvider(
				$url,
				$expectedOriginalUrl, 
				$expectedQueueId, 
				$placeInQueueEncrypted, 
				(string)$unixTimestamp, 
				$expectedCustomerId,
				$expectedEventId,
				"queue");
		
		//Act
		$knownUser = QueueIT\Security\KnownUserFactory::verifyMd5Hash(
				$sharedKey,
				$urlProvider,
				$prefix);
		
		$this->assertNotNull($knownUser);
		$this->assertEqual($expectedQueueId, $knownUser->getQueueId());
		$this->assertEqual($expectedPlaceInqueue, $knownUser->getPlaceInQueue());
		$this->assertEqual($expectedTimeStamp, $knownUser->getTimeStamp());
		$this->assertEqual($expectedCustomerId, $knownUser->getCustomerId());
		$this->assertEqual($expectedEventId, $knownUser->getEventId());
		$this->assertEqual($expectedRedirectType, $knownUser->getRedirectType());
		$this->assertEqual($expectedOriginalUrl, $knownUser->getOriginalUrl());
	}
	
	function test_verifyMd5Hash_inifile() {
		//Arrange
		$prefix = null;
		$sharedKey = "zaqxswcdevfrbgtnhymjukiloZAQCDEFRBGTNHYMJUKILOPlkjhgfdsapoiuytrewqmnbvcx";
	
		$expectedPlaceInqueue = 7810;
		$expectedQueueId = "fe070f51-5548-403c-9f0a-2626c15cb81b";
		$placeInQueueEncrypted = "3d20e598-0304-474f-87e8-371a34073d3b";
		$unixTimestamp = 1360241766;
		$expectedTimeStamp = new DateTime("2013-02-07 12:56:06", new DateTimeZone("UTC"));
		$expectedCustomerId = "somecust";
		$expectedEventId = "someevent";
		$expectedOriginalUrl = "http://www.example.com/test.aspx?prop=value";
	
		$urlNoHash = $expectedOriginalUrl . "?".$prefix."c=somecust&".$prefix."e=someevent&".$prefix."q=".$expectedQueueId."&".$prefix."p=".$placeInQueueEncrypted."&".$prefix."ts=".$unixTimestamp."&".$prefix."h=";
	
		$expectedHash = md5(utf8_encode($urlNoHash . $sharedKey));
	
		$url = $urlNoHash.$expectedHash;
	
		$urlProvider = new MockUrlProvider(
				$url,
				$expectedOriginalUrl,
				$expectedQueueId,
				$placeInQueueEncrypted,
				(string)$unixTimestamp,
				$expectedCustomerId,
				$expectedEventId);
	
		//Act
		
		KnownUserFactory::reset(true);
		$knownUser = KnownUserFactory::verifyMd5Hash(null, $urlProvider, null);
	
		$this->assertNotNull($knownUser);
		$this->assertEqual($expectedQueueId, $knownUser->getQueueId());
		$this->assertEqual($expectedPlaceInqueue, $knownUser->getPlaceInQueue());
		$this->assertEqual($expectedTimeStamp, $knownUser->getTimeStamp());
		$this->assertEqual($expectedCustomerId, $knownUser->getCustomerId());
		$this->assertEqual($expectedEventId, $knownUser->getEventId());
		$this->assertEqual($expectedOriginalUrl, $knownUser->getOriginalUrl());
	}
	
	function test_verifyMd5Hash_withprefix() {
		//Arrange
		$prefix = 'pre';
		$sharedKey = "zaqxswcdevfrbgtnhymjukiloZAQCDEFRBGTNHYMJUKILOPlkjhgfdsapoiuytrewqmnbvcx";
	
		$expectedPlaceInqueue = 7810;
		$expectedQueueId = "fe070f51-5548-403c-9f0a-2626c15cb81b";
		$placeInQueueEncrypted = "3d20e598-0304-474f-87e8-371a34073d3b";
		$unixTimestamp = 1360241766;
		$expectedTimeStamp = new DateTime("2013-02-07 12:56:06", new DateTimeZone("UTC"));
		$expectedCustomerId = "somecust";
		$expectedEventId = "someevent";
	
		$urlNoHash = "http://q.queue-it.net/inqueue.aspx?".$prefix."c=somecust&".$prefix."e=someevent&".$prefix."q=".$expectedQueueId."&".$prefix."p=".$placeInQueueEncrypted."&".$prefix."ts=".$unixTimestamp."&".$prefix."h=";
	
		$expectedHash = md5(utf8_encode($urlNoHash . $sharedKey));
	
		$url = $urlNoHash.$expectedHash;
	
		$urlProvider = new MockUrlProvider(
				$url,
				"http://q.queue-it.net/inqueue.aspx", 
				$expectedQueueId, 
				$placeInQueueEncrypted, 
				(string)$unixTimestamp, 
				$expectedCustomerId,
				$expectedEventId);
	
		//Act
		$knownUser = KnownUserFactory::verifyMd5Hash(
				$sharedKey,
				$urlProvider,
				$prefix);
	
		$this->assertNotNull($knownUser);
	}
	
	function test_verifyMd5Hash_notokens() {
		//Arrange
		$prefix = null;
		$sharedKey = "zaqxswcdevfrbgtnhymjukiloZAQCDEFRBGTNHYMJUKILOPlkjhgfdsapoiuytrewqmnbvcx";

		$url = "http://q.queue-it.net/inqueue.aspx?prop=value";
		
		$urlProvider = new MockUrlProvider(
				$url,
				$url);
	
		//Act
		$knownUser = KnownUserFactory::verifyMd5Hash(
				$sharedKey,
				$urlProvider,
				$prefix);
	
		$this->assertNull($knownUser);
	}
	
	function test_verifyMd5Hash_missingparameters() {
		
		$this->expectException(new QueueIT\Security\InvalidKnownUserUrlException());
		
		//Arrange
		$prefix = null;
		$sharedKey = "zaqxswcdevfrbgtnhymjukiloZAQCDEFRBGTNHYMJUKILOPlkjhgfdsapoiuytrewqmnbvcx";
	
		$urlNoHash = "http://www.example.com/test.aspx?prop=value&q=fe070f51-5548-403c-9f0a-2626c15cb81b&h=asdfasdfasdfasdfasdfasdfasfasdf";
		
		$urlProvider = new MockUrlProvider(
				$urlNoHash,
				$urlNoHash,
				"fe070f51-5548-403c-9f0a-2626c15cb81b");
	
		//Act
		$knownUser = KnownUserFactory::verifyMd5Hash(
				$sharedKey,
				$urlProvider,
				$prefix);
		
	}
	
	function test_verifyMd5Hash_InvalidHash() {
		$this->expectException(new QueueIT\Security\InvalidKnownUserHashException('The hash of the request is invalid'));
		
		//Arrange
		$prefix = null;
		$sharedKey = "zaqxswcdevfrbgtnhymjukiloZAQCDEFRBGTNHYMJUKILOPlkjhgfdsapoiuytrewqmnbvcx";
	
		$expectedPlaceInqueue = 7810;
		$expectedQueueId = "fe070f51-5548-403c-9f0a-2626c15cb81b";
		$placeInQueueEncrypted = "3d20e598-0304-474f-87e8-371a34073d3b";
		$unixTimestamp = 1360241766;
		$expectedTimeStamp = new DateTime("2013-02-07 12:56:06", new DateTimeZone("UTC"));
		$expectedCustomerId = "somecust";
		$expectedEventId = "someevent";
		$expectedOriginalUrl = "http://www.example.com/test.aspx?prop=value";
	
		$urlNoHash = $expectedOriginalUrl . "?".$prefix."c=somecust&".$prefix."e=someevent&".$prefix."q=".$expectedQueueId."&".$prefix."p=".$placeInQueueEncrypted."&".$prefix."ts=".$unixTimestamp."&".$prefix."h=";
	
		$expectedHash = "INVALIDHASHxxxxxxxxxxxxxxxxxxxx";
	
		$url = $urlNoHash.$expectedHash;
	
		$urlProvider = new MockUrlProvider(
				$url,
				$expectedOriginalUrl,
				$expectedQueueId,
				$placeInQueueEncrypted,
				(string)$unixTimestamp,
				$expectedCustomerId,
				$expectedEventId);
	
		//Act
		$knownUser = KnownUserFactory::verifyMd5Hash(
				$sharedKey,
				$urlProvider,
				$prefix);
	}
	
	function test_verifyMd5Hash_KnownUserException() {
	
		//Arrange
		$prefix = null;
		$sharedKey = "zaqxswcdevfrbgtnhymjukiloZAQCDEFRBGTNHYMJUKILOPlkjhgfdsapoiuytrewqmnbvcx";
	
		$expectedPlaceInqueue = 7810;
		$expectedQueueId = "fe070f51-5548-403c-9f0a-2626c15cb81b";
		$placeInQueueEncrypted = "3d20e598-0304-474f-87e8-371a34073d3b";
		$unixTimestamp = 1360241766;
		$expectedTimeStamp = new DateTime("2013-02-07 12:56:06", new DateTimeZone("UTC"));
		$expectedCustomerId = "somecust";
		$expectedEventId = "someevent";
		$expectedOriginalUrl = "http://www.example.com/test.aspx?prop=value";
	
		$urlNoHash = $expectedOriginalUrl . "?".$prefix."c=somecust&".$prefix."e=someevent&".$prefix."q=".$expectedQueueId."&".$prefix."p=".$placeInQueueEncrypted."&".$prefix."ts=".$unixTimestamp."&".$prefix."h=";
	
		$expectedHash = "INVALIDHASHxxxxxxxxxxxxxxxxxxxx";
	
		$url = $urlNoHash.$expectedHash;
	
		$urlProvider = new MockUrlProvider(
				$url,
				$expectedOriginalUrl,
				$expectedQueueId,
				$placeInQueueEncrypted,
				(string)$unixTimestamp,
				$expectedCustomerId,
				$expectedEventId);
	
		//Act
		try {
			$knownUser = KnownUserFactory::verifyMd5Hash(
					$sharedKey,
					$urlProvider,
					$prefix);
		} catch (QueueIT\Security\KnownUserException $e) {
			$this->assertEqual($url, $e->getValidationUrl());
			$this->assertEqual($expectedOriginalUrl, $e->getOriginalUrl());
		}		
	}
}

class MockUrlProvider implements \QueueIT\Security\IKnownUserUrlProvider
{
	private $url;
	private $queueId;
	private $placeInQueue;
	private $timestamp;
	private $eventId;
	private $customerId;
	private $redirectType;
	private $originalUrl;
		
	public function __construct(
			$url, 
			$originalUrl = null, 
			$queueId = null, 
			$placeInQueue = null, 
			$timestamp = null, 
			$customerId = null, 
			$eventId = null,
			$redirectType = null)
	{
		$this->url = $url;
		$this->queueId = $queueId;
		$this->placeInQueue = $placeInQueue;
		$this->timestamp = $timestamp;
		$this->eventId = $eventId;
		$this->customerId = $customerId;
		$this->redirectType = $redirectType;
		$this->originalUrl = $originalUrl;
	}
	
	public function getUrl()
	{
		return $this->url;
	}
	
	public function getQueueId($queueStringPrefix)
	{
		return $this->queueId;		
	}
	public function getPlaceInQueue($queueStringPrefix)
	{
		return $this->placeInQueue;
	}
	public function getTimeStamp($queueStringPrefix)
	{
		return $this->timestamp;
	}
	public function getEventId($queueStringPrefix)
	{
		return $this->eventId;
	}
	public function getCustomerId($queueStringPrefix)
	{
		return $this->customerId;
	}
	public function getRedirectType($queueStringPrefix)
	{
		return $this->redirectType;
	}	
	public function getOriginalUrl($queueStringPrefix)
	{
		return $this->originalUrl;
	}
}

?>