<?php namespace QueueIT\Security;
require_once('DefaultKnownUserUrlProvider.php');
require_once('KnownUserException.php');
require_once('InvalidKnownUserHashException.php');
require_once('InvalidKnownUserUrlException.php');
require_once('Md5KnownUser.php');
require_once('RedirectType.php');

use \InvalidArgumentException, \DateTime, \DateTimeZone;

class KnownUserFactory
{
	private static $defaultQueueStringPrefix;
	private static $defaultSecretKey;
	private static $defaultUrlProviderFactory;
	
	static function reset($loadConfiguration = false)
	{
		global $defaultQueryStringPrefix, $defaultSecretKey, $defaultUrlProviderFactory;
		
		$defaultQueryStringPrefix = null;
		$defaultSecretKey = null;
		$defaultUrlProviderFactory = function() { return new DefaultKnownUserUrlProvider(); };
		
		if (!$loadConfiguration)
			return;
		
		$iniFileName = $_SERVER['DOCUMENT_ROOT'] . "\queueit.ini";
		
		if (!file_exists($iniFileName))
			return;
		
		$settings_array = parse_ini_file($iniFileName, true);
		
		if (!$settings_array)
			return;
		
		$settings = $settings_array['settings'];
		
		if ($settings == null)
			return;
		
		if (isset($settings['secretKey']) && $settings['secretKey'] != null)
			$defaultSecretKey = $settings['secretKey'];
		if (isset($settings['queryStringPrefix']) && $settings['queryStringPrefix'] != null)
			$defaultQueryStringPrefix = $settings['queryStringPrefix'];	
	}

	static function configure($sharedEventKey = null, $urlProviderFactory = null, $querystringPrefix = null)
	{
		global $defaultQueryStringPrefix, $defaultSecretKey, $defaultUrlProviderFactory;
		
		if ($sharedEventKey != null)
			$defaultSecretKey = $sharedEventKey;
		if ($urlProviderFactory != null)
			$defaultUrlProviderFactory = $urlProviderFactory;
		if ($querystringPrefix != null)
			$defaultQueryStringPrefix = $querystringPrefix;
	}	
		
	public static function verifyMd5Hash($secretKey = null, $urlProvider = null, $queryStringPrefix = null)
	{
		global $defaultQueryStringPrefix, $defaultSecretKey, $defaultUrlProviderFactory;
		
		if ($urlProvider == null)
			$urlProvider = $defaultUrlProviderFactory();	
		if ($secretKey == null)
			$secretKey = $defaultSecretKey;
		if ($queryStringPrefix == null)
			$queryStringPrefix = $defaultQueryStringPrefix;

		if ($secretKey == null)
			throw new InvalidArgumentException("Secret key is null");
		
		try {
			if ($urlProvider->getQueueId($queryStringPrefix) == null && $urlProvider->getPlaceInQueue($queryStringPrefix) == null && $urlProvider->getTimeStamp($queryStringPrefix) == null)
				return null;	
			
			if ($urlProvider->getQueueId($queryStringPrefix) == null || $urlProvider->getPlaceInQueue($queryStringPrefix) == null || $urlProvider->getTimeStamp($queryStringPrefix) == null)
				throw new InvalidKnownUserUrlException();			
			
			KnownUserFactory::verifyUrl($urlProvider->getUrl(), $secretKey);
			
			return new Md5KnownUser(
					$urlProvider->getQueueId($queryStringPrefix), 
					KnownUserFactory::decryptPlaceInQueue($urlProvider->getPlaceInQueue($queryStringPrefix)),
					KnownUserFactory::decodeTimestamp($urlProvider->getTimeStamp($queryStringPrefix)),
					$urlProvider->getCustomerId($queryStringPrefix),
					$urlProvider->getEventId($queryStringPrefix),
					KnownUserFactory::decodeRedirectType($urlProvider->getRedirectType($queryStringPrefix)),
					$urlProvider->getOriginalUrl($queryStringPrefix));
		} catch (InvalidKnownUserUrlException $e) {
			throw $e;
		} catch (InvalidKnownUserHashException $e) {
			throw $e;
		}
	}
	
	private static function decodeTimestamp($timestamp)
	{	
		if ($timestamp != null && is_numeric($timestamp))
		{		
			$date = new DateTime("now", new DateTimeZone("UTC"));
			$date->setTimestamp(intval($timestamp));
			
			return $date;
		} 
		
		throw new InvalidKnownUserUrlException();
	}
	
	private static function decodeRedirectType($redirectType)
	{
		return RedirectType::FromString($redirectType);
	}
	
	private static function decryptPlaceInQueue($encryptedPlaceInQueue)
	// The users QueueNumber is his initial place in queue
	// To decrypt the parsed “p” parameter from the query
	// to the actual queue number the following function is used:
	{
		if ($encryptedPlaceInQueue == null || strlen($encryptedPlaceInQueue) != 36)
			throw new InvalidKnownUserUrlException();
		
		$e = $encryptedPlaceInQueue;
		$p = substr($e,30,1).substr($e,3,1).substr($e,11,1).substr($e,20,1).substr($e,7,1).substr($e,26,1).substr($e,9,1); //uses one char of each string at a given starting point
		return $p;
	}
	
	private static function verifyUrl($url, $sharedEventKey)
	{
		$expectedHash = substr($url, -32);
		$urlNoHash=substr($url, 0, -32) . $sharedEventKey; //Remove hash value and add SharedEventKey
		$actualhash = md5(utf8_encode($urlNoHash));

		if (strcmp($actualhash, $expectedHash) != 0) {
			throw new invalidKnownUserHashException('The hash of the request is invalid');
		}
	}
}

KnownUserFactory::reset(true);

?>
