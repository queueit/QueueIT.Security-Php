<?php namespace QueueIT\Security;
require_once('DefaultKnownUserUrlProvider.php');
require_once('KnownUserException.php');
require_once('InvalidKnownUserHashException.php');
require_once('InvalidKnownUserUrlException.php');
require_once('Md5KnownUser.php');
require_once('RedirectType.php');
require_once('Guid.php');

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
		
	public static function getSecretKey()
	{
		global $defaultSecretKey;
		
		return $defaultSecretKey;
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
		} catch (KnownUserException $e) {
			$e->setValidationUrl($urlProvider->getUrl());
			$e->setOriginalUrl($urlProvider->getOriginalUrl($queryStringPrefix));
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
	
	public static function decryptPlaceInQueue($encryptedPlaceInQueue)
	{
		if ($encryptedPlaceInQueue == null || strlen($encryptedPlaceInQueue) != 36)
			throw new InvalidKnownUserUrlException();
		
		$e = $encryptedPlaceInQueue;
		$p = substr($e,30,1).substr($e,3,1).substr($e,11,1).substr($e,20,1).substr($e,7,1).substr($e,26,1).substr($e,9,1); //uses one char of each string at a given starting point
		return (int)$p;
	}
	
	public static function encryptPlaceInQueue($placeInQueue)
	{
		$encryptedPlaceInQueue = guid();
		
		$paddedPlaceInQueue = str_pad($placeInQueue, 7, "0", STR_PAD_LEFT);
		
		$encryptedPlaceInQueue[9] = $paddedPlaceInQueue[6];
		$encryptedPlaceInQueue[26] = $paddedPlaceInQueue[5];
		$encryptedPlaceInQueue[7] = $paddedPlaceInQueue[4];
		$encryptedPlaceInQueue[20] = $paddedPlaceInQueue[3];
		$encryptedPlaceInQueue[11] = $paddedPlaceInQueue[2];
		$encryptedPlaceInQueue[3] = $paddedPlaceInQueue[1];
		$encryptedPlaceInQueue[30] = $paddedPlaceInQueue[0];
		
		return $encryptedPlaceInQueue;
	}
	private static function verifyUrl($url, $sharedEventKey)
	{
		$expectedHash = substr($url, -32);
		$urlNoHash=substr($url, 0, -32) . $sharedEventKey; //Remove hash value and add SharedEventKey
		$actualhashHttp = md5(utf8_encode(preg_replace("/^https:\/\/(.*)$/", 'http://$1', $urlNoHash)));
		$actualhashHttps = md5(utf8_encode(preg_replace("/^http:\/\/(.*)$/", 'https://$1', $urlNoHash)));

		if (strcmp($actualhashHttp, $expectedHash) == 0 || strcmp($actualhashHttps, $expectedHash) == 0 ) {
			return;
		}
		
		throw new invalidKnownUserHashException('The hash of the request is invalid');
	}	
}

KnownUserFactory::reset(true);

?>
