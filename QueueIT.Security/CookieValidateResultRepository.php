<?php namespace QueueIT\Security;
require_once('IValidateResultRepository.php');
require_once('ValidateResultRepositoryBase.php');
require_once('AcceptedConfirmedResult.php');
require_once('Md5KnownUser.php');
require_once('Queue.php');
require_once('IQueue.php');

class CookieValidateResultRepository extends ValidateResultRepositoryBase
{
	static function reset($loadConfiguration = false)
	{
		global $cookieDomain;
	
		$cookieDomain = null;
	
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
	
		if (isset($settings['cookieDomain']) && $settings['cookieDomain'] != null)
			$cookieDomain = $settings['cookieDomain'];
	}
	
	public function getValidationResult($queue)
	{
		$key = $this->generateKey($queue->getCustomerId(), $queue->getEventId());
		
		if (isset($_COOKIE[$key])) {
			try {
				$values = $_COOKIE[$key];
				
				$queueId = $values["QueueId"];
				$originalUrl = $values["OriginalUrl"];
				$placeInQueue = KnownUserFactory::decryptPlaceInQueue($values["PlaceInQueue"]);
				$redirectType = $values["RedirectType"];
				$timeStamp = $values["TimeStamp"];
				$actualHash = $values["Hash"];
				
				$expectedHash = $this->generateHash($queueId, $originalUrl, $placeInQueue, $redirectType, $timeStamp);
				
				if ($actualHash != $expectedHash)
					return null;

				$parsedTimeStamp = new \DateTime("now", new \DateTimeZone("UTC"));
				$parsedTimeStamp->setTimestamp(intval($timeStamp));
				
				return new AcceptedConfirmedResult(
						$queue, 
						new Md5KnownUser(
								$queueId, 
								$placeInQueue, 
								$parsedTimeStamp, 
								$queue->getCustomerId(), 
								$queue->getEventId(), 
								$redirectType, 
								$originalUrl), 
						false);
			}
			catch (InvalidKnownUserUrlException $e)
			{
				return null;
			}
			
		}

		return $result;

	}

	public function setValidationResult($queue, $validationResult)
	{	
		global $cookieDomain;
		
		if ($validationResult instanceof AcceptedConfirmedResult)
		{		
			$key = $this->generateKey($queue->getCustomerId(), $queue->getEventId());

			$queueId = (string)$validationResult->getKnownUser()->getQueueId();
			$originalUrl = $validationResult->getKnownUser()->getOriginalUrl();
			$placeInQueue = (string)$validationResult->getKnownUser()->getPlaceInQueue();
			$redirectType = (string)$validationResult->getKnownUser()->getRedirectType();
			$timeStamp = (string)$validationResult->getKnownUser()->getTimeStamp()->getTimestamp();
			
			setcookie($key . "[QueueId]", $queueId, null, null, $cookieDomain);
			setcookie($key . "[OriginalUrl]", $originalUrl, null, null, $cookieDomain);
			setcookie($key . "[PlaceInQueue]", KnownUserFactory::encryptPlaceInQueue($placeInQueue), null, null, $cookieDomain);
			setcookie($key . "[RedirectType]", $redirectType, null, null, $cookieDomain);
			setcookie($key . "[TimeStamp]", $timeStamp, null, null, $cookieDomain);
			setcookie($key . "[Hash]", $this->generateHash($queueId, $originalUrl, $placeInQueue, $redirectType, $timeStamp), null, null, $cookieDomain);		
			
		}
	}
	
	private function generateHash($queueId, $originalUrl, $placeInQueue, $redirectType, $timestamp)
	{
		return hash("sha256", $queueId . $originalUrl . $placeInQueue . $redirectType . $timestamp . KnownUserFactory::getSecretKey());
	}
}

CookieValidateResultRepository::reset(true);

?>
