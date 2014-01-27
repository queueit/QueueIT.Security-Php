<?php namespace QueueIT\Security;
require_once('IValidateResultRepository.php');
require_once('AcceptedConfirmedResult.php');
require_once('Md5KnownUser.php');
require_once('Queue.php');
require_once('IQueue.php');

abstract class ValidateResultRepositoryBase implements IValidateResultRepository
{
	private $sessionQueueId = "QueueITAccepted-SDFrts345E-";

	protected function generateKey($customerId, $eventId)
	{
		return $this->sessionQueueId . $customerId . "-" . $eventId;
	}
}
?>