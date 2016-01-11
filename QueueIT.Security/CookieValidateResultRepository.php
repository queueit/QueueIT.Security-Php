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
		global $cookieExpiration;
		global $idleExpiration;
		global $extendValidity;

		$cookieDomain = null;
		$cookieExpiration = 1200;
		$idleExpiration = 180;
		$extendValidity = true;

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

		if (isset($settings['cookieExpiration']) && $settings['cookieExpiration'] != null)
			$cookieExpiration = (int)$settings['cookieExpiration'];
		if (isset($settings['idleExpiration']) && $settings['idleExpiration'] != null)
			$idleExpiration = (int)$settings['idleExpiration'];
		if (isset($settings['extendValidity']))
		  $extendValidity = $settings['extendValidity'] == 1 ? true : false;
	}

	public static function configure(
			$cookieDomainValue = null,
			$cookieExpirationValue = null,
			$idleExpirationValue = null,
			$extendValidityValue = null)
	{
		global $cookieDomain;
		global $cookieExpiration;
		global $idleExpiration;
		global $extendValidity;

		if ($cookieDomainValue != null)
			$cookieDomain = $cookieDomainValue;
		if ($cookieExpirationValue != null)
			$cookieExpiration = $cookieExpirationValue;
		if ($idleExpirationValue != null)
			$idleExpiration = $idleExpirationValue;
		if ($extendValidityValue != null)
			$extendValidity = $extendValidityValue;
	}

	public function getValidationResult($queue)
	{
		global $cookieExpiration;
		global $extendValidity;

		$key = $this->generateKey($queue->getCustomerId(), $queue->getEventId());

		if (!isset($_COOKIE[$key]))
			return null;
			
		try {
			$values = $_COOKIE[$key];

			$queueId = $values["QueueId"];
			$originalUrl = $values["OriginalUrl"];
			$placeInQueue = KnownUserFactory::decryptPlaceInQueue($values["PlaceInQueue"]);
			$redirectType = $values["RedirectType"];
			$timeStamp = $values["TimeStamp"];
			$actualHash = $values["Hash"];
			$expires = $values["Expires"];

			if (!is_numeric($expires))
				return null;

			$expirationTime = intval($expires);

			if ($expirationTime < time())
				return null;

			$expectedHash = $this->generateHash($queueId, $originalUrl, $placeInQueue, $redirectType, $timeStamp, $expirationTime);

			if ($actualHash != $expectedHash)
				return null;

			if ($extendValidity && $redirectType != RedirectType::Idle)
			{
				$newExpirationTime = time()+$cookieExpiration;
				$newHash = $this->generateHash($queueId, $originalUrl, $placeInQueue, $redirectType, $timeStamp, $newExpirationTime);
				$this->writeCookie($queue, $queueId, $originalUrl, $placeInQueue, $redirectType, $timeStamp, $newHash, $newExpirationTime);
			}

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

	public function setValidationResult($queue, $validationResult, $expirationTime = null)
	{
		global $cookieExpiration;
		global $idleExpiration;

		if ($validationResult instanceof AcceptedConfirmedResult)
		{
			$queueId = (string)$validationResult->getKnownUser()->getQueueId();
			$originalUrl = $validationResult->getKnownUser()->getOriginalUrl();
			$placeInQueue = (string)$validationResult->getKnownUser()->getPlaceInQueue();
			$redirectType = (string)$validationResult->getKnownUser()->getRedirectType();
			$timeStamp = (string)$validationResult->getKnownUser()->getTimeStamp()->getTimestamp();

			if ($expirationTime == null)
			{
				if ($redirectType == RedirectType::Idle)
					$expirationTime = time()+$idleExpiration;
				else
					$expirationTime = time()+$cookieExpiration;
			}

			$hash = $this->generateHash($queueId, $originalUrl, $placeInQueue, $redirectType, $timeStamp, $expirationTime);
			$this->writeCookie($queue, $queueId, $originalUrl, $placeInQueue, $redirectType, $timeStamp, $hash, $expirationTime);
		}
	}

	public function cancel($queue, $validationResult)
	{
		$this->setValidationResult($queue, $validationResult, time()-86400);
	}

	private function writeCookie($queue, $queueId, $originalUrl, $placeInQueue, $redirectType, $timeStamp, $hash, $expirationTime)
	{
		global $cookieDomain;

		$expires = $expirationTime;

		$key = $this->generateKey($queue->getCustomerId(), $queue->getEventId());

		setcookie($key . "[QueueId]", $queueId, $expires, "/", $cookieDomain, false, true);
		setcookie($key . "[OriginalUrl]", $originalUrl, $expires, "/", $cookieDomain, false, true);
		setcookie($key . "[PlaceInQueue]", KnownUserFactory::encryptPlaceInQueue($placeInQueue), $expires, "/", $cookieDomain, false, true);
		setcookie($key . "[RedirectType]", $redirectType, $expires, "/", $cookieDomain, false, true);
		setcookie($key . "[TimeStamp]", $timeStamp, $expires, "/", $cookieDomain, false, true);
		setcookie($key . "[Hash]", $hash, $expires, "/", $cookieDomain, false, true);
		setcookie($key . "[Expires]", $expirationTime, $expires, "/", $cookieDomain, false, true);
	}

	private function generateHash($queueId, $originalUrl, $placeInQueue, $redirectType, $timestamp, $expirationTime)
	{
		if ($placeInQueue == null)
			$placeInQueue = 0;
		return hash("sha256",
				$queueId .
				$originalUrl .
				$placeInQueue .
				$redirectType .
				$timestamp .
				$expirationTime .
				KnownUserFactory::getSecretKey());
	}
}

CookieValidateResultRepository::reset(true);

?>
