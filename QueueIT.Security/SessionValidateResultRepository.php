<?php namespace QueueIT\Security;
require_once('IValidateResultRepository.php');
require_once('ValidateResultRepositoryBase.php');
require_once('SessionStateModel.php');
require_once('AcceptedConfirmedResult.php');
require_once('Md5KnownUser.php');
require_once('Queue.php');
require_once('IQueue.php');

class SessionValidateResultRepository extends ValidateResultRepositoryBase
{
	static function reset($loadConfiguration = false)
	{
		global $idleExpiration;
		global $extendValidity;

		$idleExpiration = 180;

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

		if (isset($settings['$idleExpiration']) && $settings['$idleExpiration'] != null)
			$idleExpiration = (int)$settings['$idleExpiration'];
		if (isset($settings['extendValidity']))
		  $extendValidity = $settings['extendValidity'] == 1 ? true : false;

	}

	public static function configure(
			$idleExpirationValue = null,
			$extendValidityValue = null)
	{
		global $idleExpiration;
		global $extendValidity;

		if ($idleExpirationValue != null)
			$idleExpiration = $idleExpirationValue;
		if ($extendValidityValue != null)
			$extendValidity = $extendValidityValue;
	}

	public function getValidationResult($queue)
	{
		$key = $this->generateKey($queue->getCustomerId(), $queue->getEventId());

		if (!isset($_SESSION[$key]))
			return null;

		$model = unserialize($_SESSION[$key]);

		if ($model->expiration != null && $model->expiration < time())
			return null;

		$result = new AcceptedConfirmedResult(
			$queue,
			new Md5KnownUser(
				$model->queueId,
				$model->placeInQueue,
				$model->timeStamp,
				$queue->getCustomerId(),
				$queue->getEventId(),
				$model->redirectType,
				$model->originalUrl),
			false);

		return $result;
	}

	public function setValidationResult($queue, $validationResult, $expirationTime = null)
	{
		global $idleExpiration;
		global $extendValidity;

		if ($validationResult instanceof AcceptedConfirmedResult)
		{
			if ($expirationTime != null)
				$expiration = $expirationTime;
			elseif ($validationResult->getKnownUser()->getRedirectType() == RedirectType::Idle)
				$expiration = time() + idleExpiration;
			elseif ($extendValidity == false)
				$expiration = time() + ini_get("session.gc_maxlifetime");
			else
				$expiration = null;

			$model = new SessionStateModel();
			$model->queueId = $validationResult->getKnownUser()->getQueueId();
			$model->originalUrl = $validationResult->getKnownUser()->getOriginalUrl();
			$model->timeStamp = $validationResult->getKnownUser()->getTimeStamp();
			$model->redirectType = $validationResult->getKnownUser()->getRedirectType();
			$model->placeInQueue = $validationResult->getKnownUser()->getPlaceInQueue();
			$model->expiration = $expiration;

			$key = $this->generateKey($queue->getCustomerId(), $queue->getEventId());
			$_SESSION[$key] = serialize($model);
		}
	}

	public function cancel($queue, $validationResult)
	{
		$key = $this->generateKey($queue->getCustomerId(), $queue->getEventId());
		$_SESSION[$key] = null;
	}
}

SessionValidateResultRepository::reset(true);
?>
